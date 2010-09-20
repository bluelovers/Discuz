
/**
 * [品牌空间] (C)2001-2010 Comsenz Inc. This is NOT a freeware, use is subject to
 * license terms
 * 
 * $Id: admincp.js 4420 2010-09-13 10:04:50Z fanshengshuai $
 */

function redirect(url) {
	window.location.replace(url);
}
function checkall(form, prefix, checkall, type, changestyle) {

	var checkall = checkall ? checkall: 'chkall';
	var type = type ? type: 'name';

	for (var i = 0; i < form.elements.length; i++) {
		var e = form.elements[i];

		if (type == 'value' && e.type == "checkbox" && e.name != checkall) {
			if (e.name != checkall && (prefix && e.value == prefix)) {
				e.checked = form.elements[checkall].checked;
			}
		} else if (type == 'name' && e.type == "checkbox" && e.name != checkall) {
			if ((!prefix || (prefix && e.name.match(prefix)))) {
				e.checked = form.elements[checkall].checked;
				if(changestyle && e.parentNode && e.parentNode.tagName.toLowerCase() == 'li') {
					e.parentNode.className = e.checked ? 'checked' : '';
				}
			}
		}

	}

}

function altStyle(obj) {
	function altStyleClear(obj) {
		var input, lis, i;
		lis = obj.parentNode.getElementsByTagName('li');
		for (i = 0; i < lis.length; i++) {
			lis[i].className = '';
		}
	}

	var input, lis, i, cc, o;
	cc = 0;
	lis = obj.getElementsByTagName('li');
	for (i = 0; i < lis.length; i++) {
		lis[i].onclick = function(e) {
			o = BROWSER.ie ? event.srcElement.tagName: e.target.tagName;
			if (cc) {
				return;
			}
			cc = 1;
			input = this.getElementsByTagName('input')[0];
			if (input.getAttribute('type') == 'checkbox' || input.getAttribute('type') == 'radio') {
				if (input.getAttribute('type') == 'radio') {
					altStyleClear(this);
				}

				if (BROWSER.ie || o != 'INPUT' && input.onclick) {
					input.click();
				}
				if (this.className != 'checked') {
					this.className = 'checked';
					input.checked = true;
				} else {
					this.className = '';
					input.checked = false;
				}
			}
		}
		lis[i].onmouseup = function(e) {
			cc = 0;
		}
	}
}

var addrowdirect = 0;
function addrow(obj, type) {
	var table = obj.parentNode.parentNode.parentNode.parentNode;
	if (!addrowdirect) {
		var row = table.insertRow(obj.parentNode.parentNode.parentNode.rowIndex);
	} else {
		var row = table.insertRow(obj.parentNode.parentNode.parentNode.rowIndex + 1);
	}
	var typedata = rowtypedata[type];
	for (var i = 0; i <= typedata.length - 1; i++) {
		var cell = row.insertCell(i);
		cell.colSpan = typedata[i][0];
		var tmp = typedata[i][1];
		if (typedata[i][2]) {
			cell.className = typedata[i][2];
		}
		tmp = tmp.replace(/\{(\d+)\}/g, function($1, $2) {
			return addrow.arguments[parseInt($2) + 1];
		});
		cell.innerHTML = tmp;
	}
	addrowdirect = 0;
}

function dropmenu(obj) {
	showMenu({
		'ctrlid': obj.id,
		'menuid': obj.id + 'child',
		'evt': 'mouseover'
	});
	$(obj.id + 'child')[0].style.top = (parseInt($(obj.id + 'child')[0].style.top) - document.documentElement.scrollTop) + 'px';
}

function textareasize(obj, op) {
	if (!op) {
		if (obj.scrollHeight > 70) {
			obj.style.height = (obj.scrollHeight < 300 ? obj.scrollHeight: 300) + 'px';
			if (obj.style.position == 'absolute') {
				obj.parentNode.style.height = obj.style.height;
			}
		}
	} else {
		if (obj.style.position == 'absolute') {
			obj.style.position = '';
			obj.style.width = '';
			obj.parentNode.style.height = '';
		} else {
			obj.parentNode.style.height = obj.parentNode.offsetHeight + 'px';
			obj.style.width = BROWSER.ie > 6 || ! BROWSER.ie ? '90%': '600px';
			obj.style.position = 'absolute';
		}
	}
}

