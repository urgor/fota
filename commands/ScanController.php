<?php
namespace app\commands;

use Yii;
use yii\console\Controller;

class ScanController extends Controller {

	// private $params = [
	// 	'rethumb' => ['desc'=> 're create thumbnails', 'val' => false],
	// 	'update_info' => ['desc'=> 'read exif and refill db', 'val' => false],
	// ];

	// public function getHelp() {
	// 	$out = 'Usage: ./yiic scan [' . implode('] [', array_keys($this->params)) . ']';
	// 	$out .= "\nwhere\n";
	// 	foreach ($this->params as $k => $v) {
	// 		$out .= "\t{$k} -- {$v['desc']}\n";
	// 	}
	// 	return $out;
	// }

	public function optionAliases() {
		return ['e' => 'extra'];
	}

	public function actionIndex() {
		// foreach (array_keys($this->params) as $key) {
		// 	if (in_array($key, $args)) $this->params[$key]['val'] = true;
		// }

		foreach (['0','1','2','3','4','5','6','7','8','9','a','b','c','d','e','f'] as $l) {
			$path = Yii::$app->params['thumbRealPath'] . DIRECTORY_SEPARATOR . $l;
			if (!file_exists($path)) mkdir($path);
			elseif (!is_dir($path)) die('You shuld clean up ' . Yii::$app->params['thumbRealPath'] . ' directory first.' . PHP_EOL);
		}
		\app\models\Files::updateAll(['processed' => 0], []);
		$root = \app\models\Folders::findOne(['level' => 0]);
		if (is_null($root)) {
			$root = new \app\models\Folders;
			$root->name = Yii::$app->params['rootFolderName'];
			$root->makeRoot();
		}
		$this->scanDir('', $root);
		$rmFiles = \app\models\Files::findAll(['processed' => 0]);
		if (0 === count($rmFiles)) {
			echo "No thumbs to delete.\n";
		} else {
			echo "Deleting thumbs:\n";
            foreach($rmFiles as $file) {
				$fName = self::makeThumbPath($file->md_path);
				echo $fName.' ';
				if (file_exists($fName)) {
					if (!is_writable($fName)) {
						echo "not writeable!\n";
						continue;
					}
					if(!unlink($fName)) {
						echo 'cant delete file fron FS!';
						continue;
					}
				}
				\app\models\AlbumFiles::deleteAll(['file_id' => $file->file_id]);
				$file->delete();
				echo $file->file_id . ' deleted';

				echo PHP_EOL;
			}
		}
	}

	/**
	 * [scanDir description]
	 * @param  str $dir          [description]
	 * @param  \app\models\Folders $parentFolder [description]
	 * @return [type]                           [description]
	 */
	private function scanDir($dir, \app\models\Folders $parentFolder) {
		$d = dir(Yii::$app->params['sourceFolderPath'] . $dir);
		$entitiesHere = 0;
		while ($entry = $d->read()) {
			$firstSymbol = mb_substr($entry, 0, 1);
			if (
				'..' == $entry || '.' == $firstSymbol || Yii::$app->params['preventSccanDirBeginsFrom'] == $firstSymbol
			) continue;
			$path = Yii::$app->params['sourceFolderPath'] . $dir . DIRECTORY_SEPARATOR . $entry;
			if (is_dir($path)) {
				echo "Folder $path\n";
				$entitiesHere++;
				$folder = false;
				foreach($parentFolder->children(1)->all() as $child) if ($child->name === $entry) {
					$folder = $child;
					break;
				}
				if (!$folder) {
					$folder = new \app\models\Folders;
					$folder->name = $entry;
					$folder->appendTo($parentFolder);
				}
				if(0 == $this->scanDir($dir . DIRECTORY_SEPARATOR . $entry, $folder)) {
					$folder->delete();
					echo "Folder $entry deleted\n";
				}
				unset($folder);
			} elseif (is_file($path)) {
				$entitiesHere++;
				$mdPath = md5($dir . DIRECTORY_SEPARATOR . $entry);

				// $oldMdContent = md5(file_get_contents($path));
				$output = [];
				$returnVar = 0;
				exec('md5sum --binary ' . preg_replace('/(["\' \(\);])/', '\\\$1', $path), $output, $returnVar);
				if (0 != $returnVar) throw new \Exception('Error calculating md5 sum', 1);
				$mdContent = substr($output[0], 0, 32);

				$ext = pathinfo($entry, PATHINFO_EXTENSION);
				if (!in_array(strtolower($ext), ['jpg', 'tiff', 'tif', 'png'])) continue;
				$findPath = \app\models\Files::find()->where(['md_path' => $mdPath])->one();
				$findContent = \app\models\Files::find()->where(['md_content' => $mdContent])->one();

				if (is_null($findPath)) {
					if (is_null($findContent)) {
						// simply new file
						if(0 != $this->makeThumb($path, $mdPath)) {
							echo "File $entry Cant create thumbnail; skip it!!!\n";
							continue;
						}
						$file = new \app\models\Files();
						$file->folder_id = $parentFolder->getAttribute('folder_id');
						$file->original_name = $entry;
						$file->md_path = $mdPath;
						$file->md_content = $mdContent;
						$file->processed = 1;
						$file->save();
						$this->fillInfo($file->file_id, $path);
						echo "File $entry Saved new\n";
					} else {
						// -path +content => content set cur path
						if (self::isMovable($findContent->md_path))
							rename(self::makeThumbPath($findContent->md_path), self::makeThumbPath($mdPath));
						else $this->makeThumb($path, $mdPath);
						// if ($this->params['rethumb']['val']) $this->makeThumb($path, $mdPath); // option rethumb

						$findContent->folder_id = $parentFolder->getAttribute('folder_id');
						$findContent->md_path = $mdPath;
						$findContent->processed = 1;
						$l = $findContent->save();
						// if ($this->params['update_info']['val']) $this->fillInfo($findContent->file_id, $path); // option update_info
						echo "File $entry Moved\n";
					}
				} else {
					if (is_null($findContent)) {
						// +path -content => path set current content
						$findPath->md_content = $mdContent;
						$findPath->processed = 1;
						$findPath->save();
						$this->makeThumb($path, $mdPath);
						// if ($this->params['update_info']['val']) $this->fillInfo($findPath->file_id, $path); // option update_info
						echo "File $entry Updated\n";
					} else {
						// +path +content => processed
						$findContent->processed = 1;
						$findContent->save();
						// if ($this->params['rethumb']['val']) $this->makeThumb($path, $mdPath); // option rethumb
						// if ($this->params['update_info']['val']) $this->fillInfo($findContent->file_id, $path); // option update_info
						echo "\r";
					}
				}

			}
		}
		$d->close();
		return $entitiesHere;
	}

