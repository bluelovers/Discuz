/*
	[Discuz!] (C)2001-2009 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: portal_upload.js 15155 2010-08-19 08:16:19Z monkey $
*/

var nowid = 0;

var extensions = '';

function addAttach() {
	newnode = $('upload').cloneNode(true);
	var id = nowid;
	var tags;

	newnode.id = 'upload_' + id;
	tags = newnode.getElementsByTagName('input');
	for(i in tags) {
		if(tags[i].name == 'attach') {
			tags[i].id = 'attach_' + id;
			tags[i].name = 'attach';
			tags[i].onchange = function() {insertAttach(id)};
			tags[i].unselectable = 'on';
		}
	}
	tags = newnode.getElementsByTagName('span');
	for(i in tags) {
		if(tags[i].id == 'localfile') {
			tags[i].id = 'localfile_' + id;
		}
	}
	nowid++;

	$('attachbody').appendChild(newnode);
}

function insertAttach(id) {

	var path = $('attach_' + id).value;
	if(path == '') {
		return;
	}
	var ext = path.lastIndexOf('.') == -1 ? '' : path.substr(path.lastIndexOf('.') + 1, path.length).toLowerCase();
	var re = new RegExp("(^|\\s|,)" + ext + "($|\\s|,)", "ig");
	if(extensions != '' && (re.exec(extensions) == null || ext == '')) {
		alert('對不起，不支持上傳此類文件');
		return;
	}
	var localfile = $('attach_' + id).value.substr($('attach_' + id).value.replace(/\\/g, '/').lastIndexOf('/') + 1);
	$('localfile_' + id).innerHTML = localfile + ' 上傳中...';
	$('attach_' + id).style.display = 'none';
	$('upload_' + id).action += '&attach_target_id=' + id;
	$('upload_' + id).submit();

	addAttach();
}

function deleteAttach(attachid, url) {
	ajaxget(url);
	$('attach_list_' + attachid).style.display = 'none';
}

function setConver(attach) {
	$('conver').value = attach;
}

addAttach();