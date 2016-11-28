<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class FolderProperty extends ActiveRecord {

	static $tableName = 'folder_properties';

	static $primaryKey = 'folder_id';

	// public function defaultScope() {
	// 	return array(
	// 		'order' => 'file_id ASC',
	// 	);
	// }
}