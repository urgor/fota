<?php

namespace app\commands;

use yii\console\Controller;
use app\workers\FileSystem as FS;
use app\workers\Scaner;
use app\models\AlbumFiles;
use app\models\RuntimeParameters;
use app\managers\Folder as FolderManager;
use app\managers\File as FileManager;

/**
 * Run scan and indexing of images
 */
class ScanController extends Controller {

    /**
     * @var bool Force update EXIF and other file info at any found file
     */
    public $updateInfo = false;
    /**
     * @var bool Force create new thumbnail for any found file
     */
    public $rethumb = false;

    public function options($actionId) {
        return ['updateInfo', 'rethumb'];
    }

    public function actionIndex() {
        FileManager::resetProcessed();
        $root = FolderManager::getRoot();
        if (is_null($root)) {
            echo "There is no root directory in database. You should use `init` command before scan/\n";
            return Controller::EXIT_CODE_ERROR;
        }

        $params = new RuntimeParameters([
            'updateInfo' => $this->updateInfo,
            'rethumb' => $this->rethumb,
        ]);

        $scaner = new Scaner($params);

        $scaner->scanMain('', $root);
        $this->deleteEmptyFolders();
        $this->deleteUnprocessedFiles();
        return Controller::EXIT_CODE_NORMAL;
    }

    private function deleteUnprocessedFiles() {
        $rmFiles = FileManager::getUnprocessed();
        if (0 === count($rmFiles)) {
            echo "No thumbs to delete.\n";
            return;
        }
        echo "Deleting thumbs:\n";
        foreach ($rmFiles as $file) {
            $fileName = FS::buildThumbPathFile($file->md_path);
            echo $fileName . ' ';
            if (!FS::unlink($fileName)) {
                echo 'cant delete thumb.';
            }

            AlbumFiles::deleteFromAllAlbums($file->file_id);
            $file->delete();
            echo $file->file_id . ' deleted';

            echo PHP_EOL;
        }
    }

    private function deleteEmptyFolders()
    {
        while ($folders = FolderManager::findEmpty()) {
            foreach ($folders as $folder) {
                echo "Delete empty folder " . $folder->name . PHP_EOL;
                $folder->delete();
            }
        }
    }

}
