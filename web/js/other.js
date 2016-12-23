LS = {
	pockets: [],
	settings : {
		pocketVisible: true
	},
	init: function() {
		// localStorage.removeItem('pocket');
		if (!localStorage.getItem('pockets') || !localStorage.getItem('settings')) {
			// localstorage init
			this.pockets = [{},{},{},{},{}];
			this.save();
		} else {
			this.pockets = JSON.parse( localStorage.getItem('pockets') );
			this.settings = JSON.parse( localStorage.getItem('settings') );
		}
	},
	save: function() { // сохранение кармашков в локалсторадже
		localStorage.setItem('pockets', JSON.stringify(this.pockets));
		localStorage.setItem('settings', JSON.stringify(this.settings));
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
	},
	unsubscribe(event) {
		if (undefined === this.events[event]) delete this.events[event];
	}
}

popups = {
	albumId: 0,
	element: {},
	shortCode: false,
	showMenu: function(event, element) {
		// event.preventDefault();
		event.stopPropagation();
		navigation.hiddable = false;
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
		navigation.hiddable = true;
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
			case 'ppGlAd': popups.albumAdd(1); break;
			case 'ppGlAd2': popups.albumAdd(2); break;
			case 'ppGlAd3': popups.albumAdd(3); break;
			case 'ppGlAd4': popups.albumAdd(4); break;
			case 'ppGlDc': popups.albumDec(1); break;
			case 'ppGlDc2': popups.albumDec(2); break;
			case 'ppGlDc3': popups.albumDec(3); break;
			case 'ppGlDc4': popups.albumDec(4); break;
			case 'ppfSlAl': popups.selectAll(1); break;
			case 'ppfSlAl2': popups.selectAll(2); break;
			case 'ppfSlAl3': popups.selectAll(3); break;
			case 'ppfSlAl4': popups.selectAll(4); break;
			case 'ppfDsAl': popups.deselectAll(1); break;
			case 'ppfDsAl2': popups.deselectAll(2); break;
			case 'ppfDsAl3': popups.deselectAll(3); break;
			case 'ppfDsAl4': popups.deselectAll(4); break;
			case 'ppfGlDl': popups.downloadFolder(); break;
			case 'ppfDlLn': popups.showFolderDownloadLink(); break;
			case 'ppfAcsLn': popups.showFolderAccessLink(); break;
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
	albumAdd: function(pocketNo) {
		this.close();
		var _this = this;
		$.ajax({
			type: 'POST',
			url: 'http://'+config.baseUrl + '/album/add',
			data: {
				albumId: this.albumId,
				items: Pocket.prepareToSend(pocketNo)
			},
			cache: false
		})
		.done(function(response) {
			if (response.error) alert(response.msg);
			else {
				Pocket.clear(pocketNo);
				_this.albumId = 0;
				albumTreeTab.clickHandler({}, $(_this.element).parent());
				// alert('Изображения успешно добавлены');
			}
		})
		.fail(function(jqXHR, textStatus) {alert("Request failed: " + textStatus); });
	},
	albumDec: function(pocketNo) {
		this.close();
		var _this = this;
		$.ajax({
			type: 'POST',
			url: 'http://'+config.baseUrl + '/album/dec',
			data: {
				albumId: this.albumId,
				items: Pocket.prepareToSend(pocketNo)
			},
			cache: false
		})
		.done(function(response) {
			if (response.error) alert(response.msg);
			else {
				Pocket.clear(pocketNo);
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
		$('#mainBlock input.pocketChkbox[data-pocket-no="'+pocket+'"]').each(function(indx, element){
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
	},
	showFolderAccessLink: function() {
		this.close();
		$.ajax({
			type: 'GET',
			url: '/folder/accessLink/' + folderTab.folderId,
			cache: false
		})
		.done(function(response) {
			alert('http://' + config.baseUrl + '/#folder=' + response.data);
		})
		.fail(function(jqXHR, textStatus) {alert("Request failed: " + textStatus);});
	}
}

authHandler = {
	dependItems: ['.formCreateGalley', '#ppGlDc', '#ppGlDc2', '#ppGlDc3', '#ppGlDc4', '#ppGlAd', '#ppGlAd2', '#ppGlAd3', '#ppGlAd4', '#ppGlRm'],
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
				Pocket.toggleVisibility();
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
			case 49: // 1
				Pocket.keyPress(1);
				return false;
			case 50: // 2
				Pocket.keyPress(2);
				return false;
			case 51: // 3
				Pocket.keyPress(3);
				return false;
			case 52: // 4
				Pocket.keyPress(4);
				return false;
			case 77: // m
				navigation.toggleHiddable();
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
		// $('#navigation').attr('style', 'height:'+viewPort.h+'px');
		viewPort.maxWidth = viewPort.w-viewPort.marginHor;
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
		layout.jumpToImage(Pager.currentIndex);
	}
}

Pocket = {
	init: function(state) {
		if (!state) this.hide();
	},
	toggleVisibility: function() {
		if (LS.settings.pocketVisible) this.hide(); else this.show();
	},
	hide: function() {
		LS.settings.pocketVisible = false;
		LS.save();
		$('input.pocketChkbox').each(function(indx, element){ $(element).css('display', 'none'); });
	},
	show: function() {
		LS.settings.pocketVisible = true;
		LS.save();
		$('input.pocketChkbox').each(function(indx, element){ $(element).css('display', ''); });

	},
	clickOn: function (item) {
		item = $(item)
		var id = item.attr('data-image-id');
		var pocketNo = parseInt(item.attr('data-pocket-no'))
		if (item.prop('checked')) {
			LS.pockets[pocketNo][id] = id;
		} else {
			delete LS.pockets[pocketNo][id]
		}
		LS.save();
	},
	toPlace: function() { // расставить чекбоксы при обновлении старницы
		for (pocketNo = 1; pocketNo <= 4; pocketNo++) {
			for (var i in LS.pockets[pocketNo]) {
				$('#selected_' + i + '_' + pocketNo).prop('checked', true);
			}
		}
	},
	prepareToSend: function(pocketNo) {
		return LS.pockets[pocketNo];
	},
	countImg: function(pocketNo) {
		var c=0;
		for (i in LS.pockets[pocketNo]) c++;
		return c;
	},
	clear: function(pocketNo) {
		$('.pocket[name="selectedImage['+pocketNo+'][]"]').prop('checked', false);
		LS.pockets[pocketNo] = {};
		LS.save();
	},
	keyPress: function(pocketNo) {
		if ('undefined' === typeof xhrData.files || 'undefined' === typeof xhrData.files[i]) return false;
		$('#selected_' + xhrData.files[Pager.currentIndex].id + '_' + pocketNo).click();
	}
}

Pager = {
	init: function() {
		this.currentIndex = 0;
		this.total = 0;
	},
	setTotal: function() {
		if ('undefined' !== typeof xhrData.files) Pager.total = xhrData.files.length;
		$('#pagerTotal').html(Pager.total);
	},
	setCurrentIndex: function(idx) {
		Pager.currentIndex = idx;
		Pager.draw();
	},
	inc: function() {
		Pager.currentIndex++;
		Pager.draw();
	},
	dec: function() {
		Pager.currentIndex--;
		Pager.draw();
	},
	draw: function() {
		$('#pagerCurrent').html(Pager.currentIndex + 1);
	}
}
