About Fota Web Album
============================

Fota Web Album is tool to create, photo web albums (Your K.O.)

It can scan some directory subtree and create it web representation by making photo thumbnails and store some info to DB.

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
- PHP 7.0
- mbstring 
- Yii 2.0.10
- imagemagick (convert)
- exiftool
- md5sum

INSTALLATION
------------

Checkout project

`git clone git@github.com:urgor/fota.git <projectRoot>`

Install composer and type `composer install` to install all necessary libraries.

CONFIGURATION
-------------

** Basic CLI configuration **

Make copy of `<projectRoot>/config/local_example.php` to `<projectRoot>/config/local.php` (it is under .gitignore). Also You can use [CONFIG_EXAMPLE.md](CONFIG_EXAMPLE.md) as example.

Create database, db user, grant them permissions;

Edit the file `<projectRoot>/config/local.php`. Fill all underscores inside it to your own host values.

Run `./yii migrate/up` to create database structure.

Now You can run `./Yii init` This will create root directory in DB, and folder structure for thumbnails.

Set <projectRoot>/web/assets directory writable by web server user.

** Web sercver configuration **

Use [nginx exmaple config file](CONFIG_EXAMPLE.md) to setup You Nginx web server to `<projectRoot>/web` directory.

** Other config **

Set cookie validation key in `<projectRoot>/config/local.php` file to some random secret string:

```php
'request' => [
    'cookieValidationKey' => '<secret random string goes here>',
],
```

AUTHORS
-------

Alexandr Olejnik, [mailto Urgor](mailto:urgorka@gmail.com)

INSPIRATION
-----------

Cosy or minimalistic web interfaces that i prefere to inherit:

- [workflowy.com](http://workflowy.com)
- [Jira](https://ru.atlassian.com/software/jira)
- [tiddlyspot.com/](http://tiddlyspot.com/)

LICENSE
-------

Copyleft. Use it as you want (if you want).
