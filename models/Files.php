<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use app\models\FileInfo;

class Files extends ActiveRecord {

    public function defaultScope() {
        return [
            'order' => 'original_name ASC',
        ];
    }

    public static function getByFolder($folderId) {
        $data = [];
        foreach (static::find()->where(['folder_id' => $folderId])->orderBy('original_name')->all() as $file) {
            $data[] = [
                'id' => $file->file_id,
                'thumb' => $file->md_path,
                'name' => $file->original_name,
                'info' => FileInfo::getByFile($file->file_id),
            ];
        }
        return $data;
    }

    public static function create($folderId, $name, $path, $content) {
        $file = new \app\models\Files();
        $file->folder_id = $folderId;
        $file->original_name = $name;
        $file->md_path = $path;
        $file->md_content = $content;
        $file->processed = 1;
        $file->save();
        return $file;
    }

    public static function findOneByPath($path) {
        return self::find()->where(['md_path' => $path])->one();
    }

    public static function findOneByContent($content) {
        return self::find()->where(['md_content' => $content])->one();
    }

    public static function updatePath(Files $file, $folderId, $path) {
        $file->folder_id = $folderId;
        $file->md_path = $path;
        $file->processed = 1;
        return $file->save();
    }

    public static function updateContent(Files $file, $content) {
        $file->md_content = $content;
        $file->processed = 1;
        return $file->save();
    }

    public static function updateProcessed(Files $file, $processed) {
        $file->processed = $processed;
        return $file->save();
    }
    
    

}
