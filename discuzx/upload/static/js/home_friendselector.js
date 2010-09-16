/*
	[Discuz!] (C)2001-2009 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: home_friendselector.js 15155 2010-08-19 08:16:19Z monkey $
*/

var friendSelector = function(parameter) {
	this.dataSource = {};
	this.selectUser = {};
	this.showObj = $(isUndefined(parameter['showId']) ? 'selectorBox' : parameter['showId']);
	if(!this.showObj) return;
	this.handleObj = $(isUndefined(parameter['searchId']) ? 'valueId' : parameter['searchId']);
	this.showType = isUndefined(parameter['showType']) ? 0 : parameter['showType'];
	this.searchStr = null;
	this.selectNumber = 0;
	this.maxSelectNumber = isUndefined(parameter['maxSelectNumber']) ? 0 : parseInt(parameter['maxSelectNumber']);
	this.allNumber = 0;
	this.handleKey = isUndefined(parameter['handleKey']) ? 'this' : parameter['handleKey'];
	this.selectTabId = isUndefined(parameter['selectTabId']) ? 'selectTabId' : parameter['selectTabId'];
	this.unSelectTabId = isUndefined(parameter['unSelectTabId']) ? 'unSelectTabId' : parameter['unSelectTabId'];
	this.maxSelectTabId = isUndefined(parameter['maxSelectTabId']) ? 'maxSelectTabId' : parameter['maxSelectTabId'];
	this.formId = isUndefined(parameter['formId']) ? '' : parameter['formId'];
	this.filterUser = isUndefined(parameter['filterUser']) ? {} : parameter['filterUser'];
	this.showAll = true;
	this.newPMUser = {};
	this.interlaced = true;
	this.handover = true;
	this.initialize();
	return this;
};

friendSelector.prototype.addDataSource = function(data, clear) {
	if(typeof data == 'object') {
		var userData = data['userdata'];
		var clear = isUndefined(clear) ? 0: clear;
		if(clear) {
			this.showObj.innerHTML = "";
		}
		for(var i in userData) {
			if(typeof this.filterUser[i] != 'undefined') {
				continue;
			}
			var append = clear ? true : false;
			if(typeof this.dataSource[i] == 'undefined') {
				this.dataSource[i] = userData[i];
				append = true;
				this.allNumber++;
			}
			if(append) {
				this.interlaced = !this.interlaced;
				this.append(i);
			}
		}
		if(this.showType == 1) {
			this.showSelectNumber();
		} else if(this.showType == 2) {
			if(this.newPMUser) {
				window.setInterval(this.handleKey+".handoverCSS()", 400);
			}
		}
	}
};
friendSelector.prototype.addFilterUser = function(data) {
	var filterData = {};
	if(typeof data != 'object') {
		filterData[data] = data;
	} else if(typeof data == 'object') {
		filterData = data;
	} else {
		return false;
	}
	for(var id in filterData) {
		this.filterUser[filterData[id]] = filterData[id];
	}
	return true;
};

friendSelector.prototype.handoverCSS = function() {
	for(var uid in this.newPMUser) {
		$('avt_'+uid).className = this.handover ? 'avt newpm' : 'avt';
	}
	this.handover = !this.handover;
};

friendSelector.prototype.handleEvent = function(key, event) {
	this.showObj.innerHTML = "";
	var username = '';
	this.searchStr = '';
	if(key != "") {
		var reg = new RegExp(key, "ig");
		this.searchStr = key;
		for(var uid in this.dataSource) {
			username = this.dataSource[uid]['username'];
			if(username.match(reg)) {
				this.append(uid, 1);
			}
		}
	} else {
		for(var uid in this.dataSource) {
			this.append(uid);
		}
	}
};

friendSelector.prototype.directionKeyDown = function(event) {

};

friendSelector.prototype.clearDataSource = function() {
	this.dataSource = {};
	this.selectUser = {};
};

friendSelector.prototype.showUser = function(type) {
	this.showObj.innerHTML = '';
	type = isUndefined(type) ? 0 : parseInt(type);
	this.showAll = true;
	if(type == 1) {
		for(var uid in this.selectUser) {
			this.append(uid);
		}
		this.showAll = false;
	} else {
		for(var uid in this.dataSource) {
			if(type == 2) {
				if(typeof this.selectUser[uid] != 'undefined') {
					continue;
				}
				this.showAll = false;
			}
			this.append(uid);
		}
	}
	if(this.showType == 1) {
		for(var i = 0; i < 3; i++) {
			$('showUser_'+i).className = '';
		}
		$('showUser_'+type).className = 'a brs';
	}
};

