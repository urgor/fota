<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\Folders;
use app\models\Files;
use app\models\FileInfo;
use app\models\FolderProperty;


class FolderController extends Controller {

	public function actionIndex() { // $folderId = false

		$folderId = Yii::$app->request->get('id');
		$accessHash = Yii::$app->request->get('accessHash');

		$data = [];

		try {
			// if (Yii::$app->user->isGuest) throw new \Exception("Need login", 1);
			$parent = Folders::findOne(['folder_id' => $folderId]);
			if (is_null($parent)) throw new \Exception("Folder not found", 1);

			if (Yii::$app->user->isGuest) {
				if (empty($accessHash)) throw new \Exception("Need login", 1);

				elseif ($parent->isRoot()) {
					$data['folders'] = [self::mapFields($this->getFolderByAccesHash($accessHash))];

				} elseif ($this->checkAccessByHash($parent, $accessHash)) {
					$data['folders'] = $this->getSubFolders($parent);
					$data['files'] = $this->getFiles($folderId);
				} else {
					throw new \Exception("Permission denied", 1);

				}
			} else {
				$data['folders'] = $this->getSubFolders($parent);
				$data['files'] = $this->getFiles($folderId);
			}

		} catch (\Exception $e) {
			$data['error'] = true;
			$data['msg'] = $e->getMessage();
		}
		return $data;
	}

	private function getSubFolders($parent) {
		return array_map('\app\controllers\FolderController::mapFields', $parent->children(1)->orderBy('name')->all());
	}

	private static function mapFields($folder) {
		return [
			'id' => $folder->folder_id,
			'name' => $folder->name,
			'leaf' => $folder->isLeaf(),
		];
	}

	private function getFolderByAccesHash($accessHash) {
		$prop = FolderProperty::find()->where(['access_hash' => $accessHash])->one();
		if (empty($prop)) throw new \Exception("Folder not found", 1);
		$folder = Folders::findOne($prop->folder_id);

		return $folder;
	}

	private function checkAccessByHash($parent, $accessHash) {
		$hashedFolder = $this->getFolderByAccesHash($accessHash);
		return $parent->isChildOf($hashedFolder) || $parent->folder_id == $hashedFolder->folder_id;
	}

	private function getFiles($folderId) {
		$data = [];
		foreach (Files::find()->where(['folder_id' => $folderId])->orderBy('original_name')->all() as $file) {
			$info = [];
			foreach (FileInfo::findAll(['file_id' => $file->file_id]) as $infoBit) {
				$info[$infoBit['key']] = $infoBit['value'];
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

	public function actionAccessLink() {
		$folderId = (int)Yii::$app->request->get('id');
		$prop = FolderProperty::findOne($folderId);
		if (empty($prop)) {
			$prop = new FolderProperty();
			$prop->folder_id = $folderId;
			$prop->access_hash = md5(microtime() . '_' . $folderId);
			$prop->save();
		}

		return ['data' => $prop->access_hash];
	}

}