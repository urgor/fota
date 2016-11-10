<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Files extends ActiveRecord {

	public function defaultScope() {
		return array(
			'order' => 'original_name ASC',
		);
	}

}