var j = jQuery.noConflict();

if (typeof disallowfloat == 'undefined' || disallowfloat === null) {
	var disallowfloat = '';
}

var currentNormalEditDisplay = 0;

j(document).ready(function() {
	ajaxGetSearchResultThreads();

	j('#previewForm').submit(function() {
		return previewFormSubmit();
	});

});

function previewFormSubmit() {
	saveAllThread();

		if (!selectedTopicId || selectedNormalIds.length < 1) {
		alert('Select at least one topic to Push');
			return false;
		}

		var i = 1;
		for (var k = 1; k <= 5; k++) {
			var input_displayorder = j('#normal_thread_' + k).find('.preview_displayorder');
			if (input_displayorder.size()) {
				input_displayorder.val(i);
				i++;
			}
		}
		return true;
}

function initSelect() {
	var initTopicObj = j('#search_result .qqqun_op .qqqun_op_topon');
	initTopicObj.addClass('qqqun_op_top');
	initTopicObj.removeClass('qqqun_op_topon');
	var initNormalObj = j('#search_result .qqqun_op .qqqun_op_liston');
	initNormalObj.addClass('qqqun_op_list');
	initNormalObj.removeClass('qqqun_op_liston');
	selectedTopicId = parseInt(selectedTopicId);
	if (selectedTopicId) {
		j('#thread_addtop_' + selectedTopicId).addClass('qqqun_op_topon');
		j('#thread_addtop_' + selectedTopicId).removeClass('qqqun_op_top');
	}
	j.each(selectedNormalIds, function(k, v) {
		v = parseInt(v);
		if (v) {
			j('#thread_addlist_' + v).addClass('qqqun_op_liston');
			j('#thread_addlist_' + v).removeClass('qqqun_op_list');
		}
	});
}

function ajaxChangeSearch() {
	j('#srchtid').val('');
	ajaxGetSearchResultThreads();
}

function ajaxGetSearchResultThreads() {
	j('#search_result').html('<tr><td colspan="3">Loading...</td></tr>');
	ajaxpost('search_form', 'search_result', null, null, null, function() {initSelect(); return false});
	return false;
}

function ajaxGetPageResultThreads(page, mpurl) {
	j('#search_result').html('<tr><td colspan="3">加载中...</td></tr>');
	if (typeof page == 'undefined' || page === null) {
		page = 1;
	}
	if (typeof mpurl == 'undefined' || !mpurl) {
		return false;
	}
	ajaxget(mpurl + '&page=' + page, 'search_result', null, null, null, function() {initSelect();} );
}

function addMiniportalTop(tid) {
	tid = parseInt(tid);
	if (j.inArray(tid, selectedNormalIds) > -1) {
		removeNormalThreadByTid(tid);
	}
	addMiniportalTopId(tid);
	initSelect();
	ajaxget(adminscript + '?action=cloud&operation=qqgroup&anchor=block&op=getTopicThread&tid=' + tid, 'topicDiv', null, null, null, function() { clickTopicEditor(); });
}

function addMiniportalTopId(tid) {
	selectedTopicId = tid;
}

function showPreviewEditor(topic, hideall) {
	if (hideall) {
		j('.qqqun_list .qqqun_editor').hide();
		j('.qqqun_list .qqqun_xl li').removeClass('current');
		j('.qqqun_list').removeClass('qqqun_list_editor');
		j('.qqqun_top .qqqun_editor').hide();
		j('.qqqun_top').removeClass('qqqun_top_editor');
	} else {
		if (topic) {
			j('.qqqun_list .qqqun_editor').hide();
			j('.qqqun_list .qqqun_xl li').removeClass('current');
			j('.qqqun_list').removeClass('qqqun_list_editor');
			j('.qqqun_top .qqqun_editor').show();
			j('.qqqun_top').addClass('qqqun_top_editor');
		} else {
			j('.qqqun_list .qqqun_editor').show();
			j('.qqqun_list').addClass('qqqun_list_editor');
			j('.qqqun_list .qqqun_xl li').removeClass('current');
			j('.qqqun_top .qqqun_editor').hide();
			j('.qqqun_top').removeClass('qqqun_top_editor');
		}
	}

}

