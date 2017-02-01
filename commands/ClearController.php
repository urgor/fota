<?php

namespace app\commands;

use yii\console\Controller;
use app\models\FileSystem as FS;
use app\models\Folders;
use app\models\FolderProperty;
use app\models\Files;
use app\models\FileInfo;
use app\models\Albums;
use app\models\AlbumFiles;

/**
 * Delete all thumb files and database records
 */
class ClearController extends Controller {

    public $realy_clear = false;

    public function options($actionId) {
        return ['realy_clear'];
    }

    public function actionIndex() {
        foreach (['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f'] as $l) {
            $path = FS::buildThumbPath($l);
            if (FS::isFileExists($path)) {
                echo "rmdir('$path');\n";
                if ($this->realy_clear) {
                    FS::delTree($path);
                }
            }
        }
        
        echo "Delete all folders\n";
        if ($this->realy_clear) {
            Folders::deleteAll();
        }
        
        echo "Delete all folder properties\n";
        if ($this->realy_clear) {
            FolderProperty::deleteAll();
        }
        
        echo "Delete all files\n";
        if ($this->realy_clear) {
            Files::deleteAll();
        }
        
        echo "Delete all file info\n";
        if ($this->realy_clear) {
            FileInfo::deleteAll();
        }
        
        echo "Delete all albums\n";
        if ($this->realy_clear) {
            AlbumFiles::deleteAll();
            Albums::deleteAll();
        }
        if (!$this->realy_clear) {
            echo "If You realy wish to do this all, then use --realy_clear flag.\n";
        }
    }
}