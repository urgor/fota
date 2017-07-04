<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use app\models\AlbumFiles;

class Albums extends ActiveRecord
{

    static $tableName = 'albums';

    static $primaryKey = 'album_id';

    public function defaultScope()
    {
        return array(
            'order' => 'name ASC',
        );
    }

    public static function create($name)
    {
        $album = new Albums;
        $album->name = $name;
        if (!$album->save()) {
            throw new \Exception("Error creating album", 1);
        }

        return $album;
    }

    public static function getAll()
    {
        return self::find()->all();
    }

    public static function getById(int $albumId)
    {
        $album = self::findOne($albumId);
        if (!$album) {
            throw new \Exception("No such album", 1);
        }
        return $album;
    }

    public static function deleteById($albumId)
    {
        self::deleteAll(['album_id' => $albumId]);
    }

}
