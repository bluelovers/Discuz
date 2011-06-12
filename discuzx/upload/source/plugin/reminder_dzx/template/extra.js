var getnewcount = 0, getnewtimeout = 5000, getnew_handle, first = 1, querystring, fid, newreminder;
function getnew(query_string, settime) {
	var timeout = isUndefined(settime) ? getnewtimeout : settime;
	var x = new Ajax();
	query_string = isUndefined(query_string) ? '' : query_string;
	x.get('plugin.php?id=reminder_dzx&inajax=yes&action=checknew'+query_string+'&fresh=' + Math.random(), function(s){
		if(typeof s === 'string' && s.indexOf('dataempty') == -1) {
			var list = [];
			var jsndata = (new Function("return " + s))();
			newreminder = makeuplist(jsndata, list);
			insertobjnode($('rtip'), 'div', 'rr_close', 'addclose', jsndata.lasttime);
			$('contents').innerHTML = list.join('');

			startremind();
			if(!isUndefined(getnew_handle)) {
				clearTimeout(getnew_handle);
			}
		} else {
			if(getnewcount < 101) {
				if(getnewcount > 0) {
					var multiple =  Math.ceil(100 / getnewcount);
					if(multiple < 10) {
						timeout = getnewtimeout * (10 - multiple + 1);
					}
				}
				getnew_handle = setTimeout(function () {getnew(query_string);}, timeout);
			}
		}
		getnewcount++;
	});
	first = 0;

}

$('remindtip').style.height='0px';
var handle;
function startremind() {
	var obj = $("remindtip");
	if(parseInt(obj.style.height) == 0) {
		obj.style.display = "block";
		handle = setInterval("changeH('up')", 20);
	} else {
		handle = setInterval("changeH('down')", 4);
	}
}

function changeH(str) {
	var obj = $("remindtip");
	if(str == "up") {
		if(parseInt(obj.style.height) > 160) {
			clearInterval(handle);
		} else {
			obj.style.height = (parseInt(obj.style.height) + 8).toString()+"px";
		}
	}

	if(str == "down") {
		if(parseInt(obj.style.height) < 8) {
			clearInterval(handle);
			obj.style.display="none";
			getnew_handle = setTimeout(function () {getnew(querystring);}, getnewtimeout);
		} else {
			obj.style.height=(parseInt(obj.style.height)-8).toString()+"px";
		}
	}
}

function makeuplist(jsndata, list) {
	var title;
	for(var one in jsndata) {
		switch(one) {
			case 'newprompt':
				newreminder = 'newprompt';
				for(var two in jsndata[one]) {
					newreminder = newreminder+'_'+two
					if(!isUndefined(jsndata[one][two].author)) {
						title = jsndata[one][two].note;
						list.push('<li>' + title + '</li>');
					}
				}
				break;
			case 'newpm':
				newreminder = 'newpm';
				for(var two in jsndata[one]) {
					newreminder = newreminder+'_'+two
					list.push('<li><a href="home.php?mod=space&uid='+jsndata[one][two].lastauthorid+'" target="_blank">'+jsndata[one][two].lastauthor+'</a> 说 :<span id="p_gpmid_'+jsndata[one][two].pmid+'">'+jsndata[one][two].lastsummary+'</span> &nbsp; <a href="home.php?mod=space&do=pm&subop=view&touid='+jsndata[one][two].lastauthorid+'#last" id="pmlist_'+jsndata[one][two].pmid+'_a" target="_blank">回复</a></li>');
				}
				break;
			case 'newthread':
				newreminder = 'newthread';
				for(var two in jsndata[one]) {
					newreminder = newreminder+'_'+two
					list.push('</li>' + '本版有新的回复主题： <a href="forum.php?mod=viewthread&tid='+jsndata[one][two].tid+'" target="_blank">' + jsndata[one][two].subject + '</a></li>');
				}
				break;
		}
	}
	return newreminder;
}

function insertobjnode(obj, insertobj, id, nextid, lasttime) {
	var type = getcookie('reminder').split('D');
	var poptype = type[2].split('_');
	var remindertype = newreminder.split('_');
	if(remindertype[0] == 'newprompt') {
		rtype = 0;
	} else if(remindertype[0] == 'newpm') {
		rtype = 1;
	} else if(remindertype[0] == 'newthread') {
		rtype = 2;
	}
	querystring = '&type=' + type['2'] + '&time=' + lasttime + (!isUndefined(fid) ? '&fid=' + fid : '');
	$('r_close').onclick = function(){startremind();poptype[rtype] == 3 && clearnew(newreminder);return false;};
}

function clearnew(type) {
	var x = new Ajax();
	x.get('plugin.php?id=reminder_dzx&inajax=yes&action=clearnew&new='+type+'&fresh=' + Math.random(), function(s){});
}

function deleteQueryNotice(uid, type) {
	var dlObj = $(type + '_' + uid);
	if(dlObj != null) {
		var id = dlObj.getAttribute('notice');
		var x = new Ajax();
		x.get('home.php?mod=misc&ac=ajax&op=delnotice&inajax=1&id='+id, function(s){
			dlObj.parentNode.removeChild(dlObj);
		});
	}
}