<?php

namespace app\models;

use Yii;
use yii\base\Model;
use app\workers\FileSystem as  FS;
use app\workers\OperationSystem;

class Thumbnail extends Model {

    /**
     * Create thumbnail of specified path
     *
     * @param str $path Full path to original file
     * @param str $destPath
     * @return type
     */
    public static function create($path, $destPath) {
        $os = new OperationSystem();
        $os->execute(
                'convert %s -auto-orient '
                . '-thumbnail ' . Yii::$app->params['thumbnail']['big']['maxWidth'] . 'x' . Yii::$app->params['thumbnail']['big']['maxHeight']
                . '\\> -quality ' . Yii::$app->params['thumbnail']['big']['quality'] . ' %s',
                [
                    $path,
                    $destPath,
                ]
        );
        return $os->getReturnVar();
    }

}
