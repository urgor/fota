<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\managers\Folder as FolderManager;

class FaceController extends Controller {

	public function actionIndex() {
		\Yii::$app->response->format = \yii\web\Response::FORMAT_HTML;
		$root = FolderManager::getRoot();

		return $this->renderPartial('index', [
			'root' => $root,
			'pageTitle' => 'none',
		]);
	}
}