function showanchor(obj) {
	var navs = $('#submenu LI');
	for (var i = 0; i < navs.length; i++) {
		if (navs[i].id.substr(0, 4) == 'nav_' && navs[i].id != obj.id) {
			navs[i].className = '';
			$("#" + navs[i].id.substr(4))[0].style.display = 'none';
			if ($("#" + navs[i].id.substr(4) + '_tips')[0]) $("#" + navs[i].id.substr(4) + '_tips')[0].style.display = 'none';
		}
	}
	obj.className = 'current';
	currentAnchor = obj.id.substr(4);
	$("#" + currentAnchor)[0].style.display = '';
	if ($("#" + currentAnchor + '_tips')[0]) $("#" + currentAnchor + '_tips').show("fast");
	if ($("#" + currentAnchor + 'form')[0]) {
		$("#" + currentAnchor + 'form')[0].anchor.value = currentAnchor;
	} else if ($("#" + 'cpform')[0]) {
		$("#" + 'cpform')[0].anchor.value = currentAnchor;
	}
}

function updatecolorpreview(obj) {
	$("#" + obj)[0].style.background = $("#" + obj + '_v')[0].value;
}

function entersubmit(e, name) {
	var e = e ? e: event;
	if (e.keyCode != 13) {
		return;
	}
	var tag = BROWSER.ie ? e.srcElement.tagName: e.target.tagName;
	if (tag != 'TEXTAREA') {
		doane(e);
		if ($('#submit_' + name)[0].offsetWidth) {
			$('#formscrolltop')[0].value = document.documentElement.scrollTop;
			$('#submit_' + name)[0].click();
		}
	}
}

function parsetag(tag) {
	var str = document.body.innerHTML.replace(/(^|>)([^<]+)(?=<|$)/ig, function($1, $2, $3) {
		if (tag && $3.indexOf(tag) != - 1) {
			$3 = $3.replace(tag, '<h_>');
		}
		return $2 + $3;
	});
	document.body.innerHTML = str.replace(/<h_>/ig, function($1, $2) {
		return '<font color="#c60a00">' + tag + '</font>';
	});
}

