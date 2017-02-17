<?php

namespace app\workers;

use Yii;

class FileSystem  {

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

    public static function rmdir($dirname)
    {
        return rmdir($dirname);
    }

    public static function rename($oldName, $newName)
    {
        return rename ($oldName, $newName);
    }

    public static function unlink($fileName)
    {
        return unlink($fileName);
    }

    public static function mkdir($name, $mode, $recursive)
    {
        return mkdir($name, $mode, $recursive);
    }

    public static function symlink($target, $link)
    {
        return symlink($target, $link);
    }

    public static function chdir($dir)
    {
        return chdir($dir);
    }

    public static function filesize($file)
    {
        return filesize($file);
    }

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

    public static function buildPath($dirs) {
        return Yii::$app->params['sourceFolderPath'] . self::implodeDirs($dirs);
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
            $name = self::implodeDirs([$dir, $file]);
            (is_dir($name)) ? self::delTree($name) : self::unlink($name);
        }
        return self::rmdir($dir);
    }

    /**
     * Replace dangerous symbols to create safe file name
     *
     * @param string $name
     * @return string
     */
    public static function normalizeFilename($name)
    {
        return preg_replace('/[\\`:\?\*\/]+/', '_', $name);
    }

    /**
     * Create unique temporary directory and return its name
     *
     * @param string $name
     * @return string
     */
    public static function createTemporaryDir($name)
    {
		$contentDir = Yii::$app->params['temporaryDirPrefix'] . mt_rand(1000, 9999999);
		self::mkdir(self::implodeDirs([$contentDir, $name]), 0777, true);

        return $contentDir;
    }

}
