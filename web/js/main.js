xhrData = {};

$(document).ready(function() {
	// layouts bind
	var proto = new layoutProto();
	vListLayout.prototype = proto;
	hListLayout.prototype = proto;
	singleLayout.prototype = proto;
	tileLayout.prototype = proto;
	layout = new vListLayout();
	viewPort.init();
	pager = new Pager();
	$('#layoutSelect input[name=layout]').on('change', function() {
		var ind = layout.currentImage;
		layout.destroy();
		eval('window.layout = new ' + $(this).attr('value') + 'Layout()');
		layout.currentImage = ind;
		layout.redrawMain();
	});
	$('#layoutSelect input[value=vList]').trigger('click');

	$('#folderTree').on('click', '.name', function(event) {folderTab.clickEvent(event, this);});
	$('#folderTree').on('click', '.folderMenuButton', function(event) {popups.showMenu(event, this);});
	$('#folderTree').on('mouseover', 'li.node', function(event) {folderTab.mouseOver(event, this); return false;});
	$('#folderTree').on('mouseout', 'li.node', function(event) {folderTab.mouseOut(event, this); return false;});
	$('#albumList').on('mouseover', '.albumFolder', function(event) {albumTreeTab.mouseOver(event, this);});
	$('#albumList').on('mouseout', '.albumFolder', function(event) {albumTreeTab.mouseOut(event, this);});
	$('#albumList').on('click', '.albumMenuButton', function(event) {popups.showMenu(event, this);});
	$('#albumList').on('click', '.albumFolder', function(event) {albumTreeTab.clickHandler(event, this);});
	$('.popupMenu').on('mouseover', 'li', function() {$(this).addClass('activeBack');});
	$('.popupMenu').on('mouseout', 'li', function() {$(this).removeClass('activeBack');});
	$('.popupMenu').on('click', 'li', function() {popups.clickHandler(this);});
	$('#popupOverlay').on('click', popups.close);
	$('#popupClose').on('click', popups.close);
	$('#shortcodeForm').on('change', 'input', function(){popups.shortCodeRenew();});
	// $('#ppGlSh').on('click', function() {popups.shortCodeRise(this);});
	// $('#ppGlDl').on('click', function() {popups.download(this);});
	// $('#ppGlRm').on('click', function() {popups.albumRemove(this);});
	// $('#ppGlAd').on('click', function() {popups.albumAdd(this);});
	// $('#ppGlDc').on('click', function() {popups.albumDec(this);});
	$('input, textarea').on('focus', kbd.unbindMe);
	$('input, textarea').on('blur', kbd.bindMe);
	$('#mainBlock').on('click', '.tileImage', function(event){layout.clickHandler(event, this)});

	$('.hinting').each(function(){
		var _this = $(this);
		if('password' === _this.attr('type')) _this.attr('type', 'text');
		_this.attr('value', _this.attr('hint'));
		_this.addClass('hint');
		_this.focus(function(){
	        if (_this.val() === _this.attr('hint')){
	            _this.attr('value', '');
				_this.removeClass('hint');
				if(_this.hasClass('password')) _this.attr('type', 'password');
	        }
	    }).blur(function(){
	        if (_this.val() === ''){
	            _this.attr('value', _this.attr('hint'));
				_this.addClass('hint');
				if(_this.hasClass('password')) _this.attr('type', 'text');
	        }
	    });
	});

	// main
	tabButton.init();
	authHandler.init();
	LS.init();
	kbd.init();
	information.init();
	pockets.init(LS.settings.pocketVisible);
});
////////////////////////////////////////////////////////////////////////////////
//									END ONREADY

