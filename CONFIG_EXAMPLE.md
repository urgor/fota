Example of configuration file
=============================

Nginx
-----

`server {
	listen 80;
	server_name <sitename>;
	root <projectRoot>/web;
	index index.php;
    error_log /var/log/nginx/<sitename>-error.log info;
    access_log /var/log/nginx/<sitename>-access.log;

	location / {
	    try_files $uri $uri/ /index.php?$args;
	}

	location ~ ^/(protected|framework|themes/\w+/views) {
		deny  all;
	}

	location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php7.0-fpm.sock;
	}

	location ~* \.(js|css|png|jpg|gif|swf|ico|pdf|mov|fla|zip|rar)$ {
		# try_files $uri =404;
		expires max;
		log_not_found off;
		access_log off;
	}

	location ~ /\. {
		deny all;
		access_log off;
		log_not_found off;
	}
}`

local.php
---------

`<?php

return [
    'bootstrap' => ['log'],

	'components' => [
		'db' => [
			'class' => 'yii\db\Connection',
			'dsn' => 'mysql:host=127.0.0.1;dbname=photos_db',
			'username' => 'photos_user',
			'password' => 'photos_password',
			'charset' => 'utf8',
		],
		'request' => [
			'cookieValidationKey' => 'brtjnwr654h244jte56i77kmnl0qe5j',
		],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
	],

	'params' => [
		'adminEmail' => 'webmaster@example.com',
		'baseUrl' => 'myphotos.com',
		'rootFolderName' => 'Photo archive',
		'siteTitle' => 'My photo archive',
		'users' => [
			'q' => 'q',
		],
		'thumbnail' => [
			'big' => [
				'maxWidth' => 1900,
				'maxHeight' => 1000,
				'quality' => 80,
			]
		],
		'preventScanDirBeginsFrom' => '.',
		'sourceFolderPath' => '/var/photos/',
		'thumbRealPath' => '/var/photos-thumbs/',
		'thumbsPath' => '/images/thumbs/',
        'temporaryDirPrefix' => '/tmp/fotagallery',
	],

];`