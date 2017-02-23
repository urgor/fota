<?php

namespace app\models;

use yii\db\ActiveRecord;
use creocoder\nestedsets\NestedSetsBehavior;

class Folders extends ActiveRecord {

	public static function tableName() {
		return 'folders';
	}

	public function behaviors() {
		return [
			'tree' => [
				'class' => NestedSetsBehavior::className(),
				// 'treeAttribute' => 'tree',
				'leftAttribute' => 'left',
				'rightAttribute' => 'right',
				'depthAttribute' => 'level',
			],
		];
	}

	public function transactions()
	{
		return [
			self::SCENARIO_DEFAULT => self::OP_ALL,
		];
	}

	public static function find()
	{
		return new FolderQuery(get_called_class());
	}

}