////////////////////////////////////////////////////////////////////////////////
//									COMMON CLASSES
LS = {
	pockets: [],
	settings : {
		pocketVisible: true
	},
	init: function() {
		// localStorage.removeItem('pocket');
		if (!localStorage.getItem('pockets') || !localStorage.getItem('settings')) {
			// localstorage init
			this.pockets = [{}, {}];
			this.save();
		} else {
			this.pockets = JSON.parse( localStorage.getItem('pockets') );
			this.settings = JSON.parse( localStorage.getItem('settings') );
		}
	},
	save: function() { // сохранение кармашков в локалсторадже
		localStorage.setItem('pockets', JSON.stringify(this.pockets));
		localStorage.setItem('settings', JSON.stringify(this.settings));
	},

	//// Убрать нахуй ////

	clickOn: function (item) {
		item = $(item)
		var id = item.attr('id').substr(9);
		if (item.prop('checked')) {
			this.pockets[1][id] = id;
		} else {
			delete this.pockets[1][id]
		}
		this.save();
	},
	toPlace: function() { // расставить чекбоксы при обновлении старницы
		for (var i in this.pockets[1]) {
			$('#selected_'+i).prop('checked', true);
		}
	},
	prepareToSend: function() {
		return this.pockets[1];
	},
	countImg: function() {
		var c=0;
		for (i in this.pockets[1]) c++;
		return c;
	},
	clear: function(pocket) {
		$('.pocket[name="selectedImage['+pocket+'][]"]').prop('checked', false);
		this.pockets[pocket] = {};
		this.save();
	}
};

Observer = {
	events: {},
	notify: function(event) {
		if (undefined === this.events[event]) return false;
		for (i in this.events[event]) {
			this.events[event][i]();
		}
	},
	subscribe: function(event, func) {
		if (undefined === this.events[event]) this.events[event] = [];
		this.events[event].push(func);
	}
}

