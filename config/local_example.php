<?php

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
		'baseUrl' => '____', // Domain name
		'rootFolderName' => '____', // Name of root folder for web representation (might be any). Root will be created by `./yii init` command.
		'siteTitle' => '____', // Page title for web
		'users' => [
			'____' => '____', // User name and password to full access.
		],
		'thumbnail' => [
			'big' => [
				'maxWidth' => 1900,
				'maxHeight' => 1000,
				'quality' => 80,
			]
		],
		'preventScanDirBeginsFrom' => '_', // Folder name begins from this symbol will be not scanned.
		'sourceFolderPath' => '____', // File system path to Your photo library.
		'thumbRealPath' => '____', // File system path to store thumbnails. Must be writable and readable.
		'thumbsPath' => '____/', // Path to thumbnails in the web. This will be created by `./yii init` command as symlink to 'thumbRealPath'.
        'temporaryFilePath' => '/tmp/fotagallery', // Temporary folder and prefix folder name for creating archive when user gets some download.
	],

];