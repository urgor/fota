<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\manager\Folder as FolderManager;
use app\models\Files;
use app\models\Albums;
use app\models\AlbumFiles;
use app\models\FileSystem as FS;
use app\workers\ArchiveZip;
use app\workers\PathAssembler;
use app\workers\ArchiveInterface;

class DownloadController extends Controller {

	public $enableCsrfValidation = false;

	public function actionAlbum()
    {
        $albumId = (int)Yii::$app->request->get('id');
        $album = Albums::getById($albumId);
        $pathAssembler = new PathAssembler(AlbumFiles::getByAlbum($albumId));

        $archive = new ArchiveZip;
        $archive->pack($album->name, $pathAssembler);

        $this->outputArchive($archive);
	}

	public function actionFolder()
    {
        $folderId = (int)Yii::$app->request->get('id');
        $folder = FolderManager::getById($folderId);
        $pathAssembler = new PathAssembler(Files::getByFolder($folderId));

        $archive = new ArchiveZip;
        $archive->pack($folder->name, $pathAssembler);

        $this->outputArchive($archive);
	}

    private function outputArchive(ArchiveInterface $archive)
    {
        $fullpathFilename = $archive->getFullPathFileName();
        Yii::$app->getResponse()->getHeaders()
            ->set('Content-Description', 'File Transfer')
            ->set('Expires', '0')
            ->set('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
            ->set('Pragma', 'public')
            ->set('Content-Transfer-Encoding', 'binary')
            ->set('Content-Length', FS::filesize($fullpathFilename));

        Yii::$app->getResponse()->sendFile($fullpathFilename, $archive->getFilename(), [
            'mimeType' => $archive->getMimeType(),
            'inline' => false,
        ]);

        $archive->clean();
    }
}