popups = {
	albumId: 0,
	element: {},
	shortCode: false,
	showMenu: function(event, element) {
		// event.preventDefault();
		event.stopPropagation();
		this.albumId = $(element).parent().attr('data-id');
		this.element = element;
		if($(element).parent().hasClass('albumFolder')) {
			$('#popupOverlay').css('display', 'block');
			$('#albumPopupMenu').css({display: 'block', top: event.clientY, left: event.clientX});
		} else {
			$('#popupOverlay').css('display', 'block');
			$('#folderPopupMenu').css({display: 'block', top: event.clientY, left: event.clientX});
		}
	},
	close: function() {
		$('#popupWindow').css('display', 'none');
		$('#popupOverlay').css('display', 'none');
		$('#albumPopupMenu').css('display', 'none');
		$('#folderPopupMenu').css('display', 'none');
	},
	clickHandler: function(obj) {
		switch ($(obj).attr('id')) {
			case 'ppGlSh': popups.shortCodeRise(); break;
			case 'ppGlDl': popups.downloadAlbum(); break;
			case 'ppGlRm': popups.albumRemove(); break;
			case 'ppGlAd': popups.albumAdd(); break;
			case 'ppGlDc': popups.albumDec(); break;
			case 'ppfSlAl': popups.selectAll(1); break;
			case 'ppfDsAl': popups.deselectAll(1); break;
			case 'ppfGlDl': popups.downloadFolder(); break;
			case 'ppfDlLn': popups.showFolderDownloadLink(); break;
		}
	},
	shortCodeRise: function() {
		// event.preventDefault(); // выключаем стандартную роль элемента
		$('#albumPopupMenu').css('display', 'none');
		$('#popupOverlay').css('display', 'block');
		$('#popupWindow').css('display', 'block');
		this.shortCodeRenew();

		var l = 'http://'+config.baseUrl+'/#album=%%ALBUMNO%%';
		$('#popupWindow input[name="linkForAlbum"]').val(l.replace('%%ALBUMNO%%', this.albumId));
		$('#popupWindow a[name="linkForAlbum"]').prop('href', l.replace('%%ALBUMNO%%', this.albumId))
	},
	shortCodeRenew: function() {
		var params = JSON.stringify({
			albumId: this.albumId,
			width: $('#shortcodeForm input[name="width"]').prop('value'),
			// heigth: $('#shortcodeForm input[name="height"]').prop('value'),
		});
		if (!this.shortCode) this.shortCode = $('#shortCodeValue').text();
		$('#shortCodeValue').text(this.shortCode.replace('%%INITOBJ%%', params) );
	},
	albumRemove: function () {
		this.close();
		if (!confirm('Удалить альбом "'+$(this.element).siblings('.name').text()+'" ?')) return false;
		var _this = this;
		$.ajax({
			type: 'POST',
			url: 'http://'+config.baseUrl + '/album/delete',
			data: {albumId: this.albumId},
			cache: false
		})
		.done(function(response) {
			if (response.error) alert(response.msg);
			else {
				$(_this.element).parent().remove();
				_this.albumId = 0;
			}
		})
		.fail(function(jqXHR, textStatus) {alert("Request failed: " + textStatus); });
	},
	albumAdd: function() {
		this.close();
		var _this = this;
		$.ajax({
			type: 'POST',
			url: 'http://'+config.baseUrl + '/album/add',
			data: {
				albumId: this.albumId,
				items: LS.prepareToSend()
			},
			cache: false
		})
		.done(function(response) {
			if (response.error) alert(response.msg);
			else {
				LS.clear(1);
				_this.albumId = 0;
				albumTreeTab.clickHandler({}, $(_this.element).parent());
				// alert('Изображения успешно добавлены');
			}
		})
		.fail(function(jqXHR, textStatus) {alert("Request failed: " + textStatus); });
	},
	albumDec: function() {
		this.close();
		var _this = this;
		$.ajax({
			type: 'POST',
			url: 'http://'+config.baseUrl + '/album/dec',
			data: {
				albumId: this.albumId,
				items: LS.prepareToSend()
			},
			cache: false
		})
		.done(function(response) {
			if (response.error) alert(response.msg);
			else {
				LS.clear(1);
				_this.albumId = 0;
				albumTreeTab.clickHandler({}, $(_this.element).parent());
				// alert('Изображения убраны из альбома');
			}
		})
		.fail(function(jqXHR, textStatus) {alert("Request failed: " + textStatus); });
	},
	downloadAlbum: function() {
		this.close();
		window.location = '/download/album/'+$(popups.element).parent().attr('data-id');
	},
	selectAll: function(pocket) {
		this.close();
		$('#mainBlock input[name="selectedImage['+pocket+'][]"]').each(function(indx, element){
			if (!$(element).prop('checked')) $(element).trigger('click');
		});
	},
	deselectAll: function(pocket) {
		this.close();
		$('#mainBlock input[name="selectedImage['+pocket+'][]"]').each(function(indx, element){
			if ($(element).prop('checked')) $(element).trigger('click');
		});
	},
	downloadFolder: function() {
		this.close();
		window.location = '/download/folder/'+folderTab.folderId;
	},
	showFolderDownloadLink: function() {
		this.close();
		alert('http://'+config.baseUrl+'/download/folder/'+folderTab.folderId);
	}
}

authHandler = {
	dependItems: ['.formCreateGalley', '#ppGlDc', '#ppGlAd', '#ppGlRm'],
	init: function() {
		if (config.isGuest) this.hide();
		// logout button bind
		$('#userInfo').on('click', '.ajax_button', function(){
			var button = $( this );
			$.ajax({type: 'POST', url: button.attr('href'), cache: false})
			.done(function(response) {authHandler.logout(button, response);})
			.fail(function(jqXHR, textStatus) {alert("Request failed: " + textStatus);});
			return false;
		});
	},
	formLogin: function() { // login button press
		kbd.bindMe();
		var form = $('.formLogin');
		$.ajax({
			type: form.attr('method'),
			url: form.attr('action'),
			cache: false,
			data: form.serialize()
		})
		.done(function(response) {
			authHandler.login(form, response);
		})
		.fail(function(jqXHR, textStatus) {alert("Request failed: " + textStatus);});
		return false;
	},
	login: function(obj, response){ // on login response
		if (response.error) {alert(response.msg); return false;}
		else {
			$('#userInfo').html(response.msg);
			for (i in this.dependItems) $(this.dependItems[i]).show();
			return true;
		}
	},

	logout: function(obj, response) { // on logout response
		$('#userInfo').html(response.msg);
		this.hide();
	},
	hide: function() {
		for (i in this.dependItems) $(this.dependItems[i]).hide();
	}
}

