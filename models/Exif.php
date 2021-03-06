<?php

namespace app\models;

use yii\base\Model;
use app\workers\FileSystem as FS;
use app\workers\OperationSystem;

class Exif extends Model {

    /**
     *
     * @param type $path Path to original file
     * @param type $thumbnail Path to thumbnail
     * @return array
     */
    public static function getInfo($path, $thumbnail) {
        $os = new OperationSystem;
        $os->execute('exiftool -j %s', [$path]);
        $data = $os->getStringOutput();

        if (0 != $os->getReturnVar() || empty($data)) {
            throw new \Exception('No exif');
        }
        $data = json_decode($data, true);
        if (!$data) {
            throw new \Exception('Cant decode exif');
        }
        $data = reset($data);

        $timestamp = 0;
        if (!empty($data['DateTimeOriginal'])) {
            $timestamp = strtotime($data['DateTimeOriginal']);
        } elseif (!empty($data['FileModifyDate'])) {
            $timestamp = strtotime($data['FileModifyDate']);
        }

        $info = [];
        if (!empty($timestamp)) {
            $info['exif_create_timestamp'] = $timestamp;
        }
        if (!empty($data['Subject'])) {
            if (is_array($data['Subject'])) {
                $info['exif_keywords'] = implode(', ', $data['Subject']);
            } else {
                $info['exif_keywords'] = $data['Subject'];
            }
        }

        list($info['width'], $info['height']) = getimagesize($thumbnail);

        return [$data, $info];
    }

}
