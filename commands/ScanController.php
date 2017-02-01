<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use app\models\FileSystem as FS;

class ScanController extends Controller {

    public $update_info = false;

    public function options($actionId) {
        return ['update_info'];
    }

    public function actionIndex() {
        \app\models\Files::updateAll(['processed' => 0], []);
        $root = \app\models\Folders::findOne(['level' => 0]);
        if (is_null($root)) {
            echo "There is no root directory in database/. You should use `init` command before scan/\n";
            return 1;
        }
        $this->scanMain('', $root);
        $this->deleteEmpty();
    }

    /**
     * Make main scan
     * 
     * @param  str $dir Relative directory path
     * @param  \app\models\Folders $parentFolder [description]
     * @return [type]                           [description]
     */
    private function scanMain($dir, \app\models\Folders $parentFolder) {
        $entitiesHere = 0;
        foreach(FS::readDir($dir) as $entry) {

            if (Yii::$app->params['preventSccanDirBeginsFrom'] == $entry[1]){
                continue;
            }

            $path = FS::buildPath([$dir, $entry]);
            if (FS::isDir($path)) {
                $entitiesHere += $this->processDir($dir, $entry, $parentFolder);
            } elseif (FS::isFile($path)) {
                $entitiesHere += $this->processFile($dir, $entry, $parentFolder);
            }
        }
        return $entitiesHere;
    }

    private function processDir($dir, $entry, \app\models\Folders $parentFolder)
    {
        echo "Folder $entry\n";
        $folder = false;
        foreach ($parentFolder->children(1)->all() as $child) {
            if ($child->name === $entry) {
                $folder = $child;
                break;
            }
        }
        if (!$folder) {
            $folder = new \app\models\Folders;
            $folder->name = $entry;
            $folder->appendTo($parentFolder);
        }
        $subItems = $this->scanMain(FS::implodeDirs([$dir, $entry]), $folder);
        if (0 == $subItems) {
            $folder->delete();
            echo "Folder $entry deleted\n";
        }
        
        return $subItems;
    }
    
    private function processFile($dir, $entry, \app\models\Folders $parentFolder)
    {
        $path = FS::buildPath([$dir, $entry]);
        $mdPath = md5($dir . DIRECTORY_SEPARATOR . $entry);

        // $oldMdContent = md5(file_get_contents($path));
        $output = [];
        $returnVar = 0;
        exec('md5sum --binary ' . FS::escapePath($path), $output, $returnVar);
        if (0 != $returnVar) {
            throw new \Exception('Error calculating md5 sum', 1);
        }
        $mdContent = substr($output[0], 0, 32);

        $ext = pathinfo($entry, PATHINFO_EXTENSION);
        if (!in_array(strtolower($ext), ['jpg', 'tiff', 'tif', 'png']))
        {
            return 0;
        }

        $findPath = \app\models\Files::find()->where(['md_path' => $mdPath])->one();
        $findContent = \app\models\Files::find()->where(['md_content' => $mdContent])->one();

        if (is_null($findPath)) {
            if (is_null($findContent)) {
                // simply new file
                if (0 != $this->makeThumb($path, $mdPath)) {
                    echo "File $entry Cant create thumbnail; skip it!!!\n";
                    return 0;
                }
                $file = new \app\models\Files();
                $file->folder_id = $parentFolder->getAttribute('folder_id');
                $file->original_name = $entry;
                $file->md_path = $mdPath;
                $file->md_content = $mdContent;
                $file->processed = 1;
                $file->save();
                $this->fillInfo($file->file_id, $path, self::makeThumbPath($mdPath));
                echo "File $entry Saved new\n";
            } else {
                // -path +content => content set cur path
                $file = self::makeThumbPath($findContent->md_path);
                if (FS::isFileExists($file) && FS::isFileWritable($file)) {
                    rename(self::makeThumbPath($findContent->md_path), self::makeThumbPath($mdPath));
                } else {
                    $this->makeThumb($path, $mdPath);
                }
                // if ($this->params['rethumb']['val']) $this->makeThumb($path, $mdPath); // option rethumb

                $findContent->folder_id = $parentFolder->getAttribute('folder_id');
                $findContent->md_path = $mdPath;
                $findContent->processed = 1;
                $findContent->save();
                if ($this->update_info) {
                    $this->fillInfo($findContent->file_id, $path, self::makeThumbPath($mdPath));
                }
                echo "File $entry Moved\n";
            }
        } else {
            if (is_null($findContent)) {
                // +path -content => path set current content
                $findPath->md_content = $mdContent;
                $findPath->processed = 1;
                $findPath->save();
                $this->makeThumb($path, $mdPath);
                if ($this->update_info) {
                    $this->fillInfo($findPath->file_id, $path, self::makeThumbPath($mdPath));
                }
                echo "File $entry Updated\n";
            } else {
                // +path +content => processed
                $findContent->processed = 1;
                $findContent->save();
                // if ($this->params['rethumb']['val']) $this->makeThumb($path, $mdPath); // option rethumb
                if ($this->update_info) {
                    $this->fillInfo($findContent->file_id, $path, self::makeThumbPath($mdPath));
                }
                echo "\r";
            }
        }
        return 1;
    }
    