tabButton = {
	currentId: false,
	init: function() {
		var r = /^#album=(\d+).*$/;
		if (r.test(window.location.hash)) {
			res = r.exec(window.location.hash)
			Observer.subscribe('albumsTabInited', function(){
				var id = '#albumList #'+res[1];
				albumTreeTab.clickHandler('', $('.albumFolder[data-id="'+res[1]+'"]').get());
			});
		}

		// переключение табов
		$('#toolbarBlock > div').on('click', function(event) {tabButton.clickEvent(event, this);});
		this.clickEvent({}, $('#toolbarBlock > div.button_albums').get()); // show this tab by default

		albumTreeTab.init();
		folderTab.init();
	},
	clickEvent: function(event, element) {
		if (tabButton.currentId) {
			$(tabButton.currentId).hide();
			$('#toolbarBlock > div[href="' + tabButton.currentId + '"]').removeClass('active');
		}
		tabButton.currentId = $(element).attr('href');
		$(element).addClass('active');
		$(tabButton.currentId).show();
		var tabHandler = $(element).attr('href').substr(1)+'Tab';
		if (undefined !== window[tabHandler]) eval(tabHandler+'.init()');
	}
}

kbd = {
	init: function() {
		kbd.bindMe();
	},

	bindMe: function () {
		$('body').keydown(kbd.handler);
	},

	unbindMe: function() {
		$('body').unbind('keydown', kbd.handler);
	},

	handler: function(event) {
		// console.log('key = ' + event.key+ ' keyCode = ' + event.keyCode + ' char = ' + event.char);
		switch (event.keyCode) {
			case 40:
			case 39:
				bindMap('nextImage');
				return false;
			case 38:
			case 37:
				bindMap('previousImage');
				return false;
			case 27: // esc
				fullScreenOff();
				return false;
			case 80: // p
				pockets.toggleVisibility();
				return false;
			case 84: // t
				$('#toolbarBlock > div[href="#folderTree"]').trigger('click');
				return false;
			case 79: // o
				$('#toolbarBlock > div[href="#layoutSelect"]').trigger('click');
				return false;
			case 70: // f
				fullScreenOn();
				return false;
			case 72: // h
				$('#toolbarBlock > div[href="#helpSide"]').trigger('click');
				return false;
			case 65: // a
				$('#toolbarBlock > div[href="#albumTree"]').trigger('click');
				return false;
			case 73: // i
				information.toggle();
				return false;
		}
	}
}

		// resize main blocks
viewPort = {
	// говно мамонта. удалить нахуй
	h: 0,
	w: 0,
	maxWidth: 0,
	maxHeight: 0,
	naviWidth: 200,
	init: function() {
		$(window).on('resize', viewPort.resize);
		viewPort.resize();
		viewPort.marginVer = parseInt($('.mainImage').css('margin-top')) + parseInt($('.mainImage').css('margin-bottom'));
		viewPort.marginHor = parseInt($('.mainImage').css('margin-left')) + parseInt($('.mainImage').css('margin-right'));
		viewPort.resize();
	},
	resize: function() {
		viewPort.h = $(window).height();
		viewPort.w = $(window).width();
		$('#navigation').attr('style', 'height:'+viewPort.h+'px');
		// $('#mainBlock').attr('style', 'height: '+viewPort.h+'px; width: '+(viewPort.w-viewPort.naviWidth) + 'px');
		viewPort.maxWidth = viewPort.w-viewPort.naviWidth-viewPort.marginHor;
		viewPort.maxHeight = viewPort.h-viewPort.marginVer;
		if ('undefined' !== typeof xhrData.files) layout.onResizeViewPort();
	}
}

