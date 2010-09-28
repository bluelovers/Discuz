/*
	[Discuz!] (C)2001-2009 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: common.js 17196 2010-09-26 06:27:21Z monkey $
*/

var BROWSER = {};
var USERAGENT = navigator.userAgent.toLowerCase();
browserVersion({'ie':'msie','firefox':'','chrome':'','opera':'','safari':'','mozilla':'','webkit':'','maxthon':'','qq':'qqbrowser'});
if(BROWSER.safari) {
	BROWSER.firefox = true;
}
BROWSER.opera = BROWSER.opera ? opera.version() : 0;

HTMLNODE = document.getElementsByTagName('head')[0].parentNode;
if(BROWSER.ie) {
	HTMLNODE.className = 'ie_all ie' + parseInt(BROWSER.ie);
}

var CSSLOADED = [];
var JSMENU = [];
JSMENU['active'] = [];
JSMENU['timer'] = [];
JSMENU['drag'] = [];
JSMENU['layer'] = 0;
JSMENU['zIndex'] = {'win':200,'menu':300,'dialog':400,'prompt':500};
JSMENU['float'] = '';
var AJAX = [];
AJAX['url'] = [];
AJAX['stack'] = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
var CURRENTSTYPE = null;
var discuz_uid = isUndefined(discuz_uid) ? 0 : discuz_uid;
var creditnotice = isUndefined(creditnotice) ? '' : creditnotice;
var cookiedomain = isUndefined(cookiedomain) ? '' : cookiedomain;
var cookiepath = isUndefined(cookiepath) ? '' : cookiepath;

var DISCUZCODE = [];
DISCUZCODE['num'] = '-1';
DISCUZCODE['html'] = [];

var USERABOUT_BOX = true;
var USERCARDST = null;
var CLIPBOARDSWFDATA = '';
var NOTICETITLE = [];

if(BROWSER.firefox && window.HTMLElement) {
	HTMLElement.prototype.__defineSetter__('outerHTML', function(sHTML) {
			var r = this.ownerDocument.createRange();
		r.setStartBefore(this);
		var df = r.createContextualFragment(sHTML);
		this.parentNode.replaceChild(df,this);
		return sHTML;
	});

	HTMLElement.prototype.__defineGetter__('outerHTML', function() {
		var attr;
		var attrs = this.attributes;
		var str = '<' + this.tagName.toLowerCase();
		for(var i = 0;i < attrs.length;i++){
			attr = attrs[i];
			if(attr.specified)
			str += ' ' + attr.name + '="' + attr.value + '"';
		}
		if(!this.canHaveChildren) {
			return str + '>';
		}
		return str + '>' + this.innerHTML + '</' + this.tagName.toLowerCase() + '>';
		});

	HTMLElement.prototype.__defineGetter__('canHaveChildren', function() {
		switch(this.tagName.toLowerCase()) {
			case 'area':case 'base':case 'basefont':case 'col':case 'frame':case 'hr':case 'img':case 'br':case 'input':case 'isindex':case 'link':case 'meta':case 'param':
			return false;
			}
		return true;
	});
}

function $(id) {
	return !id ? null : document.getElementById(id);
}

function $C(classname, ele, tag) {
	var returns = [];
	ele = ele || document;
	tag = tag || '*';
	if(ele.getElementsByClassName) {
		var eles = ele.getElementsByClassName(classname);
		if(tag != '*') {
			for (var i = 0, L = eles.length; i < L; i++) {
				if (eles[i].tagName.toLowerCase() == tag.toLowerCase()) {
						returns.push(eles[i]);
				}
			}
		} else {
			returns = eles;
		}
	}else {
		eles = ele.getElementsByTagName(tag);
		var pattern = new RegExp("(^|\\s)"+classname+"(\\s|$)");
		for (i = 0, L = eles.length; i < L; i++) {
				if (pattern.test(eles[i].className)) {
						returns.push(eles[i]);
				}
		}
	}
	return returns;
}

function _attachEvent(obj, evt, func, eventobj) {
	eventobj = !eventobj ? obj : eventobj;
	if(obj.addEventListener) {
		obj.addEventListener(evt, func, false);
	} else if(eventobj.attachEvent) {
		obj.attachEvent('on' + evt, func);
	}
}

function _detachEvent(obj, evt, func, eventobj) {
	eventobj = !eventobj ? obj : eventobj;
	if(obj.removeEventListener) {
		obj.removeEventListener(evt, func, false);
	} else if(eventobj.detachEvent) {
		obj.detachEvent('on' + evt, func);
	}
}

function browserVersion(types) {
	var other = 1;
	for(i in types) {
		var v = types[i] ? types[i] : i;
		if(USERAGENT.indexOf(v) != -1) {
			var re = new RegExp(v + '(\\/|\\s)([\\d\\.]+)', 'ig');
			var matches = re.exec(USERAGENT);
			var ver = matches != null ? matches[2] : 0;
			other = ver !== 0 && v != 'mozilla' ? 0 : other;
		}else {
			var ver = 0;
		}
		eval('BROWSER.' + i + '= ver');
	}
	BROWSER.other = other;
}

function getEvent() {
	if(document.all) return window.event;
	func = getEvent.caller;
	while(func != null) {
		var arg0 = func.arguments[0];
		if (arg0) {
			if((arg0.constructor  == Event || arg0.constructor == MouseEvent) || (typeof(arg0) == "object" && arg0.preventDefault && arg0.stopPropagation)) {
				return arg0;
			}
		}
		func=func.caller;
	}
	return null;
}

function isUndefined(variable) {
	return typeof variable == 'undefined' ? true : false;
}

function in_array(needle, haystack) {
	if(typeof needle == 'string' || typeof needle == 'number') {
		for(var i in haystack) {
			if(haystack[i] == needle) {
					return true;
			}
		}
	}
	return false;
}

function trim(str) {
	return (str + '').replace(/(\s+)$/g, '').replace(/^\s+/g, '');
}

function strlen(str) {
	return (BROWSER.ie && str.indexOf('\n') != -1) ? str.replace(/\r?\n/g, '_').length : str.length;
}

function mb_strlen(str) {
	var len = 0;
	for(var i = 0; i < str.length; i++) {
		len += str.charCodeAt(i) < 0 || str.charCodeAt(i) > 255 ? (charset == 'utf-8' ? 3 : 2) : 1;
	}
	return len;
}

function mb_cutstr(str, maxlen, dot) {
	var len = 0;
	var ret = '';
	var dot = !dot ? '...' : '';
	maxlen = maxlen - dot.length;
	for(var i = 0; i < str.length; i++) {
		len += str.charCodeAt(i) < 0 || str.charCodeAt(i) > 255 ? (charset == 'utf-8' ? 3 : 2) : 1;
		if(len > maxlen) {
			ret += dot;
			break;
		}
		ret += str.substr(i, 1);
	}
	return ret;
}

function preg_replace(search, replace, str, regswitch) {
	var regswitch = !regswitch ? 'ig' : regswitch;
	var len = search.length;
	for(var i = 0; i < len; i++) {
		re = new RegExp(search[i], regswitch);
		str = str.replace(re, typeof replace == 'string' ? replace : (replace[i] ? replace[i] : replace[0]));
	}
	return str;
}

function htmlspecialchars(str) {
	return preg_replace(['&', '<', '>', '"'], ['&amp;', '&lt;', '&gt;', '&quot;'], str);
}

function display(id) {
	$(id).style.display = $(id).style.display == '' ? 'none' : '';
}

function checkall(form, prefix, checkall) {
	var checkall = checkall ? checkall : 'chkall';
	count = 0;
	for(var i = 0; i < form.elements.length; i++) {
		var e = form.elements[i];
		if(e.name && e.name != checkall && (!prefix || (prefix && e.name.match(prefix)))) {
			e.checked = form.elements[checkall].checked;
			if(e.checked) {
				count++;
			}
		}
	}
	return count;
}

function setcookie(cookieName, cookieValue, seconds, path, domain, secure) {
	var expires = new Date();
	if(cookieValue == '' || seconds < 0) {
		cookieValue = '';
		seconds = -2592000;
	}
	expires.setTime(expires.getTime() + seconds * 1000);
	domain = !domain ? cookiedomain : domain;
	path = !path ? cookiepath : path;
	document.cookie = escape(cookiepre + cookieName) + '=' + escape(cookieValue)
		+ (expires ? '; expires=' + expires.toGMTString() : '')
		+ (path ? '; path=' + path : '/')
		+ (domain ? '; domain=' + domain : '')
		+ (secure ? '; secure' : '');
}

function getcookie(name, nounescape) {
	name = cookiepre + name;
	var cookie_start = document.cookie.indexOf(name);
	var cookie_end = document.cookie.indexOf(";", cookie_start);
	if(cookie_start == -1) {
		return '';
	} else {
		var v = document.cookie.substring(cookie_start + name.length + 1, (cookie_end > cookie_start ? cookie_end : document.cookie.length));
		return !nounescape ? unescape(v) : v;
	}
}

function Ajax(recvType, waitId) {

	for(var stackId = 0; stackId < AJAX['stack'].length && AJAX['stack'][stackId] != 0; stackId++);
	AJAX['stack'][stackId] = 1;

	var aj = new Object();

	aj.loading = '請稍候...';
	aj.recvType = recvType ? recvType : 'XML';
	aj.waitId = waitId ? $(waitId) : null;

	aj.resultHandle = null;
	aj.sendString = '';
	aj.targetUrl = '';
	aj.stackId = 0;
	aj.stackId = stackId;

	aj.setLoading = function(loading) {
		if(typeof loading !== 'undefined' && loading !== null) aj.loading = loading;
	};

	aj.setRecvType = function(recvtype) {
		aj.recvType = recvtype;
	};

	aj.setWaitId = function(waitid) {
		aj.waitId = typeof waitid == 'object' ? waitid : $(waitid);
	};

	aj.createXMLHttpRequest = function() {
		var request = false;
		if(window.XMLHttpRequest) {
			request = new XMLHttpRequest();
			if(request.overrideMimeType) {
				request.overrideMimeType('text/xml');
			}
		} else if(window.ActiveXObject) {
			var versions = ['Microsoft.XMLHTTP', 'MSXML.XMLHTTP', 'Microsoft.XMLHTTP', 'Msxml2.XMLHTTP.7.0', 'Msxml2.XMLHTTP.6.0', 'Msxml2.XMLHTTP.5.0', 'Msxml2.XMLHTTP.4.0', 'MSXML2.XMLHTTP.3.0', 'MSXML2.XMLHTTP'];
			for(var i=0; i<versions.length; i++) {
				try {
					request = new ActiveXObject(versions[i]);
					if(request) {
						return request;
					}
				} catch(e) {}
			}
		}
		return request;
	};

	aj.XMLHttpRequest = aj.createXMLHttpRequest();
	aj.showLoading = function() {
		if(aj.waitId && (aj.XMLHttpRequest.readyState != 4 || aj.XMLHttpRequest.status != 200)) {
			aj.waitId.style.display = '';
			aj.waitId.innerHTML = '<span><img src="' + IMGDIR + '/loading.gif" class="vm"> ' + aj.loading + '</span>';
		}
	};

	aj.processHandle = function() {
		if(aj.XMLHttpRequest.readyState == 4 && aj.XMLHttpRequest.status == 200) {
			for(k in AJAX['url']) {
				if(AJAX['url'][k] == aj.targetUrl) {
					AJAX['url'][k] = null;
				}
			}
			if(aj.waitId) {
				aj.waitId.style.display = 'none';
			}
			if(aj.recvType == 'HTML') {
				aj.resultHandle(aj.XMLHttpRequest.responseText, aj);
			} else if(aj.recvType == 'XML') {
				if(!aj.XMLHttpRequest.responseXML || !aj.XMLHttpRequest.responseXML.lastChild || aj.XMLHttpRequest.responseXML.lastChild.localName == 'parsererror') {
					aj.resultHandle('<a href="' + aj.targetUrl + '" target="_blank" style="color:red">內部錯誤，無法顯示此內容</a>' , aj);
				} else {
					aj.resultHandle(aj.XMLHttpRequest.responseXML.lastChild.firstChild.nodeValue, aj);
				}
			}
			AJAX['stack'][aj.stackId] = 0;
		}
	};

	aj.get = function(targetUrl, resultHandle) {
		targetUrl = hostconvert(targetUrl);
		setTimeout(function(){aj.showLoading()}, 250);
		if(in_array(targetUrl, AJAX['url'])) {
			return false;
		} else {
			AJAX['url'].push(targetUrl);
		}
		aj.targetUrl = targetUrl;
		aj.XMLHttpRequest.onreadystatechange = aj.processHandle;
		aj.resultHandle = resultHandle;
		var attackevasive = isUndefined(attackevasive) ? 0 : attackevasive;
		var delay = attackevasive & 1 ? (aj.stackId + 1) * 1001 : 100;
		if(window.XMLHttpRequest) {
			setTimeout(function(){
			aj.XMLHttpRequest.open('GET', aj.targetUrl);
			aj.XMLHttpRequest.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
			aj.XMLHttpRequest.send(null);}, delay);
		} else {
			setTimeout(function(){
			aj.XMLHttpRequest.open("GET", targetUrl, true);
			aj.XMLHttpRequest.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
			aj.XMLHttpRequest.send();}, delay);
		}
	};
	aj.post = function(targetUrl, sendString, resultHandle) {
		targetUrl = hostconvert(targetUrl);
		setTimeout(function(){aj.showLoading()}, 250);
		if(in_array(targetUrl, AJAX['url'])) {
			return false;
		} else {
			AJAX['url'].push(targetUrl);
		}
		aj.targetUrl = targetUrl;
		aj.sendString = sendString;
		aj.XMLHttpRequest.onreadystatechange = aj.processHandle;
		aj.resultHandle = resultHandle;
		aj.XMLHttpRequest.open('POST', targetUrl);
		aj.XMLHttpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		aj.XMLHttpRequest.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
		aj.XMLHttpRequest.send(aj.sendString);
	};
	return aj;
}

function getHost(url) {
	var host = "null";
	if(typeof url == "undefined"|| null == url) {
		url = window.location.href;
	}
	var regex = /.*\:\/\/([^\/]*).*/;
	var match = url.match(regex);
	if(typeof match != "undefined" && null != match) {
		host = match[1];
	}
	return host;
}