    private static function makeThumbPath($name) {
        return Yii::$app->params['thumbRealPath'] . $name[0] . DIRECTORY_SEPARATOR . $name . '.jpg';
    }

    private function makeThumb($path, $mdPath) {
        exec(
                'convert ' . FS::escapePath($path) . ' -auto-orient '
                . '-thumbnail ' . Yii::$app->params['thumbnail']['big']['maxWidth'] . 'x' . Yii::$app->params['thumbnail']['big']['maxHeight']
                . '\\> -quality ' . Yii::$app->params['thumbnail']['big']['quality'] . ' ' . self::makeThumbPath($mdPath)
                , $output, $returnVar
        );
        return $returnVar;
    }

    /**
     * Достаёт необходимые EXIF теги из файла и запускае сохранение
     *
     * @param $file_id int ИД файла
     * @param $path str Путь и имя к файлу в ФС
     * @return bool
     */
    private function fillInfo($fileId, $path, $thumbnail) {
        $data = shell_exec('exiftool -j ' . FS::escapePath($path));
        if (!$data)
            return false;
        $data = json_decode($data, true);
        if (!$data)
            return false;
        $data = reset($data);

        if (!empty($data['DateTimeOriginal']))
            $timestamp = strtotime($data['DateTimeOriginal']);
        elseif (!empty($data['FileModifyDate']))
            $timestamp = strtotime($data['FileModifyDate']);
        else
            return false;

        $info = [];
        if (!empty($timestamp))
            $info['exif_create_timestamp'] = $timestamp;
        if (!empty($data['Subject'])) {
            if (is_array($data['Subject']))
                $info['exif_keywords'] = implode(', ', $data['Subject']);
            else
                $info['exif_keywords'] = $data['Subject'];
        }

        list($info['width'], $info['height']) = getimagesize($thumbnail);

        foreach ([
    // 'ExifImageWidth' => 'width',
    // 'ExifImageHeight' => 'height',
    // 'ImageWidth' => 'width', // upper -- higher priority
    // 'ImageHeight' => 'height',
    'Description' => 'exif_description',
    'Title' => 'exif_title',
        ] as $exif => $myKey) {
            if (!empty($data[$exif]) && empty($info[$myKey]))
                $info[$myKey] = $data[$exif];
        }

        \app\models\FileInfo::writeInfo($fileId, $info);

        return true;
    }

    /**
     * Вставляет указанную пару ключ-значение в file_info, обновляя при необходимости
     *
     * @param $file_id int ИД файла
     * @param $key str Ключ
     * @param $value str Значение
     * @return bool
     */
    private function writeInfo($file_id, $key, $value) {
        $FileInfo = \app\models\FileInfo::findOne(['file_id' => $file_id, 'key' => $key]);
        if (!$FileInfo) {
            $FileInfo = new \app\models\FileInfo;
            $FileInfo->file_id = $file_id;
            $FileInfo->key = $key;
        }
        $FileInfo->value = $value;
        return (bool) $FileInfo->save();
    }

    private function deleteEmpty() {
        $rmFiles = \app\models\Files::findAll(['processed' => 0]);
        if (0 === count($rmFiles)) {
            echo "No thumbs to delete.\n";
        } else {
            echo "Deleting thumbs:\n";
            foreach ($rmFiles as $file) {
                $fName = self::makeThumbPath($file->md_path);
                echo $fName . ' ';
                if (file_exists($fName)) {
                    if (!is_writable($fName)) {
                        echo "not writeable!\n";
                        continue;
                    }
                    if (!unlink($fName)) {
                        echo 'cant delete file fron FS!';
                        continue;
                    }
                }
                \app\models\AlbumFiles::deleteAll(['file_id' => $file->file_id]);
                $file->delete();
                echo $file->file_id . ' deleted';

                echo PHP_EOL;
            }
        }

        while ($folders = \app\models\Folders::findEmpty()) {
            foreach ($folders as $folder) {
                echo "Delete empty folder " . $folder->name . PHP_EOL;
                $folder->delete();
            }
        }
    }

}