function clickTopicEditor(topicFocus) {
	if (typeof topicFocus == 'undefined') {
		var topicFocus = 'title';
	}
	showPreviewEditor(true, false);
	if (topicFocus == 'title') {
		j('#topic-editor-input-title').addClass('pt_focus');
		j('#topic-editor-input-title').focus();
	} else if (topicFocus == 'content') {
		j('#topic-editor-textarea-content').addClass('pt_focus');
		j('#topic-editor-textarea-content').focus();
	}
	currentNormalEditDisplay = 0;
}

function blurTopic(obj) {
	var thisobj = j(obj);
	thisobj.removeClass('pt_focus');
}

function clickNormalEditor(obj) {
	var thisobj = j(obj);
	showPreviewEditor(false, false);
	thisobj.addClass('pt_focus');
	thisobj.focus();
	currentNormalEditDisplay = parseInt(thisobj.parent().attr('displayorder'));
}

function blurNormalTextarea(obj) {
	var thisobj = j(obj);
	liObj = thisobj.parent();
	var displayorder = parseInt(liObj.attr('displayorder'));
	if (displayorder == currentNormalEditDisplay) {
		liObj.addClass('current');
	}
	j('.qqqun_list .qqqun_xl textarea').removeClass('pt_focus');
}

function addMiniportalList(tid) {
	tid = parseInt(tid);
	if (j.inArray(tid, selectedNormalIds) > -1) {
		return false;
	}
	if (selectedNormalIds.length >= 5) {
		alert('Push Post number has reached five, in the right to cancel a number and try again.');
		return false;
	}
	if (tid == selectedTopicId) {
		selectedTopicId = 0;
		ajaxget(adminscript + '?action=cloud&operation=qqgroup&anchor=block&op=getTopicThread&tid=0', 'topicDiv');
	}
	addMiniportalListId(tid);
	initSelect();
	var insertPos = 'normal_thread_' + selectedNormalIds.length;
	ajaxget(adminscript + '?action=cloud&operation=qqgroup&anchor=block&op=getNormalThread&tid=' + tid, insertPos, null, null, null, function() { clickNormalEditor(j('#' + insertPos).find('textarea')); });
}

function addMiniportalListId(tid) {
	selectedNormalIds.push(tid);
}

function editNormalThread() {
	var threadLi = j('#normal_thread_' + currentNormalEditDisplay);
	clickNormalEditor(threadLi.find('textarea'));
}

function saveAllThread() {

	showPreviewEditor(false, true);

	currentNormalEditDisplay = 0;
}

function moveNormalThread(up) {
	var displayorder = currentNormalEditDisplay;
	var threadLi = j('#normal_thread_' + displayorder);
	if (!threadLi.attr('id') || !displayorder) {
		return false;
	}
	var newDisplayorder = 0;
	if (up) {
		newDisplayorder = displayorder - 1;
	} else {
		newDisplayorder = displayorder + 1;
	}
	if (newDisplayorder < 1 || newDisplayorder > 5) {
		return false;
	}
	var newLiId = 'normal_thread_' + newDisplayorder;
	var newThreadLi = j('#' + newLiId);
	if (!newThreadLi.find('textarea').size()) {
		return false;
	}
	var tmpHtml = newThreadLi.html();
	newThreadLi.html(threadLi.html());
	threadLi.html(tmpHtml);
	newThreadLi.addClass('current');
	threadLi.removeClass('current');
	currentNormalEditDisplay = newDisplayorder;
}

function removeTopicThread(tid) {
	tid = parseInt(tid);
	selectedTopicId = 0;
	initSelect();
	ajaxget(adminscript + '?action=cloud&operation=qqgroup&anchor=block&op=getTopicThread', 'topicDiv', null, null, null, function() { showPreviewEditor(false, true)});
}

