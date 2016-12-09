<?php

namespace app\models;

use Yii;
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

	public static function findEmpty() {
		return static::findBySql('select f.*, count(file_id) cnt
				from `' . self::tableName() . '` f
					left join `files` using (folder_id)
				where `left`+1 = `right`
				group by f.folder_id
				having cnt = 0
			')->all();
	}
}