fotaAlbum = {
	domain: 'fota.local',
	config: {},
	init: function(config) {
		this.config = config;
		var script = document.createElement('script');
		script.src = 'http://'+this.domain+'/album/getFilesJs?id='+this.config.albumId;
		document.getElementsByTagName('head')[0].appendChild(script);
	},
	draw: function(data) {
		data = $.parseJSON(data);
		if (data.error) {alert('FotaAlbum: '+data.msg); return;}
		var div = $('div#fotaAlbum');
		div.css('width', this.config.width);
		for(i in data.files) {
			var img = document.createElement('img');
			$(img).prop('src', 'http://'+this.domain+'/images/thumbs/' + data.files[i].thumb.substr(0, 1) + '/' + data.files[i].thumb + '.jpg');
			div.append(img);
		}
		var info = document.createElement('div');
		$(info).addClass('albumInfo');
		$(info).html('<span>Альбом <a href="http://'+this.domain+'/#album='+this.config.albumId+'_'+data.folders[0].name+'">'+data.folders[0].name+'</a> на '+this.domain+'</span>');
		div.append(info);
	}
}