<?php

namespace app\managers;

use app\models\FolderProperty;
use app\models\Folders;

class Folder
{

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

}