	private static function makeThumbPath($name) {
		return Yii::$app->params['thumbRealPath'] . $name[0] . DIRECTORY_SEPARATOR . $name . '.jpg';
	}

	private static function isMovable ($name) {
		$file = self::makeThumbPath($name);
		return file_exists($file) && is_writable($file);
	}

	private function makeThumb($path, $mdPath) {
		exec (
			'convert "'.str_replace('"', '\"', $path).'" -auto-orient '
			.'-thumbnail '.Yii::$app->params['thumbnail']['big']['maxWidth'].'x'.Yii::$app->params['thumbnail']['big']['maxHeight']
			.'\\> -quality '.Yii::$app->params['thumbnail']['big']['quality'].' '.self::makeThumbPath($mdPath)
			, $output, $returnVar
		);
		return $returnVar;
	}

	/**
	 * Достаёт необходимые EXIF теги из файла и запускае сохранение
	 *
	 * @param $file_id int ИД файла
	 * @param $path str Путь и имя к файлу в ФС
	 * @return bool
	 */
	private function fillInfo($file_id, $path) {
		$data = shell_exec('exiftool -j '.str_replace([' ', '(', ')'], ['\ ', '\(', '\)'], $path));
		if(!$data) return false;
		$data = json_decode($data, true);
		if(!$data) return false;
		$data = reset($data);

		if (!empty($data['DateTimeOriginal'])) $timestamp = strtotime($data['DateTimeOriginal']);
		elseif(!empty($data['FileModifyDate'])) $timestamp = strtotime($data['FileModifyDate']);
		else return false;

		if (!empty($timestamp)) $this->writeInfo($file_id, 'exif_create_timestamp', $timestamp);
		if (!empty($data['Description'])) $this->writeInfo($file_id, 'exif_description', $data['Description']);
		if (!empty($data['Title'])) $this->writeInfo($file_id, 'exif_title', $data['Title']);
		if (!empty($data['Subject'])) {
			if (is_array($data['Subject'])) $this->writeInfo($file_id, 'exif_keywords', implode(', ', $data['Subject']));
			else $this->writeInfo($file_id, 'exif_keywords', $data['Subject']);
		}
		return true;
	}

	/**
	 * Вставляет указанную пару ключ-значение в file_info, обновляя при необходимости
	 *
	 * @param $file_id int ИД файла
	 * @param $key str Ключ
	 * @param $value str Значение
	 * @return bool
	 */
	private function writeInfo($file_id, $key, $value) {
		$FileInfo = \app\models\FileInfo::findOne(['file_id' => $file_id, 'key' => $key]);
		if (!$FileInfo) {
			$FileInfo = new \app\models\FileInfo;
			$FileInfo->file_id = $file_id;
			$FileInfo->key = $key;
		}
		$FileInfo->value = $value;
		return (bool)$FileInfo->save();
	}

}