information = {
	state: 0,
	titleState: false,
	descriptionState: false,
	keywordsState: false,
	init: function() {
		this.state = 7;
		this.titleState = true;
		this.descriptionState = true;
		this.keywordsState = true;
	},
	toggle: function() {
		switch (this.state) {
			case 7:
				this.state = 0;
				$('.photoTitle').hide();
				$('.photoDescription').hide();
				$('.photoKeywords').hide();
				this.titleState = false;
				this.descriptionState = false;
				this.keywordsState = false;
				break;
			case 3:
				this.state = 7;
				$('.photoKeywords').show();
				this.keywordsState = true;
				break;
			case 1:
				this.state = 3;
				$('.photoDescription').show();
				this.descriptionState = true;
				break;
			case 0:
				this.state = 1;
				$('.photoTitle').show();
				this.titleState = true;
				break;
		}
		layout.onResizeViewPort();
		layout.jumpToImage(pager.currentIndex);
	}
}

pockets = {
	init: function(state) {
		if (!state) this.hide();
	},
	toggleVisibility: function() {
		if (LS.settings.pocketVisible) this.hide(); else this.show();
	},
	hide: function() {
		LS.settings.pocketVisible = false;
		LS.save(); // говнецо
		$('input.pocket').each(function(indx, element){ $(element).css('display', 'none'); });
	},
	show: function() {
		LS.settings.pocketVisible = true;
		LS.save(); // говнецо
		$('input.pocket').each(function(indx, element){ $(element).css('display', ''); });

	}
}

////////////////////////////////////////////////////////////////////////////////
//								END COMMON CLASSES

////////////////////////////////////////////////////////////////////////////////
//								TAB CLASSES

folderTab = {
	folderId: null,
	init: function() {
		var root = $('<ul class="container"></ul>').appendTo('#folderTree');
		folder = $('#prototypes li.node').clone().attr('id', config.rootId).addClass('root');
		folder.find('.name').html(config.rootName);
		folder.find('.icon').addClass('closed');
		folder.appendTo(root);
		// $('#folderTree #'+config.rootId+' .name').trigger('click');
	},
	load: function(folderId) {
		$.ajax({
			url: 'http://' + config.baseUrl + '/folder/' + folderId,
			// data: {folderId: folderId},
			success: function(data, textStatus, jqXHR) {
				if (data.error) { alert(data.msg); return false; }
				xhrData = data;
				$('#' + folderId + ' > .name').addClass('selected');
				folder = $('#prototypes li.node');
				var last;
				for (var i in data.folders) {
					sub = folder.clone();
					sub.attr('id', data.folders[i].id);
					sub.find('.name').html(data.folders[i].name);
					sub.find('.icon').addClass(data.folders[i].leaf ? 'leaf' : 'closed');

					last = sub.appendTo('#folderTree li#' + folderId + ' > ul.container');
				}
				$(last).addClass('last');
				layout.redrawMain();
			},
			dataType: 'json' //  Default: Intelligent Guess (xml, json, script, or html).
		});
	},
	clickEvent: function(event, element) {
		var folder = $(element).parent();
		this.folderId = folder.attr('id');
		var icon = $(element).siblings('.icon');
		$('#folderTree .selected').removeClass('selected');
		if (icon.hasClass('closed')) {
			icon.removeClass('closed')
			icon.addClass('opened')
			this.load(folder.attr('id'));
		} else if (icon.hasClass('opened')) { ///
			icon.removeClass('opened');
			icon.addClass('closed');
			folder.find('.container').html('');
		} else {
			this.load(folder.attr('id'));
		}
	},
	mouseOver: function(event, element) {
		if ($(element).prop('id') !== this.folderId) return false;
		$(element).find('.folderMenuButton:first').css('visibility', 'visible');
		return false;
	},
	mouseOut: function(event, element) {
		if ($(element).prop('id') !== this.folderId) return false;
		$(element).find('.folderMenuButton:first').css('visibility', 'hidden');
		return false;
	}
};