function showMenu(v) {
	var ctrlid = isUndefined(v['ctrlid']) ? v: v['ctrlid'];
	var showid = isUndefined(v['showid']) ? ctrlid: v['showid'];
	var menuid = isUndefined(v['menuid']) ? showid + '_menu': v['menuid'];
	var ctrlObj = $("#" + ctrlid)[0];
	var menuObj = $("#" + menuid)[0];
	if (!menuObj) return;
	var mtype = isUndefined(v['mtype']) ? 'menu': v['mtype'];
	var evt = isUndefined(v['evt']) ? 'mouseover': v['evt'];
	var pos = isUndefined(v['pos']) ? '43': v['pos'];
	var layer = isUndefined(v['layer']) ? 1: v['layer'];
	var duration = isUndefined(v['duration']) ? 2: v['duration'];
	var timeout = isUndefined(v['timeout']) ? 250: v['timeout'];
	var maxh = isUndefined(v['maxh']) ? 500: v['maxh'];
	var cache = isUndefined(v['cache']) ? 1: v['cache'];
	var drag = isUndefined(v['drag']) ? '': v['drag'];
	var dragobj = drag && $("#" + drag)[0] ? $("#" + drag)[0] : menuObj;
	var fade = isUndefined(v['fade']) ? 0: v['fade'];
	var cover = isUndefined(v['cover']) ? 0: v['cover'];
	var zindex = isUndefined(v['zindex']) ? JSMENU['zIndex']['menu'] : v['zindex'];
	if (typeof JSMENU['active'][layer] == 'undefined') {
		JSMENU['active'][layer] = [];
	}

	if (evt == 'click' && in_array(menuid, JSMENU['active'][layer]) && mtype != 'win') {
		hideMenu(menuid, mtype);
		return;
	}
	if (mtype == 'menu') {
		hideMenu(layer, mtype);
	}

	if (ctrlObj) {
		if (!ctrlObj.initialized) {
			ctrlObj.initialized = true;
			ctrlObj.unselectable = true;

			ctrlObj.outfunc = typeof ctrlObj.onmouseout == 'function' ? ctrlObj.onmouseout: null;
			ctrlObj.onmouseout = function() {
				if (this.outfunc) this.outfunc();
				if (duration < 3 && ! JSMENU['timer'][menuid]) JSMENU['timer'][menuid] = setTimeout('hideMenu(\'' + menuid + '\', \'' + mtype + '\')', timeout);
			};

			ctrlObj.overfunc = typeof ctrlObj.onmouseover == 'function' ? ctrlObj.onmouseover: null;
			ctrlObj.onmouseover = function(e) {
				doane(e);
				if (this.overfunc) this.overfunc();
				if (evt == 'click') {
					clearTimeout(JSMENU['timer'][menuid]);
					JSMENU['timer'][menuid] = null;
				} else {
					for (var i in JSMENU['timer']) {
						if (JSMENU['timer'][i]) {
							clearTimeout(JSMENU['timer'][i]);
							JSMENU['timer'][i] = null;
						}
					}
				}
			};
		}
	}

	var dragMenu = function(menuObj, e, op) {
		e = e ? e: window.event;
		if (op == 1) {
			if (in_array(BROWSER.ie ? e.srcElement.tagName: e.target.tagName, ['TEXTAREA', 'INPUT', 'BUTTON', 'SELECT'])) {
				return;
			}
			JSMENU['drag'] = [e.clientX, e.clientY];
			JSMENU['drag'][2] = parseInt(menuObj.style.left);
			JSMENU['drag'][3] = parseInt(menuObj.style.top);
			document.onmousemove = function(e) {
				try {
					dragMenu(menuObj, e, 2);
				} catch(err) {}
			};
			document.onmouseup = function(e) {
				try {
					dragMenu(menuObj, e, 3);
				} catch(err) {}
			};
			doane(e);
		} else if (op == 2 && JSMENU['drag'][0]) {
			var menudragnow = [e.clientX, e.clientY];
			menuObj.style.left = (JSMENU['drag'][2] + menudragnow[0] - JSMENU['drag'][0]) + 'px';
			menuObj.style.top = (JSMENU['drag'][3] + menudragnow[1] - JSMENU['drag'][1]) + 'px';
			doane(e);
		} else if (op == 3) {
			JSMENU['drag'] = [];
			document.onmousemove = null;
			document.onmouseup = null;
		}
	};

	if (!menuObj.initialized) {
		menuObj.initialized = true;
		menuObj.ctrlkey = ctrlid;
		menuObj.mtype = mtype;
		menuObj.layer = layer;
		menuObj.cover = cover;
		if (ctrlObj && ctrlObj.getAttribute('fwin')) {
			menuObj.scrolly = true;
		}
		menuObj.style.position = 'absolute';
		menuObj.style.zIndex = zindex + layer;
		menuObj.onclick = function(e) {
			if (!e || BROWSER.ie) {
				window.event.cancelBubble = true;
				return window.event;
			} else {
				e.stopPropagation();
				return e;
			}
		};
		if (duration < 3) {
			if (duration > 1) {
				menuObj.onmouseover = function() {
					clearTimeout(JSMENU['timer'][menuid]);
					JSMENU['timer'][menuid] = null;
				};
			}
			if (duration != 1) {
				menuObj.onmouseout = function() {
					JSMENU['timer'][menuid] = setTimeout('hideMenu(\'' + menuid + '\', \'' + mtype + '\')', timeout);
				};
			}
		}
		if (drag) {
			dragobj.style.cursor = 'move';
			dragobj.onmousedown = function(event) {
				try {
					dragMenu(menuObj, event, 1);
				} catch(e) {}
			};
		}
		if (cover) {
			var coverObj = document.createElement('div');
			coverObj.id = menuid + '_cover';
			coverObj.style.position = 'absolute';
			coverObj.style.zIndex = menuObj.style.zIndex - 1;
			coverObj.style.left = coverObj.style.top = '0px';
			coverObj.style.width = '100%';
			coverObj.style.height = document.body.scrollHeight + 'px';
			coverObj.style.backgroundColor = '#000';
			coverObj.style.filter = 'progid:DXImageTransform.Microsoft.Alpha(opacity=50)';
			coverObj.style.opacity = 0.5;
			$('#append_parent')[0].appendChild(coverObj);
		}
	}
	menuObj.style.display = '';
	if (cover) $("#" + menuid + '_cover')[0].style.display = '';
	if (fade) {
		var O = 0;
		var fadeIn = function(O) {
			if (O == 100) {
				clearTimeout(fadeInTimer);
				return;
			}
			menuObj.style.filter = 'progid:DXImageTransform.Microsoft.Alpha(opacity=' + O + ')';
			menuObj.style.opacity = O / 100;
			O += 10;
			var fadeInTimer = setTimeout(function() {
				fadeIn(O);
			},
			50);
		};
		fadeIn(O);
		menuObj.fade = true;
	} else {
		menuObj.style.filter = 'progid:DXImageTransform.Microsoft.Alpha(opacity=100)';
		menuObj.style.opacity = 1;
		menuObj.fade = false;
	}
	setMenuPosition(showid, menuid, pos);
	if (maxh && menuObj.scrollHeight > maxh) {
		menuObj.style.height = maxh + 'px';
		if (BROWSER.opera) {
			menuObj.style.overflow = 'auto';
		} else {
			menuObj.style.overflowY = 'auto';
		}
	}

	if (!duration) {
		setTimeout('hideMenu(\'' + menuid + '\', \'' + mtype + '\')', timeout);
	}

	if (!in_array(menuid, JSMENU['active'][layer])) JSMENU['active'][layer].push(menuid);
	menuObj.cache = cache;
	if (layer > JSMENU['layer']) {
		JSMENU['layer'] = layer;
	}
}
function hideMenu(attr, mtype) {
	attr = isUndefined(attr) ? '': attr;
	mtype = isUndefined(mtype) ? 'menu': mtype;
	if (attr == '') {
		for (var i = 1; i <= JSMENU['layer']; i++) {
			hideMenu(i, mtype);
		}
		return;
	} else if (typeof attr == 'number') {
		for (var j in JSMENU['active'][attr]) {
			hideMenu(JSMENU['active'][attr][j], mtype);
		}
		return;
	} else if (typeof attr == 'string') {
		var menuObj = $("#" + attr)[0];
		if (!menuObj || (mtype && menuObj.mtype != mtype)) return;
		clearTimeout(JSMENU['timer'][attr]);
		var hide = function() {
			if (menuObj.cache) {
				menuObj.style.display = 'none';
				if (menuObj.cover) $("#" + attr + '_cover')[0].style.display = 'none';
			} else {
				menuObj.parentNode.removeChild(menuObj);
				if (menuObj.cover) $("#" + attr + '_cover')[0].parentNode.removeChild($("#" + attr + '_cover')[0]);
			}
			var tmp = [];
			for (var k in JSMENU['active'][menuObj.layer]) {
				if (attr != JSMENU['active'][menuObj.layer][k]) tmp.push(JSMENU['active'][menuObj.layer][k]);
			}
			JSMENU['active'][menuObj.layer] = tmp;
		};
		if (menuObj.fade) {
			var O = 100;
			var fadeOut = function(O) {
				if (O == 0) {
					clearTimeout(fadeOutTimer);
					hide();
					return;
				}
				menuObj.style.filter = 'progid:DXImageTransform.Microsoft.Alpha(opacity=' + O + ')';
				menuObj.style.opacity = O / 100;
				O -= 10;
				var fadeOutTimer = setTimeout(function() {
					fadeOut(O);
				},
				50);
			};
			fadeOut(O);
		} else {
			hide();
		}
	}
}
function setMenuPosition(showid, menuid, pos) {
	var showObj = $("#" + showid)[0];
	var menuObj = menuid ? $("#" + menuid)[0] : $("#" + showid + '_menu')[0];
	if (isUndefined(pos)) pos = '43';
	var basePoint = parseInt(pos.substr(0, 1));
	var direction = parseInt(pos.substr(1, 1));
	var sxy = sx = sy = sw = sh = ml = mt = mw = mcw = mh = mch = bpl = bpt = 0;

	if (!menuObj || (basePoint > 0 && ! showObj)) return;
	if (showObj) {
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

	switch (basePoint) {
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
	switch (direction) {
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
	if (in_array(direction, [1, 4]) && ml < 0) {
		ml = bpl;
		if (in_array(basePoint, [1, 4])) ml += sw;
	} else if (ml + mw > document.documentElement.scrollLeft + document.body.clientWidth && sx >= mw) {
		ml = bpl - mw;
		if (in_array(basePoint, [2, 3])) ml -= sw;
	}
	if (in_array(direction, [1, 2]) && mt < 0) {
		mt = bpt;
		if (in_array(basePoint, [1, 2])) mt += sh;
	} else if (mt + mh > document.documentElement.scrollTop + document.documentElement.clientHeight && sy >= mh) {
		mt = bpt - mh;
		if (in_array(basePoint, [3, 4])) mt -= sh;
	}
	if (pos == '210') {
		ml += 69 - sw / 2;
		mt -= 5;
		if (showObj.tagName == 'TEXTAREA') {
			ml -= sw / 2;
			mt += sh / 2;
		}
	}
	if (direction == 0 || menuObj.scrolly) {
		if (BROWSER.ie && BROWSER.ie < 7) {
			if (direction == 0) mt += Math.max(document.documentElement.scrollTop, document.body.scrollTop);
		} else {
			if (menuObj.scrolly) mt -= Math.max(document.documentElement.scrollTop, document.body.scrollTop);
			menuObj.style.position = 'fixed';
		}
	}
	if (ml) menuObj.style.left = ml + 'px';
	if (mt) menuObj.style.top = mt + 'px';
	if (direction == 0 && BROWSER.ie && ! document.documentElement.clientHeight) {
		menuObj.style.position = 'absolute';
		menuObj.style.top = (document.body.clientHeight - menuObj.clientHeight) / 2 + 'px';
	}
	if (menuObj.style.clip && ! BROWSER.opera) {
		menuObj.style.clip = 'rect(auto, auto, auto, auto)';
	}
}

var JSMENU = [];
JSMENU['active'] = [];
JSMENU['timer'] = [];
JSMENU['drag'] = [];
JSMENU['layer'] = 0;
JSMENU['zIndex'] = {
	'win': 200,
	'menu': 300,
	'prompt': 400,
	'dialog': 500
};
JSMENU['float'] = '';
function in_array(needle, haystack) {
	if (typeof needle == 'string' || typeof needle == 'number') {
		for (var i in haystack) {
			if (haystack[i] == needle) {
				return true;
			}
		}
	}
	return false;
}

function isUndefined(variable) {
	return typeof variable == 'undefined' ? true: false;
}

function showhidenObj(obj, flag) {
	if (flag == 1) {
		$('#' + obj).show();
	} else {
		$('#' + obj).hide();
		if (flag != 0) {
			$('#' + obj).css("display", flag);
		}
	}
}

function getbyid(id) {
	if (document.getElementById) {
		return document.getElementById(id);
	} else if (document.all) {
		return document.all[id];
	} else if (document.layers) {
		return document.layers[id];
	} else {
		return null;
	}
}

function reset_style(id) {
	$("#c" + id + "_v")[0].value="";
	$('#c'+id+'').css('background', '#000');
	var objsubject = $('#'+id+'')[0];
	var objfontcolor = $('#c'+id+'_v')[0];
	$('#em'+id)[0].checked = false;
	$('#strong'+id)[0].checked = false;
	$('#underline'+id)[0].checked = false;
	$('#c'+id+'').css('background', '#000');
	objsubject.style.color = "#000";

	objsubject.style.fontStyle = "";
	objsubject.style.fontWeight = "";
	objsubject.style.textDecoration = "none";
}

function load_style(id, color) {
	var objsubject = $('#'+id)[0];
	var objfontcolor = $('#c'+id+'_v')[0];
	var objem = $('#em'+id)[0];
	var objstrong = $('#strong'+id)[0];
	var objunderline = $('#underline'+id)[0];

	// $.style.color 在 IE 下和firefox下的值不一样

	var colorstr = objsubject.style.color;

	if ($.trim(color) != "") {
		$('#c'+id+'_v').val(color);
		$('#c'+id+'').css('background', color);
	} else {
		$('#c'+id+'').css('background', '#000');
	}

	if (objsubject.style.fontWeight == "bold") {
		objstrong.checked = true;
	} else {
		objstrong.checked = false;
	}
	if (objsubject.style.fontStyle == "italic") {
		objem.checked = true;
	} else {
		objem.checked = false;
	}
	if (objsubject.style.textDecoration == "underline") {
		objunderline.checked = true;
	} else {
		objunderline.checked = false;
	}
}

/**
 * 改变样式
 * 
 * @param id
 */
function set_style(id) {
	var objsubject = $('#'+id+'')[0];
	var objfontcolor = $('#c'+id+'_v')[0];
	var objem = $('#em'+id)[0];
	var objstrong = $('#strong'+id)[0];
	var objunderline = $('#underline'+id)[0];
	objsubject.style.color = objfontcolor.value;
	if (objem.checked == true) {
		objsubject.style.fontStyle = "italic";
	} else {
		objsubject.style.fontStyle = "";
	}
	if (objstrong.checked == true) {
		objsubject.style.fontWeight = "bold";
	} else {
		objsubject.style.fontWeight = "";
	}
	if (objunderline.checked == true) {
		objsubject.style.textDecoration = "underline";
	} else {
		objsubject.style.textDecoration = "none";
	}
}

function strLen(str) {
	var charset = is_ie ? document.charset: document.characterSet;
	var len = 0;
	for (var i = 0; i < str.length; i++) {
		len += str.charCodeAt(i) < 0 || str.charCodeAt(i) > 255 ? (charset.toLowerCase() == "utf-8" ? 3: 2) : 1;
	}
	return len;
}

function rgbToHex(color) {
	color = color.replace("rgb(", "")
	color = color.replace(")", "")
	color = color.split(",")

	r = parseInt(color[0]);
	g = parseInt(color[1]);
	b = parseInt(color[2]);

	r = r.toString(16);
	if (r.length == 1) {
		r = '0' + r;
	}
	g = g.toString(16);
	if (g.length == 1) {
		g = '0' + g;
	}
	b = b.toString(16);
	if (b.length == 1) {
		b = '0' + b;
	}
	return ("#" + r + g + b).toUpperCase();
}

function start_edit_album_subject (album_id) {
	$("#label_subject_" + album_id).html('');
	$('#div_subject_' + album_id).show();
	$('#input_subject_' + album_id).focus();
}

function start_edit_photo_subject(photo_id) {
	$("#label_subject_" + photo_id).html('');
	$('#div_subject_' + photo_id).show();
	$('#input_subject_' + photo_id).focus();
}

function edit_album_subject(album_id, subject) {
	$.get("?action=ajax&opt=edit_album_subject", {album_id:album_id,subject:subject}, function (data, textStatus){
		this;
		
		$("#div_subject_" + album_id).hide();
		$("#label_subject_" + album_id).html(subject + "&nbsp;<img src=\"static/image/ico_edit.png\" />");
	});
}

function edit_photo_subject(photo_id, subject) {
	$.get("?action=ajax&opt=edit_photo_subject", {photo_id:photo_id,subject:subject}, function (data, textStatus){
		this;
		
		$("#div_subject_" + photo_id).hide();
		$("#label_subject_" + photo_id).html(subject + "&nbsp;<img src=\"static/image/ico_edit.png\" />");
	});
}
