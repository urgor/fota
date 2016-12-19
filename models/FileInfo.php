<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class FileInfo extends ActiveRecord {

	static $tableName = 'file_info';

	public static function writeInfo($fileId, $data) {
		if (0 == count($data)) return false;

		foreach ($data as $key => &$val) {
			$val = '('.$fileId.','.\Yii::$app->db->quoteValue($key).','.\Yii::$app->db->quoteValue($val).')';
		}
		$connection = Yii::$app->getDb();
		$command = $connection->createCommand('
			INSERT INTO file_info (file_id, `key`, `value`) VALUES '.implode(',', $data).'
			ON DUPLICATE KEY UPDATE `value` = values(`value`)'
		);

		$command->query();
	}

}