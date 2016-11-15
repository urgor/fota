<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\Folders;
use app\models\Files;
use app\models\FileInfo;


class FolderController extends Controller {

	public function actionIndex() { // $folderId = false

		$folderId = Yii::$app->request->get('id');
		$data = [];

		try {
			if (Yii::$app->user->isGuest) throw new \Exception("Need login", 1);
			$data['folders'] = $this->getFolders($folderId);
			$data['files'] = $this->getFiles($folderId);
		} catch (\Exception $e) {
			$data['error'] = true;
			$data['msg'] = $e->getMessage();
		}
		return $data;
	}

    private function getFolders($folderId) {
        $data = [];
        $parent = Folders::findOne(['folder_id' => $folderId]);
		if (is_null($parent)) throw new \Exception("Folder not found", 1);

		foreach ($parent->children(1)->orderBy('name')->all() as $children) {
			$data[] = [
				'id' => $children->folder_id,
				'name' => $children->name,
				'leaf' => $children->isLeaf(),
			];
		}

        return $data;
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

}