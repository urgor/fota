<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class AlbumFiles extends ActiveRecord
{

    static $tableName = 'album_files';
    static $primaryKey = 'album_files_id';

    public function defaultScope()
    {
        return array(
            'order' => 'file_id ASC',
        );
    }

    public function getFileInfo()
    {
        return $this->hasMany(FileInfo::className(), ['file_id' => 'file_id']);
    }

    public function getFile()
    {
        return $this->hasOne(Files::className(), ['file_id' => 'file_id']);
    }

    public static function deleteFromAllAlbums($fileId)
    {
        static::deleteAll(['file_id' => $fileId]);
    }

    public static function deleteByAlbum($albumId)
    {
        AlbumFiles::deleteAll(['album_id' => $albumId]);
    }

    public static function deleteFilesFromAlbum($albumId, $items)
    {
        self::deleteAll(['and', ['album_id' => $albumId], ['file_id' => $items]]);
    }
}
