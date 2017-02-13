<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class ALbumFiles extends ActiveRecord
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

    public static function getByAlbum($albumId)
    {
        $ret = [];
        foreach (self::find()->where(['album_id' => $albumId])->all() as $albumFile) {
            $ret[] = $albumFile->getFile()->asArray()->one();
        }

        return $ret;
    }

    /**
     * Add file to album
     * @param int $albumId
     * @param int $fileId
     * @return \app\models\AlbumFiles
     * @throws \Exception
     */
    public static function addFileToAlbum(int $albumId, int $fileId)
    {
        $albumFiles = new AlbumFiles;
        $albumFiles->album_id = $albumId;
        $albumFiles->file_id = $fileId;
        if (!$albumFiles->save()) {
            throw new \Exception("Error adding images", 1);
        }

        return $albumFiles;
    }

    public static function deleteFilesFromAlbum($albumId, $items)
    {
        self::deleteAll(['and', ['album_id' => $albumId], ['file_id' => $items]]);
    }
}
