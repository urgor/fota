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
		'preventSccanDirBeginsFrom' => '_',
		'sourceFolderPath' => '____',
		'thumbRealPath' => '____/',
		'thumbsPath' => '____/',
	],

];