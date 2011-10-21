var swf_width=320
var swf_height=250
var config='5|0xffffff|0x000000|50|0xffffff|0x0099ff|0x000000'
// config 设置分别为: 自动播放时间(秒)|文字颜色|文字背景色|文字背景透明度|按键数字色|当前按键色|普通按键色

var files = '';
var links = '';
var texts = '';
var xsImg = document.getElementById("slidedata");
var j = 0;var k = 0;
for(var i in xsImg.childNodes) {
	var a = xsImg.childNodes[i];
	if(a.title) {
		if(a.title == 'img') {
			if(files != '') files += '|';
			files += a.innerHTML;
		} else if(a.title == 'link') {
			if(links != '') links += '|';
			links += a.innerHTML;
		} else if(a.title == 'subject') {
			if(texts != '') texts += '|';
			texts += a.innerHTML;
		}
		j++;
		if(j == 3) {
			j = 0;
			k++;
		}
	}
}

links = links.replace(/\&amp;/g, '%26');
links=encodeURIComponent(links);
files=encodeURIComponent(files);

var content = '';
content += '<object classid="clsid:D27CDB6E-AE6D-11CF-96B8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0" width="' + swf_width + '" height="' + swf_height + '">';
content += '<param name="movie" value="eis_diystyle/09/eis_flash.swf" />';
content += '<param name="quality" value="high" />';
content += '<param name="menu" value="false" />';
content += '<param name=wmode value="opaque" />';
content += '<param name="FlashVars" value="config='+config+'&bcastr_flie='+files+'&bcastr_link='+links+'&bcastr_title='+texts+'" />';
content += '<embed src="/images/flashslide.swf" wmode="opaque" FlashVars="config='+config+'&bcastr_flie='+files+'&bcastr_link='+links+'&bcastr_title='+texts+'& menu="false" quality="high" width="'+ swf_width +'" height="'+ swf_height +'" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />';
content += '</object>';

document.getElementById("slidecontent").innerHTML = content;