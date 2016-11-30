tabButton = {
	currentId: false,
	init: function() {
		var defaultTabName = 'button_albums';

		var r = /^#(album|folder)=(.+)$/;
		if (r.test(window.location.hash)) {
			res = r.exec(window.location.hash)
			if (res[1] == 'album') {
				Observer.subscribe('albumsTabInited', function(){
					var id = '#albumList #'+res[2];
					albumTreeTab.clickHandler('', $('.albumFolder[data-id="'+res[2]+'"]').get());
				});
			} else if (res[1] == 'folder') {
				defaultTabName = 'button_tree';
				Observer.subscribe('foldersTabInited', function(){
					console.log('folder tab inited observe');
					folderTab.accessHash = res[2];
					folderTab.clickEvent({}, $(folderTab.rootFolder).find('div.name'));
				});
			}
		}

		// переключение табов
		$('#toolbarBlock > div').on('click', function(event) {tabButton.clickEvent(event, this);});
		this.clickEvent({}, $('#toolbarBlock > div.' + defaultTabName).get()); // show this tab by default

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

folderTab = {
	folderId: null,
	rootFolder: null,
	accessHash: null,
	init: function() {
		var root = $('<ul class="container"></ul>').appendTo('#folderTree');
		this.rootFolder = $('#prototypes li.node').clone().attr('id', config.rootId).addClass('root');
		this.rootFolder.find('.name').html(config.rootName);
		this.rootFolder.find('.icon').addClass('closed');
		this.rootFolder.appendTo(root);
		Observer.notify('foldersTabInited');
		// $('#folderTree #'+config.rootId+' .name').trigger('click');
	},
	load: function() {
		$.ajax({
			url: 'http://' + config.baseUrl + '/folder/' + this.folderId,
			data: this.accessHash ? {accessHash: this.accessHash} : null,
			success: function(data) {
				folderTab.render(data);
			},
			dataType: 'json' //  Default: Intelligent Guess (xml, json, script, or html).
		});
	},
	render: function(data) {
		if (data.error) { alert(data.msg); return false; }
		xhrData = data;
		$('#' + folderTab.folderId + ' > .name').addClass('selected');
		folder = $('#prototypes li.node');
		var last;
		for (var i in data.folders) {
			sub = folder.clone();
			sub.attr('id', data.folders[i].id);
			sub.find('.name').html(data.folders[i].name);
			sub.find('.icon').addClass(data.folders[i].leaf ? 'leaf' : 'closed');

			last = sub.appendTo('#folderTree li#' + folderTab.folderId + ' > ul.container');
		}
		$(last).addClass('last');
		layout.redrawMain();
		// This is for rendering sub folders and files
		if (1 == Object.keys(data.folders).length && (data.folders[0].folders || data.folders[0].files)) {
			folderTab.openFolder(sub.find('.name'));
			folderTab.render(data.folders[0]);
		}
	},
	clickEvent: function(event, element) {
		$open = this.openFolder(element);
		if ($open) this.load(this);
	},
	openFolder(element) {
		var folder = $(element).parent();
		this.folderId = folder.attr('id');
		var icon = $(element).siblings('.icon');
		$('#folderTree .selected').removeClass('selected');
		if (icon.hasClass('closed')) {
			icon.removeClass('closed');
			icon.addClass('opened');
			return true;
		} else if (icon.hasClass('opened')) { ///
			icon.removeClass('opened');
			icon.addClass('closed');
			folder.find('.container').html('');
			return false;
		} else {
			return true;
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