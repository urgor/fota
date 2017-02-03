<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use app\models\FileInfo;
use app\models\Files;
use app\models\FileSystem as FS;
use app\models\Thumbnail as Thumb;
use app\models\AlbumFiles;

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
        $this->deleteEmptyFolders();
        $this->deleteUnprocessedFiles();
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

            if (Yii::$app->params['preventScanDirBeginsFrom'] == $entry[1]){
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

        $findPath = Files::findOneByPath($mdPath);
        $findContent = Files::findOneByContent($mdContent);

        switch (((is_null($findPath) ? 0 : 1) << 1 ) | (is_null($findContent) ? 0 : 1)) {
            case 0b00:
                // simply new file
                if (0 != Thumb::create($path, FS::buildThumbPathFile($mdPath))) {
                    echo "File $entry Cant create thumbnail; skip it!!!\n";
                    return 0;
                }
                $file = Files::create($parentFolder->getAttribute('folder_id'), $entry, $mdPath, $mdContent);
                FileInfo::fill($file->file_id, $path, FS::buildThumbPathFile($mdPath));
                echo "File $entry Saved new\n";
                break;
            case 0b01:
                // -path +content => content set cur path
                $file = FS::buildThumbPathFile($findContent->md_path);
                if (FS::isFileExists($file) && FS::isFileWritable($file)) {
                    FS::rename(FS::buildThumbPathFile($findContent->md_path), FS::buildThumbPathFile($mdPath));
                } else {
                    Thumb::create($path, FS::buildThumbPathFile($mdPath));
                }
                // if ($this->params['rethumb']['val']) Thumb::create($path, FS::buildThumbPathFile($mdPath)); // option rethumb

                Files::updatePath($findContent, $parentFolder->getAttribute('folder_id'), $mdPath);
                
                if ($this->update_info) {
                    FileInfo::fill($findContent->file_id, $path, FS::buildThumbPathFile($mdPath));
                }
                echo "File $entry Moved\n";
                break;
            case 0b10;
                // +path -content => path set current content
                Files::updateContent($findPath, $mdContent);
                Thumb::create($path, FS::buildThumbPathFile($mdPath));
                if ($this->update_info) {
                    FileInfo::fill($findPath->file_id, $path, FS::buildThumbPathFile($mdPath));
                }
                echo "File $entry Updated\n";
                break;
            case 0b11;
                // +path +content => processed
                Files::updateProcessed($findContent, 1);

                // if ($this->params['rethumb']['val']) Thumb::create($path, FS::buildThumbPathFile($mdPath)); // option rethumb
                if ($this->update_info) {
                    FileInfo::fill($findContent->file_id, $path, FS::buildThumbPathFile($mdPath));
                }
                echo "\r";
                break;
        }
        return 1;
    }

    private function deleteUnprocessedFiles() {
        $rmFiles = Files::findAll(['processed' => 0]);
        if (0 === count($rmFiles)) {
            echo "No thumbs to delete.\n";
            return;
        }
        echo "Deleting thumbs:\n";
        foreach ($rmFiles as $file) {
            $fileName = FS::buildThumbPathFile($file->md_path);
            echo $fileName . ' ';
            if (!FS::unlink($fileName)) {
                echo 'cant delete thumb.';
            }
                
            AlbumFiles::deleteFromAllAlbums($file->file_id);
            $file->delete();
            echo $file->file_id . ' deleted';

            echo PHP_EOL;
        }
    }
    
    private function deleteEmptyFolders()
    {
        while ($folders = \app\models\Folders::findEmpty()) {
            foreach ($folders as $folder) {
                echo "Delete empty folder " . $folder->name . PHP_EOL;
                $folder->delete();
            }
        }
    }

}