albumTreeTab = {
	isInit: false,
	selectedAlbum: 0,
	selectedElement: {},
	init: function() {
		if (this.isInit) return;
		this.isInit = true;
		this.load();
	},
	load: function() {
		$.ajax({
			url: '/album/getList',
			data: '',
			success: function(data, textStatus, jqXHR) {
				if (data.error) {
					alert(data.msg);
				} else {
					xhrData = data;
					$('#albumList').html('');
					if (0 < data.folders.length) {
						album = $('#prototypes .albumFolder');
						for (var i in data.folders) {
							sub = album.clone();
							sub.attr('data-id', data.folders[i].id);
							var albumNameNode = sub.find('.name')
							albumNameNode.html(data.folders[i].name);
							sub.appendTo('#albumList');
						}
					}
					Observer.notify('albumsTabInited');
				}
			},
			dataType: 'json' //  Default: Intelligent Guess (xml, json, script, or html).
		});
	},
	clickHandler: function (event, element) {
		var _this = this;
		if (0 !== this.selectedAlbum) {
			$(this.selectedElement).removeClass('selected');
			this.mouseOut({}, this.selectedElement);
		}
		this.selectedAlbum = $(element).attr('data-id');
		this.selectedElement = element;
		$.ajax({
			type: 'GET',
			url: 'http://'+config.baseUrl + '/album/getFiles/' + this.selectedAlbum,
			data: {},
			cache: false
		})
		.done(function(response) {
			if (response.error) alert(response.msg);
			else {
				xhrData = response;
				layout.redrawMain();
				$(element).addClass('selected');
				_this.mouseOver({}, element);
			}
		})
		.fail(function(jqXHR, textStatus) {alert("Request failed: " + textStatus); });
	},
	mouseOver: function(event, element) {
		// if ($(element).prop('id') === this.selectedAlbum) return false;
		$(element).find('.albumMenuButton').css('visibility', 'visible');
	},
	mouseOut: function(event, element) {
		// if ($(element).prop('id') === this.selectedAlbum) return false;
		$(element).find('.albumMenuButton').css('visibility', 'hidden');
	}
}
////////////////////////////////////////////////////////////////////////////////
//								END TAB LASSES

////////////////////////////////////////////////////////////////////////////////
//								LAYOUT CLASSES

