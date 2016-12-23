<?php
use yii\helpers\Html;
use app\assets\AppAsset;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<? /* @var $this Controller */ ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="language" content="en" />
	<!--[if lt IE 8]>
	<link rel="stylesheet" type="text/css" href="http://<?= Yii::$app->params['baseUrl']; ?>/css/ie.css" media="screen, projection" />
	<![endif]-->
	<link rel="stylesheet" type="text/css" href="http://<?= Yii::$app->params['baseUrl'] ?>/css/main.css" />
	<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
	<script type="text/javascript" src="http://<?= Yii::$app->params['baseUrl']; ?>/js/main.js"></script>
	<script type="text/javascript" src="http://<?= Yii::$app->params['baseUrl']; ?>/js/tab.js"></script>
	<script type="text/javascript" src="http://<?= Yii::$app->params['baseUrl']; ?>/js/layout.js"></script>
	<script type="text/javascript" src="http://<?= Yii::$app->params['baseUrl']; ?>/js/functions.js"></script>
	<script type="text/javascript" src="http://<?= Yii::$app->params['baseUrl']; ?>/js/other.js"></script>

	<script type="text/javascript">
	config = {
		'baseUrl': '<?= Yii::$app->params['baseUrl']; ?>',
		// 'thumsPath': '< ?= Yii::$app->params['webThumbsPath']; ?>',
		'rootName': '<?= $root['name'] ?>',
		'rootId': '<?= $root['folder_id'] ?>',
		'thumbsPath': '<?= Yii::$app->params['thumbsPath']; ?>',
		'isGuest': <?= Yii::$app->user->isGuest ? 'true' : 'false' ?>
	};
	</script>
	<link href="/images/favicon32.png" rel="icon" type="image/png" />
	<title><?= Html::encode(Yii::$app->params['siteTitle']); ?></title>
</head>
<body>
<?php $this->beginBody() ?>
<div id="navigationGhost"></div>
<div id="navigation">
	<div id="logo">&nbsp;</div>
	<div id="toolbarBlock">
		<div id="btnTree"	class="toolbarItem" href="#folderTree"	 title="(T)ree of folders">Дерево каталогов</div>
		<div id="btnAlbums"	class="toolbarItem" href="#albumTree"	 title="(A)lbums">Альбомы</div>
		<div id="btnView"	class="toolbarItem" href="#layoutSelect" title="(O)ptions">Опции</div>
		<div id="btnUser"	class="toolbarItem" href="#userInfo"	 title="User">Автоирзация</div>
		<div id="btnHelp"	class="toolbarItem" href="#helpSide"	 title="(H)elp">Справка</div>
	</div>
	<div id="folderTree" style="display: none">
		<h5>Каталоги</h5>
	</div>
	<div id="layoutSelect" style="display: none">
		<h5>Опции</h5>
		<h6>Изображения</h6>
		<input class="noBullet" type="radio" name="layout" value="vList" id="optViewVert" /><label for="optViewVert">Вертикальным списком</label>
		<input class="noBullet" type="radio" name="layout" value="hList" id="optViewHor" /><label for="optViewHor">Горизонтальным списком</label>
		<input class="noBullet" type="radio" name="layout" value="tile" id="optViewTile" /><label for="optViewTile">Плиткой</label>
		<input class="noBullet" type="radio" name="layout" value="single" id="optViewSingle" /><label for="optViewSingle">Одно изображение</label>
	</div>
	<div id="userInfo" style="display: none">
		<h5>Авторизация</h5>
		<?php $this->beginContent('@app/views/authentificate/'.(Yii::$app->user->isGuest ? 'anonymous' : 'authentificated').'.php'); $this->endContent(); ?>
	</div>
	<div id="albumTree" style="display: none">
		<h5>Альбомы</h5>
		<h6>Создать новый</h6>
		<form class="formCreateGalley" action="/album/create" method="POST" autocomplete="off" onsubmit="return false;">
			<input type="text" name="name" size="15" hint="название альбома" title="название нового альбома" class="hinting inpAlbumName" />
			<a class="btnCreateAlbum" title="Создать альбом из кармана #1" onclick="return albumTreeTab.createGalley(1); return false;">1</a>
			<a class="btnCreateAlbum" title="Создать альбом из кармана #2" onclick="return albumTreeTab.createGalley(2); return false;">2</a>
			<a class="btnCreateAlbum" title="Создать альбом из кармана #3" onclick="return albumTreeTab.createGalley(3); return false;">3</a>
			<a class="btnCreateAlbum" title="Создать альбом из кармана #4" onclick="return albumTreeTab.createGalley(4); return false;">4</a>
		</form>
		<h6>Альбомы</h6>
		<div id="albumList"></div>
	</div>
	<div id="helpSide" style="display: none">
		<h5>Справка</h5>
		<h6>Навигация</h6>
		<b>&darr;</b>, <b>&rarr;</b> &#8212; Следующее изображение<br />
		<b>&uarr;</b>, <b>&larr;</b> &#8212; Предыдущее изображение<br />
		<h6>Вкладки</h6>
		<b>t</b> &#8212; Дерево каталогов (folder Tree)<br />
		<b>o</b> &#8212; Опции отображения (Options)<br />
		<b>h</b> &#8212; Справка (Help)<br />
		<b>a</b> &#8212; Альбомы (Albums)<br />
		<h6>Опции отображения</h6>
        <b>f</b> &#8212; Полноекранный режим (Fullscreen)<br />
		<b>i</b> &#8212; Переключние информации (3 режима) (Information)<br />
		<b>p</b> &#8212; Переключить отображение карманов (чекбоков) (Pockets)<br />
		<b>m</b> &#8212; Переключить режим скрытия меню (убирается или нет)<br />
		<h6>Карманы</h6>
        <b>1</b> &#8212; Поместить текущее изображение в 1й карман, либо удалить<br />
        <b>2</b> &#8212; Поместить текущее изображение в 2й карман, либо удалить<br />
        <b>3</b> &#8212; Поместить текущее изображение в 3й карман, либо удалить<br />
        <b>4</b> &#8212; Поместить текущее изображение в 4й карман, либо удалить<br />
	</div>
