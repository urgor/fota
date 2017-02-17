<?php

error_reporting(E_NONE);
ini_set('display_errors', '0');

return [

	'components' => [
		'db' => [
			'class' => 'yii\db\Connection',
			'dsn' => 'mysql:host=____;dbname=____',
			'username' => '____',
			'password' => '____',
			'charset' => 'utf8',
		],
		'request' => [
			'cookieValidationKey' => '____',
		],
	],

	'params' => [
		'adminEmail' => '____',
		'baseUrl' => '____',
		'rootFolderName' => '____',
		'siteTitle' => '____',
		'users' => [
			'____' => '____',
		],
		'thumbnail' => [
			'big' => [
				'maxWidth' => 1900,
				'maxHeight' => 1000,
				'quality' => 80,
			]
		],
		'preventScanDirBeginsFrom' => '_', // Folder name begins from this symbol will be not scanned.
		'sourceFolderPath' => '____', // Path to You photo library.
		'thumbRealPath' => '____/', // Path to store thumbnails. Must be writable and readable.
		'thumbsPath' => '____/', // Path to thumbnails in the web. This will be created by `init` command as symlink to 'thumbRealPath'.
        'temporaryFilePath' => '/tmp/fotagallery', // Temporary folder and prefix folder name for creating archive when user gets some download.
	],

];