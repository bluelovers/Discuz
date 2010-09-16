/*
	[Discuz!] (C)2001-2009 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: forum.js 15378 2010-08-24 01:44:13Z monkey $
*/

function saveData(ignoreempty) {
	var ignoreempty = isUndefined(ignoreempty) ? 0 : ignoreempty;
	var obj = $('postform') && (($('fwin_newthread') && $('fwin_newthread').style.display == '') || ($('fwin_reply') && $('fwin_reply').style.display == '')) ? $('postform') : ($('fastpostform') ? $('fastpostform') : $('postform'));
	if(!obj) return;
	var data = subject = message = '';
	for(var i = 0; i < obj.elements.length; i++) {
		var el = obj.elements[i];
		if(el.name != '' && (el.tagName == 'TEXTAREA' || el.tagName == 'INPUT' && (el.type == 'text' || el.type == 'checkbox' || el.type == 'radio')) && el.name.substr(0, 6) != 'attach') {
			var elvalue = el.value;
			if(el.name == 'subject') {
				subject = trim(elvalue);
			} else if(el.name == 'message') {
				if(typeof wysiwyg != 'undefined' && wysiwyg == 1) {
					elvalue = html2bbcode(editdoc.body.innerHTML);
				}
				message = trim(elvalue);
			}
			if((el.type == 'checkbox' || el.type == 'radio') && !el.checked) {
				continue;
			}
			if(trim(elvalue)) {
				data += el.name + String.fromCharCode(9) + el.tagName + String.fromCharCode(9) + el.type + String.fromCharCode(9) + elvalue + String.fromCharCode(9, 9);
			}
		}
	}

	if(!subject && !message && !ignoreempty) {
		return;
	}

	saveUserdata('forum', data);
}

function switchAdvanceMode(url) {
	var obj = $('postform') && (($('fwin_newthread') && $('fwin_newthread').style.display == '') || ($('fwin_reply') && $('fwin_reply').style.display == '')) ? $('postform') : $('fastpostform');
	if(obj && obj.message.value != '') {
		saveData();
		url += (url.indexOf('?') != -1 ? '&' : '?') + 'cedit=yes';
	}
	location.href = url;
	return false;
}

function sidebar_collapse(lang) {
	if(lang[0]) {
		toggle_collapse('sidebar', null, null, lang);
		$('wrap').className = $('wrap').className == 'wrap with_side s_clear' ? 'wrap s_clear' : 'wrap with_side s_clear';
	} else {
		var collapsed = getcookie('collapse');
		collapsed = updatestring(collapsed, 'sidebar', 1);
		setcookie('collapse', collapsed, (collapsed ? 2592000 : -2592000));
		location.reload();
	}
}

function keyPageScroll(e, prev, next, url, page) {
	e = e ? e : window.event;
	var tagname = BROWSER.ie ? e.srcElement.tagName : e.target.tagName;
	if(tagname == 'INPUT' || tagname == 'TEXTAREA') return;
	actualCode = e.keyCode ? e.keyCode : e.charCode;
	if(next && actualCode == 39) {
		window.location = url + '&page=' + (page + 1);
	}
	if(prev && actualCode == 37) {
		window.location = url + '&page=' + (page - 1);
	}
}

