<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\Folders;

class FaceController extends Controller {

	public function actionIndex() {
		\Yii::$app->response->format = \yii\web\Response::FORMAT_HTML;
		try {
			$root = Folders::find()->roots()->all();
		} catch (\Exception $e) {
			error_log('$e->getMessage() = '.var_export($e->getMessage(), true));

		}

		return $this->renderPartial('index', [
			'root' => $root[0],
			'pageTitle' => 'boobs',
		]);
	}
}