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
	},
	this.resizeElemets = function(item, img, fileInfo) {
		var h=300, w=300;
		img.css('max-height', h + 'px');
		img.css('max-width', w + 'px');
	};
}