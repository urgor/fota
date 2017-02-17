<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\FileInfo;
use app\models\Albums;
use app\models\AlbumFiles;
use app\managers\File as FileManager;

class AlbumController extends Controller {

	private $_data = ['error' => false];
	public $enableCsrfValidation = false;

	public function actionIndex() {
		die;
	}

	/**
	 * Create album
	 */
	public function actionCreate() {
        if (Yii::$app->user->isGuest) {
            throw new \Exception("Need login", 1);
        }
        $request = Yii::$app->request;
        if (empty($request->post('name')) || empty($request->post('items'))) {
            throw new \Exception("Empty data", 1);
        }

        $album = Albums::create(filter_var($request->post('name'), FILTER_SANITIZE_SPECIAL_CHARS));

        foreach ($request->post('items') as $item) {
            FileManager::addFileToAlbum($album->album_id, $item);
        }

		return $this->_data;
	}

    /**
     * Get list of albums
     */
	public function actionGetList() {
		foreach (Albums::getAll() as $album) {
			$this->_data['folders'][] = [
				'id' => $album->album_id,
				'name' => $album->name,
			];
		}
		return $this->_data;
	}

	/**
     * Get album files
     */
    public function actionGetFiles() {
        $id = (int) Yii::$app->request->get('id');
        $album = Albums::getById($id);

        $this->_data['folders'][] = [
            'id' => $album->album_id,
            'name' => $album->name,
        ];

        FileManager::getByAlbum($id);

        foreach (AlbumFiles::getByAlbum($id) as $file) {
            $this->_data['files'][] = [
                'id' => $file['file_id'],
                'thumb' =>$file['md_path'],
                'name' => $file['original_name'],
                'info' => array_merge(FileInfo::getByFile($file['file_id']), $file),
            ];
        }

		return $this->_data;
	}

    /**
	 * Remove album
	 */
	public function actionDelete(){
        if (Yii::$app->user->isGuest) {
            throw new \Exception("Need login", 1);
        }
        if (empty(Yii::$app->request->post('albumId'))) {
            throw new \Exception("Empty data", 1);
        }
        AlbumFiles::deleteByAlbum((int) Yii::$app->request->post('albumId'));
        Albums::deleteById((int) Yii::$app->request->post('albumId'));

		return $this->_data;
	}

	/**
	 * Add images to album
	 */
	public function actionAdd(){
        if (Yii::$app->user->isGuest) {
            throw new \Exception("Need login", 1);
        }
        if (empty(Yii::$app->request->post('items')) || empty(Yii::$app->request->post('albumId'))) {
            throw new \Exception("Empty data", 1);
        }
        $album = Albums::getById((int) Yii::$app->request->post('albumId'));

        foreach (Yii::$app->request->post('items') as $itemId) {
            AlbumFiles::addFileToAlbum($album->album_id, (int) $itemId);
        }

        return $this->_data;
	}

	/**
	 * Remove images from album
	 */
	public function actionDec(){
        if (Yii::$app->user->isGuest) {
            throw new \Exception("Need login", 1);
        }
        if (empty(Yii::$app->request->post('items'))) {
            throw new \Exception("Error removing images", 1);
        }

        $album = Albums::getById((int) Yii::$app->request->post('albumId'));

        $items = array_map('intval', Yii::$app->request->post('items'));
        AlbumFiles::deleteFilesFromAlbum($album->album_id, $items);

		return $this->_data;
	}
}