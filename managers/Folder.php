<?php

namespace app\managers;

use app\models\FolderProperty;
use app\models\Folders;

class Folder
{

    public static function getById($folderId)
    {
        $folder = Folders::findOne($folderId);
        if(empty($folder)) {
            throw new Exception('There is no folder');
        }

        return $folder;
    }

    public static function getRoot()
    {
//        return self::find()->roots()->all();
        return Folders::findOne(['level' => 0]);
    }

    public static function createRoot($name)
    {
        $root = new Folders;
        $root->name = $name;
        $root->makeRoot();

        return $root;
    }

    public static function getFolderByAccesHash($accessHash)
    {
        $prop = FolderProperty::find()->where(['access_hash' => $accessHash])->one();
        if (empty($prop)) {
            throw new \Exception('Folder not found');
        }
        $folder = Folders::getById($prop->folder_id);

        return $folder;
    }

    public static function checkAccessByHash($parent, $accessHash)
    {
        $hashedFolder = self::getFolderByAccesHash($accessHash);
        return $parent->isChildOf($hashedFolder) || $parent->folder_id == $hashedFolder->folder_id;
    }

    public static function getSubFolders(\yii\base\Model $folder)
    {
        return $folder->children(1)->orderBy('name')->all();
    }

    public static function findEmpty()
    {
        return Folders::findBySql('select f.*, count(file_id) cnt
            from `' . Folders::tableName() . '` f
            left join `files` using (folder_id)
            where `left`+1 = `right`
            group by f.folder_id
            having cnt = 0
        ')->all();
    }

}
