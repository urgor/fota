<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use app\models\FileSystem as FS;

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

        $root = \app\models\Folders::findOne(['level' => 0]);
        if (is_null($root)) {
            $root = new \app\models\Folders;
            $root->name = Yii::$app->params['rootFolderName'];
            $root->makeRoot();
        } else {
            echo "There is root directory exists in database! May be You want use `clear` command?\n";
            return 1;
        }
        
        echo "Done.\n";
    }
}