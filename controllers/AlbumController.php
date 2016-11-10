<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;

class AlbumController extends Controller {

	private $_data = ['error' => false];
	public $enableCsrfValidation = false;

	public function actionIndex() {
		die;
	}

	/**
	 * Создание альбома
	 */
	public function actionCreate() {
		try {
			if (Yii::$app->user->isGuest) throw new Exception("Need login", 1);
			if (empty($_POST['name']) || empty($_POST['items'])) throw new Exception("Empty data", 1);
			$album = new \app\models\Albums;
			$album->name = filter_var($_POST['name'], FILTER_SANITIZE_SPECIAL_CHARS);
			if (!$album->save()) throw new Exception("Error creating album", 1);

			foreach ($_POST['items'] as $item) {
				$albumFiles = new \app\models\AlbumFiles;
				$albumFiles->album_id = $album->album_id;
				$albumFiles->file_id = $item;
				if (!$albumFiles->save()) throw new Exception("Error adding images", 1);
			}
		} catch (Exception $e) {
			$this->_data['error'] = true;
			$this->_data['msg'] = $e->getMessage();
		}
		return $this->_data;
	}

	public function actionGetList() {
		foreach (\app\models\Albums::find()->all() as $album) {
			$this->_data['folders'][] = [
				'id' => $album->album_id,
				'name' => $album->name,
			];
		}
		return $this->_data;
	}

	public function actionGetFiles() {
		$this->getFiles((int)Yii::$app->request->get('id'));
		return $this->_data;
	}

	/**
	 * Получение информации о альбоме со стороннего сайта
	 */
	public function actionGetFilesJs($id) {
		if ($this->getFiles($id)) {
			header('Content-type: application/x-javascript');
			echo 'fotaAlbum.draw(\''.json_encode($this->_data).'\');';
		} else {
			$this->_jsonRender();
		}
	}

	/**
	 * Удаление альбома и связей с файлами
	 */
	public function actionDelete(){
		try {
			if (Yii::$app->user->isGuest) throw new Exception("Need login", 1);
			if (empty(Yii::$app->request->post('albumId'))) throw new Exception("Empty data", 1);
			\app\models\AlbumFiles::deleteAll(['album_id' => (int)Yii::$app->request->post('albumId')]);
			\app\models\Albums::deleteAll(['album_id' => (int)Yii::$app->request->post('albumId')]);
		} catch (Exception $e) {
			$this->_data['error'] = true;
			$this->_data['msg'] = $e->getMessage();
		}
		return $this->_data;
	}

	/**
	 * Добавляет переданные POSTом изображения в альбом
	 */
	public function actionAdd(){
		try {
			if (Yii::$app->user->isGuest) throw new Exception("Need login", 1);
			if (empty(Yii::$app->request->post('items')) || empty(Yii::$app->request->post('albumId'))) throw new Exception("Empty data", 1);

			$album = \app\models\Albums::findOne((int)Yii::$app->request->post('albumId'));
			if (!$album) throw new Exception("There is no such album", 1);

			foreach (Yii::$app->request->post('items') as $itemId) {
				$itemId = (int) $itemId;
				$albumFiles = new \app\models\AlbumFiles;
				$albumFiles->album_id = $album->album_id;
				$albumFiles->file_id = $itemId;
				if (!$albumFiles->insert()) throw new Exception("Error adding image", 1);
			}
		} catch (Exception $e) {
			$this->_data['error'] = true;
			$this->_data['msg'] = $e->getMessage();
		}
		return $this->_data;
	}

	/**
	 * Удаляет переданные POSTом изображения из альбома
	 */
	public function actionDec(){
		try {
			if (Yii::$app->user->isGuest) throw new Exception("Need login", 1);
			$album = \app\models\Albums::findOne((int)Yii::$app->request->post('albumId'));

			if (!$album) throw new Exception("No such album", 1);
			if (empty(Yii::$app->request->post('items'))) throw new Exception("Error removing images", 1);

			$items = array_map('intval', Yii::$app->request->post('items'));
			\app\models\AlbumFiles::deleteAll(['and', ['album_id' => $album->album_id], ['file_id' => $items]]);

		} catch (Exception $e) {
			$this->_data['error'] = true;
			$this->_data['msg'] = $e->getMessage();
		}
		return $this->_data;
	}

	private function getFiles($id) {
		if ($album = \app\models\Albums::findOne($id)) {
			$this->_data['folders'][] = [
				'id' => $album->album_id,
				'name' => $album->name,
			];

			$query = \app\models\AlbumFiles::find()->where(['album_id' => $id])->all();
			foreach ($query as $albumFile) {
				$file = $albumFile->getFile()->asArray()->one();
				$this->_data['files'][] = [
					'id' => $file['file_id'],
					'thumb' =>$file['md_path'],
					'name' => $file['original_name'],
					'info' => $albumFile,
				];
			}
			return true;
		} else {
			$this->_data['error'] = true;
			$this->_data['msg'] = 'Нет такой галереи';
			return false;
		}
	}
}