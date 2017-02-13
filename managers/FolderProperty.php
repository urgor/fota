<?php

namespace app\managers;

use app\models\FolderProperty as Property;
use app\models\Folders;

class FolderProperty
{

    public static function getByFolderId($folderId)
    {
        return Property::findOne($folderId);
    }

    public static function createAccessHash($folderId, $accessHash)
    {
        $prop = new Property();
        $prop->folder_id = $folderId;
        $prop->access_hash = $accessHash;
        $prop->save();

        return $prop;
    }

}