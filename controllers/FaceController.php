<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\Folders;

class FaceController extends Controller {

	public function actionIndex() {
		\Yii::$app->response->format = \yii\web\Response::FORMAT_HTML;
		$root = Folders::getRoot();

		return $this->renderPartial('index', [
			'root' => $root[0],
			'pageTitle' => 'none',
		]);
	}
}