function layoutProto() {
	pager.currentIndex = 0;
	var width = 0;
	this.redrawMain = function() {
		$('#mainBlock').html('');
		var layuot = $('#prototypes > .' + this.layoutView + 'Layout').clone().appendTo('#mainBlock');
		var imgClass = $('#prototypes > .' + this.layoutView + 'Layout').attr('data-img-class');
		var itemProto = $('#prototypes > .' + this.layoutView + 'Element').clone();
		this.layoutWidth = layuot.width();
		for (var i in xhrData.files) {
			item = itemProto.clone().attr('id', xhrData.files[i].thumb);
			var img = item.find('img.'+imgClass).attr('src', config.thumbsPath + xhrData.files[i].thumb.substr(0, 1) + '/' + xhrData.files[i].thumb + '.jpg')
					.attr('data-index_number', i);
			item.find('input.pocket').attr('id', 'selected_'+xhrData.files[i].id).on('click', function() { LS.clickOn(this); });
			item.appendTo(layuot);
			this.resizeElemets(item, img, xhrData.files[i]['info']);
		}
		pager.setTotal();
		LS.toPlace();
		this.jumpToImage(pager.currentIndex);
	};
	this.onResizeViewPort = function() {};
	this.resizeElemets = function(item, img, fileInfo) {
		// EXIF
		var h=0, w=0;
		if (fileInfo.exif_title && information.titleState)  {
			var exifTitleElement = item.find('div.photoTitle').html(fileInfo.exif_title);
			// w += exifTitleElement.outerWidth(true);
			h += exifTitleElement.outerHeight(true);
		}
		if (fileInfo.exif_description && information.descriptionState)  {
			var exifDescriptionElement = item.find('div.photoDescription').html(fileInfo.exif_description);
			// w += exifDescriptionElement.outerWidth(true);
			h += exifDescriptionElement.outerHeight(true);
		}
		if (fileInfo.exif_keywords && information.keywordsState)  {
			var exifKeywordsElement = item.find('div.photoKeywords').html(fileInfo.exif_keywords);
			// w += exifKeywordsElement.outerWidth(true);
			h += exifKeywordsElement.outerHeight(true);
		}
		img.css('max-height', viewPort.maxHeight-h+'px');
		img.css('max-width', this.layoutWidth - viewPort.marginHor +'px');
	};
	this.previousImage = function() {
		if (pager.currentIndex > 0) {
			pager.dec();
			this.jumpToImage(pager.currentIndex);
		}
		return false;
	};
	this.nextImage = function() {
		if (pager.currentIndex < xhrData.files.length - 1) {
			pager.inc();
			this.jumpToImage(pager.currentIndex);
		}
		return false;
	};
	this.destroy = function() {
		$('#mainBlock').unbind('scroll');
	};
}

function vListLayout() {
	var _this = this;
	this.layoutView = 'vList';
	this.jumpToImage = function(i) {
		// if ('undefined' === typeof xhrData.files || 'undefined' === typeof xhrData.files[i]) i=1;
		if ('undefined' === typeof xhrData.files || 'undefined' === typeof xhrData.files[i]) return false;
		var pos = getAbsolutePosition(document.getElementById(xhrData.files[i].thumb)).y;
		$('#mainBlock').animate({scrollTop: pos}, 0);
	};
	this.onScrollFunction = function() { // scroll main area
		// @TODO Оптимизировать. вызывается много раз при одном скролле при каждом перемещении блока
		var imgs = document.getElementsByTagName('img');
		for (var i = 0; i < imgs.length; i++) {
			var img = imgs[i];
			if (_this.isVisible(img)) {
				pager.setCurrentIndex(parseInt($(img).attr('data-index_number')));
				return true;
			}
		}
	};
	this.isVisible = function(elem) {
		var coords = getAbsolutePosition(elem);
		var windowTop = window.pageYOffset || document.documentElement.scrollTop;
		var windowBottom = windowTop + document.documentElement.clientHeight;
		coords.bottom = coords.top + elem.offsetHeight;
		// верхняя граница elem в пределах видимости
		// ИЛИ нижняя граница видима
		var topVisible = coords.top > windowTop && coords.top < windowBottom;
		var bottomVisible = coords.bottom < windowBottom && coords.bottom > windowTop;
		return topVisible || bottomVisible;
	};
	// При изменении размера окна пересчитать размер тумб
	this.onResizeViewPort = function() {
		$('#mainBlock .vListElement').each(function(i, element){
			var item = $(element);
			layout.resizeElemets(item, item.find('img'), xhrData.files[i]['info']);
		});
	};
	$('#mainBlock').on('scroll', this.onScrollFunction);
}

function hListLayout() {
	this.layoutView = 'hList';
	this.jumpToImage = function(i) {
		var pos = getAbsolutePosition(document.getElementById(xhrData.files[i].thumb)).x;
		$('#mainBlock').animate({scrollLeft: pos - 220}, 1000);
	};
}

