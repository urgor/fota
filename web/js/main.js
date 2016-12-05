xhrData = {};

window.onload=function() {
	// layouts bind
	var proto = new layoutProto();
	vListLayout.prototype = proto;
	hListLayout.prototype = proto;
	singleLayout.prototype = proto;
	tileLayout.prototype = proto;
	layout = new vListLayout();
	viewPort.init();
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
	$('#navigationGhost').mouseenter(navigation.show); // over
	$('#navigation').mouseleave(navigation.hide); // out

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
	Pocket.init(LS.settings.pocketVisible);
	Pager.init();
}