function hostconvert(url) {
	if(!url.match(/^https?:\/\//)) url = SITEURL + url;
	var url_host = getHost(url);
	var cur_host = getHost().toLowerCase();
	if(url_host && cur_host != url_host) {
		url = url.replace(url_host, cur_host);
	}
	return url;
}

function newfunction(func) {
	var args = [];
	for(var i=1; i<arguments.length; i++) args.push(arguments[i]);
	return function(event) {
		doane(event);
		window[func].apply(window, args);
		return false;
	}
}

function evalscript(s) {
	if(s.indexOf('<script') == -1) return s;
	var p = /<script[^\>]*?>([^\x00]*?)<\/script>/ig;
	var arr = [];
	while(arr = p.exec(s)) {
		var p1 = /<script[^\>]*?src=\"([^\>]*?)\"[^\>]*?(reload=\"1\")?(?:charset=\"([\w\-]+?)\")?><\/script>/i;
		var arr1 = [];
		arr1 = p1.exec(arr[0]);
		if(arr1) {
			appendscript(arr1[1], '', arr1[2], arr1[3]);
		} else {
			p1 = /<script(.*?)>([^\x00]+?)<\/script>/i;
			arr1 = p1.exec(arr[0]);
			appendscript('', arr1[2], arr1[1].indexOf('reload=') != -1);
		}
	}
	return s;
}

function appendscript(src, text, reload, charset) {
	var id = hash(src + text);
	var evalscripts = [];
	if(!reload && in_array(id, evalscripts)) return;
	if(reload && $(id)) {
		$(id).parentNode.removeChild($(id));
	}

	evalscripts.push(id);
	var scriptNode = document.createElement("script");
	scriptNode.type = "text/javascript";
	scriptNode.id = id;
	scriptNode.charset = charset ? charset : (BROWSER.firefox ? document.characterSet : document.charset);
	try {
		if(src) {
			scriptNode.src = src;
		} else if(text){
			scriptNode.text = text;
		}
		$('append_parent').appendChild(scriptNode);
	} catch(e) {}
}

function stripscript(s) {
	return s.replace(/<script.*?>.*?<\/script>/ig, '');
}

function ajaxupdateevents(obj, tagName) {
	tagName = tagName ? tagName : 'A';
	var objs = obj.getElementsByTagName(tagName);
	for(k in objs) {
		var o = objs[k];
		ajaxupdateevent(o);
	}
}

function ajaxupdateevent(o) {
	if(typeof o == 'object' && o.getAttribute) {
		if(o.getAttribute('ajaxtarget')) {
			if(!o.id) o.id = Math.random();
			var ajaxevent = o.getAttribute('ajaxevent') ? o.getAttribute('ajaxevent') : 'click';
			var ajaxurl = o.getAttribute('ajaxurl') ? o.getAttribute('ajaxurl') : o.href;
			_attachEvent(o, ajaxevent, newfunction('ajaxget', ajaxurl, o.getAttribute('ajaxtarget'), o.getAttribute('ajaxwaitid'), o.getAttribute('ajaxloading'), o.getAttribute('ajaxdisplay')));
			if(o.getAttribute('ajaxfunc')) {
				o.getAttribute('ajaxfunc').match(/(\w+)\((.+?)\)/);
				_attachEvent(o, ajaxevent, newfunction(RegExp.$1, RegExp.$2));
			}
		}
	}
}

function ajaxget(url, showid, waitid, loading, display, recall) {
	waitid = typeof waitid == 'undefined' || waitid === null ? showid : waitid;
	var x = new Ajax();
	x.setLoading(loading);
	x.setWaitId(waitid);
	x.display = typeof display == 'undefined' || display == null ? '' : display;
	x.showId = $(showid);
	if(x.showId) x.showId.orgdisplay = typeof x.showId.orgdisplay === 'undefined' ? x.showId.style.display : x.showId.orgdisplay;

	if(url.substr(strlen(url) - 1) == '#') {
		url = url.substr(0, strlen(url) - 1);
		x.autogoto = 1;
	}

	var url = url + '&inajax=1&ajaxtarget=' + showid;
	x.get(url, function(s, x) {
		var evaled = false;
		if(s.indexOf('ajaxerror') != -1) {
			evalscript(s);
			evaled = true;
		}
		if(!evaled && (typeof ajaxerror == 'undefined' || !ajaxerror)) {
			if(x.showId) {
				x.showId.style.display = x.showId.orgdisplay;
				x.showId.style.display = x.display;
				x.showId.orgdisplay = x.showId.style.display;
				ajaxinnerhtml(x.showId, s);
				ajaxupdateevents(x.showId);
				if(x.autogoto) scroll(0, x.showId.offsetTop);
			}
		}

		ajaxerror = null;
		if(typeof recall == 'function') {
			recall();
		} else {
			eval(recall);
		}
		if(!evaled) evalscript(s);
	});
}

function ajaxpost(formid, showid, waitid, showidclass, submitbtn, recall) {
	var waitid = typeof waitid == 'undefined' || waitid === null ? showid : (waitid !== '' ? waitid : '');
	var showidclass = !showidclass ? '' : showidclass;
	var ajaxframeid = 'ajaxframe';
	var ajaxframe = $(ajaxframeid);
	var formtarget = $(formid).target;

	var handleResult = function() {
		var s = '';
		var evaled = false;

		showloading('none');
		try {
			if(BROWSER.ie) {
				s = $(ajaxframeid).contentWindow.document.XMLDocument.text;
			} else {
				if(BROWSER.safari > 0) {
					s = $(ajaxframeid).contentWindow.document.documentElement.firstChild.wholeText;
				} else {
					s = $(ajaxframeid).contentWindow.document.documentElement.firstChild.nodeValue;
				}
			}
		} catch(e) {
			s = '內部錯誤，無法顯示此內容';
		}

		if(s != '' && s.indexOf('ajaxerror') != -1) {
			evalscript(s);
			evaled = true;
		}
		if(showidclass) {
			$(showid).className = showidclass;
		}
		if(submitbtn) {
			submitbtn.disabled = false;
		}
		if(!evaled && (typeof ajaxerror == 'undefined' || !ajaxerror)) {
			ajaxinnerhtml($(showid), s);
		}
		ajaxerror = null;
		if($(formid)) $(formid).target = formtarget;
		if(typeof recall == 'function') {
			recall();
		} else {
			eval(recall);
		}
		if(!evaled) evalscript(s);
		ajaxframe.loading = 0;
		$('append_parent').removeChild(ajaxframe.parentNode);
	};
	if(!ajaxframe) {
		var div = document.createElement('div');
		div.style.display = 'none';
		div.innerHTML = '<iframe name="' + ajaxframeid + '" id="' + ajaxframeid + '" loading="1"></iframe>';
		$('append_parent').appendChild(div);
		ajaxframe = $(ajaxframeid);
	} else if(ajaxframe.loading) {
		return false;
	}

	_attachEvent(ajaxframe, 'load', handleResult);

	showloading();
	$(formid).target = ajaxframeid;
	var action = $(formid).getAttribute('action');
	action = hostconvert(action);
	$(formid).action = action.replace(/\&inajax\=1/g, '')+'&inajax=1';
	$(formid).submit();
	if(submitbtn) {
		submitbtn.disabled = true;
	}
	doane();
	return false;
}

function ajaxmenu(ctrlObj, timeout, cache, duration, pos, recall, idclass) {
	if(!ctrlObj.getAttribute('mid')) {
		var ctrlid = ctrlObj.id;
		if(!ctrlid) {
			ctrlObj.id = 'ajaxid_' + Math.random();
		}
	} else {
		var ctrlid = ctrlObj.getAttribute('mid');
		if(!ctrlObj.id) {
			ctrlObj.id = 'ajaxid_' + Math.random();
		}
	}
	var menuid = ctrlid + '_menu';
	var menu = $(menuid);
	if(isUndefined(timeout)) timeout = 3000;
	if(isUndefined(cache)) cache = 1;
	if(isUndefined(pos)) pos = '43';
	if(isUndefined(duration)) duration = timeout > 0 ? 0 : 3;
	if(isUndefined(idclass)) idclass = 'p_pop';
	var func = function() {
		showMenu({'ctrlid':ctrlObj.id,'menuid':menuid,'duration':duration,'timeout':timeout,'pos':pos,'cache':cache,'layer':2});
		if(typeof recall == 'function') {
			recall();
		} else {
			eval(recall);
		}
	};

	if(menu) {
		if(menu.style.display == '') {
			hideMenu(menuid);
		} else {
			func();
		}
	} else {
		menu = document.createElement('div');
		menu.id = menuid;
		menu.style.display = 'none';
		menu.className = idclass;
		menu.innerHTML = '<div class="p_opt" id="' + menuid + '_content"></div>';
		$('append_parent').appendChild(menu);
		var url = (!isUndefined(ctrlObj.href) ? ctrlObj.href : ctrlObj.attributes['href'].value);
		url += (url.indexOf('?') != -1 ? '&' :'?') + 'ajaxmenu=1';
		ajaxget(url, menuid + '_content', 'ajaxwaitid', '', '', func);
	}
	doane();
}

function hash(string, length) {
	var length = length ? length : 32;
	var start = 0;
	var i = 0;
	var result = '';
	filllen = length - string.length % length;
	for(i = 0; i < filllen; i++){
		string += "0";
	}
	while(start < string.length) {
		result = stringxor(result, string.substr(start, length));
		start += length;
	}
	return result;
}

function stringxor(s1, s2) {
	var s = '';
	var hash = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	var max = Math.max(s1.length, s2.length);
	for(var i=0; i<max; i++) {
		var k = s1.charCodeAt(i) ^ s2.charCodeAt(i);
		s += hash.charAt(k % 52);
	}
	return s;
}

function showloading(display, waiting) {
	var display = display ? display : 'block';
	var waiting = waiting ? waiting : '請稍候...';
	$('ajaxwaitid').innerHTML = waiting;
	$('ajaxwaitid').style.display = display;
}

function ajaxinnerhtml(showid, s) {
	if(showid.tagName != 'TBODY') {
		showid.innerHTML = s;
	} else {
		while(showid.firstChild) {
			showid.firstChild.parentNode.removeChild(showid.firstChild);
		}
		var div1 = document.createElement('DIV');
		div1.id = showid.id+'_div';
		div1.innerHTML = '<table><tbody id="'+showid.id+'_tbody">'+s+'</tbody></table>';
		$('append_parent').appendChild(div1);
		var trs = div1.getElementsByTagName('TR');
		var l = trs.length;
		for(var i=0; i<l; i++) {
			showid.appendChild(trs[0]);
		}
		var inputs = div1.getElementsByTagName('INPUT');
		var l = inputs.length;
		for(var i=0; i<l; i++) {
			showid.appendChild(inputs[0]);
		}
		div1.parentNode.removeChild(div1);
	}
}

function doane(event) {
	e = event ? event : window.event;
	if(!e) e = getEvent();
	if(e && BROWSER.ie) {
		e.returnValue = false;
		e.cancelBubble = true;
	} else if(e) {
		e.stopPropagation();
		e.preventDefault();
	}
}

function loadcss(cssname) {
	if(!CSSLOADED[cssname]) {
		if(!$('css_' + cssname)) {
			css = document.createElement('link');
			css.id = 'css_' + cssname,
			css.type = 'text/css';
			css.rel = 'stylesheet';
			css.href = 'data/cache/style_' + STYLEID + '_' + cssname + '.css?' + VERHASH;
			var headNode = document.getElementsByTagName("head")[0];
			headNode.appendChild(css);
		} else {
			$('css_' + cssname).href = 'data/cache/style_' + STYLEID + '_' + cssname + '.css?' + VERHASH;
		}
		CSSLOADED[cssname] = 1;
	}
}

function showMenu(v) {
	var ctrlid = isUndefined(v['ctrlid']) ? v : v['ctrlid'];
	var showid = isUndefined(v['showid']) ? ctrlid : v['showid'];
	var menuid = isUndefined(v['menuid']) ? showid + '_menu' : v['menuid'];
	var ctrlObj = $(ctrlid);
	var menuObj = $(menuid);
	if(!menuObj) return;
	var mtype = isUndefined(v['mtype']) ? 'menu' : v['mtype'];
	var evt = isUndefined(v['evt']) ? 'mouseover' : v['evt'];
	var pos = isUndefined(v['pos']) ? '43' : v['pos'];
	var layer = isUndefined(v['layer']) ? 1 : v['layer'];
	var duration = isUndefined(v['duration']) ? 2 : v['duration'];
	var timeout = isUndefined(v['timeout']) ? 250 : v['timeout'];
	var maxh = isUndefined(v['maxh']) ? 600 : v['maxh'];
	var cache = isUndefined(v['cache']) ? 1 : v['cache'];
	var drag = isUndefined(v['drag']) ? '' : v['drag'];
	var dragobj = drag && $(drag) ? $(drag) : menuObj;
	var fade = isUndefined(v['fade']) ? 0 : v['fade'];
	var cover = isUndefined(v['cover']) ? 0 : v['cover'];
	var zindex = isUndefined(v['zindex']) ? JSMENU['zIndex']['menu'] : v['zindex'];
	var winhandlekey = isUndefined(v['win']) ? '' : v['win'];
	zindex = cover ? zindex + 200 : zindex;
	if(typeof JSMENU['active'][layer] == 'undefined') {
		JSMENU['active'][layer] = [];
	}

	if(evt == 'click' && in_array(menuid, JSMENU['active'][layer]) && mtype != 'win') {
		hideMenu(menuid, mtype);
		return;
	}
	if(mtype == 'menu') {
		hideMenu(layer, mtype);
	}

	if(ctrlObj) {
		if(!ctrlObj.initialized) {
			ctrlObj.initialized = true;
			ctrlObj.unselectable = true;

			ctrlObj.outfunc = typeof ctrlObj.onmouseout == 'function' ? ctrlObj.onmouseout : null;
			ctrlObj.onmouseout = function() {
				if(this.outfunc) this.outfunc();
				if(duration < 3 && !JSMENU['timer'][menuid]) JSMENU['timer'][menuid] = setTimeout('hideMenu(\'' + menuid + '\', \'' + mtype + '\')', timeout);
			};

			ctrlObj.overfunc = typeof ctrlObj.onmouseover == 'function' ? ctrlObj.onmouseover : null;
			ctrlObj.onmouseover = function(e) {
				doane(e);
				if(this.overfunc) this.overfunc();
				if(evt == 'click') {
					clearTimeout(JSMENU['timer'][menuid]);
					JSMENU['timer'][menuid] = null;
				} else {
					for(var i in JSMENU['timer']) {
						if(JSMENU['timer'][i]) {
							clearTimeout(JSMENU['timer'][i]);
							JSMENU['timer'][i] = null;
						}
					}
				}
			};
		}
	}

	var dragMenu = function(menuObj, e, op) {
		e = e ? e : window.event;
		if(op == 1) {
			if(in_array(BROWSER.ie ? e.srcElement.tagName : e.target.tagName, ['TEXTAREA', 'INPUT', 'BUTTON', 'SELECT'])) {
				return;
			}
			JSMENU['drag'] = [e.clientX, e.clientY];
			JSMENU['drag'][2] = parseInt(menuObj.style.left);
			JSMENU['drag'][3] = parseInt(menuObj.style.top);
			document.onmousemove = function(e) {try{dragMenu(menuObj, e, 2);}catch(err){}};
			document.onmouseup = function(e) {try{dragMenu(menuObj, e, 3);}catch(err){}};
			doane(e);
		}else if(op == 2 && JSMENU['drag'][0]) {
			var menudragnow = [e.clientX, e.clientY];
			menuObj.style.left = (JSMENU['drag'][2] + menudragnow[0] - JSMENU['drag'][0]) + 'px';
			menuObj.style.top = (JSMENU['drag'][3] + menudragnow[1] - JSMENU['drag'][1]) + 'px';
			doane(e);
		}else if(op == 3) {
			JSMENU['drag'] = [];
			document.onmousemove = null;
			document.onmouseup = null;
		}
	};

	if(!menuObj.initialized) {
		menuObj.initialized = true;
		menuObj.ctrlkey = ctrlid;
		menuObj.mtype = mtype;
		menuObj.layer = layer;
		menuObj.cover = cover;
		if(ctrlObj && ctrlObj.getAttribute('fwin')) {menuObj.scrolly = true;}
		menuObj.style.position = 'absolute';
		menuObj.style.zIndex = zindex + layer;
		menuObj.onclick = function(e) {
			if(!e || BROWSER.ie) {
				window.event.cancelBubble = true;
				return window.event;
			} else {
				e.stopPropagation();
				return e;
			}
		};
		if(duration < 3) {
			if(duration > 1) {
				menuObj.onmouseover = function() {
					clearTimeout(JSMENU['timer'][menuid]);
					JSMENU['timer'][menuid] = null;
				};
			}
			if(duration != 1) {
				menuObj.onmouseout = function() {
					JSMENU['timer'][menuid] = setTimeout('hideMenu(\'' + menuid + '\', \'' + mtype + '\')', timeout);
				};
			}
		}
		if(cover) {
			var coverObj = document.createElement('div');
			coverObj.id = menuid + '_cover';
			coverObj.style.position = 'absolute';
			coverObj.style.zIndex = menuObj.style.zIndex - 1;
			coverObj.style.left = coverObj.style.top = '0px';
			coverObj.style.width = '100%';
			coverObj.style.height = document.body.offsetHeight + 'px';
			coverObj.style.backgroundColor = '#000';
			coverObj.style.filter = 'progid:DXImageTransform.Microsoft.Alpha(opacity=50)';
			coverObj.style.opacity = 0.5;
			$('append_parent').appendChild(coverObj);
			_attachEvent(window, 'load', function () {
				coverObj.style.height = document.body.offsetHeight + 'px';
			}, document);
		}
	}
	if(drag) {
		dragobj.style.cursor = 'move';
		dragobj.onmousedown = function(event) {try{dragMenu(menuObj, event, 1);}catch(e){}};
	}
	menuObj.style.display = '';
	if(cover) $(menuid + '_cover').style.display = '';
	if(fade) {
		var O = 0;
		var fadeIn = function(O) {
			if(O == 100) {
				clearTimeout(fadeInTimer);
				return;
			}
			menuObj.style.filter = 'progid:DXImageTransform.Microsoft.Alpha(opacity=' + O + ')';
			menuObj.style.opacity = O / 100;
			O += 10;
			var fadeInTimer = setTimeout(function () {
				fadeIn(O);
			}, 50);
		};
		fadeIn(O);
		menuObj.fade = true;
	} else {
		menuObj.fade = false;
	}
	if(pos != '*') {
		setMenuPosition(showid, menuid, pos);
	}
	if(BROWSER.ie && BROWSER.ie < 7 && winhandlekey && $('fwin_' + winhandlekey)) {
		$(menuid).style.left = (parseInt($(menuid).style.left) - parseInt($('fwin_' + winhandlekey).style.left)) + 'px';
		$(menuid).style.top = (parseInt($(menuid).style.top) - parseInt($('fwin_' + winhandlekey).style.top)) + 'px';
	}
	if(maxh && menuObj.scrollHeight > maxh) {
		menuObj.style.height = maxh + 'px';
		if(BROWSER.opera) {
			menuObj.style.overflow = 'auto';
		} else {
			menuObj.style.overflowY = 'auto';
		}
	}

	if(!duration) {
		setTimeout('hideMenu(\'' + menuid + '\', \'' + mtype + '\')', timeout);
	}

	if(!in_array(menuid, JSMENU['active'][layer])) JSMENU['active'][layer].push(menuid);
	menuObj.cache = cache;
	if(layer > JSMENU['layer']) {
		JSMENU['layer'] = layer;
	}
}

function setMenuPosition(showid, menuid, pos) {
	var showObj = $(showid);
	var menuObj = menuid ? $(menuid) : $(showid + '_menu');
	if(isUndefined(pos)) pos = '43';
	var basePoint = parseInt(pos.substr(0, 1));
	var direction = parseInt(pos.substr(1, 1));
	var sxy = 0, sx = 0, sy = 0, sw = 0, sh = 0, ml = 0, mt = 0, mw = 0, mcw = 0, mh = 0, mch = 0, bpl = 0, bpt = 0;

	if(!menuObj || (basePoint > 0 && !showObj)) return;
	if(showObj) {
		sxy = fetchOffset(showObj);
		sx = sxy['left'];
		sy = sxy['top'];
		sw = showObj.offsetWidth;
		sh = showObj.offsetHeight;
	}
	mw = menuObj.offsetWidth;
	mcw = menuObj.clientWidth;
	mh = menuObj.offsetHeight;
	mch = menuObj.clientHeight;

	switch(basePoint) {
		case 1:
			bpl = sx;
			bpt = sy;
			break;
		case 2:
			bpl = sx + sw;
			bpt = sy;
			break;
		case 3:
			bpl = sx + sw;
			bpt = sy + sh;
			break;
		case 4:
			bpl = sx;
			bpt = sy + sh;
			break;
	}
	switch(direction) {
		case 0:
			menuObj.style.left = (document.body.clientWidth - menuObj.clientWidth) / 2 + 'px';
			mt = (document.documentElement.clientHeight - menuObj.clientHeight) / 2;
			break;
		case 1:
			ml = bpl - mw;
			mt = bpt - mh;
			break;
		case 2:
			ml = bpl;
			mt = bpt - mh;
			break;
		case 3:
			ml = bpl;
			mt = bpt;
			break;
		case 4:
			ml = bpl - mw;
			mt = bpt;
			break;
	}
	var scrollTop = Math.max(document.documentElement.scrollTop, document.body.scrollTop);
	var scrollLeft = Math.max(document.documentElement.scrollLeft, document.body.scrollLeft);
	if(in_array(direction, [1, 4]) && ml < 0) {
		ml = bpl;
		if(in_array(basePoint, [1, 4])) ml += sw;
	} else if(ml + mw > scrollLeft + document.body.clientWidth && sx >= mw) {
		ml = bpl - mw;
		if(in_array(basePoint, [2, 3])) ml -= sw;
	}
	if(in_array(direction, [1, 2]) && mt < 0) {
		mt = bpt;
		if(in_array(basePoint, [1, 2])) mt += sh;
	} else if(mt + mh > scrollTop + document.documentElement.clientHeight && sy >= mh) {
		mt = bpt - mh;
		if(in_array(basePoint, [3, 4])) mt -= sh;
	}
	if(pos == '210') {
		ml += 69 - sw / 2;
		mt -= 5;
		if(showObj.tagName == 'TEXTAREA') {
			ml -= sw / 2;
			mt += sh / 2;
		}
	}
	if(direction == 0 || menuObj.scrolly) {
		if(BROWSER.ie && BROWSER.ie < 7) {
			if(direction == 0) mt += scrollTop;
		} else {
			if(menuObj.scrolly) mt -= scrollTop;
			menuObj.style.position = 'fixed';
		}
	}
	if(ml) menuObj.style.left = ml + 'px';
	if(mt) menuObj.style.top = mt + 'px';
	if(direction == 0 && BROWSER.ie && !document.documentElement.clientHeight) {
		menuObj.style.position = 'absolute';
		menuObj.style.top = (document.body.clientHeight - menuObj.clientHeight) / 2 + 'px';
	}
	if(menuObj.style.clip && !BROWSER.opera) {
		menuObj.style.clip = 'rect(auto, auto, auto, auto)';
	}
}

function hideMenu(attr, mtype) {
	attr = isUndefined(attr) ? '' : attr;
	mtype = isUndefined(mtype) ? 'menu' : mtype;
	if(attr == '') {
		for(var i = 1; i <= JSMENU['layer']; i++) {
			hideMenu(i, mtype);
		}
		return;
	} else if(typeof attr == 'number') {
		for(var j in JSMENU['active'][attr]) {
			hideMenu(JSMENU['active'][attr][j], mtype);
		}
		return;
	}else if(typeof attr == 'string') {
		var menuObj = $(attr);
		if(!menuObj || (mtype && menuObj.mtype != mtype)) return;
		clearTimeout(JSMENU['timer'][attr]);
		var hide = function() {
			if(menuObj.cache) {
				menuObj.style.display = 'none';
				if(menuObj.cover) $(attr + '_cover').style.display = 'none';
			}else {
				menuObj.parentNode.removeChild(menuObj);
				if(menuObj.cover) $(attr + '_cover').parentNode.removeChild($(attr + '_cover'));
			}
			var tmp = [];
			for(var k in JSMENU['active'][menuObj.layer]) {
				if(attr != JSMENU['active'][menuObj.layer][k]) tmp.push(JSMENU['active'][menuObj.layer][k]);
			}
			JSMENU['active'][menuObj.layer] = tmp;
		};
		if(menuObj.fade) {
			var O = 100;
			var fadeOut = function(O) {
				if(O == 0) {
					clearTimeout(fadeOutTimer);
					hide();
					return;
				}
				menuObj.style.filter = 'progid:DXImageTransform.Microsoft.Alpha(opacity=' + O + ')';
				menuObj.style.opacity = O / 100;
				O -= 10;
				var fadeOutTimer = setTimeout(function () {
					fadeOut(O);
				}, 50);
			};
			fadeOut(O);
		} else {
			hide();
		}
	}
}

function getCurrentStyle(obj, cssproperty, csspropertyNS) {
	if(obj.style[cssproperty]){
		return obj.style[cssproperty];
	}
	if (obj.currentStyle) {
		return obj.currentStyle[cssproperty];
	} else if (document.defaultView.getComputedStyle(obj, null)) {
		var currentStyle = document.defaultView.getComputedStyle(obj, null);
		var value = currentStyle.getPropertyValue(csspropertyNS);
		if(!value){
			value = currentStyle[cssproperty];
		}
		return value;
	} else if (window.getComputedStyle) {
		var currentStyle = window.getComputedStyle(obj, "");
		return currentStyle.getPropertyValue(csspropertyNS);
	}
}

function fetchOffset(obj, mode) {
	var left_offset = 0, top_offset = 0, mode = !mode ? 0 : mode;

	if(obj.getBoundingClientRect && !mode) {
		var rect = obj.getBoundingClientRect();
		var scrollTop = Math.max(document.documentElement.scrollTop, document.body.scrollTop);
		var scrollLeft = Math.max(document.documentElement.scrollLeft, document.body.scrollLeft);
		if(document.documentElement.dir == 'rtl') {
			scrollLeft = scrollLeft + document.documentElement.clientWidth - document.documentElement.scrollWidth;
		}
		left_offset = rect.left + scrollLeft - document.documentElement.clientLeft;
		top_offset = rect.top + scrollTop - document.documentElement.clientTop;
	}
	if(left_offset <= 0 || top_offset <= 0) {
		left_offset = obj.offsetLeft;
		top_offset = obj.offsetTop;
		while((obj = obj.offsetParent) != null) {
			position = getCurrentStyle(obj, 'position', 'position');
			if(position == 'relative') {
				continue;
			}
			left_offset += obj.offsetLeft;
			top_offset += obj.offsetTop;
		}
	}
	return {'left' : left_offset, 'top' : top_offset};
}

function showTip(ctrlobj) {
	if(!ctrlobj.id) {
		ctrlobj.id = 'tip_' + Math.random();
	}
	menuid = ctrlobj.id + '_menu';
	if(!$(menuid)) {
		var div = document.createElement('div');
		div.id = ctrlobj.id + '_menu';
		div.className = 'prmm up';
		div.style.display = 'none';
		div.innerHTML = '<div class="prmc"><ul><li>' + ctrlobj.getAttribute('tip') + '</li></ul></div>';
		$('append_parent').appendChild(div);
	}
	showMenu({'mtype':'prompt','ctrlid':ctrlobj.id,'pos':'210','duration':2,'timeout':250,'zindex':JSMENU['zIndex']['prompt']});
}

function showPrompt(ctrlid, evt, msg, timeout) {
	var menuid = ctrlid ? ctrlid + '_pmenu' : 'ntcwin';
	var duration = timeout ? 0 : 3;
	if($(menuid)) {
		$(menuid).parentNode.removeChild($(menuid));
	}
	var div = document.createElement('div');
	div.id = menuid;
	div.className = ctrlid ? 'prmm up' : 'ntcwin';
	div.style.display = 'none';
	$('append_parent').appendChild(div);
	if(ctrlid) {
		msg = '<div id="' + ctrlid + '_prompt" class="prmc"><ul><li>' + msg + '</li></ul></div>';
	} else {
		msg = '<table cellspacing="0" cellpadding="0" class="popupcredit"><tr><td class="pc_l">&nbsp;</td><td class="pc_c"><div class="pc_inner">' + msg +
			'</td><td class="pc_r">&nbsp;</td></tr></table>';
	}
	div.innerHTML = msg;
	if(ctrlid) {
		if($(ctrlid).evt !== false) {
			var prompting = function() {
				showMenu({'mtype':'prompt','ctrlid':ctrlid,'evt':evt,'menuid':menuid,'pos':'210'});
			};
			if(evt == 'click') {
				$(ctrlid).onclick = prompting;
			} else {
				$(ctrlid).onmouseover = prompting;
			}
		}
		showMenu({'mtype':'prompt','ctrlid':ctrlid,'evt':evt,'menuid':menuid,'pos':'210','duration':duration,'timeout':timeout,'zindex':JSMENU['zIndex']['prompt']});
		$(ctrlid).unselectable = false;
	} else {
		showMenu({'mtype':'prompt','pos':'00','menuid':menuid,'duration':duration,'timeout':timeout,'zindex':JSMENU['zIndex']['prompt']});
		$(menuid).style.top = (parseInt($(menuid).style.top) - 100) + 'px';
	}
}

function showCreditPrompt() {
	var notice = getcookie('creditnotice').split('D');
	var basev = getcookie('creditbase').split('D');
	var creditrule = decodeURI(getcookie('creditrule', 1)).replace(String.fromCharCode(9), ' ');
	if(!discuz_uid || notice.length < 2 || notice[9] != discuz_uid) {
		setcookie('creditnotice', '');
		setcookie('creditrule', '');
		return;
	}
	var creditnames = creditnotice.split(',');
	var creditinfo = [];
	var e;
	for(var i in creditnames) {
		e = creditnames[i].split('|');
		creditinfo[e[0]] = [e[1], e[2]];
	}
	creditShow(creditinfo, notice, basev, 0, 1, creditrule);
}
function creditShow(creditinfo, notice, basev, bk, first, creditrule) {
	var s = '', check = 0;
	for(i = 1; i <= 8; i++) {
		v = parseInt(Math.abs(parseInt(notice[i])) / 5) + 1;
		if(notice[i] !== '0' && creditinfo[i]) {
			s += '<span>' + creditinfo[i][0] + (notice[i] != 0 ? (notice[i] > 0 ? '<em>+' : '<em class="desc">') + notice[i] + '</em>' : '') + creditinfo[i][1] + '</span>';
		}
		if(notice[i] > 0) {
			notice[i] = parseInt(notice[i]) - v;
			basev[i] = parseInt(basev[i]) + v;
		} else if(notice[i] < 0) {
			notice[i] = parseInt(notice[i]) + v;
			basev[i] = parseInt(basev[i]) - v;
		}
		if($('hcredit_' + i)) {
			$('hcredit_' + i).innerHTML = basev[i];
		}
	}
	for(i = 1; i <= 8; i++) {
		if(notice[i] != 0) {
			check = 1;
		}
	}
	if(!s || first) {
		setcookie('creditnotice', '');
		setcookie('creditbase', '');
		setcookie('creditrule', '');
		if(!s) {
			return;
		}
	}
	if(!$('creditpromptdiv')) {
		showPrompt(null, null, '<div id="creditpromptdiv">' + (creditrule ? '<i>' + creditrule + '</i> ' : '') + s + '</div>', 0);
	} else {
		$('creditpromptdiv').innerHTML = s;
	}
	setTimeout(function () {hideMenu(1, 'prompt');$('append_parent').removeChild($('ntcwin'));}, 1500);
}

function showDialog(msg, mode, t, func, cover, funccancel, leftmsg, confirmtxt, canceltxt) {
	cover = isUndefined(cover) ? (mode == 'info' ? 0 : 1) : cover;
	leftmsg = isUndefined(leftmsg) ? '' : leftmsg;
	mode = in_array(mode, ['confirm', 'notice', 'info']) ? mode : 'alert';
	var menuid = 'fwin_dialog';
	var menuObj = $(menuid);
	confirmtxt = confirmtxt ? confirmtxt : '確定';
	canceltxt = canceltxt ? canceltxt : '取消';

	if(menuObj) hideMenu('fwin_dialog', 'dialog');
	menuObj = document.createElement('div');
	menuObj.style.display = 'none';
	menuObj.className = 'fwinmask';
	menuObj.id = menuid;
	$('append_parent').appendChild(menuObj);
	var s = '<table cellpadding="0" cellspacing="0" class="fwin"><tr><td class="t_l"></td><td class="t_c"></td><td class="t_r"></td></tr><tr><td class="m_l">&nbsp;&nbsp;</td><td class="m_c"><h3 class="flb"><em>';
	s += t ? t : '提示信息';
	s += '</em><span><a href="javascript:;" id="fwin_dialog_close" class="flbc" onclick="hideMenu(\'' + menuid + '\', \'dialog\')" title="關閉">關閉</a></span></h3>';
	if(mode == 'info') {
		s += msg ? msg : '';
	} else {
		s += '<div class="c altw"><div class="' + (mode == 'alert' ? 'alert_error' : 'alert_info') + '"><p>' + msg + '</p></div></div>';
		s += '<p class="o pns">' + (leftmsg ? '<span class="z xg1">' + leftmsg + '</span>' : '') + '<button id="fwin_dialog_submit" value="true" class="pn pnc"><strong>'+confirmtxt+'</strong></button>';
		s += mode == 'confirm' ? '<button id="fwin_dialog_cancel" value="true" class="pn" onclick="hideMenu(\'' + menuid + '\', \'dialog\')"><strong>'+canceltxt+'</strong></button>' : '';
		s += '</p>';
	}
	s += '</td><td class="m_r"></td></tr><tr><td class="b_l"></td><td class="b_c"></td><td class="b_r"></td></tr></table>';
	menuObj.innerHTML = s;
	if($('fwin_dialog_submit')) $('fwin_dialog_submit').onclick = function() {
		if(typeof func == 'function') func();
		else eval(func);
		hideMenu(menuid, 'dialog');
	};
	if($('fwin_dialog_cancel')) {
		$('fwin_dialog_cancel').onclick = function() {
			if(typeof funccancel == 'function') funccancel();
			else eval(funccancel);
			hideMenu(menuid, 'dialog');
		};
		$('fwin_dialog_close').onclick = $('fwin_dialog_cancel').onclick;
	}
	showMenu({'mtype':'dialog','menuid':menuid,'duration':3,'pos':'00','zindex':JSMENU['zIndex']['dialog'],'cache':0,'cover':cover});
}

function showWindow(k, url, mode, cache, menuv) {
	mode = isUndefined(mode) ? 'get' : mode;
	cache = isUndefined(cache) ? 1 : cache;
	var menuid = 'fwin_' + k;
	var menuObj = $(menuid);
	var drag = null;
	var loadingst = null;

	if(disallowfloat && disallowfloat.indexOf(k) != -1) {
		if(BROWSER.ie) url += (url.indexOf('?') != -1 ?  '&' : '?') + 'referer=' + escape(location.href);
		location.href = url;
		doane();
		return;
	}

	var fetchContent = function() {
		if(mode == 'get') {
			menuObj.url = url;
			url += (url.search(/\?/) > 0 ? '&' : '?') + 'infloat=yes&handlekey=' + k;
			url += cache == -1 ? '&t='+(+ new Date()) : '';
			ajaxget(url, 'fwin_content_' + k, null, '', '', function() {initMenu();show();});
		} else if(mode == 'post') {
			menuObj.act = $(url).action;
			ajaxpost(url, 'fwin_content_' + k, '', '', '', function() {initMenu();show();});
		}
		loadingst = setTimeout(function() {showDialog('', 'info', '<img src="' + IMGDIR + '/loading.gif"> 請稍候...')}, 500);
	};
	var initMenu = function() {
		clearTimeout(loadingst);
		var objs = menuObj.getElementsByTagName('*');
		var fctrlidinit = false;
		for(var i = 0; i < objs.length; i++) {
			if(objs[i].id) {
				objs[i].setAttribute('fwin', k);
			}
			if(objs[i].className == 'flb' && !fctrlidinit) {
				if(!objs[i].id) objs[i].id = 'fctrl_' + k;
				drag = objs[i].id;
				fctrlidinit = true;
			}
		}
	};
	var show = function() {
		hideMenu('fwin_dialog', 'dialog');
		v = {'mtype':'win','menuid':menuid,'duration':3,'pos':'00','zindex':JSMENU['zIndex']['win'],'drag':typeof drag == null ? '' : drag,'cache':cache};
		for(k in menuv) {
			v[k] = menuv[k];
		}
		showMenu(v);
	};

	if(!menuObj) {
		menuObj = document.createElement('div');
		menuObj.id = menuid;
		menuObj.className = 'fwinmask';
		menuObj.style.display = 'none';
		$('append_parent').appendChild(menuObj);
		menuObj.innerHTML = '<table cellpadding="0" cellspacing="0" class="fwin"><tr><td class="t_l"></td><td class="t_c" ondblclick="hideWindow(\'' + k + '\')"></td><td class="t_r"></td></tr><tr><td class="m_l" ondblclick="hideWindow(\'' + k + '\')">&nbsp;&nbsp;</td><td class="m_c" id="fwin_content_' + k + '">'
			+ '</td><td class="m_r" ondblclick="hideWindow(\'' + k + '\')"></td></tr><tr><td class="b_l"></td><td class="b_c" ondblclick="hideWindow(\'' + k + '\')"></td><td class="b_r"></td></tr></table>';
		if (mode == 'html') {
			$('fwin_content_' + k).innerHTML = url;
			initMenu();
			show();
		} else {
			fetchContent();
		}
	} else if((mode == 'get' && (url != menuObj.url || cache != 1)) || (mode == 'post' && $(url).action != menuObj.act)) {
		fetchContent();
	} else {
		show();
	}
	doane();
}
function hideWindow(k, all, clear) {
	all = isUndefined(all) ? 1 : all;
	clear = isUndefined(clear) ? 1 : clear;
	hideMenu('fwin_' + k, 'win');
	if(clear && $('fwin_' + k)) {
		$('append_parent').removeChild($('fwin_' + k));
	}
	if(all) {
		hideMenu();
	}
}

function AC_FL_RunContent() {
	var str = '';
	if(AC_DetectFlashVer(9,0,124)) {
		var ret = AC_GetArgs(arguments, "clsid:d27cdb6e-ae6d-11cf-96b8-444553540000", "application/x-shockwave-flash");
		if(BROWSER.ie && !BROWSER.opera) {
			str += '<object ';
			for (var i in ret.objAttrs) {
				str += i + '="' + ret.objAttrs[i] + '" ';
			}
			str += '>';
			for (var i in ret.params) {
				str += '<param name="' + i + '" value="' + ret.params[i] + '" /> ';
			}
			str += '</object>';
		} else {
			str += '<embed ';
			for (var i in ret.embedAttrs) {
				str += i + '="' + ret.embedAttrs[i] + '" ';
			}
			str += '></embed>';
		}
	} else {
		str = '此內容需要 Adobe Flash Player 9.0.124 或更高版本<br /><a href="http://www.adobe.com/go/getflashplayer/" target="_blank"><img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="下載 Flash Player" /></a>';
	}
	return str;
}
function AC_GetArgs(args, classid, mimeType) {
	var ret = new Object();
	ret.embedAttrs = new Object();
	ret.params = new Object();
	ret.objAttrs = new Object();
	for (var i = 0; i < args.length; i = i + 2){
		var currArg = args[i].toLowerCase();
		switch (currArg){
			case "classid":break;
			case "pluginspage":ret.embedAttrs[args[i]] = 'http://www.macromedia.com/go/getflashplayer';break;
			case "src":ret.embedAttrs[args[i]] = args[i+1];ret.params["movie"] = args[i+1];break;
			case "codebase":ret.objAttrs[args[i]] = 'http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0';break;
			case "onafterupdate":case "onbeforeupdate":case "onblur":case "oncellchange":case "onclick":case "ondblclick":case "ondrag":case "ondragend":
			case "ondragenter":case "ondragleave":case "ondragover":case "ondrop":case "onfinish":case "onfocus":case "onhelp":case "onmousedown":
			case "onmouseup":case "onmouseover":case "onmousemove":case "onmouseout":case "onkeypress":case "onkeydown":case "onkeyup":case "onload":
			case "onlosecapture":case "onpropertychange":case "onreadystatechange":case "onrowsdelete":case "onrowenter":case "onrowexit":case "onrowsinserted":case "onstart":
			case "onscroll":case "onbeforeeditfocus":case "onactivate":case "onbeforedeactivate":case "ondeactivate":case "type":
			case "id":ret.objAttrs[args[i]] = args[i+1];break;
			case "width":case "height":case "align":case "vspace": case "hspace":case "class":case "title":case "accesskey":case "name":
			case "tabindex":ret.embedAttrs[args[i]] = ret.objAttrs[args[i]] = args[i+1];break;
			default:ret.embedAttrs[args[i]] = ret.params[args[i]] = args[i+1];
		}
	}
	ret.objAttrs["classid"] = classid;
	if(mimeType) {
		ret.embedAttrs["type"] = mimeType;
	}
	return ret;
}
function AC_DetectFlashVer(reqMajorVer, reqMinorVer, reqRevision) {
	var versionStr = -1;
	if(navigator.plugins != null && navigator.plugins.length > 0 && (navigator.plugins["Shockwave Flash 2.0"] || navigator.plugins["Shockwave Flash"])) {
		var swVer2 = navigator.plugins["Shockwave Flash 2.0"] ? " 2.0" : "";
		var flashDescription = navigator.plugins["Shockwave Flash" + swVer2].description;
		var descArray = flashDescription.split(" ");
		var tempArrayMajor = descArray[2].split(".");
		var versionMajor = tempArrayMajor[0];
		var versionMinor = tempArrayMajor[1];
		var versionRevision = descArray[3];
		if(versionRevision == "") {
			versionRevision = descArray[4];
		}
		if(versionRevision[0] == "d") {
			versionRevision = versionRevision.substring(1);
		} else if(versionRevision[0] == "r") {
			versionRevision = versionRevision.substring(1);
			if(versionRevision.indexOf("d") > 0) {
				versionRevision = versionRevision.substring(0, versionRevision.indexOf("d"));
			}
		}
		versionStr = versionMajor + "." + versionMinor + "." + versionRevision;
	} else if(BROWSER.ie && !BROWSER.opera) {
		try {
			var axo = new ActiveXObject("ShockwaveFlash.ShockwaveFlash.7");
			versionStr = axo.GetVariable("$version");
		} catch (e) {}
	}
	if(versionStr == -1 ) {
		return false;
	} else if(versionStr != 0) {
		if(BROWSER.ie && !BROWSER.opera) {
			tempArray = versionStr.split(" ");
			tempString = tempArray[1];
			versionArray = tempString.split(",");
		} else {
			versionArray = versionStr.split(".");
		}
		var versionMajor = versionArray[0];
		var versionMinor = versionArray[1];
		var versionRevision = versionArray[2];
		return versionMajor > parseFloat(reqMajorVer) || (versionMajor == parseFloat(reqMajorVer)) && (versionMinor > parseFloat(reqMinorVer) || versionMinor == parseFloat(reqMinorVer) && versionRevision >= parseFloat(reqRevision));
	}
}

function simulateSelect(selectId, widthvalue) {
	var selectObj = $(selectId);
	if(!selectObj) return;
	if(BROWSER.other) {
		if(selectObj.getAttribute('change')) {
			selectObj.onchange = function () {eval(selectObj.getAttribute('change'));}
		}
		return;
	}
	var widthvalue = widthvalue ? widthvalue : 70;
	var defaultopt = selectObj.options[0] ? selectObj.options[0].innerHTML : '';
	var defaultv = '';
	var menuObj = document.createElement('div');
	var ul = document.createElement('ul');
	var handleKeyDown = function(e) {
		e = BROWSER.ie ? event : e;
		if(e.keyCode == 40 || e.keyCode == 38) doane(e);
	};
	var selectwidth = (selectObj.getAttribute('width', i) ? selectObj.getAttribute('width', i) : widthvalue) + 'px';
	var tabindex = selectObj.getAttribute('tabindex', i) ? selectObj.getAttribute('tabindex', i) : 1;

	for(var i = 0; i < selectObj.options.length; i++) {
		var li = document.createElement('li');
		li.innerHTML = selectObj.options[i].innerHTML;
		li.k_id = i;
		li.k_value = selectObj.options[i].value;
		if(selectObj.options[i].selected) {
			defaultopt = selectObj.options[i].innerHTML;
			defaultv = selectObj.options[i].value;
			li.className = 'current';
			selectObj.setAttribute('selecti', i);
		}
		li.onclick = function() {
			if($(selectId + '_ctrl').innerHTML != this.innerHTML) {
				var lis = menuObj.getElementsByTagName('li');
				lis[$(selectId).getAttribute('selecti')].className = '';
				this.className = 'current';
				$(selectId + '_ctrl').innerHTML = this.innerHTML;
				$(selectId).setAttribute('selecti', this.k_id);
				$(selectId).options.length = 0;
				$(selectId).options[0] = new Option('', this.k_value);
				eval(selectObj.getAttribute('change'));
			}
			hideMenu(menuObj.id);
			return false;
		};
		ul.appendChild(li);
	}

	selectObj.options.length = 0;
	selectObj.options[0]= new Option('', defaultv);
	selectObj.style.display = 'none';
	selectObj.outerHTML += '<a href="javascript:;" id="' + selectId + '_ctrl" style="width:' + selectwidth + '" tabindex="' + tabindex + '">' + defaultopt + '</a>';

	menuObj.id = selectId + '_ctrl_menu';
	menuObj.className = 'sltm';
	menuObj.style.display = 'none';
	menuObj.style.width = selectwidth;
	menuObj.appendChild(ul);
	$('append_parent').appendChild(menuObj);

	$(selectId + '_ctrl').onclick = function(e) {
		$(selectId + '_ctrl_menu').style.width = selectwidth;
		showMenu({'ctrlid':(selectId == 'loginfield' ? 'account' : selectId + '_ctrl'),'menuid':selectId + '_ctrl_menu','evt':'click','pos':'43'});
		doane(e);
	};
	$(selectId + '_ctrl').onfocus = menuObj.onfocus = function() {
		_attachEvent(document.body, 'keydown', handleKeyDown);
	};
	$(selectId + '_ctrl').onblur = menuObj.onblur = function() {
		_detachEvent(document.body, 'keydown', handleKeyDown);
	};
	$(selectId + '_ctrl').onkeyup = function(e) {
		e = e ? e : window.event;
		value = e.keyCode;
		if(value == 40 || value == 38) {
			if(menuObj.style.display == 'none') {
				$(selectId + '_ctrl').onclick();
			} else {
				lis = menuObj.getElementsByTagName('li');
				selecti = selectObj.getAttribute('selecti');
				lis[selecti].className = '';
				if(value == 40) {
					selecti = parseInt(selecti) + 1;
				} else if(value == 38) {
					selecti = parseInt(selecti) - 1;
				}
				if(selecti < 0) {
					selecti = lis.length - 1
				} else if(selecti > lis.length - 1) {
					selecti = 0;
				}
				lis[selecti].className = 'current';
				selectObj.setAttribute('selecti', selecti);
				lis[selecti].parentNode.scrollTop = lis[selecti].offsetTop;
			}
		} else if(value == 13) {
			var lis = menuObj.getElementsByTagName('li');
			lis[selectObj.getAttribute('selecti')].onclick();
		} else if(value == 27) {
			hideMenu(menuObj.id);
		}
	};
}

function detectCapsLock(e, obj) {
	var valueCapsLock = e.keyCode ? e.keyCode : e.which;
	var valueShift = e.shiftKey ? e.shiftKey : (valueCapsLock == 16 ? true : false);
	this.clearDetect = function () {
		obj.className = 'txt';
	};

	obj.className = (valueCapsLock >= 65 && valueCapsLock <= 90 && !valueShift || valueCapsLock >= 97 && valueCapsLock <= 122 && valueShift) ? 'clck txt' : 'txt';

	if(BROWSER.ie) {
		event.srcElement.onblur = this.clearDetect;
	} else {
		e.target.onblur = this.clearDetect;
	}
}

function switchTab(prefix, current, total, activeclass) {
	activeclass = !activeclass ? 'a' : activeclass;
	for(var i = 1; i <= total;i++) {
		var classname = ' '+$(prefix + '_' + i).className+' ';
		$(prefix + '_' + i).className = classname.replace(' '+activeclass+' ','').substr(1);
		$(prefix + '_c_' + i).style.display = 'none';
	}
	$(prefix + '_' + current).className = $(prefix + '_' + current).className + ' '+activeclass;
	$(prefix + '_c_' + current).style.display = '';
}

function imageRotate(imgid, direct) {
	var image = $(imgid);
	if(!image.getAttribute('deg')) {
		var deg = 0;
		image.setAttribute('ow', image.width);
		image.setAttribute('oh', image.height);
		if(BROWSER.ie) {
			image.setAttribute('om', parseInt(image.currentStyle.marginBottom));
		}
	} else {
		var deg = parseInt(image.getAttribute('deg'));
	}
	var ow = image.getAttribute('ow');
	var oh = image.getAttribute('oh');
	deg = direct == 1 ? deg - 90 : deg + 90;
	if(deg > 270) {
		deg = 0;
	} else if(deg < 0) {
		deg = 270;
	}
	image.setAttribute('deg', deg);
	if(BROWSER.ie) {
		image.style.marginBottom = (image.getAttribute('om') + (BROWSER.ie < 8 ? 0 : (deg == 90 || deg == 270 ? Math.abs(ow - oh) : 0))) + 'px';
		image.style.filter = 'progid:DXImageTransform.Microsoft.BasicImage(rotation=' + (deg / 90) + ')';
	} else {
	        switch(deg) {
			case 90:var cow = oh, coh = ow, cx = 0, cy = -oh;break;
			case 180:var cow = ow, coh = oh, cx = -ow, cy = -oh;break;
			case 270:var cow = oh, coh = ow, cx = -ow, cy = 0;break;
	        }
		var canvas = $(image.getAttribute('canvasid'));
		if(!canvas) {
			var i = document.createElement("canvas");
			i.id = 'canva_' + Math.random();
			image.setAttribute('canvasid', i.id);
			image.parentNode.insertBefore(i, image);
			canvas = $(i.id);
		}
		if(deg) {
			var canvasContext = canvas.getContext('2d');
			canvas.setAttribute('width', cow);
			canvas.setAttribute('height', coh);
			canvasContext.rotate(deg * Math.PI / 180);
			canvasContext.drawImage(image, cx, cy, ow, oh);
			image.style.display = 'none';
			canvas.style.display = '';
		} else {
			image.style.display = '';
			canvas.style.display = 'none';
		}
	}
}

function thumbImg(obj, method) {
	if(!obj) {
		return;
	}
	obj.onload = null;
	file = obj.src;
	zw = obj.offsetWidth;
	zh = obj.offsetHeight;
	if(zw < 2) {
		if(!obj.id) {
			obj.id = 'img_' + Math.random();
		}
		setTimeout("thumbImg($('" + obj.id + "'), " + method + ")", 100);
		return;
	}
	zr = zw / zh;
	method = !method ? 0 : 1;
	if(method) {
		fixw = obj.getAttribute('_width');
		fixh = obj.getAttribute('_height');
		if(zw > fixw) {
			zw = fixw;
			zh = zw / zr;
		}
		if(zh > fixh) {
			zh = fixh;
			zw = zh * zr;
		}
	} else {
		var imagemaxwidth = isUndefined(imagemaxwidth) ? '600' : imagemaxwidth;
		var widthary = imagemaxwidth.split('%');
		if(widthary.length > 1) {
			fixw = $('wrap').clientWidth - 200;
			if(widthary[0]) {
				fixw = fixw * widthary[0] / 100;
			} else if(widthary[1]) {
				fixw = fixw < widthary[1] ? fixw : widthary[1];
			}
		} else {
			fixw = widthary[0];
		}
		if(zw > fixw) {
			zw = fixw;
			zh = zw / zr;
			obj.style.cursor = 'pointer';
			if(!obj.onclick) {
				obj.onclick = function() {
					zoom(obj, obj.src);
				};
			}
		}
	}
	obj.width = zw;
	obj.height = zh;
}

var zoomclick = 0, zoomstatus = 1;
function zoom(obj, zimg, nocover) {
	zimg = !zimg ? obj.src : zimg;
	nocover = !nocover ? 0 : nocover;
	if(!zoomstatus) {
		window.open(zimg, '', '');
		return;
	}
	if(!obj.id) obj.id = 'img_' + Math.random();
	var menuid = obj.id + '_zmenu';
	var menu = $(menuid);
	var imgid = menuid + '_img';
	var zoomid = menuid + '_zimg';
	var maxh = (document.documentElement.clientHeight ? document.documentElement.clientHeight : document.body.clientHeight) - 70;

	if(!menu) {
		menu = document.createElement('div');
		menu.id = menuid;
		var objpos = fetchOffset(obj);
		menu.innerHTML = '<div onclick="$(\'append_parent\').removeChild($(\'' + obj.id + '_zmenu\'))" style="z-index:600;filter:alpha(opacity=50);opacity:0.5;background:#FFF;position:absolute;width:' + obj.clientWidth + 'px;height:' + obj.clientHeight + 'px;left:' + objpos['left'] + 'px;top:' + objpos['top'] + 'px"><table width="100%" height="100%"><tr><td valign="middle" align="center"><img src="' + IMGDIR + '/loading.gif" /></td></tr></table></div>' +
			'<div style="position:absolute;top:-100000px;display:none"><img id="' + imgid + '" src="' + zimg + '"></div>';
		$('append_parent').appendChild(menu);
		$(imgid).onload = function() {
			$(imgid).parentNode.style.display = '';
			var imgw = $(imgid).width;
			var imgh = $(imgid).height;
			var r = imgw / imgh;
			var w = document.body.clientWidth * 0.95;
			w = imgw > w ? w : imgw;
			var h = w / r;
			if(h > maxh) {
				h = maxh;
				w = h * r;
			}
			$('append_parent').removeChild(menu);
			menu = document.createElement('div');
			menu.id = menuid;
			menu.style.overflow = 'visible';
			menu.style.width = (w < 300 ? 300 : w) + 20 + 'px';
			menu.style.height = h + 50 + 'px';
			menu.innerHTML = !nocover ? '<div class="zoominner"><p id="' + menuid + '_ctrl"><span class="y"><a href="' + zimg + '" class="imglink" target="_blank" title="在新窗口打開">在新窗口打開</a><a href="javascipt:;" id="' + menuid + '_adjust" class="imgadjust" title="實際大小">實際大小</a><a href="javascript:;" onclick="hideMenu()" class="imgclose" title="關閉">關閉</a></span>鼠標滾輪縮放圖片</p><div align="center" onmousedown="zoomclick=1" onmousemove="zoomclick=2" onmouseup="if(zoomclick==1) hideMenu()"><img id="' + zoomid + '" src="' + zimg + '" width="' + w + '" height="' + h + '" w="' + imgw + '" h="' + imgh + '"></div></div>' :
					'<div class="popupmenu_popup" style="width:auto" onmousedown="zoomclick=1" onmousemove="zoomclick=2" onmouseup="if(zoomclick==1) hideMenu()"><span class="right y"><a href="javascript:;" onclick="hideMenu()" class="flbc" style="width:20px;margin:0 0 2px 0">關閉</a></span>鼠標滾輪縮放圖片<img id="' + zoomid + '" src="' + zimg + '" width="' + w + '" height="' + h + '" w="' + imgw + '" h="' + imgh + '"></div>';
			$('append_parent').appendChild(menu);
			if(!nocover) {
				$(menuid + '_adjust').onclick = function(e) {adjust(e, 1)};
			}
			if(BROWSER.ie || BROWSER.chrome){
				menu.onmousewheel = adjust;
			} else {
				menu.addEventListener('DOMMouseScroll', adjust, false);
			}
			showMenu({'menuid':menuid,'duration':3,'pos':'00','cover':!nocover,'drag':menuid,'maxh':maxh+70});
		};
	} else {
		showMenu({'menuid':menuid,'duration':3,'pos':'00','cover':!nocover,'drag':menuid,'maxh':menu.clientHeight});
	}
	if(BROWSER.ie) doane(event);
	var adjust = function(e, a) {
		var imgw = $(zoomid).getAttribute('w');
		var imgh = $(zoomid).getAttribute('h');
		var imgwstep = imgw / 10;
		var imghstep = imgh / 10;
		if(!a) {
			if(!e) e = window.event;
			if(e.altKey || e.shiftKey || e.ctrlKey) return;
			if(e.wheelDelta <= 0 || e.detail > 0) {
				if($(zoomid).width - imgwstep <= 200 || $(zoomid).height - imghstep <= 200) {
					doane(e);return;
				}
				$(zoomid).width -= imgwstep;
				$(zoomid).height -= imghstep;
			} else {
				if($(zoomid).width + imgwstep >= imgw) {
					doane(e);return;
				}
				$(zoomid).width += imgwstep;
				$(zoomid).height += imghstep;
			}
		} else {
			$(zoomid).width = imgw;
			$(zoomid).height = imgh;
		}
		menu.style.width = (parseInt($(zoomid).width < 300 ? 300 : parseInt($(zoomid).width)) + 20) + 'px';
		menu.style.height = (parseInt($(zoomid).height) + 50) + 'px';
		setMenuPosition('', menuid, '00');
		doane(e);
	};
}

function showselect(obj, inpid, t, rettype) {
	if(!obj.id) {
		var t = !t ? 0 : t;
		var rettype = !rettype ? 0 : rettype;
		obj.id = 'calendarexp_' + Math.random();
		div = document.createElement('div');
		div.id = obj.id + '_menu';
		div.style.display = 'none';
		div.className = 'p_pop';
		$('append_parent').appendChild(div);
		s = '';
		if(!t) {
			s += showselect_row(inpid, '一天', 1, 0, rettype);
			s += showselect_row(inpid, '一周', 7, 0, rettype);
			s += showselect_row(inpid, '一個月', 30, 0, rettype);
			s += showselect_row(inpid, '三個月', 90, 0, rettype);
			s += showselect_row(inpid, '自定義', -2);
		} else {
			if($(t)) {
				var lis = $(t).getElementsByTagName('LI');
				for(i = 0;i < lis.length;i++) {
					s += '<a href="javascript:;" onclick="$(\'' + inpid + '\').value = this.innerHTML">' + lis[i].innerHTML + '</a>';
				}
				s += showselect_row(inpid, '自定義', -1);
			} else {
				s += '<a href="javascript:;" onclick="$(\'' + inpid + '\').value = \'0\'">永久</a>';
				s += showselect_row(inpid, '7 天', 7, 1, rettype);
				s += showselect_row(inpid, '14 天', 14, 1, rettype);
				s += showselect_row(inpid, '一個月', 30, 1, rettype);
				s += showselect_row(inpid, '三個月', 90, 1, rettype);
				s += showselect_row(inpid, '半年', 182, 1, rettype);
				s += showselect_row(inpid, '一年', 365, 1, rettype);
				s += showselect_row(inpid, '自定義', -1);
			}
		}
		$(div.id).innerHTML = s;
	}
	showMenu({'ctrlid':obj.id,'evt':'click'});
	if(BROWSER.ie && BROWSER.ie < 7) {
		doane(event);
	}
}

function showselect_row(inpid, s, v, notime, rettype) {
	if(v >= 0) {
		if(!rettype) {
			var notime = !notime ? 0 : 1;
			t = today.getTime();
			t += 86400000 * v;
			d = new Date();
			d.setTime(t);
			return '<a href="javascript:;" onclick="$(\'' + inpid + '\').value = \'' + d.getFullYear() + '-' + (d.getMonth() + 1) + '-' + d.getDate() + (!notime ? ' ' + d.getHours() + ':' + d.getMinutes() : '') + '\'">' + s + '</a>';
		} else {
			return '<a href="javascript:;" onclick="$(\'' + inpid + '\').value = \'' + v + '\'">' + s + '</a>';
		}
	} else if(v == -1) {
		return '<a href="javascript:;" onclick="$(\'' + inpid + '\').focus()">' + s + '</a>';
	} else if(v == -2) {
		return '<a href="javascript:;" onclick="$(\'' + inpid + '\').onclick()">' + s + '</a>';
	}
}

function showColorBox(ctrlid, layer, k) {
	if(!$(ctrlid + '_menu')) {
		var menu = document.createElement('div');
		menu.id = ctrlid + '_menu';
		menu.className = 'p_pop colorbox';
		menu.unselectable = true;
		menu.style.display = 'none';
		var coloroptions = ['Black', 'Sienna', 'DarkOliveGreen', 'DarkGreen', 'DarkSlateBlue', 'Navy', 'Indigo', 'DarkSlateGray', 'DarkRed', 'DarkOrange', 'Olive', 'Green', 'Teal', 'Blue', 'SlateGray', 'DimGray', 'Red', 'SandyBrown', 'YellowGreen', 'SeaGreen', 'MediumTurquoise', 'RoyalBlue', 'Purple', 'Gray', 'Magenta', 'Orange', 'Yellow', 'Lime', 'Cyan', 'DeepSkyBlue', 'DarkOrchid', 'Silver', 'Pink', 'Wheat', 'LemonChiffon', 'PaleGreen', 'PaleTurquoise', 'LightBlue', 'Plum', 'White'];
		var colortexts = ['黑色', '赭色', '暗橄欖綠色', '暗綠色', '暗灰藍色', '海軍色', '靛青色', '墨綠色', '暗紅色', '暗桔黃色', '橄欖色', '綠色', '水鴨色', '藍色', '灰石色', '暗灰色', '紅色', '沙褐色', '黃綠色', '海綠色', '間綠寶石', '皇家藍', '紫色', '灰色', '紅紫色', '橙色', '黃色', '酸橙色', '青色', '深天藍色', '暗紫色', '銀色', '粉色', '淺黃色', '檸檬綢色', '蒼綠色', '蒼寶石綠', '亮藍色', '洋李色', '白色'];
		var str = '';
		for(var i = 0; i < 40; i++) {
			str += '<input type="button" style="background-color: ' + coloroptions[i] + '"' + (typeof setEditorTip == 'function' ? ' onmouseover="setEditorTip(\'' + colortexts[i] + '\')" onmouseout="setEditorTip(\'\')"' : '') + ' onclick="'
			+ (typeof wysiwyg == 'undefined' ? 'seditor_insertunit(\'' + k + '\', \'[color=' + coloroptions[i] + ']\', \'[/color]\')' : (ctrlid == editorid + '_tbl_param_4' ? '$(\'' + ctrlid + '\').value=\'' + coloroptions[i] + '\';hideMenu(2)' : 'discuzcode(\'forecolor\', \'' + coloroptions[i] + '\')'))
			+ '" title="' + colortexts[i] + '" />' + (i < 39 && (i + 1) % 8 == 0 ? '<br />' : '');
		}
		menu.innerHTML = str;
		$('append_parent').appendChild(menu);
	}
	showMenu({'ctrlid':ctrlid,'evt':'click','layer':layer});
}

function smilies_show(id, smcols, seditorkey) {
	if(seditorkey && !$(seditorkey + 'sml_menu')) {
		var div = document.createElement("div");
		div.id = seditorkey + 'sml_menu';
		div.style.display = 'none';
		div.className = 'sllt';
		$('append_parent').appendChild(div);
		var div = document.createElement("div");
		div.id = id;
		div.style.overflow = 'hidden';
		$(seditorkey + 'sml_menu').appendChild(div);
	}
	if(typeof smilies_type == 'undefined') {
		var scriptNode = document.createElement("script");
		scriptNode.type = "text/javascript";
		scriptNode.charset = charset ? charset : (BROWSER.firefox ? document.characterSet : document.charset);
		scriptNode.src = 'data/cache/common_smilies_var.js?' + VERHASH;
		$('append_parent').appendChild(scriptNode);
		if(BROWSER.ie) {
			scriptNode.onreadystatechange = function() {
				smilies_onload(id, smcols, seditorkey);
			};
		} else {
			scriptNode.onload = function() {
				smilies_onload(id, smcols, seditorkey);
			};
		}
	} else {
		smilies_onload(id, smcols, seditorkey);
	}
}
function smilies_onload(id, smcols, seditorkey) {
	seditorkey = !seditorkey ? '' : seditorkey;
	smile = getcookie('smile').split('D');
	if(typeof smilies_type == 'object') {
		if(smile[0] && smilies_array[smile[0]]) {
			CURRENTSTYPE = smile[0];
		} else {
			for(i in smilies_array) {
				CURRENTSTYPE = i;break;
			}
		}
		smiliestype = '<div class="slg"><ul>';
		for(i in smilies_type) {
			if(smilies_type[i][0]) {
				smiliestype += '<li><a href="javascript:;" hidefocus="true" ' + (CURRENTSTYPE == i ? 'class="current"' : '') + ' id="'+seditorkey+'stype_'+i+'" onclick="smilies_switch(\'' + id + '\', \'' + smcols + '\', '+i+', 1, \'' + seditorkey + '\');if(CURRENTSTYPE) {$(\''+seditorkey+'stype_\'+CURRENTSTYPE).className=\'\';}this.className=\'current\';CURRENTSTYPE='+i+';doane(event);">'+smilies_type[i][0]+'</a></li>';
			}
		}
		smiliestype += '</ul></div>';
		$(id).innerHTML = smiliestype + '<div id="' + id + '_data"></div><div class="sllt_p" id="' + id + '_page"></div>';
		smilies_switch(id, smcols, CURRENTSTYPE, smile[1], seditorkey);
		smilies_fastdata = '';
		if(seditorkey == 'fastpost' && $('fastsmilies') && smilies_fast) {
			var j = 0;
			for(i = 0;i < smilies_fast.length; i++) {
				if(j == 0) {
					smilies_fastdata += '<tr>';
				}
				j = ++j > 3 ? 0 : j;
				s = smilies_array[smilies_fast[i][0]][smilies_fast[i][1]][smilies_fast[i][2]];
				smilieimg = STATICURL + 'image/smiley/' + smilies_type[smilies_fast[i][0]][1] + '/' + s[2];
				img[k] = new Image();
				img[k].src = smilieimg;
				smilies_fastdata += s ? '<td onmouseover="smilies_preview(\'' + seditorkey + '\', \'fastsmiliesdiv\', this, ' + s[5] + ')" onclick="' + (typeof wysiwyg != 'undefined' ? 'insertSmiley(' + s[0] + ')': 'seditor_insertunit(\'' + seditorkey + '\', \'' + s[1].replace(/'/, '\\\'') + '\')') +
					'" id="' + seditorkey + 'smilie_' + s[0] + '_td"><img id="smilie_' + s[0] + '" width="' + s[3] +'" height="' + s[4] +'" src="' + smilieimg + '" alt="' + s[1] + '" />' : '<td>';
			}
			$('fastsmilies').innerHTML = '<table cellspacing="0" cellpadding="0"><tr>' + smilies_fastdata + '</tr></table>';
		}
	}
}
function smilies_switch(id, smcols, type, page, seditorkey) {
	page = page ? page : 1;
	if(!smilies_array[type] || !smilies_array[type][page]) return;
	setcookie('smile', type + 'D' + page, 31536000);
	smiliesdata = '<table id="' + id + '_table" cellpadding="0" cellspacing="0"><tr>';
	j = k = 0;
	img = [];
	for(i in smilies_array[type][page]) {
		if(j >= smcols) {
			smiliesdata += '<tr>';
			j = 0;
		}
		s = smilies_array[type][page][i];
		smilieimg = STATICURL + 'image/smiley/' + smilies_type[type][1] + '/' + s[2];
		img[k] = new Image();
		img[k].src = smilieimg;
		smiliesdata += s && s[0] ? '<td onmouseover="smilies_preview(\'' + seditorkey + '\', \'' + id + '\', this, ' + s[5] + ')" onclick="' + (typeof wysiwyg != 'undefined' ? 'insertSmiley(' + s[0] + ')': 'seditor_insertunit(\'' + seditorkey + '\', \'' + s[1].replace(/'/, '\\\'') + '\')') +
			'" id="' + seditorkey + 'smilie_' + s[0] + '_td"><img id="smilie_' + s[0] + '" width="' + s[3] +'" height="' + s[4] +'" src="' + smilieimg + '" alt="' + s[1] + '" />' : '<td>';
		j++;k++;
	}
	smiliesdata += '</table>';
	smiliespage = '';
	if(smilies_array[type].length > 2) {
		prevpage = ((prevpage = parseInt(page) - 1) < 1) ? smilies_array[type].length - 1 : prevpage;
		nextpage = ((nextpage = parseInt(page) + 1) == smilies_array[type].length) ? 1 : nextpage;
		smiliespage = '<div class="z"><a href="javascript:;" onclick="smilies_switch(\'' + id + '\', \'' + smcols + '\', ' + type + ', ' + prevpage + ', \'' + seditorkey + '\');doane(event);">上頁</a>' +
			'<a href="javascript:;" onclick="smilies_switch(\'' + id + '\', \'' + smcols + '\', ' + type + ', ' + nextpage + ', \'' + seditorkey + '\');doane(event);">下頁</a></div>' +
			page + '/' + (smilies_array[type].length - 1);
	}
	$(id + '_data').innerHTML = smiliesdata;
	$(id + '_page').innerHTML = smiliespage;
}
function smilies_preview(seditorkey, id, obj, w) {
	var menu = $('smilies_preview');
	if(!menu) {
		menu = document.createElement('div');
		menu.id = 'smilies_preview';
		menu.className = 'sl_pv';
		menu.style.display = 'none';
		$('append_parent').appendChild(menu);
	}
	menu.innerHTML = '<img width="' + w + '" src="' + obj.childNodes[0].src + '" />';
	mpos = fetchOffset($(id + '_data'));
	spos = fetchOffset(obj);
	pos = spos['left'] >= mpos['left'] + $(id + '_data').offsetWidth / 2 ? '13' : '24';
	showMenu({'ctrlid':obj.id,'showid':id + '_data','menuid':menu.id,'pos':pos,'layer':3});
}

function seditor_showimgmenu(seditorkey) {
	var imgurl = $(seditorkey + '_image_param_1').value;
	var width = parseInt($(seditorkey + '_image_param_2').value);
	var height = parseInt($(seditorkey + '_image_param_3').value);
	var extparams = '';
	if(width || height) {
		extparams = '=' + width + ',' + height
	}
	seditor_insertunit(seditorkey, '[img' + extparams + ']' + imgurl, '[/img]', null, 1);
	$(seditorkey + '_image_param_1').value = '';
	hideMenu();
}
function seditor_menu(seditorkey, tag) {
	var sel = false;
	if(!isUndefined($(seditorkey + 'message').selectionStart)) {
		sel = $(seditorkey + 'message').selectionEnd - $(seditorkey + 'message').selectionStart;
	} else if(document.selection && document.selection.createRange) {
		$(seditorkey + 'message').focus();
		var sel = document.selection.createRange();
		$(seditorkey + 'message').sel = sel;
		sel = sel.text ? true : false;
	}
	if(sel) {
		seditor_insertunit(seditorkey, '[' + tag + ']', '[/' + tag + ']');
		return;
	}
	var ctrlid = seditorkey + tag;
	var menuid = ctrlid + '_menu';
	if(!$(menuid)) {
		switch(tag) {
			case 'url':
				str = '請輸入鏈接地址:<br /><input type="text" id="' + ctrlid + '_param_1" sautocomplete="off" style="width: 98%" value="" class="px" />' +
					'<br />請輸入鏈接文字:<br /><input type="text" id="' + ctrlid + '_param_2" style="width: 98%" value="" class="px" />';
				submitstr = "$('" + ctrlid + "_param_2').value !== '' ? seditor_insertunit('" + seditorkey + "', '[url='+$('" + ctrlid + "_param_1').value+']'+$('" + ctrlid + "_param_2').value, '[/url]', null, 1) : seditor_insertunit('" + seditorkey + "', '[url]'+$('" + ctrlid + "_param_1').value, '[/url]', null, 1);hideMenu();";
				break;
			case 'code':
			case 'quote':
				var tagl = {'quote' : '請輸入要插入的引用', 'code' : '請輸入要插入的代碼'};
					str = tagl[tag] + ':<br /><textarea id="' + ctrlid + '_param_1" style="width: 98%" cols="50" rows="5" class="txtarea"></textarea>';
				submitstr = "seditor_insertunit('" + seditorkey + "', '[" + tag + "]'+$('" + ctrlid + "_param_1').value, '[/" + tag + "]', null, 1);hideMenu();";
				break;
			case 'img':
				str = '請輸入圖片地址:<br /><input type="text" id="' + ctrlid + '_param_1" style="width: 98%" value="" class="px" onchange="loadimgsize(this.value, \'' + seditorkey + '\',\'' + tag + '\')" />' +
					'<p class="mtm">寬(可選): <input type="text" id="' + ctrlid + '_param_2" style="width: 15%" value="" class="px" /> &nbsp;' +
					'高(可選): <input type="text" id="' + ctrlid + '_param_3" style="width: 15%" value="" class="px" /></p>';
				submitstr = "seditor_insertunit('" + seditorkey + "', '[img' + ($('" + ctrlid + "_param_2').value !== '' && $('" + ctrlid + "_param_3').value !== '' ? '='+$('" + ctrlid + "_param_2').value+','+$('" + ctrlid + "_param_3').value : '')+']'+$('" + ctrlid + "_param_1').value, '[/img]', null, 1);hideMenu();";
				break;
		}
		var menu = document.createElement('div');
		menu.id = menuid;
		menu.style.display = 'none';
		menu.className = 'p_pof upf';
		menu.style.width = '270px';
		$('append_parent').appendChild(menu);
		menu.innerHTML = '<span class="y"><a onclick="hideMenu()" class="flbc" href="javascript:;">關閉</a></span><div class="p_opt cl"><form onsubmit="' + submitstr + ';return false;" autocomplete="off"><div>' + str + '</div><div class="pns mtn"><button type="submit" id="' + ctrlid + '_submit" class="pn pnc"><strong>提交</strong></button><button type="button" onClick="hideMenu()" class="pn"><em>取消</em></button></div></form></div>';
	}
	showMenu({'ctrlid':ctrlid,'evt':'click','duration':3,'cache':0,'drag':1});

}
function seditor_insertunit(key, text, textend, moveend, selappend) {
	$(key + 'message').focus();
	textend = isUndefined(textend) ? '' : textend;
	moveend = isUndefined(textend) ? 0 : moveend;
	selappend = isUndefined(selappend) ? 1 : selappend;
	startlen = strlen(text);
	endlen = strlen(textend);
	if(!isUndefined($(key + 'message').selectionStart)) {
		if(selappend) {
			var opn = $(key + 'message').selectionStart + 0;
			if(textend != '') {
				text = text + $(key + 'message').value.substring($(key + 'message').selectionStart, $(key + 'message').selectionEnd) + textend;
			}
			$(key + 'message').value = $(key + 'message').value.substr(0, $(key + 'message').selectionStart) + text + $(key + 'message').value.substr($(key + 'message').selectionEnd);
			if(!moveend) {
				$(key + 'message').selectionStart = opn + strlen(text) - endlen;
				$(key + 'message').selectionEnd = opn + strlen(text) - endlen;
			}
		} else {
			text = text + textend;
			$(key + 'message').value = $(key + 'message').value.substr(0, $(key + 'message').selectionStart) + text + $(key + 'message').value.substr($(key + 'message').selectionEnd);
		}
	} else if(document.selection && document.selection.createRange) {
		var sel = document.selection.createRange();
		if(!sel.text.length && $(key + 'message').sel) {
			sel = $(key + 'message').sel;
			$(key + 'message').sel = null;
		}
		if(selappend) {
			if(textend != '') {
				text = text + sel.text + textend;
			}
			sel.text = text.replace(/\r?\n/g, '\r\n');
			if(!moveend) {
				sel.moveStart('character', -endlen);
				sel.moveEnd('character', -endlen);
			}
			sel.select();
		} else {
			sel.text = text + textend;
		}
	} else {
		$(key + 'message').value += text;
	}
	hideMenu(2);
	if(BROWSER.ie) {
		doane();
	}
}
function seditor_ctlent(event, script) {
	if(event.ctrlKey && event.keyCode == 13 || event.altKey && event.keyCode == 83) {
		eval(script);
	}
}
function ctrlEnter(event, btnId, onlyEnter) {
	if(isUndefined(onlyEnter)) onlyEnter = 0;
	if((event.ctrlKey || onlyEnter) && event.keyCode == 13) {
		$(btnId).click();
		return false;
	}
	return true;
}
function loadimgsize(imgurl, editor, p) {
	var editor = !editor ? editorid : editor;
	var s = new Object();
	var p = !p ? '_image' : p;
	s.img = new Image();
	s.img.src = imgurl;
	s.loadCheck = function () {
		if(s.img.complete) {
			$(editor + p + '_param_2').value = s.img.width ? s.img.width : '';
			$(editor + p + '_param_3').value = s.img.height ? s.img.height : '';
		} else {
			setTimeout(function () {s.loadCheck();}, 100);
		}
	};
	s.loadCheck();
}
function parseurl(str, mode, parsecode) {
	if(isUndefined(parsecode)) parsecode = true;
	if(parsecode) str= str.replace(/\s*\[code\]([\s\S]+?)\[\/code\]\s*/ig, function($1, $2) {return codetag($2);});
	str = str.replace(/([^>=\]"'\/@]|^)((((https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|ed2k|thunder|synacast):\/\/))([\w\-]+\.)*[:\.@\-\w\u4e00-\u9fa5]+\.([\.a-zA-Z0-9]+|\u4E2D\u56FD|\u7F51\u7EDC|\u516C\u53F8)((\?|\/|:)+[\w\.\/=\?%\-&~`@':+!#]*)*)/ig, mode == 'html' ? '$1<a href="$2" target="_blank">$2</a>' : '$1[url]$2[/url]');
	str = str.replace(/([^\w>=\]"'\/@]|^)((www\.)([\w\-]+\.)*[:\.@\-\w\u4e00-\u9fa5]+\.([\.a-zA-Z0-9]+|\u4E2D\u56FD|\u7F51\u7EDC|\u516C\u53F8)((\?|\/|:)+[\w\.\/=\?%\-&~`@':+!#]*)*)/ig, mode == 'html' ? '$1<a href="$2" target="_blank">$2</a>' : '$1[url]$2[/url]');
	str = str.replace(/([^\w->=\]:"'\.\/]|^)(([\-\.\w]+@[\.\-\w]+(\.\w+)+))/ig, mode == 'html' ? '$1<a href="mailto:$2">$2</a>' : '$1[email]$2[/email]');
	if(parsecode) {
		for(var i = 0; i <= DISCUZCODE['num']; i++) {
			str = str.replace("[\tDISCUZ_CODE_" + i + "\t]", DISCUZCODE['html'][i]);
		}
	}
	return str;
}
function codetag(text) {
	DISCUZCODE['num']++;
	if(typeof wysiwyg != 'undefined' && wysiwyg) text = text.replace(/<br[^\>]*>/ig, '\n').replace(/<(\/|)[A-Za-z].*?>/ig, '');
	DISCUZCODE['html'][DISCUZCODE['num']] = '[code]' + text + '[/code]';
	return '[\tDISCUZ_CODE_' + DISCUZCODE['num'] + '\t]';
}
function parsepmcode(theform) {
	theform.message.value = parseurl(theform.message.value);
}

function saveUserdata(name, data) {
	if(BROWSER.ie){
		with(document.documentElement) {
			setAttribute("value", data);
			save('Discuz_' + name);
		}
	} else if(window.sessionStorage){
		sessionStorage.setItem('Discuz_' + name, data);
		}
}

function loadUserdata(name) {
	if(BROWSER.ie){
		with(document.documentElement) {
					load('Discuz_' + name);
			return getAttribute("value");
			}
	} else if(window.sessionStorage){
		return sessionStorage.getItem('Discuz_' + name);
		}
}

function initTab(frameId, type) {
	if (typeof document['diyform'] == 'object' || $(frameId).className.indexOf('tab') < 0) return false;
	type = type || 'click';
	var tabs = $(frameId+'_title').childNodes;
	var arrTab = [];
	for(var i in tabs) {
		if (tabs[i]['nodeType'] == 1 && tabs[i]['className'].indexOf('move-span') > -1) {
			arrTab.push(tabs[i]);
		}
	}
	var counter = 0;
	var tab = document.createElement('ul');
	tab.className = 'tb cl';
	var len = arrTab.length;
	for(var i = 0;i < len; i++) {
		var tabId = arrTab[i].id;
		if (hasClass(arrTab[i],'frame') || hasClass(arrTab[i],'tab')) {
			var arrColumn = [];
			for (var j in arrTab[i].childNodes) {
				if (typeof arrTab[i].childNodes[j] == 'object' && !hasClass(arrTab[i].childNodes[j],'title')) arrColumn.push(arrTab[i].childNodes[j]);
			}
			var frameContent = document.createElement('div');
			frameContent.id = tabId+'_content';
			frameContent.className = hasClass(arrTab[i],'frame') ? 'content cl '+arrTab[i].className.substr(arrTab[i].className.lastIndexOf(' ')+1) : 'content cl';
			var colLen = arrColumn.length;
			for (var k = 0; k < colLen; k++) {
				frameContent.appendChild(arrColumn[k]);
			}
		} else {
			var frameContent = $(tabId+'_content');
			frameContent = frameContent || document.createElement('div');
		}
		frameContent.style.display = counter ? 'none' : '';
		$(frameId+'_content').appendChild(frameContent);

		var li = document.createElement('li');
		li.id = tabId;
		li.className = counter ? '' : 'a';
		li.innerHTML = arrTab[i]['innerText'] ? arrTab[i]['innerText'] : arrTab[i]['textContent'];
		var a = arrTab[i].getElementsByTagName('a');
		var href = a && a[0] ? a[0].href : 'javascript:;';
		var onclick = type == 'click' ? ' onclick="return false;"' : '';
		li.innerHTML = '<a href="'+href+'"'+onclick+' onfocus="this.blur();">'+li.innerHTML+'</a>';
		_attachEvent(li, type, switchTabUl);
		tab.appendChild(li);
		$(frameId+'_title').removeChild(arrTab[i]);
		counter++;
	}
	$(frameId+'_title').appendChild(tab);
}

function openDiy(){
	window.location.href = ((window.location.href + '').replace(/[\?\&]diy=yes/g, '').split('#')[0] + ( window.location.search && window.location.search.indexOf('?diy=yes') < 0 ? '&diy=yes' : '?diy=yes'));
}

function switchTabUl (e) {
	e = e || window.event;
	var aim = e.target || e.srcElement;
	var tabId = aim.id;
	var parent = aim.parentNode;
	while(parent['nodeName'] != 'UL' && parent['nodeName'] != 'BODY') {
		tabId = parent.id;
		parent = parent.parentNode;
	}
	if(parent['nodeName'] == 'BODY') return false;
	var tabs = parent.childNodes;
	var len2 = tabs.length;
	for(var j = 0; j < len2; j++) {
		tabs[j].className = (tabs[j].id == tabId) ? 'a' : '';
		var content = $(tabs[j].id+'_content');
		if (content) content.style.display = tabs[j].id == tabId ? '' : 'none';
	}
}

function hasClass(elem, className) {
	return elem.className && (" " + elem.className + " ").indexOf(" " + className + " ") != -1;
}

function runslideshow() {
	var slideshows = $C('slidebox');
	for(var i=0,L=slideshows.length; i<L; i++) {
		new slideshow(slideshows[i]);
	}
}
function slideshow(el) {
	var obj = this;
	if(!el.id) el.id = Math.random();
	if(typeof slideshow.entities == 'undefined') {
		slideshow.entities = {};
	}
	this.id = el.id;
	if(slideshow.entities[this.id]) return false;
	slideshow.entities[this.id] = this;

	this.slideshows = [];
	this.slidebar = [];
	this.slideother = [];
	this.slidebarup = '';
	this.slidebardown = '';
	this.slidenum = 0;
	this.slidestep = 0;

	this.container = el;
	this.imgs = [];
	this.imgLoad = [];
	this.imgLoaded = 0;
	this.imgWidth = 0;
	this.imgHeight = 0;

	this.getMEvent = function(ele, value) {
		value = !value ? 'mouseover' : value;
		var mevent = !ele ? '' : ele.getAttribute('mevent');
		mevent = (mevent == 'click' || mevent == 'mouseover') ? mevent : value;
		return mevent;
	};
	this.slideshows = $C('slideshow', el);
	this.slideshows = this.slideshows.length>0 ? this.slideshows[0].childNodes : null;
	this.slidebar = $C('slidebar', el);
	this.slidebar = this.slidebar.length>0 ? this.slidebar[0] : null;
	this.barmevent = this.getMEvent(this.slidebar);
	this.slideother = $C('slideother', el);
	this.slidebarup = $C('slidebarup', el);
	this.slidebarup = this.slidebarup.length>0 ? this.slidebarup[0] : null;
	this.barupmevent = this.getMEvent(this.slidebarup, 'click');
	this.slidebardown = $C('slidebardown', el);
	this.slidebardown = this.slidebardown.length>0 ? this.slidebardown[0] : null;
	this.bardownmevent = this.getMEvent(this.slidebardown, 'click');
	this.slidenum = parseInt(this.container.getAttribute('slidenum'));
	this.slidestep = parseInt(this.container.getAttribute('slidestep'));
	this.timestep = parseInt(this.container.getAttribute('timestep'));
	this.timestep = !this.timestep ? 2500 : this.timestep;

	this.index = this.length = 0;
	if(!this.slideshows) {
		var nodes = el.childNodes;
		for(var i=0, L=nodes.length; i<L; i++) {
			if (nodes[i].nodeType == 1) {
				this.slideshows[this.length] = nodes[i];
			}
		}
	}
	for(i=0, L=this.slideshows.length; i<L; i++) {
		if(this.slideshows[i].nodeType == 1) {
			this.slideshows[i].style.display = "none";
			this.length += 1;
		}
	}
	for(i=0, L=this.slideother.length; i<L; i++) {
		for(var j=0;j<this.slideother[i].childNodes.length;j++) {
			if(this.slideother[i].childNodes[j].nodeType == 1) {
				this.slideother[i].childNodes[j].style.display = "none";
			}
		}
	}

	if(!this.slidebar) {
		if(!this.slidenum && !this.slidestep) {
			this.container.parentNode.style.position = 'relative';
			this.slidebar = document.createElement('div');
			this.slidebar.className = 'slidebar';
			this.slidebar.style.position = 'absolute';
			this.slidebar.style.top = '5px';
			this.slidebar.style.left = '4px';
			this.slidebar.style.display = 'none';
			var html = '<ul>';
			for(var i=0; i<this.length; i++) {
				html += '<li on'+this.barmevent+'="slideshow.entities[' + this.id + '].xactive(' + i + '); return false;">' + (i + 1).toString() + '</li>';
			}
			html += '</ul>';
			this.slidebar.innerHTML = html;
			this.container.parentNode.appendChild(this.slidebar);
			this.controls = this.slidebar.getElementsByTagName('li');
		}
	} else {
		this.controls = this.slidebar.childNodes;
		for(i=0; i<this.controls.length; i++) {
			if(this.slidebarup == this.controls[i] || this.slidebardown == this.controls[i]) continue;
			_attachEvent(this.controls[i], this.barmevent, function(){slidexactive()});
		}
	}
	if(this.slidebarup) {
		_attachEvent(this.slidebarup, this.barupmevent, function(){slidexactive('up')});
	}
	if(this.slidebardown) {
		_attachEvent(this.slidebardown, this.bardownmevent, function(){slidexactive('down')});
	}
	this.activeByStep = function(index) {
		var showindex = 0,i = 0;
		if(index == 'down') {
			showindex = this.index + 1;
			if(showindex >= this.length) {
				this.runRoll();
			} else {
				for (i = 0; i < this.slidestep; i++) {
					if(showindex >= this.length) showindex = 0;
					this.index = this.index - this.slidenum + 1;
					if(this.index < 0) this.index = this.length - Math.abs(this.index);
					this.active(showindex);
					showindex++;
				}
			}
		} else if (index == 'up') {
			var tempindex = this.index;
			showindex = this.index - this.slidenum;
			if(showindex < 0) return false;
			for (i = 0; i < this.slidestep; i++) {
				if(showindex < 0) showindex = this.length - Math.abs(showindex);
				this.active(showindex);
				this.index = tempindex = tempindex - 1;
				if(this.index <0) this.index = this.length - 1;
				showindex--;
			}
		}
		return false;
	};
	this.active = function(index) {
		this.slideshows[this.index].style.display = "none";
		this.slideshows[index].style.display = "block";
		if(this.controls.length > 0) {
			this.controls[this.index].className = '';
			this.controls[index].className = 'on';
		}
		for(var i=0,L=this.slideother.length; i<L; i++) {
			this.slideother[i].childNodes[this.index].style.display = "none";
			this.slideother[i].childNodes[index].style.display = "block";
		}
		this.index = index;
	};
	this.xactive = function(index) {
		if(!this.slidenum && !this.slidestep) {
			clearTimeout(this.timer);
			if(index == 'down') index = this.index == this.length-1 ? 0 : this.index+1;
			if(index == 'up') index = this.index == 0 ? this.length-1 : this.index-1;
			this.active(index);
			var ss = this;
			this.timer = setTimeout(function(){
				ss.run();
			}, 8000);
		} else {
			this.activeByStep(index);
		}
	};
	this.run = function() {
		var index = this.index + 1 < this.length ? this.index + 1 : 0;
		this.active(index);
		var ss = this;
		this.timer = setTimeout(function(){
			ss.run();
		}, this.timestep);
	};

	this.runRoll = function() {
		for(var i = 0; i < this.slidenum; i++) {
			if(this.slideshows[i] && typeof this.slideshows[i].style != 'undefined') this.slideshows[i].style.display = "block";
			for(var j=0,L=this.slideother.length; j<L; j++) {
				this.slideother[j].childNodes[i].style.display = "block";
			}
		}
		this.index = this.slidenum - 1;
	};
	var imgs = this.slideshows.length ? this.slideshows[0].parentNode.getElementsByTagName('img') : [];
	for(i=0, L=imgs.length; i<L; i++) {
		this.imgs.push(imgs[i]);
		this.imgLoad.push(new Image());
		this.imgLoad[i].src = this.imgs[i].src;
		this.imgLoad[i].onerror = function (){obj.imgLoaded ++;};
	}

	this.getSize = function () {
		if(this.imgs.length == 0) return false;
		var img = this.imgs[0];
		this.imgWidth = img.width ? parseInt(img.width) : 0;
		this.imgHeight = img.height ? parseInt(img.height) : 0;
		var ele = img.parentNode;
		while ((!this.imgWidth || !this.imgHeight) && !hasClass(ele,'slideshow') && ele != document.body) {
			this.imgWidth = ele.style.width ? parseInt(ele.style.width) : 0;
			this.imgHeight = ele.style.height ? parseInt(ele.style.height) : 0;
			ele = ele.parentNode;
		}
		return true;
	};

	this.getSize();

	this.checkLoad = function () {
		var obj = this;
		for(i = 0;i < this.imgs.length;i++) {
			if(this.imgLoad[i].complete && !this.imgLoad[i].status) {
				this.imgLoaded++;
				this.imgLoad[i].status = 1;
			}
		}
		var percentEle = $(this.id+'_percent');
		if(this.imgLoaded < this.imgs.length) {
			if (!percentEle) {
				var dom = document.createElement('div');
				dom.id = this.id+"_percent";
				dom.style.width = this.imgWidth ? this.imgWidth+'px' : '150px';
				dom.style.height = this.imgHeight ? this.imgHeight+'px' : '150px';
				dom.style.lineHeight = this.imgHeight ? this.imgHeight+'px' : '150px';
				dom.style.backgroundColor = '#ccc';
				dom.style.textAlign = 'center';
				dom.style.top = '0';
				dom.style.left = '0';
				dom.style.marginLeft = 'auto';
				dom.style.marginRight = 'auto';
				this.slideshows[0].parentNode.appendChild(dom);
				percentEle = dom;
			}
			el.parentNode.style.position = 'relative';
			percentEle.innerHTML = (parseInt(this.imgLoaded / this.imgs.length * 100)) + '%';
			setTimeout(function () {obj.checkLoad();}, 100)
		} else {
			if (percentEle) percentEle.parentNode.removeChild(percentEle);
			this.container.style.display = 'block';
			if(this.slidebar) this.slidebar.style.display = '';
			this.index = this.length - 1 < 0 ? 0 : this.length - 1;
			if(this.slideshows.length > 0) {
				if(!this.slidenum || !this.slidestep) {
					this.run();
				} else {
					this.runRoll();
				}
			}
		}
	};
	this.checkLoad();
}

function slidexactive(step) {
	var e = getEvent();
	var aim = e.target || e.srcElement;
	var parent = aim.parentNode;
	var xactivei = null, slideboxid = null,currentslideele = null;
	currentslideele = hasClass(parent, 'slidebar') ? aim : null;
	while(parent && parent != document.body) {
		if(!currentslideele && hasClass(parent.parentNode, 'slidebar')) {
			currentslideele = parent;
		}
		if(hasClass(parent, 'slidebox')) {
			slideboxid = parent.id;
			break;
		}
		parent = parent.parentNode;
	}
	if(step && !currentslideele) {
		while(aim != document.body) {
			if(hasClass(aim, 'slidebarup') || hasClass(aim, 'slidebardown')) {
				currentslideele = aim;
				break;
			}
			aim = aim.parentNode;
		}
	}
	var slidebar = $C('slidebar', parent);
	var children = slidebar.length == 0 ? [] : slidebar[0].childNodes;
	if(currentslideele && (hasClass(currentslideele, 'slidebarup') || hasClass(currentslideele, 'slidebardown'))) {
		xactivei = step;
	} else {
		for(var j=0,i=0,L=children.length;i<L;i++){
			if(currentslideele && children[i] == currentslideele) {
				xactivei = j;
				break;
			}
			if(!hasClass(children[i], 'slidebarup') && !hasClass(children[i], 'slidebardown')) j++;
		}
	}
	if(slideboxid != null && xactivei != null) slideshow.entities[slideboxid].xactive(xactivei);
}

function showPreview(val, id) {
	var showObj = $(id);
	if(showObj) {
		showObj.innerHTML = val.replace(/\n/ig, "<bupdateseccoder />");
	}
}

function toggle_collapse(objname, noimg, complex, lang) {
	var obj = $(objname);
	if(obj) {
		obj.style.display = obj.style.display == '' ? 'none' : '';
		var collapsed = getcookie('collapse');
		collapsed = updatestring(collapsed, objname, !obj.style.display);
		setcookie('collapse', collapsed, (collapsed ? 2592000 : -2592000));
	}
	if(!noimg) {
		var img = $(objname + '_img');
		if(img.tagName != 'IMG') {
			if(img.className.indexOf('_yes') == -1) {
				img.className = img.className.replace(/_no/, '_yes');
				if(lang) {
					img.innerHTML = lang[0];
				}
			} else {
				img.className = img.className.replace(/_yes/, '_no');
				if(lang) {
					img.innerHTML = lang[1];
				}
			}
		} else {
			img.src = img.src.indexOf('_yes.gif') == -1 ? img.src.replace(/_no\.gif/, '_yes\.gif') : img.src.replace(/_yes\.gif/, '_no\.gif');
		}
		img.blur();
	}
	if(complex) {
		var objc = $(objname + '_c');
		if(objc) {
			objc.className = objc.className == 'umh' ? 'umh umn' : 'umh';
		}
	}

}
function updatestring(str1, str2, clear) {
	str2 = '_' + str2 + '_';
	return clear ? str1.replace(str2, '') : (str1.indexOf(str2) == -1 ? str1 + str2 : str1);
}

function getClipboardData() {
	window.document.clipboardswf.SetVariable('str', CLIPBOARDSWFDATA);
}

function setCopy(text, msg){
	if(BROWSER.ie) {
		clipboardData.setData('Text', text);
		if(msg) {
			showPrompt(null, null, '<span>' + msg + '</span>', 1500);
		}
	} else {
		var msg = '<div class="c"><div style="width: 200px; text-align: center; text-decoration:underline;">點此複製到剪貼板</div>' +
		AC_FL_RunContent('id', 'clipboardswf', 'name', 'clipboardswf', 'devicefont', 'false', 'width', '200', 'height', '40', 'src', STATICURL + 'image/common/clipboard.swf', 'menu', 'false',  'allowScriptAccess', 'sameDomain', 'swLiveConnect', 'true', 'wmode', 'transparent', 'style' , 'margin-top:-20px') + '</div>';
		showDialog(msg, 'info');
		text = text.replace(/[\xA0]/g, ' ');
		CLIPBOARDSWFDATA = text;
	}
}

function copycode(obj) {
	setCopy(BROWSER.ie ? obj.innerText.replace(/\r\n\r\n/g, '\r\n') : obj.textContent, '代碼已複製到剪貼板');
}

function showdistrict(container, elems, totallevel, changelevel) {
	var getdid = function(elem) {
		var op = elem.options[elem.selectedIndex];
		return op['did'] || op.getAttribute('did') || '0';
	};
	var pid = changelevel >= 1 && elems[0] && $(elems[0]) ? getdid($(elems[0])) : 0;
	var cid = changelevel >= 2 && elems[1] && $(elems[1]) ? getdid($(elems[1])) : 0;
	var did = changelevel >= 3 && elems[2] && $(elems[2]) ? getdid($(elems[2])) : 0;
	var coid = changelevel >= 4 && elems[3] && $(elems[3]) ? getdid($(elems[3])) : 0;
	var url = "home.php?mod=misc&ac=ajax&op=district&container="+container
		+"&province="+elems[0]+"&city="+elems[1]+"&district="+elems[2]+"&community="+elems[3]
		+"&pid="+pid + "&cid="+cid+"&did="+did+"&coid="+coid+'&level='+totallevel+'&handlekey='+container+'&inajax=1';
	ajaxget(url, container, '');
}

function setDoodle(fid, oid, url, tid, from) {
	if(tid == null) {
		hideWindow(fid);
	} else {
		$(tid).style.display = '';
		$(fid).style.display = 'none';
	}
	var doodleText = '[img]'+url+'[/img]';
	if($(oid) != null) {
		if(from == "editor") {
			insertImage(url);
		} else if(from == "fastpost") {
			seditor_insertunit('fastpost', doodleText);
		} else if(from == "forumeditor") {
			if(wysiwyg) {
				insertText('<img src="' + url + '" border="0" alt="" />', false);
			} else {
				insertText(doodleText, strlen(doodleText), 0);
			}
		} else {
			insertContent(oid, doodleText);
		}
	}
}

function searchFocus(obj) {
	if(obj.value == '請輸入搜索內容') {
		obj.value = '';
	}
}
function searchBlur(obj) {
	if(obj.value == '' ) {
		obj.value = '請輸入搜索內容';
	}
}
function initSearchmenu() {
	if(!$('sctype') || !$('sctype_menu')) return false;
	var o = $('sctype');
	var s = o.getAttribute('href');
	var a = $('sctype_menu').getElementsByTagName('label');
	var r = $('sctype_menu').getElementsByTagName('input');
	var attr = 'for';
	if(BROWSER.ie && BROWSER.ie < 8) attr = 'htmlFor';
	for(var i=0; i<a.length; i++){
		a[i].onmouseover = function(){
			this.className = 'sca';
		};
		a[i].onmouseout = function(){
			this.className = '';
		};
		a[i].onclick = function(){
			o.innerHTML = this.title;
			o.setAttribute('href', s + '?mod=' + $(this.getAttribute(attr)).value);
		};
		r[i].onclick = function(){
			$('sctype_menu').style.display = 'none';
		};
		if(r[i].checked) {
			o.innerHTML = a[i].title;
		}
	}
}

function inituserabout() {
	if(!$('pp')) {
		return;
	}
	var sideArea = $('pp').parentNode;
	if(sideArea.className == 'sd pph') {
		$('ppc_menu').className = 'p_pop blk';
		$('ppc').onmouseover = function() {getAppIcon();showMenu('ppc');}
	} else {
		getAppIcon();
		$('ppc_menu').className = 'bbs';
		$('ppc_menu').style.display = 'block';
	}
}
function getAppIcon() {
	var appIcons = $('ppc_menu').getElementsByTagName('img');
	for(i=0;i<appIcons.length;i++) {
		if(appIcons[i].className=='appicn'){
			appIcons[i].src = 'http://appicon.manyou.com/icons/' + appIcons[i].name;
		}
	}
}
function showUser() {
	$('newpmbox_menu').style.display = USERABOUT_BOX ? '' : 'none';
	if(USERABOUT_BOX) {
		var x = new Ajax();
		x.get('home.php?mod=spacecp&ac=pm&op=getpmuser&inajax=1', function(s){
			USERABOUT_FS.addDataSource(eval('('+s+')'), 1);
		});
	}
	USERABOUT_BOX = !USERABOUT_BOX;
}

function extstyle(css) {
	if(!$('css_extstyle')) {
		loadcss('extstyle');
	}
	$('css_extstyle').href = css ? css + '/style.css' : STATICURL + 'image/common/extstyle_none.css';
	currentextstyle = css;
	setcookie('extstyle', css, 86400 * 30);
	if($('css_widthauto') && !$('css_widthauto').disabled) {
		CSSLOADED['widthauto'] = 0;
		loadcss('widthauto');
	}
}

function widthauto(obj) {
	if($('css_widthauto')) {
		CSSLOADED['widthauto'] = 1;
	}
	if(!CSSLOADED['widthauto'] || $('css_widthauto').disabled) {
		if(!CSSLOADED['widthauto']) {
			loadcss('widthauto');
		} else {
			$('css_widthauto').disabled = false;
		}
		HTMLNODE.className += ' widthauto';
		setcookie('widthauto', 1, 86400 * 30);
		obj.innerHTML = '切換到窄版';
	} else {
		$('css_widthauto').disabled = true;
		HTMLNODE.className = HTMLNODE.className.replace(' widthauto', '');
		setcookie('widthauto', 0, -1);
		obj.innerHTML = '切換到寬版';
	}
	hideMenu();
}

var secST = new Array();
function updatesecqaa(idhash) {
	if($('secqaa_' + idhash)) {
		$('secqaaverify_' + idhash).value = '';
		if(secST['qaa_' + idhash]) {
			clearTimeout(secST['qaa_' + idhash]);
		}
		$('checksecqaaverify_' + idhash).innerHTML = '<img src="'+ IMGDIR + '/none.gif" width="16" height="16" class="vm" />';
		ajaxget('misc.php?mod=secqaa&action=update&idhash=' + idhash, 'secqaa_' + idhash, null, '', '', function() {
			secST['qaa_' + idhash] = setTimeout(function() {$('secqaa_' + idhash).innerHTML = '<span style="cursor:pointer" class="xi2" onclick="updatesecqaa(\''+idhash+'\')">刷新驗證問答</span>';}, 180000);
		});
	}
}

function updateseccode(idhash, play) {
	if(isUndefined(play)) {
		if($('seccode_' + idhash)) {
			$('seccodeverify_' + idhash).value = '';
			if(secST['code_' + idhash]) {
				clearTimeout(secST['code_' + idhash]);
			}
			$('checkseccodeverify_' + idhash).innerHTML = '<img src="'+ IMGDIR + '/none.gif" width="16" height="16" class="vm" />';
			ajaxget('misc.php?mod=seccode&action=update&idhash=' + idhash, 'seccode_' + idhash, null, '', '', function() {
				secST['code_' + idhash] = setTimeout(function() {$('seccode_' + idhash).innerHTML = '<span style="cursor:pointer" class="xi2" onclick="updateseccode(\''+idhash+'\')">刷新驗證碼</span>';}, 180000);
			});
		}
	} else {
		eval('window.document.seccodeplayer_' + idhash + '.SetVariable("isPlay", "1")');
	}
}

function checksec(type, idhash, showmsg, recall) {
	var showmsg = !showmsg ? 0 : showmsg;
	var secverify = $('sec' + type + 'verify_' + idhash).value;
	if(!secverify) {
		return;
	}
	var x = new Ajax('XML', 'checksec' + type + 'verify_' + idhash);
	x.loading = '';
	$('checksec' + type + 'verify_' + idhash).innerHTML = '<img src="'+ IMGDIR + '/loading.gif" width="16" height="16" class="vm" />';
	x.get('misc.php?mod=sec' + type + '&action=check&inajax=1&&idhash=' + idhash + '&secverify=' + (BROWSER.ie && document.charset == 'utf-8' ? encodeURIComponent(secverify) : secverify), function(s){
		var obj = $('checksec' + type + 'verify_' + idhash);
		obj.style.display = '';
		if(s.substr(0, 7) == 'succeed') {
			obj.innerHTML = '<img src="'+ IMGDIR + '/check_right.gif" width="16" height="16" class="vm" />';
			if(showmsg) {
				recall(1);
			}
		} else {
			obj.innerHTML = '<img src="'+ IMGDIR + '/check_error.gif" width="16" height="16" class="vm" />';
			if(showmsg) {
				if(type == 'code') {
					showDialog('驗證碼錯誤，請重新填寫');
				} else if(type == 'qaa') {
					showDialog('驗證問答錯誤，請重新填寫');
				}
				recall(0);
			}
		}
	});
}

function createPalette(colorid,id) {
	var iframe = "<iframe name=\"c"+colorid+"_frame\" src=\"\" frameborder=\"0\" width=\"210\" height=\"148\" scrolling=\"no\"></iframe>";
	if (!$("c"+colorid+"_menu")) {
		var dom = document.createElement('span');
		dom.id = "c"+colorid+"_menu";
		dom.style.display = 'none';
		dom.innerHTML = iframe;
		$('append_parent').appendChild(dom);
	}
	var base = document.getElementsByTagName('base');
	var baseurl = base ? base[0].getAttribute('href') : '';
	window.frames["c"+colorid+"_frame"].location.href = baseurl+STATICURL+"image/admincp/getcolor.htm?c"+colorid+"|"+id;
	showMenu({'ctrlid':'c'+colorid});
}

function cardInit() {
	var a = document.body.getElementsByTagName('a');
	for(var i = 0;i < a.length;i++){
		if(a[i].getAttribute('c')) {
			a[i].setAttribute('mid', hash(a[i].href));
			a[i].onmouseover = function() {cardShow(this)};
			a[i].onmouseout = function() {clearTimeout(USERCARDST);};
		}
	}
}
function cardShow(obj) {
	if(BROWSER.ie && BROWSER.ie < 7 && obj.href.indexOf('username') != -1) {
		return;
	}
	pos = obj.getAttribute('c') == '1' ? '43' : obj.getAttribute('c');
	USERCARDST = setTimeout(function() {ajaxmenu(obj, 500, 1, 2, pos, null, 'p_pop card');}, 250);
}
function cardUpdatedoing(scdoing, op) {
	if($(scdoing)) {
		if(!op) {
			$('return_' + scdoing).style.display = 'none';
			$(scdoing).style.display = '';
		} else {
			$('return_' + scdoing).style.display = '';
			$(scdoing).style.display = 'none';
		}
	}
}
function cardSubmitdoing(scdoing) {
	ajaxpost(scdoing, 'return_' + scdoing);
	$('return_' + scdoing).style.display = '';
	$(scdoing).style.display = 'none';
}

function lsShowmore() {
	var objpos = fetchOffset($('ls_password'), 1);
	var objh = $('ls_password').offsetHeight;
	$('ls_more').style.position = 'absolute';
	$('ls_more').style.left = objpos['left'] + 'px';
	$('ls_more').style.top = (objpos['top'] + objh) + 'px';
	$('ls_more').style.display = '';
	if(ls_sechash && $('seccode_' + ls_sechash).innerHTML == '') {
		updateseccode(ls_sechash);
	}
}
function lsSubmit() {
	if($('ls_username').value == '' || $('ls_password').value == '') {
		showWindow('login', 'member.php?mod=logging&action=login');
		hideWindow('register');
	} else {
		ajaxpost('lsform', 'return_ls', 'return_ls', 'onerror');
	}
	return false;
}
function errorhandle_ls(str) {
	showDialog(str, 'notice');
}
function succeedhandle_ls(location, str, param) {
	if(param['syn'] != 1) {
		parent.location.href = location;
	} else {
		setTimeout('parent.location.href = location', 2000);
		showDialog(str, 'notice', null, null, 0, null, '登錄中，請稍後...');
	}
}

function navShow(id) {
	var mnobj = $('snav_mn_' + id);
	if(!mnobj) {
		return;
	}
	var uls = $('mu').getElementsByTagName('ul');
	for(i = 0;i < uls.length;i++) {
		if(uls[i].className != 'cl current') {
			uls[i].style.display = 'none';
		}
	}
	if(mnobj.className != 'cl current') {
		showMenu({'ctrlid':'mn_' + id,'menuid':'snav_mn_' + id,'pos':'*'});
		mnobj.className = 'cl floatmu';
		mnobj.style.width = ($('nv').clientWidth) + 'px';
		mnobj.style.display = '';
	}
}

function strLenCalc(obj, checklen, maxlen) {
	var v = obj.value, charlen = 0, maxlen = !maxlen ? 200 : maxlen, curlen = maxlen, len = strlen(v);
	for(var i = 0; i < v.length; i++) {
		if(v.charCodeAt(i) < 0 || v.charCodeAt(i) > 255) {
			curlen -= charset == 'utf-8' ? 2 : 1;
		}
	}
	if(curlen >= len) {
		$(checklen).innerHTML = curlen - len;
	} else {
		obj.value = mb_cutstr(v, maxlen, true);
	}
}

function noticeTitle() {
	NOTICETITLE = {'State':0, 'oldTitle':document.title, flashNumber:0, sleep:15};
	if(!getcookie('noticeTitle')) {
		window.setInterval('noticeTitleFlash();', 500);
	} else {
		window.setTimeout('noticeTitleFlash();', 500);
	}
	setcookie('noticeTitle', 1, 600);
}
function noticeTitleFlash() {
	if(NOTICETITLE.flashNumber < 5 || NOTICETITLE.flashNumber > 4 && !NOTICETITLE['State']) {
		document.title = (NOTICETITLE['State'] ? '【　　　】' : '【新提醒】') + NOTICETITLE['oldTitle'];
		NOTICETITLE['State'] = !NOTICETITLE['State'];
	}
	NOTICETITLE.flashNumber = NOTICETITLE.flashNumber < NOTICETITLE.sleep ? ++NOTICETITLE.flashNumber : 0;
}

if(typeof IN_ADMINCP == 'undefined') {
	if(creditnotice != '' && getcookie('creditnotice')) {
		_attachEvent(window, 'load', showCreditPrompt, document);
	}
	if(typeof showusercard != 'undefined' && showusercard == 1) {
		_attachEvent(window, 'load', cardInit, document);
	}
}

if(BROWSER.ie) {
	document.documentElement.addBehavior("#default#userdata");
}