function removeNormalThread() {
	var displayorder = currentNormalEditDisplay;
	var removeTid = parseInt(j('#normal_thread_' + displayorder).find('.normal_thread_tid').val());
	return removeNormalThreadByDisplayorderAndTid(displayorder, removeTid, true);
}

function removeNormalThreadByTid(tid) {
	tid = parseInt(tid);
	var threadInput = j('.qqqun_list .qqqun_xl .normal_thread_tid[value="' + tid + '"]');
	if (threadInput.size()) {
		var displayorder = threadInput.parent().attr('displayorder');
		var removeTid = tid;
		return removeNormalThreadByDisplayorderAndTid(displayorder, removeTid, false);
	}
}

function removeNormalThreadByDisplayorderAndTid(displayorder, removeTid, inNormalEditor) {
	displayorder = parseInt(displayorder);
	removeTid = parseInt(removeTid);
	var threadLi = j('#normal_thread_' + displayorder);
	if (!threadLi.attr('id') || !displayorder) {
		return false;
	}
	threadLi.removeClass('current');
	var index = j.inArray(removeTid, selectedNormalIds);
	if (index != -1) {
		selectedNormalIds.splice(index, 1);
	}
	initSelect();
	if (typeof inNormalEditor == 'udefined') {
		var inNormalEditor = false;
	}
	threadLi.slideUp(100, function() { removeNormalThreadRecall(displayorder, inNormalEditor)});

}

function removeNormalThreadRecall(displayorder, inNormalEditor) {
	for (var i = displayorder; i <= 5; i++) {
		var currentDisplayorder = i;
		var nextDisplayorder = i + 1;
		var currentLiId = 'normal_thread_' + currentDisplayorder;
		var currentThreadLi = j('#' + currentLiId);
		var nextLiId = 'normal_thread_' + nextDisplayorder;
		var nextThreadLi = j('#' + nextLiId);
		if (nextThreadLi.find('textarea').size()) {
			currentThreadLi.html(nextThreadLi.html());
			currentThreadLi.show();
		} else {
			currentThreadLi.html('');
			currentThreadLi.hide();
			break;
		}
	}
	var threadLi = j('#normal_thread_' + displayorder);
	var prevDisplayorder = displayorder - 1;
	if (threadLi.find('textarea').size()) {
		if (inNormalEditor) {
			threadLi.addClass('current');
		}
		currentNormalEditDisplay = displayorder;
	} else if (prevDisplayorder) {
		var prevThreadLi = j('#normal_thread_' + prevDisplayorder);
		if (inNormalEditor) {
			prevThreadLi.addClass('current');
		}
		currentNormalEditDisplay = prevDisplayorder;
	} else {
		var firstThreadLi =  j('#normal_thread_1');
		if (inNormalEditor) {
			saveAllThread();
		}
		firstThreadLi.html('<div class="tips">Click on the left <img src="static/image/admincp/cloud/qun_op_list.png" align="absmiddle" /> Will push the information to the list</div>');
		firstThreadLi.show();
	}
}

function ajaxUploadQQGroupImage() {
	j('#uploadImageResult').parent().show();
	j('#uploadImageResult').text('Upload image, please wait...');
	ajaxpost('uploadImage', 'uploadImageResult', null, null, null, 'uploadRecall()');
}

function uploadRecall() {
	if(j('#uploadImageResult').find('#upload_msg_success').size()) {
		j('#uploadImageResult').parent().show();
		var debug_rand = Math.random();
		var imagePath = j('#uploadImageResult #upload_msg_imgpath').text();
		var imageUrl = j('#uploadImageResult #upload_msg_imgurl').text();
		j('#topic_image_value').val(imagePath);
		j('#topic_editor_thumb').attr('src', imageUrl + '?' + debug_rand);
		j('#topic_preview_thumb').attr('src', imageUrl + '?' + debug_rand);
		setTimeout(function() {hideWindow('uploadImgWin');}, 2000);
	}
}