function announcement() {
	var ann = new Object();
	ann.anndelay = 3000;ann.annst = 0;ann.annstop = 0;ann.annrowcount = 0;ann.anncount = 0;ann.annlis = $('anc').getElementsByTagName("li");ann.annrows = new Array();
	ann.announcementScroll = function () {
		if(this.annstop) { this.annst = setTimeout(function () { ann.announcementScroll(); }, this.anndelay);return; }
		if(!this.annst) {
			var lasttop = -1;
			for(i = 0;i < this.annlis.length;i++) {
				if(lasttop != this.annlis[i].offsetTop) {
					if(lasttop == -1) lasttop = 0;
					this.annrows[this.annrowcount] = this.annlis[i].offsetTop - lasttop;this.annrowcount++;
				}
				lasttop = this.annlis[i].offsetTop;
			}
			if(this.annrows.length == 1) {
				$('an').onmouseover = $('an').onmouseout = null;
			} else {
				this.annrows[this.annrowcount] = this.annrows[1];
				$('ancl').innerHTML += $('ancl').innerHTML;
				this.annst = setTimeout(function () { ann.announcementScroll(); }, this.anndelay);
				$('an').onmouseover = function () { ann.annstop = 1; };
				$('an').onmouseout = function () { ann.annstop = 0; };
			}
			this.annrowcount = 1;
			return;
		}
		if(this.annrowcount >= this.annrows.length) {
			$('anc').scrollTop = 0;
			this.annrowcount = 1;
			this.annst = setTimeout(function () { ann.announcementScroll(); }, this.anndelay);
		} else {
			this.anncount = 0;
			this.announcementScrollnext(this.annrows[this.annrowcount]);
		}
	};
	ann.announcementScrollnext = function (time) {
		$('anc').scrollTop++;
		this.anncount++;
		if(this.anncount != time) {
			this.annst = setTimeout(function () { ann.announcementScrollnext(time); }, 10);
		} else {
			this.annrowcount++;
			this.annst = setTimeout(function () { ann.announcementScroll(); }, this.anndelay);
		}
	};
	ann.announcementScroll();
}

function removeindexheats() {
	return confirm('您確認要把此主題從熱點主題中移除麼？');
}

function showTypes(id) {
	var o = $(id);
	if(!o) return false;
	var s = o.className;
	var baseh = o.getElementsByTagName('li')[0].offsetHeight * 2;
	var tmph = o.offsetHeight;
	var lang = ['展開', '收起'];
	var cls = ['unfold', 'fold'];
	if(tmph > baseh) {
		var octrl = document.createElement('li');
		octrl.className = cls[0];
		octrl.innerHTML = lang[0];

		o.insertBefore(octrl, o.firstChild);
		o.className = s + ' cttp';

		octrl.onclick = function () {
			if(this.className == cls[0]) {
				o.style.height = 'auto';
				this.className = cls[1];
				this.innerHTML = lang[1];
			} else {
				o.style.height = '';
				this.className = cls[0];
				this.innerHTML = lang[0];
			}
		}
	}
}

function fastpostvalidate(theform, noajaxpost) {
	noajaxpost = !noajaxpost ? 0 : noajaxpost;
	s = '';
	if(theform.message.value == '' && theform.subject.value == '') {
		s = '請完成標題或內容欄。';
		theform.message.focus();
	} else if(mb_strlen(theform.subject.value) > 80) {
		s = '您的標題超過 80 個字符的限制。';
		theform.subject.focus();
	}
	if(!disablepostctrl && ((postminchars != 0 && mb_strlen(theform.message.value) < postminchars) || (postmaxchars != 0 && mb_strlen(theform.message.value) > postmaxchars))) {
		s = '您的帖子長度不符合要求。\n\n當前長度: ' + mb_strlen(theform.message.value) + ' ' + '字節\n系統限制: ' + postminchars + ' 到 ' + postmaxchars + ' 字節';
	}
	if(s) {
		$('fastpostreturn').className = 'onerror';
		$('fastpostreturn').innerHTML = s;
		$('fastpostsubmit').disabled = false;
		return false;
	}
	$('fastpostsubmit').disabled = true;
	theform.message.value = parseurl(theform.message.value);
	if(!noajaxpost) {
		ajaxpost('fastpostform', 'fastpostreturn', 'fastpostreturn', 'onerror', $('fastpostsubmit'));
		return false;
	} else {
		return true;
	}
}

function succeedhandle_fastnewpost(locationhref, message, param) {
	location.href = locationhref;
}

function errorhandle_fastnewpost() {
	$('fastpostsubmit').disabled = false;
}

function atarget(obj) {
	obj.target = getcookie('atarget') ? '_blank' : '';
}

function setatarget(v) {
	$('atarget').className = 'y atarget_' + v;
	$('atarget').onclick = function() { setatarget(v ? 0 : 1); };
	setcookie('atarget', v, (v ? 2592000 : -1));
}