<?php

namespace app\models;

use Yii;
use yii\base\Model;

class FileSystem extends Model {

    public static function readDir($dir) {
        $d = dir(Yii::$app->params['sourceFolderPath'] . $dir);
        while ($sub = $d->read()) {
            if ('.' == $sub[0]) {
                continue;
            }
            yield $sub;
        }
        $d->close();
    }

    public static function isDir($path) {
        return is_dir($path);
    }

    public static function makeDir($path, int $mode = 0777, bool $recursive = false) {
        return mkdir($path, $mode, $recursive);
    }

    public static function isFile($path) {
        return is_file($path);
    }

    public static function isFileExists($path) {
        return file_exists($path);
    }

    public static function isFileWritable($path) {
        return is_writable($path);
    }

    public static function buildPath($dirs) {
        return Yii::$app->params['sourceFolderPath'] . implode(DIRECTORY_SEPARATOR, $dirs);
    }

    public static function implodeDirs($dirs) {
        return implode(DIRECTORY_SEPARATOR, $dirs);
    }

    public static function buildThumbPath($name) {
        return Yii::$app->params['thumbRealPath'] . $name[0];
    }

    public static function buildThumbPathFile($name) {
        return self::buildThumbPath($name) . DIRECTORY_SEPARATOR . $name . '.jpg';
    }

    public static function delTree($dir) {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }
    
    public static function escapePath($path) {
        return preg_replace('/(["\' \(\);])/', '\\\$1', $path);
    }

}
