<?php

namespace app\workers;

use Yii;
use app\managers\File as FileManager;
use app\models\Thumbnail as Thumb;
use app\models\FileInfo;
use app\workers\FileSystem as FS;
use app\workers\OperationSystem;

class Scaner
{
    /**
     * @var \app\models\RuntimeParameters
     */
    protected $params;

    /**
     * @var \app\workers\OperationSystem
     */
    protected $os;

    public function __construct(\app\models\RuntimeParameters $properties) {
        $this->params = $properties;
        $this->os = new OperationSystem;
    }

    /**
     * Make main scan
     *
     * @param  str $dir Relative directory path
     * @param  \app\models\Folders $parentFolder [description]
     * @return [type]                           [description]
     */
    public function scanMain(string $dir, \app\models\Folders $parentFolder) {
        $entitiesHere = 0;
        foreach(FS::readDir(FS::buildPath([$dir])) as $entry) {
            if (Yii::$app->params['preventScanDirBeginsFrom'] == $entry[0]) {
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

    private function processDir(string $dir, string $entry, \app\models\Folders $parentFolder)
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

    private function processFile(string $dir, string $entry, \app\models\Folders $parentFolder)
    {
        $fsFullPath = FS::buildPath([$dir, $entry]);
        $mdPath = md5(FS::implodeDirs([$dir, $entry]));

        $this->os->execute('md5sum --binary %s', [$fsFullPath]);
        if (0 != $this->os->getReturnVar()) {
            throw new \Exception('Error calculating md5 sum', 1);
        }
        $mdContent = substr($this->os->getArrayOutput()[0], 0, 32);

        $ext = pathinfo($entry, PATHINFO_EXTENSION);
        if (!in_array(strtolower($ext), ['jpg', 'tiff', 'tif', 'png']))
        {
            return 0;
        }

        $findPath = FileManager::findOneByPath($mdPath);
        $findContent = FileManager::findOneByContent($mdContent);

        switch (((is_null($findPath) ? 0 : 1) << 1 ) | (is_null($findContent) ? 0 : 1)) {
            case 0b00:
                // simply new file
                $this->create($parentFolder, $entry, $fsFullPath, $mdPath, $mdContent);
                break;
            case 0b01:
                // -path +content => content set cur path
                $this->updatePath($findContent, $parentFolder, $entry, $fsFullPath, $mdPath);
                break;
            case 0b10;
                // +path -content => path set current content
                $this->updateContent($findPath, $fsFullPath, $mdContent, $mdPath, $entry);
                break;
            case 0b11;
                // +path +content => processed
                $this->updateProcessed($findContent, $fsFullPath, $mdPath);
                break;
        }
        return 1;
    }

    protected function create($parentFolder, $entry, $path, $mdPath, $mdContent)
    {
        if (0 != Thumb::create($path, FS::buildThumbPathFile($mdPath))) {
            echo "File $entry Cant create thumbnail; skip it!!!\n";
            return 0;
        }
        $file = FileManager::create($parentFolder->getAttribute('folder_id'), $entry, $mdPath, $mdContent);
        FileInfo::fill($file->file_id, $path, FS::buildThumbPathFile($mdPath));
        echo "File $entry Saved new\n";
    }

    protected function updateContent($findPath, $path, $mdContent, $mdPath, $entry)
    {
        FileManager::updateContent($findPath, $mdContent);
        Thumb::create($path, FS::buildThumbPathFile($mdPath));
        if ($this->params['updateInfo']) {
            FileInfo::fill($findPath->file_id, $path, FS::buildThumbPathFile($mdPath));
        }
        echo "File $entry Updated\n";
    }

    protected function updateProcessed($findContent, $path, $mdPath)
    {
        FileManager::updateProcessed($findContent, 1);
        if ($this->params['rethumb']) {
            Thumb::create($path, FS::buildThumbPathFile($mdPath));
        }
        if ($this->params['updateInfo']) {
            FileInfo::fill($findContent->file_id, $path, FS::buildThumbPathFile($mdPath));
        }
        echo "\r";
    }

    protected function updatePath($findContent, $parentFolder, $entry, $path, $mdPath)
    {
        $file = FS::buildThumbPathFile($findContent->md_path);
        if (FS::isFileExists($file) && FS::isFileWritable($file)) {
            FS::rename(FS::buildThumbPathFile($findContent->md_path), FS::buildThumbPathFile($mdPath));
        } else {
            Thumb::create($path, FS::buildThumbPathFile($mdPath));
        }

        FileManager::updatePath($findContent, $parentFolder->getAttribute('folder_id'), $mdPath);

        if ($this->params['rethumb']) {
            Thumb::create($path, FS::buildThumbPathFile($mdPath));
        }
        if ($this->params['updateInfo']) {
            FileInfo::fill($findContent->file_id, $path, FS::buildThumbPathFile($mdPath));
        }
        echo "File $entry Moved\n";
    }
}