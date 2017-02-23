<?php

namespace app\managers;

use app\models\Files;
use app\models\FileInfo;

class File
{

    /**
     * Set 'processed' flag to 0 for all files in $folder and all descendants
     * 
     * @param app\models\Folders $folder
     */
    public static function resetProcessed($folder)
    {
        $childrens = $folder->children()->all();
        if (empty($childrens)) {
            Files::updateAll(['processed' => 0], ['folder_id' => $folder->folder_id]);
        } else {
            $dirs = array_map(function($el){return $el->folder_id;}, $childrens);
            Files::updateAll(['processed' => 0], ['in', 'folder_id', $dirs]);
        }
    }

    public static function getUnprocessed($folderId = 0)
    {
        Files::findAll(['processed' => 0]);
    }

    public static function deleteAll()
    {
        Files::deleteAll();
    }

    public static function getByFolder($folderId)
    {
        $files = static::find()->where(['folder_id' => $folderId])->orderBy('original_name')->all();
        if (empty($files)) {
            throw new \Exception('No such folder');
        }

        return $files;
    }

    public static function getByFolderSpecial($folderId)
    {
        $data = [];
        foreach (Files::find()->where(['folder_id' => $folderId])->orderBy('original_name')->all() as $file) {
            $data[] = [
                'id' => $file->file_id,
                'thumb' => $file->md_path,
                'name' => $file->original_name,
                'info' => FileInfo::getByFile($file->file_id),
            ];
        }
        return $data;
    }

    public static function create($folderId, $name, $path, $content)
    {
        $file = new \app\models\Files();
        $file->folder_id = $folderId;
        $file->original_name = $name;
        $file->md_path = $path;
        $file->md_content = $content;
        $file->processed = 1;
        $file->save();
        return $file;
    }

    public static function findOneByPath($path)
    {
        return Files::find()->where(['md_path' => $path])->one();
    }

    public static function findOneByContent($content)
    {
        return Files::find()->where(['md_content' => $content])->one();
    }

    public static function getbyId($fileId)
    {
        $file = Files::findOne($fileId);
        if (empty($file)) {
            throw new Exception('There is no such file');
        }

        return $file;
    }

    public static function updatePath(Files $file, $folderId, $path)
    {
        $file->folder_id = $folderId;
        $file->md_path = $path;
        $file->processed = 1;
        return $file->save();
    }

    public static function updateContent(Files $file, $content)
    {
        $file->md_content = $content;
        $file->processed = 1;
        return $file->save();
    }

    public static function updateProcessed(Files $file, $processed)
    {
        $file->processed = $processed;
        return $file->save();
    }

}
