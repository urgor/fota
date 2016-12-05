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
