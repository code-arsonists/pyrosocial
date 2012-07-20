/*
"type": "video",
"version": "1.0",
"provider_name": "Dailymotion",
"provider_url": "http://www.dailymotion.com",
"title": "Uffie - Difficult",
"author_name": "Uffie",
"author_url": "http://www.dailymotion.com/Uffie",
main {
	"width": 480, "height": 270,
	"html": "<iframe src=\"http: //www.dailymotion.com/embed/video/xf02xp\"
			 width=\"480\" height=\"270\" frameborder=\"0\"></iframe>",
}
thumb {
	"thumbnail_url": "http://ak2.static.dailymotion.com/static/video/540/891/...",
	"thumbnail_width": 426,
	"thumbnail_height": 240
}

oe_provider
oe_type
*/
var oEmbedProviders = {
	youtube: 'http://www.youtube.com/oembed?url={url}&format=json',

	flickr: 'http://www.flickr.com/services/oembed?url={url}&format=json&maxwidth=200',
	vimeo : 'http://vimeo.com/api/oembed.json?url={url}';
	dailymotion:'http://www.dailymotion.com/api/oembed?url={url}&format=json',
	bliptv: 'http://blip.tv/oembed/?url={url}&format=json',
	hulu: 'http://www.hulu.com/api/oembed?url={url}&format=json',
	viddler: 'http://lab.viddler.com/services/oembed/?url={url}&format=json',
	qik: 'http://qik.com/api/oembed?url={url}&format=json',
	revision3: 'http://revision3.com/api/oembed/?url={url}&format=json',
	scribd: 'http://www.scribd.com/services/oembed?url={url}&format=json',
	wordpresstv:'http://wordpress.tv/oembed/?url={url}&format=json'

}

function get_oEmbed_Data(provider, url)
{
	var oEmbedUrl = oEmbedProviders[provider].replace('{url}', url)
	
	$.getJSON(oEmbedUrl, function(r) {
		$.post('profiles/stream')
		var $cache_info = {
			r.type: "video",
			r.version: "1.0",
			r.provider_name: "Dailymotion",
			r.provider_url: "http://www.dailymotion.com",
			r.title: "Uffie - Difficult",
			r.author_name: "Uffie",
			r.author_url: "http://www.dailymotion.com/Uffie",
			r.width: 480, r.height: 270,
			r.html: "<iframe src=\"http: //www.dailymotion.com/embed/video/xf02xp\" width=\"480\" height=\"270\" frameborder=\"0\"></iframe>",
			r.thumbnail_url: "http://ak2.static.dailymotion.com/static/video/540/891/...",
			r.thumbnail_width: 426,
			r.thumbnail_height: 240
		}

		
	});
}