friendSelector.prototype.append = function(uid, filtrate) {

	filtrate = isUndefined(filtrate) ? 0 : filtrate;
	var liObj = document.createElement("li");
	var username = this.dataSource[uid]['username'];
	liObj.userid = this.dataSource[uid]['uid'];
	if(typeof this.selectUser[uid] != 'undefined') {
		liObj.className = "a";
	}
	if(filtrate) {
		var reg  = new RegExp("(" + this.searchStr + ")","ig");
		username = username.replace(reg , "<strong>$1</strong>");
	}
	if(this.showType == 1) {
		liObj.innerHTML = '<a href="javascript:;" id="' + liObj.userid + '" onclick="' + this.handleKey + '.select(this.id)" class="cl"><span class="avt brs" style="background-image: url(' + this.dataSource[uid]['avatar'] + ');"><span></span></span><span class="d">' + username + '</span></a>';
	} else {
		if(this.dataSource[uid]['new'] && typeof this.newPMUser[uid] == 'undefined') {
			this.newPMUser[uid] = uid;
		}
		liObj.className = this.interlaced ? 'alt' : '';
		liObj.innerHTML = '<div id="avt_' + liObj.userid + '" class="avt"><a href="home.php?mod=spacecp&ac=pm&op=showmsg&handlekey=showmsg_' + liObj.userid + '&touid=' + liObj.userid + '&pmid='+this.dataSource[uid]['pmid']+'&daterange='+this.dataSource[uid]['daterange']+'" title="'+username+'" id="avatarmsg_' + liObj.userid + '" onclick="'+this.handleKey+'.delNewFlag(' + liObj.userid + ');showWindow(\'showMsgBox\', this.href, \'get\', 0);"><img src="' + this.dataSource[uid]['avatar'] + '" alt="'+username+'" /></a></div><p><a class="xg1" href="home.php?mod=spacecp&ac=pm&op=showmsg&handlekey=showmsg_' + liObj.userid + '&touid=' + liObj.userid + '&pmid='+this.dataSource[uid]['pmid']+'&daterange='+this.dataSource[uid]['daterange']+'" title="'+username+'" id="usernamemsg_' + liObj.userid + '" onclick="'+this.handleKey+'.delNewFlag(' + liObj.userid + ');showWindow(\'showMsgBox\', this.href, \'get\', 0);">'+username+'</a></p>';
	}
	this.showObj.appendChild(liObj);
};

friendSelector.prototype.select = function(uid) {
	uid = parseInt(uid);
	if(uid){
		var select = false;
		if(typeof this.selectUser[uid] == 'undefined') {
			if(this.maxSelectNumber && this.selectNumber >= this.maxSelectNumber) {
	            alert('最多只允許選擇'+this.maxSelectNumber+'個用戶');
	            return false;
	        }
			this.selectUser[uid] = this.dataSource[uid];
			this.selectNumber++;
			if(this.showType == '1') {
				$(uid).parentNode.className = 'a';
			}
			select = true;
		} else {
			delete this.selectUser[uid];
			this.selectNumber--;
			$(uid).parentNode.className = '';

		}
		if(this.formId != '') {
			var formObj = $(this.formId);
			var opId = 'selUids_' + uid;
			if(select) {
				var inputObj = document.createElement("input");
				inputObj.type = 'hidden';
				inputObj.id = opId;
				inputObj.name = 'uids[]';
				inputObj.value = uid;
				formObj.appendChild(inputObj);
			} else {
				formObj.removeChild($(opId));
			}
		}
		if(this.showType == 1) {
			this.showSelectNumber();
		}
	}
};
friendSelector.prototype.delNewFlag = function(uid) {
	delete this.newPMUser[uid];
};

friendSelector.prototype.showSelectNumber = function() {
	if(typeof $(this.selectTabId) != 'undefined') {
		$(this.selectTabId).innerHTML = this.selectNumber;
	}
	if(typeof $(this.unSelectTabId) != 'undefined') {
		$(this.unSelectTabId).innerHTML = this.allNumber - this.selectNumber;
	}
	if(this.maxSelectNumber && typeof $(this.maxSelectTabId) != 'undefined') {
		$(this.maxSelectTabId).innerHTML = this.maxSelectNumber -this.selectNumber;
	}

};

friendSelector.prototype.initialize = function() {
	var instance = this;
	this.handleObj.onkeyup = function(event) {
		instance.handleEvent(this.value, event);
	}
};