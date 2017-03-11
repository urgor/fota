<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\managers\Folder as FolderManager;
use app\managers\FolderProperty as FolderPropertyManager;
use app\managers\File as FileManager;

class FolderController extends Controller {

	public function actionIndex() {

		$folderId = Yii::$app->request->get('id');
		$accessHash = Yii::$app->request->get('accessHash');
        $parent = FolderManager::getById($folderId);
		$data = [];

        if (Yii::$app->user->isGuest) {
            if (empty($accessHash)) {
                throw new \app\exceptions\Fota("Need login");
            } elseif ($parent->isRoot()) {
                $folder = FolderManager::getFolderByAccesHash($accessHash);
                $data['folders'] = [self::mapFields($folder)];
                $data['folders'][0]['folders'] = array_map('self::mapFields', FolderManager::getSubFolders($folder));
                $data['folders'][0]['files'] = FileManager::getByFolderSpecial($folder->folder_id);

            } elseif (FolderManager::checkAccessByHash($parent, $accessHash)) {
                $data['folders'] = array_map('self::mapFields', FolderManager::getSubFolders($parent));
                $data['files'] = FileManager::getByFolderSpecial($folderId);
            } else {
                throw new \Exception("Permission denied", 1);

            }
        } else {
            $data['folders'] = array_map('self::mapFields', FolderManager::getSubFolders($parent));
            $data['files'] = FileManager::getByFolderSpecial($folderId);
        }

		return $data;
	}

	private static function mapFields($folder) {
		return [
			'id' => $folder->folder_id,
			'name' => $folder->name,
			'leaf' => $folder->isLeaf(),
		];
	}

	public function actionAccessLink() {
		$folderId = (int) Yii::$app->request->get('id');
		$prop = FolderPropertyManager::getByFolderId($folderId);
		if (empty($prop)) {
			$prop = FolderPropertyManager::createAccessHash($folderId, md5(microtime() . '_' . $folderId));
		}

		return ['data' => $prop->access_code];
	}

}