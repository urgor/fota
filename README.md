About Fota Web Album
============================

Fota Web Album is tool to create, photo web albums (Your K.O.)

It can scan some directory subtree, make photos thumbs and index to create it web representation.

Also Fota provides create album from selected images; integrate album to third-party sites; allow download album or folder as single zip archive.

Fota never changes original images or directories. Fota does not make a coffee.

DEMO
----

[fota-demo.urgor.com.ua](http://fota-demo.urgor.com.ua)

Login: fota

Password: demo

Database resets every 10 vinutes.

REQUIREMENTS
------------

The minimum requirement
- PHP 5.4.0.
- Yii 2.0.10.
- imagemagic
- md5sum

INSTALLATION
------------

Checkout project

`git clone git@github.com:urgor/fota.git <projectRoot>`

Setup web directory of your webservert host to `<projectRoot>/web`

Extract the archive file downloaded from [yiiframework.com](http://www.yiiframework.com/download/) to
a directory named `<projectRoot>/vendor`.

CONFIGURATION
-------------

Make copy of `<projectRoot>/config/local_example.php` to `<projectRoot>/config/local.php` (it is under .gitignore)

### Database

Create database and structure from file `<projectRoot>/structure.sql`

Edit the file `<projectRoot>/config/local.php` components->db section with real data, for example:

```php
[
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=yii2basic',
    'username' => 'maFotaUzer',
    'password' => '1234',
    'charset' => 'utf8',
],
```

### Other config

Set cookie validation key in `<projectRoot>/config/local.php` file to some random secret string:

```php
'request' => [
    'cookieValidationKey' => '<secret random string goes here>',
],
```

Fill all underscores in `<projectRoot>/config/local.php` to your private host values.

AUTHORS
-------

Alexandr Olejnik, [mailto Urgor](mailto:urgorka@gmail.com)

LICENSE
-------

Copyleft. Use it as you want (if you want).