function singleLayout() {
	this.layoutView = 'single';
	this.redrawMain = function() {
		$('#mainBlock').html('');
		this.layoutHtml = $('#prototypes >	.' + this.layoutView + 'Layout').clone().appendTo('#mainBlock');
		this.itemProto = $('#prototypes > .' + this.layoutView + 'Element').clone();
		pager.setTotal();
		LS.toPlace();
		this.jumpToImage(pager.currentIndex);
	};
	this.jumpToImage = function(i) {
		$(this.layoutHtml).html('');
		item = this.itemProto.clone().attr('id', xhrData.files[i].thumb);
		var img = item.find('img.mainImage').attr('src', config.thumbsPath + xhrData.files[i].thumb.substr(0, 1) + '/' + xhrData.files[i].thumb + '.jpg');
		item.appendTo(this.layoutHtml);
		this.resizeElemets(item, img, xhrData.files[i]['info']);
	};
}

function tileLayout() {
	var _this = this;
	this.layoutView = 'tile';
	this.jumpToImage = function(i) {};
	this.clickHandler = function(event, obj) {
		$('#layoutSelect input[value=single]').trigger('click');
		layout.jumpToImage($(obj).attr('data-index_number'));
	}
}

////////////////////////////////////////////////////////////////////////////////
//									END OF LAYOUTS

////////////////////////////////////////////////////////////////////////////////
//									FUNCTIONS
function fullScreenOn() {
	// https://developer.mozilla.org/en-US/docs/Web/Guide/API/DOM/Using_full_screen_mode
	if (document.documentElement.requestFullscreen) document.documentElement.requestFullscreen();
	else if (document.documentElement.msRequestFullscreen) document.documentElement.msRequestFullscreen();
	else if (document.documentElement.mozRequestFullScreen) document.documentElement.mozRequestFullScreen();
	else if (document.documentElement.webkitRequestFullscreen) document.documentElement.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
}
// function fullScreenOff() { // can`t invoke
// 	if (document.exitFullscreen) document.exitFullscreen();
// 	else if (document.msExitFullscreen) document.msExitFullscreen();
// 	else if (document.mozCancelFullScreen) document.mozCancelFullScreen();
// 	else if (document.webkitExitFullscreen) document.webkitExitFullscreen();
// }

function Pager() {
	this.currentIndex = 0;
	this.total = 0;
	var _this = this;
	this.setTotal = function() {
		if ('undefined' !== typeof xhrData.files) _this.total = xhrData.files.length;
		$('#pagerTotal').html(_this.total);
	};
	this.setCurrentIndex = function(idx) {
		_this.currentIndex = idx;
		_this.draw();
	}
	this.inc = function() {
		_this.currentIndex++;
		_this.draw();
	}
	this.dec = function() {
		_this.currentIndex--;
		_this.draw();
	}
	this.draw = function() {
		$('#pagerCurrent').html(_this.currentIndex + 1);
	};
}

function formCreateGalley() {
	var form = $('.formCreateGalley');
	if (!form.find('input[name="name"]').prop('value')) {alert('Необходимо название альбома'); return false;}
	if (0 === LS.countImg()) {alert('Нет отмеченных изображений'); return false;}
	var data = {
		items: LS.prepareToSend(),
		name: form.find('input[name="name"]').prop('value')
	};
	$.ajax({
		type: form.attr('method'),
		url: form.attr('action'),
		cache: false,
		data: data
	})
	.done(function(response) {
		if (response.error) {
			alert(response.msg);
		} else {
			LS.clear(1);
			albumTreeTab.load();
		}
	})
	.fail(function(jqXHR, textStatus) { alert("Request failed: " + textStatus);});
	return false;
};


function getAbsolutePosition(el, sub) {
	var r = {x: el.offsetLeft, y: el.offsetTop};
	if (el.offsetParent) {
		var tmp = getAbsolutePosition(el.offsetParent, true);
		r.x += tmp.x;
		r.y += tmp.y;
	}
	if ('undefined' === typeof sub) {
		c = $(el).offset();
		r.top = c.top;
		r.left = c.left;
	}
	return r;
}

function bindMap(func) {
	eval('layout.' + func + '()');
}
