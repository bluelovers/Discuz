/*
	[Discuz!] (C)2001-2009 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: portal_upload.js 18682 2010-12-01 03:35:10Z zhangguosheng $
	Translate by : Discuzindo
*/

var nowid = 0;

var extensions = '';

function addAttach() {
	var newnode = $('upload').cloneNode(true);
	var id = nowid;
	var tags;

	newnode.id = 'upload_' + id;
	tags = newnode.getElementsByTagName('input');
	for(i in tags) {
		if(tags[i].name == 'attach') {
			tags[i].id = 'attach_' + id;
			tags[i].name = 'attach';
			tags[i].onchange = function() {this.form.action = this.form.action.replace(/catid\=\d/, 'catid='+$('catid').value);insertAttach(id)};
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
		alert('Sorry, such file does not support the upload');
		return;
	}
	var localfile = $('attach_' + id).value.substr($('attach_' + id).value.replace(/\\/g, '/').lastIndexOf('/') + 1);
	$('localfile_' + id).innerHTML = localfile + ' Uploading...';
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