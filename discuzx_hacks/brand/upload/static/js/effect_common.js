
/**
 *      [品牌空間] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: effect_common.js 3776 2010-07-16 08:21:35Z yexinhao $
 */

//滾動
function simplescroll(c, config) {
	this.config = config ? config : {start_delay:0000, speed: 23, delay:3000, direction:1 , scrollItemCount:1, movecount:1};
	this.container = document.getElementById(c);
	this.pause = false;
	var _this = this;
	
	this.init = function() {
		var d = _this.container;
		_this.scrollTimeId = null;
		if(_this.config.direction == 2 || _this.config.direction == 4){
			var di = document.createElement("div");
			var size = d.getElementsByTagName('li').length;
			var _width = d.getElementsByTagName('li')[0].offsetWidth;
			var _height = d.getElementsByTagName('li')[0].offsetHeight;
			di.innerHTML  = d.innerHTML;
			d.innerHTML ="";
			di.style.width = size*_width+"px";
			d.appendChild(di);
		}
		setTimeout(_this.start,_this.config.start_delay);
	}
	
	this.start = function() {
		var d = _this.container;
		if(_this.config.direction == 1 || _this.config.direction == 3){
			var line_height = d.getElementsByTagName('li')[0].offsetHeight;
			if(d.scrollHeight-d.offsetHeight>=line_height) _this.scrollTimeId = setInterval(_this.scroll,_this.config.speed);	
		}else if(_this.config.direction == 2){
			d.scrollLeft = d.scrollWidth;
			var pre_width = d.getElementsByTagName('li')[0].offsetWidth;		
			_this.scrollTimeId = setInterval(_this.scroll,_this.config.speed);
		}else if(_this.config.direction == 4){
			var pre_width = d.getElementsByTagName('li')[0].offsetWidth;		
			_this.scrollTimeId = setInterval(_this.scroll,_this.config.speed);
		}
	};
	
	this.scroll = function() {
		if(_this.pause)return;
		var d = _this.container;
		switch (_this.config.direction){
			case 1:
				d.scrollTop+=2;
				var line_height = d.getElementsByTagName('li')[0].offsetHeight;
				if(d.scrollTop%(line_height*_this.config.scrollItemCount)<=1){
					if(_this.config.movecount != undefined)
						for(var i=0;i<_this.config.movecount;i++){d.appendChild(d.getElementsByTagName('li')[0]);}
					else for(var i=0;i<_this.config.scrollItemCount;i++){d.appendChild(d.getElementsByTagName('li')[0]);}
					d.scrollTop=0;
					clearInterval(_this.scrollTimeId);
					setTimeout(_this.start,_this.config.delay);
				}
			break;
			case 4:
				d.scrollLeft += 2;
				var pre_width = d.childNodes[0].getElementsByTagName('li')[0].offsetWidth;
				if(d.scrollLeft%(pre_width*_this.config.scrollItemCount)<=1){
					if(_this.config.movecount != undefined){
						for(var i=0;i<_this.config.movecount;i++){
							d.childNodes[0].appendChild(d.childNodes[0].getElementsByTagName('li')[0]);
						}
					}else{
						for(var i=0;i<_this.config.scrollItemCount;i++){
							d.childNodes[0].appendChild(d.childNodes[0].getElementsByTagName('li')[0]);
						}
					}
					d.scrollLeft=0;
					clearInterval(_this.scrollTimeId);
					setTimeout(_this.start,_this.config.delay);
				}
			break;
		}
		
	}
	
	this.container.onmouseover=function(){_this.pause = true;}
	this.container.onmouseout=function(){_this.pause = false;}
	
	this.init();
}

//複製URL地址
function setCopy(_sTxt){
	if(navigator.userAgent.toLowerCase().indexOf('ie') > -1) {
		clipboardData.setData('Text',_sTxt);
		alert ("網址「"+_sTxt+"」\n已經複製到您的剪貼板中\n您可以使用Ctrl+V快捷鍵粘貼到需要的地方");
	} else {
		prompt("請複製網站地址:",_sTxt); 
	}
}

//加入收藏
function addBookmark(title, url) {
	var ctrl = (navigator.userAgent.toLowerCase()).indexOf('mac') != -1 ? 'Command/Cmd' : 'CTRL'; 
	if (document.all){
		window.external.addFavorite(url,title);
	} else if (window.sidebar){
		window.sidebar.addPanel(title, url, "");
	}else {
		alert('\u60a8\u53ef\u4ee5\u5c1d\u8bd5\u901a\u8fc7\u5feb\u6377\u952e"' + ctrl + ' + D \u52a0\u5165\u5230\u6536\u85cf\u5939~');
	}
}
 
 //設為首頁
function sethomepage(obj,url){
	if(_dk.client.isIE){
		obj.style.behavior = "url(#default#homepage)";
		obj.setHomePage(url);
	} else {
		return false;
	}
}
