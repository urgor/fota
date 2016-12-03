<?php

$config = [
	'id' => 'basic',
	'basePath' => dirname(__DIR__),
	'bootstrap' => ['log'],
	// 'name' => 'Fota gallery',
	'defaultRoute' => 'face/index',
	'components' => [
		'cache' => [
			'class' => 'yii\caching\FileCache',
		],
		'user' => [
			'identityClass' => 'app\models\User',
			'enableAutoLogin' => true,
		],
		'errorHandler' => [
			'errorAction' => 'site/error',
		],
		'response' => [
			'format' => yii\web\Response::FORMAT_JSON,
			'charset' => 'UTF-8',
//            'class' => 'yii\web\Response',
//            'on beforeSend' => function ($event) {
//                $response = $event->sender;
//                if ($response->data !== null) {
//                    $response->data = [
//                        'success' => $response->isSuccessful,
//                        'data' => $response->data,
//                    ];
//                    $response->statusCode = 200;
//                }
//            },
	   ],
		'mailer' => [
			'class' => 'yii\swiftmailer\Mailer',
			// send all mails to a file by default. You have to set
			// 'useFileTransport' to false and configure a transport
			// for the mailer to send real emails.
			'useFileTransport' => true,
		],
		'log' => [
			'traceLevel' => YII_DEBUG ? 3 : 0,
			'targets' => [
				[
					'class' => 'yii\log\FileTarget',
					'levels' => ['error', 'warning'],
				],
			],
		],

		'urlManager' => [
			'enablePrettyUrl' => true,
			'showScriptName' => false,
			'enableStrictParsing' => true,
			'rules' => [
					'' => 'face/index',
					'face' => 'face/index',
					'folder/<id:\d+>' => 'folder/index',
					'folder/accessLink/<id:\d+>' => 'folder/access-link',
					'authentificate/login' => 'authentificate/login',
					'authentificate/logout' => 'authentificate/logout',
					'album/create' => 'album/create',
					'album/dec' => 'album/dec',
					'album/add' => 'album/add',
					'album/delete' => 'album/delete',
					'album/getList' => 'album/get-list',
					'album/getFiles/<id:\d+>' => 'album/get-files',

					'download/album/<id:\d+>' => 'download/album',
					'download/folder/<id:\d+>' => 'download/folder',

					'scan' => 'scan/index',
					// 'suffix' => '.json',
			],
		],

	],
];

return array_merge_recursive($config, require(__DIR__ . '/local.php'));
