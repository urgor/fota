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
	<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
	<script type="text/javascript" src="http://<?= Yii::$app->params['baseUrl']; ?>/js/main.js"></script>

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
<div id="navigation">
	<div id="logo">&nbsp;</div>
	<div id="toolbarBlock">
		<div class="button_tree" href="#folderTree" title="(T)ree of folders"></div>
		<div class="button_view" href="#layoutSelect" title="(O)ptions"></div>
		<div class="button_user" href="#userInfo" title="User"></div>
		<div class="button_albums" href="#albumTree" title="(A)lbums"></div>
		<div class="button_help" href="#helpSide" title="(H)elp"></div>
	</div>
	<div id="folderTree" style="display: none">
		<h5>Каталоги</h5>
	</div>
	<div id="layoutSelect" style="display: none">
		<h5>Опции</h5>
		<label><input class="noBullet" type="radio" name="layout" value="vList">
			<div class="boxButton" style="background: url('/images/sprite.png') 1px -80px;" title="Vertical list"></div></input>
		</label>
		<label><input class="noBullet" type="radio" name="layout" value="hList">
			<div class="boxButton" style="background: url('/images/sprite.png') -20px -80px;" title="Horizontal list"></div></input>
		</label>
		<label><input class="noBullet" type="radio" name="layout" value="tile">
			<div class="boxButton" style="background: url('/images/sprite.png') -60px -80px;" title="Horizontal list"></div></input>
		</label>
		<label><input class="noBullet" type="radio" name="layout" value="single">
			<div class="boxButton" style="background: url('/images/sprite.png') -40px -80px;" title="Single image"></div></input>
		</label>
	</div>
	<div id="userInfo" style="display: none">
		<h5>Авторизация</h5>
		<?php $this->beginContent('@app/views/face/'.(Yii::$app->user->isGuest ? 'anonymous' : 'authentificated').'.php'); $this->endContent(); ?>
	</div>
	<div id="albumTree" style="display: none">
		<h5>Альбомы</h5>
		<form class="formCreateGalley" action="/album/create" method="POST" autocomplete="off" onsubmit="return formCreateGalley(); return false;">
			<input type="text" name="name" size="15" hint="название альбома" title="название нового альбома" class="hinting" style="width: 143px" /><input id="buttonAlbumAdd" type="button" value="" title="Создать альбом" onclick="return formCreateGalley(); return false;" />
		</form>
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
		<input class="pocket" type="checkbox" id="" name="selectedImage[1][]" value="1" />
		<img class="mainImage" src="" />
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
		<img class="tileImage" src="" /><input class="pocket" type="checkbox" id="" name="selectedImage[1][]" value="1" />
	</div>
</div>
<div id="albumPopupMenu" class="popupMenu"><ul>
	<li id="ppGlSh">Шорт код и ссылка</li>
	<li id="ppGlDl">Скачать альбом</li>
	<li id="ppGlRm">Удалить альбом</li>
	<li id="ppGlAd">Добавить в альбом</li>
	<li id="ppGlDc">Удалить из альбома</li>
</ul></div>
<div id="folderPopupMenu" class="popupMenu"><ul>
	<li id="ppfSlAl">Все в 1й карман</li>
	<li id="ppfDsAl">Удалить из 1го кармана</li>
	<li id="ppfGlDl">Скачать</li>
	<li id="ppfDlLn">Ссылка для скачивания</li>

</ul></div>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>