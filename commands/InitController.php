<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use app\models\FileSystem as FS;
use app\managers\Folder as FolderManager;

/**
 * Initialize Fota environment
 */
class InitController extends Controller {

    public function actionIndex() {
        foreach (['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f'] as $l) {
            $path = FS::buildThumbPath($l);
            if (!FS::isFileExists($path)) {
                FS::makeDir($path);
            } elseif (!FS::isDir($path)) {
                die('You shuld clean up ' . Yii::$app->params['thumbRealPath'] . ' directory first.' . PHP_EOL);
            }
        }

        $root = FolderManager::getRoot();
        if (is_null($root)) {
            FolderMAnager::createRoot(Yii::$app->params['rootFolderName']);
        } else {
            echo "There is root directory exists in database! May be You want use `clear` command?\n";
            return 1;
        }

        echo "Done.\n";
    }
}