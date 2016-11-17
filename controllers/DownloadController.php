<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;

class DownloadController extends Controller {

	private $_data = ['error' => false];
	public $enableCsrfValidation = false;

	private $filename, $contentDir;

	public function actionIndex() {
		die;
	}

	public function actionAlbum() {
		try {
			$id = (int)Yii::$app->request->get('id');
			$album = \app\models\Albums::findOne($id);
			if (!$album) throw new \Exception("There is no such album", 1);
			$this->packAlbum(\app\models\AlbumFiles::find()->where(['album_id' => $id])->all(), $album->name);
			exit;
		} catch (\Exception $e) {
			$this->_data['error'] = true;
			$this->_data['msg'] = $e->getMessage();
			return $this->_data;
		}
	}

	public function actionFolder($id) {
		try {
			$id = (int)Yii::$app->request->get('id');
			$folder = \app\models\Folders::findOne($id);
			$this->packAlbum(\app\models\Files::find()->where(['folder_id' => $id])->all(), $folder->name);
			exit;
		} catch (\Exception $e) {
			$this->_data['error'] = true;
			$this->_data['msg'] = $e->getMessage();
			return $this->_data();
		}
	}

	private function packAlbum($filesObj, $namePart) {
		$files = [];
		$fullPathes = [];
		$this->prepareFS($namePart);
		foreach ($filesObj as $file) {
			$fileInfo = \app\models\Files::findOne($file->file_id);
			self::createFullPath($fileInfo->folder_id, $fullPathes);
			$files[] = $fullPathes[$fileInfo->folder_id].$fileInfo->original_name;

			$l = symlink(
				Yii::$app->params['sourceFolderPath'] . DIRECTORY_SEPARATOR . $fullPathes[$fileInfo->folder_id] . DIRECTORY_SEPARATOR . $fileInfo->original_name,
				$this->contentDir . DIRECTORY_SEPARATOR . $this->filename . DIRECTORY_SEPARATOR . $fileInfo->original_name
			);
		}

		$this->createArchive();
	}

	private static function createFullPath($folderId, &$fullPathes) {
		if (!array_key_exists($folderId, $fullPathes)) {
			$folder = \app\models\Folders::findOne($folderId);
			if (!$folder->isRoot()) $fullPathes[$folderId] = [$folder->name];

			foreach (array_reverse($folder->parents()->all()) as $sf)
				if (!$sf->isRoot())	array_unshift($fullPathes[$folderId], $sf->name);

			$fullPathes[$folderId] = isset($fullPathes[$folderId])
				? implode(DIRECTORY_SEPARATOR, $fullPathes[$folderId])
				: '';
		}
	}

	private function prepareFS($name) {
		$this->filename = str_replace(['`', '\\'], '_', $name);
		$this->contentDir = '/tmp/fotagallery'.mt_rand(1000, 9999999);
		mkdir($this->contentDir . DIRECTORY_SEPARATOR . $this->filename, 0777, true);
	}

	private function createArchive() {
		chdir($this->contentDir);
		$ext = '.zip';
		$cmd = 'zip -0 -r "' . $this->filename . $ext . '" "' . $this->filename . '"';
		$res = exec($cmd, $output, $returnCode);

		if (0 !== $returnCode) {
			$this->clean($this->contentDir);
			throw new \Exception("Cant create temporary archive", 1);
		}
		if (!file_exists($this->filename . $ext) || !is_file($this->filename . $ext) || 0 === filesize($this->filename . $ext)) {
			$this->clean($this->contentDir);
			throw new \Exception("Something wrong with temporary archive", 1);
		}

		header('Content-Description: File Transfer');
		header('Content-Disposition: attachment; filename="'.$this->filename . $ext . '"');
		header('Content-Type: application/x-tar');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($this->filename . $ext));
		readfile($this->filename . $ext);

		$this->clean($this->contentDir);
	}

	private function clean($dir) {
		foreach (glob($dir . DIRECTORY_SEPARATOR . '*') as $filename) {
    		if (is_file($filename)) unlink($filename);
    		elseif (is_dir($filename)) $this->clean($filename);
		}
		rmdir($dir);
	}
}