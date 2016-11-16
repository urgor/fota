<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\User;

class AuthentificateController extends Controller {

	public $enableCsrfValidation = false;

	public function actionLogout() {
		Yii::$app->user->logout();
		$resp = [
			'msg' => $this->renderPartial('anonymous')
		];
		return $resp;
	}

	public function actionLogin() {
		if (!Yii::$app->user->isGuest) {
			return 'auth!';
		}

		$model = new \app\models\User(Yii::$app->request->post('email'));
		if ($model->check(Yii::$app->request->post('password')) ) {
			error_log('do login');
			\Yii::$app->user->login($model);
			$resp['msg'] = $this->renderPartial('authentificated');
		} else {
			$resp = [
				'error' => true,
				'msg' => $e->getMessage().' -- '.$identity->errorMessage
			];
		}

		return $resp;
	}

}