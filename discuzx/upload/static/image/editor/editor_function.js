function uploadEdit(obj) {
	mainForm = obj.form;
	forms = $('attachbody').getElementsByTagName("FORM");
	albumid = $('uploadalbum').value;
	edit_save();
	upload();
}

function edit_save() {
	var p = window.frames['uchome-ifrHtmlEditor'];
	var obj = p.window.frames['HtmlEditor'];
	var status = p.document.getElementById('uchome-editstatus').value;
	if(status == 'code') {
		$('uchome-ttHtmlEditor').value = p.document.getElementById('sourceEditor').value;
	} else if(status == 'text') {
		if(BROWSER.ie) {
			obj.document.body.innerText = p.document.getElementById('dvtext').value;
			$('uchome-ttHtmlEditor').value = obj.document.body.innerHTML;
		} else {
			obj.document.body.textContent = p.document.getElementById('dvtext').value;
			var sOutText = obj.document.body.innerHTML;
			$('uchome-ttHtmlEditor').value = sOutText.replace(/\r\n|\n/g,"<br>");
		}
	} else {
		$('uchome-ttHtmlEditor').value = obj.document.body.innerHTML;
	}
	backupContent($('uchome-ttHtmlEditor').value);
}

function relatekw() {
	edit_save();
	var subject = cnCode($('subject').value);
	var message = cnCode($('uchome-ttHtmlEditor').value);
	if(message) {
		message = message.substr(0, 500);
	}
	var x = new Ajax();
	x.get('home.php?mod=spacecp&ac=relatekw&inajax=1&subjectenc=' + subject + '&messageenc=' + message, function(s){
		$('tag').value = s;
	});
}

function downRemoteFile() {
	edit_save();
	var formObj = $("articleform");
	var oldAction = formObj.action;
	formObj.action = "portal.php?mod=portalcp&ac=upload&op=downremotefile";
	formObj.onSubmit = "";
	formObj.target = "uploadframe";
	formObj.submit();
	formObj.action = oldAction;
	formObj.target = "";
}
function backupContent(sHTML) {
	if(sHTML.length > 11) {
		var obj = $('uchome-ttHtmlEditor').form;
		if(!obj) return;
		var data = subject = message = '';
		for(var i = 0; i < obj.elements.length; i++) {
			var el = obj.elements[i];
			if(el.name != '' && (el.tagName == 'TEXTAREA' || el.tagName == 'INPUT' && (el.type == 'text' || el.type == 'checkbox' || el.type == 'radio')) && el.name.substr(0, 6) != 'attach') {
				var elvalue = el.value;
				if(el.name == 'subject' || el.name == 'title') {
					subject = trim(elvalue);
				} else if(el.name == 'message' || el.name == 'content') {
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

		if(!subject && !message) {
			return;
		}
		saveUserdata('home', data);
	}
}

function edit_insert(html) {
	var p = window.frames['uchome-ifrHtmlEditor'];
	var obj = p.window.frames['HtmlEditor'];
	var status = p.document.getElementById('uchome-editstatus').value;
	if(status != 'html') {
		alert('本操作只在多媒体编辑模式下才有效');
		return;
	}
	obj.focus();
	if(BROWSER.ie){
		var f = obj.document.selection.createRange();
		f.pasteHTML(html);
		f.collapse(false);
		f.select();
	} else {
		obj.document.execCommand('insertHTML', false, html);
	}
}

function insertImage(image, url) {
	url = typeof url == 'undefined' || url === null ? image : url;
	var html = '<p><a href="' + url + '" target="_blank"><img src="'+image+'"></a></p>';
	edit_insert(html);
}

function insertFile(file, url) {
	url = typeof url == 'undefined' || url === null ? image : url;
	var html = '<p><a href="' + url + '" target="_blank" class="attach">' + file + '</a></p>';
	edit_insert(html);
}