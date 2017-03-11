<?php

namespace app\workers;

use app\managers\File as FileManager;
use app\managers\Folder as FolderManager;
use app\workers\FileSystem as FS;

class PathAssembler
{
    private $files;
    private $fileInfo;
    private $filesList = [];
    private $fullPathes = [];

    public function __construct(array $files)
    {
        $this->files = $files;
    }

    public function getFullPath()
    {
        return $this->fullPathes[$this->fileInfo->folder_id];
    }

    public function getOriginalName()
    {
        return $this->fileInfo->original_name;
    }

    public function prepareFiles()
    {
		foreach ($this->files as $file) {
			$this->fileInfo = FileManager::getById($file['file_id']);
			self::createFullPath($this->fileInfo->folder_id, $this->fullPathes);
			$this->filesList[] = $this->fullPathes[$this->fileInfo->folder_id].$this->fileInfo->original_name;

            yield $this;
		}
    }

    private static function createFullPath($folderId, &$fullPathes)
    {
		if (!array_key_exists($folderId, $fullPathes)) {
			$folder = FolderManager::getById($folderId);
			if (!$folder->isRoot()) {
                $fullPathes[$folderId] = [$folder->name];
            }

			foreach (array_reverse($folder->parents()->all()) as $sf) {
				if (!$sf->isRoot())	{
                    array_unshift($fullPathes[$folderId], $sf->name);
                }
            }

			$fullPathes[$folderId] = isset($fullPathes[$folderId])
				? FS::implodeDirs($fullPathes[$folderId])
				: '';
		}
	}
}