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
