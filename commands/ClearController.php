<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use app\workers\FileSystem as FS;
use app\models\Folders;
use app\models\FolderProperty;
use app\models\FileInfo;
use app\models\Albums;
use app\models\AlbumFiles;
use app\managers\File as FileManager;

/**
 * Delete all thumb files and database records
 */
class ClearController extends Controller {

    public $realyClear = false;

    public function options($actionId) {
        return ['realyClear'];
    }

    public function actionIndex() {
        echo "Delete thumbnails\n";
        foreach (['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f'] as $l) {
            $path = FS::buildThumbPath($l);
            if (FS::isFileExists($path)) {
                echo "rmdir('$path');\n";
                if ($this->realy_clear) {
                    FS::delTree($path);
                }
            }
        }

        echo "Delete web symlink to thumnail path\n";
        if ($this->realyClear) {
            FS::unlink(trim(FS::implodeDirs(['web', Yii::$app->params['thumbsPath']]), '/'));
        }

        echo "Delete all folders\n";
        if ($this->realyClear) {
            Folders::deleteAll();
        }

        echo "Delete all folder properties\n";
        if ($this->realyClear) {
            FolderProperty::deleteAll();
        }

        echo "Delete all files\n";
        if ($this->realyClear) {
            FileManager::deleteAll();
        }

        echo "Delete all file info\n";
        if ($this->realyClear) {
            FileInfo::deleteAll();
        }

        echo "Delete all albums\n";
        if ($this->realyClear) {
            AlbumFiles::deleteAll();
            Albums::deleteAll();
        }
        if (!$this->realyClear) {
            echo "If You realy wish to do this all, then use --realyClear flag.\n";
        }
    }
}