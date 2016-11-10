<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Albums extends ActiveRecord {

	static $tableName = 'albums';

	static $primaryKey = 'album_id';

	public function defaultScope() {
		return array(
			'order' => 'name ASC',
		);
	}

}