</div>
<!-- modal -->
<div id="popupWindow"><div id="popupClose"></div>
	<form id="shortcodeForm">
		<label for="width">width</label> <input class="hinting" type="text" name="width" value="800" />
		<!-- <label for="height">height</label> <input type="text" name="height" value="800" onchange="popups.shortCodeRenew();" /> -->
	</form>
	<textarea id="shortCodeValue" class="hinting">
&lt;link rel="stylesheet" href="http://<?= Yii::$app->params['baseUrl'] ?>/css/client.css"&gt;
&lt;script type='text/javascript' src='http://<?= Yii::$app->params['baseUrl'] ?>/js/client.js'&gt;&lt;/script&gt;
&lt;script type="text/javascript"&gt;$(document).ready(function() {fotaAlbum.init(%%INITOBJ%%);});&lt;/script&gt;&lt;div id="fotaAlbum"&gt;&lt;/div&gt;
</textarea>
	Ссылка на этот альбом <input type="text" name="linkForAlbum" value="#" class="hinting hint">
	<a href="#" readonly="readonly" name="linkForAlbum" title="Ссылка на этот альбом"><span class="linkImage"></a>
</div><div id="popupOverlay"></div>
<div id="mainBlock"><? //= $content; ?></div>
<div id="pager"><span id="pagerCurrent">1</span> из <span id="pagerTotal">0</span></div>
<div id='prototypes'>
	<li class="node"><div class="icon"></div><div class="folderMenuButton"></div><div class="name"></div><ul class="container"></ul></li>
	<div class="albumFolder"><div class="albumMenuButton"></div><div class="name"></div></div>
	<!--vertical list layout-->
	<div class="vListLayout" data-img-class="mainImage"></div>
	<div class="vListElement"><div class="marker"></div>
		<div class="imageWrapper">
			<input class="pocketChkbox first" type="checkbox" name="selectedImage[1][]" value="1" data-pocket-no="1" title="Карман #1" />
			<input class="pocketChkbox second" type="checkbox" name="selectedImage[2][]" value="1" data-pocket-no="2" title="Карман #2" />
			<input class="pocketChkbox third" type="checkbox" name="selectedImage[3][]" value="1" data-pocket-no="3" title="Карман #3" />
			<input class="pocketChkbox fourth" type="checkbox" name="selectedImage[4][]" value="1" data-pocket-no="4" title="Карман #4" />
			<img class="mainImage" src="" />
		</div>
		<div class="photoTitle" title="Название"></div><div class="photoDescription" title="Описание"></div><div class="photoKeywords" title="Ключевые слова"></div>
	</div>
	<!--horizontal list layout-->
	<div class="hListLayout" data-img-class="mainImage"></div>
	<div class="hListElement"><img class="mainImage" src="" /></div>
	<!-- single layout-->
	<div class="singleLayout" data-img-class="mainImage"></div>
	<div class="singleElement">
        <img class="mainImage" src="" />
        <div class="photoTitle" title="Название"></div><div class="photoDescription" title="Описание"></div><div class="photoKeywords" title="Ключевые слова"></div>
    </div>
	<!-- tile layout-->
	<div class="tileLayout" data-img-class="tileImage"></div>
	<div class="tileElement">
		<div class="imageWrapper tileWrapper">
			<input class="pocket" type="checkbox" id="" name="selectedImage[1][]" value="1" />
			<img class="tileImage" src="" />
		</div>
	</div>
</div>
<div id="albumPopupMenu" class="popupMenu"><ul>
	<li id="ppGlSh">Шорт код и ссылка</li>
	<li id="ppGlDl">Скачать альбом</li>
	<li id="ppGlRm">Удалить альбом</li>
	<li id="ppGlAd" class="topMargin">Добавить в альбом карман #1</li>
	<li id="ppGlAd2">Добавить в альбом карман #2</li>
	<li id="ppGlAd3">Добавить в альбом карман #3</li>
	<li id="ppGlAd4">Добавить в альбом карман #4</li>
	<li id="ppGlDc" class="topMargin">Удалить из альбома карман #1</li>
	<li id="ppGlDc2">Удалить из альбома карман #2</li>
	<li id="ppGlDc3">Удалить из альбома карман #3</li>
	<li id="ppGlDc4">Удалить из альбома карман #4</li>
</ul></div>
<div id="folderPopupMenu" class="popupMenu"><ul>
	<li id="ppfSlAl">Поместить все в карман #1</li>
	<li id="ppfSlAl">Поместить все в карман #2</li>
	<li id="ppfSlAl">Поместить все в карман #3</li>
	<li id="ppfSlAl">Поместить все в карман #4</li>
	<li id="ppfDsAl" class="topMargin">Убрать каталог из кармана #1</li>
	<li id="ppfDsAl2">Убрать каталог из кармана #2</li>
	<li id="ppfDsAl3">Убрать каталог из кармана #3</li>
	<li id="ppfDsAl4">Убрать каталог из кармана #4</li>
	<li id="ppfGlDl" class="topMargin">Скачать</li>
	<li id="ppfDlLn">Ссылка для скачивания</li>
	<li id="ppfAcsLn">Ссылка для доступа</li>

</ul></div>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>