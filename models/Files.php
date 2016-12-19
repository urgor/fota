<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use app\models\FileInfo;

class Files extends ActiveRecord {

	public function defaultScope() {
		return array(
			'order' => 'original_name ASC',
		);
	}

	public static function getByFolder($folderId) {
		$data = [];
		foreach (static::find()->where(['folder_id' => $folderId])->orderBy('original_name')->all() as $file) {
			$info = [];
			foreach (FileInfo::findAll(['file_id' => $file->file_id]) as $infoBit) {
				$info[$infoBit['key']] = $infoBit['value'];
			}
			foreach (['width', 'height'] as $field) {
				if (isset($info[$field])) $info[$field] = (int) $info[$field];
			}
			$data[] = [
				'id' => $file->file_id,
				'thumb' => $file->md_path,
				'name' => $file->original_name,
				'info' => $info,
			];
		}
		return $data;
	}

}