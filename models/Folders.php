<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use creocoder\nestedsets\NestedSetsBehavior;

class Folders extends ActiveRecord {

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






/*
	public function attributeNames() {
		return ['id', 'name', 'level', 'right', 'left'];
	}

	public function tableName() {
		return 'folders';
	}

	public static function model($className = __CLASS__) {
		return parent::model($className);
	}

	public function behaviors() {
		return [
			'NestedSetBehavior' => [
				// /protected/extensions/behaviors/trees/NestedSetBehavior
				'class' => 'ext.behaviors.trees.NestedSetBehavior',
				'leftAttribute' => 'left',
				'rightAttribute' => 'right',
				'levelAttribute' => 'level',
			]
		];
	}

	public function primaryKey() {
		return 'folder_id';
	}*/

//    public function truncate() {
//        Yii::app()->db->createCommand('TRUNCATE TABLE folders')
//                ->execute();
//    }
}