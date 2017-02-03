<?php

namespace app\models;

use Yii;
use yii\base\Model;
use app\models\FileSystem as  FS;

class Thumbnail extends Model {

    /**
     * Create thumbnail of specified path
     * 
     * @param str $path Full path to original file
     * @param str $destPath
     * @return type
     */
    public static function create($path, $destPath) {
        $output = [];
        $returnVar = 0;
        exec(
                'convert ' . FS::escapePath($path) . ' -auto-orient '
                . '-thumbnail ' . Yii::$app->params['thumbnail']['big']['maxWidth'] . 'x' . Yii::$app->params['thumbnail']['big']['maxHeight']
                . '\\> -quality ' . Yii::$app->params['thumbnail']['big']['quality'] . ' ' . FS::escapePath($destPath)
                , $output, $returnVar
        );
        return $returnVar;
    }

}
