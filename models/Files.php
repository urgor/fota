<?php

namespace app\models;

use yii\db\ActiveRecord;
use app\models\FileInfo;

class Files extends ActiveRecord {

    public function defaultScope() {
        return [
            'order' => 'original_name ASC',
        ];
    }

}
