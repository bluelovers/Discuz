
/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: common.js 4468 2010-09-15 02:56:02Z fanshengshuai $
 */

/**
 * 发送短消息对话框
 * @param pm_to 要发给用户ID
 */
function pm_send(pm_to) {
	show_pm_box();
	$("#iframe_pm")[0].src = 'pm.php?act=sendbox&msgto=' + pm_to + '&inajax=1';
	$("#pm_border").css({
		"height": '260px'
	});
	$("#pm_border").show("slow");
	return false;
}

/**
 * 店铺通知
 */
function pm_view(pm_type) {
	show_pm_box();
	$("#iframe_pm")[0].src = 'pm.php?act=list&msgtype=' + pm_type + '&inajax=1';
	$("#pm_border").css({
		"height": '440px'
	});
	$("#pm_border").show("slow");
	return false;
}

function show_pm_box() {
	if (!$("#pm_border")[0]) {
		$("#append_parent").append('<div id="pm_border" style="display:none;"></div>');
	}
	$("#pm_border").html('<div onclick="pm_close();" style="position:absolute;margin-left:480px;margin-top:5px;z-index:10006;cursor:pointer;color:#999;" class="pm_close">关闭</div><iframe id="iframe_pm" frameborder=0 width="100%" height="100%"></iframe>');
	$("#pm_border").css({
		"top": ($(document).scrollTop() + 100) + "px",
		"left": ($(document).width() - 520) / 2 + "px"
	});
}

/**
 * 关闭发送消息的层
 */
function pm_close() {
	$("#pm_border").hide("slow");
}

/**
 * 显示举报层
 * @param type 举报对象类型
 * @param id 举报对象ID
 */
function report(type, id) {
	if (!$("#reportdiv")[0]) {
		$("#append_parent").append('<div id="reportdiv" style="display:none;"></div>');
	}
	$("#reportdiv").html('<div onclick="closereportdiv();" style="position:absolute;margin-left:270px;margin-top:5px;z-index:10006;cursor:pointer;" class="pm_close">关闭</div><iframe src="report.php?type=' + type + '&id=' + id + '" frameborder=0 width="100%" height="100%"></iframe>');
	$("#reportdiv").css({
		"top": ($(document).scrollTop() + 100) + "px",
		"left": ($(document).width() - 520) / 2 + "px"
	});
	$("#reportdiv").show("slow");
	return false;
}

/**
 * 关闭举报层
 */
function closereportdiv() {
	$("#reportdiv").hide("slow");
}

/**
 * 登录验证码
 */
function updateseccode() {
	var img = 'seccode.php?rand=' + Math.random();
	if ($("#img_seccode")) {
		$("#img_seccode").attr('src', img);
	}
}

function showseccode() {
	if ($("#login_authcode_img").css('display') != 'block') {
		$("#login_authcode_img").css('display', 'block');
	}
}

function addseccode() {
	if ($("#login_authcode_img").css('display') == 'block') {
		$("#login_authcode_img").css('display', 'none');
	}
}

function updatecomseccode(height) {
	var img = 'seccode.php?rand=' + Math.random() + '&h=' + height;
	if ($("#img_comseccode")) {
		$("#img_comseccode").attr('src', img);
	}
}

function showcomseccode() {
	if ($("#com_authcode_img").css('display') != 'block') {
		$("#com_authcode_img").css('display', 'block');
	}
}

function addcomseccode() {
	if ($("#com_authcode_img").css('display') == 'block') {
		$("#com_authcode_img").css('display', 'none');
	}
}

function submitcheck() {
	obj = $('#seccode')[0];
	if (obj && obj.value == '') {
		showseccode();
		obj.focus();
		return false;
	}
}

function comsubmitcheck() {
	obj = $('#comseccode')[0];
	if (obj && obj.value == '') {
		showcomseccode();
		obj.focus();
		return false;
	}
}

function jump_to_url(url) {
	location = url;
}

function ajaxform_failed(data) {
	data.find("item").each(function() {
		obj = $(this).attr('name');
		msg = $(this).text();

		input_box = $('#' + obj);

		if (!input_box[0]) {
			if ($(":input[name='" + obj + "']")[0]) {
				input_box = $(":input[name='" + obj + "']");
			}

			if (!input_box[0]) {
				if ($('#input_' + obj)[0]) {
					input_box = $('#input_' + obj);
				}
			}
		}

		if (input_box[0]) {
			input_box.css({
				'background': '#FCD0FF'
			});

			input_box.bind("focus", function() {
				$(this).css({
					'background': ''
				});
				$('#span_' + $(this).attr("name").replace('input_', '')).css('color', '#999999');
				$('#ajax_status_display').html('');
			});

		}

		if ($('#span_' + obj)[0]) {
			$('#span_' + obj).css('color', 'red');
			$('#span_' + obj).html(msg);
		} else {
			alert(msg);
		}
	});
}

function ajaxform_newcomment(data) {
	msg = $.trim(data.find("message").text());
	content = $.trim(data.find("content").text());
	if (content != '') {
		$('#postlistreply').append(content);
		$('#commentmessage').val('');
	}
}

function ajaxform_newrecomment(data) {
	url = $.trim(data.find("url").text());
	msg = $.trim(data.find("message").text());
	upcid = $.trim(data.find("upcid").text());
	content = $.trim(data.find("content").text());
	if (content != '') {
		$('#commentdl' + upcid).append(content);
		$('#commentmessage').val('');
		setTimeout("jump_to_url(url)", 2000);
	}
}

function ajaxform_ok(data) {
	url = $.trim(data.find("url").text());
	msg = $.trim(data.find("message").text());
	if (url != "") {
		setTimeout("jump_to_url(url)", 2000);
	} else {
		alert(msg);
	}
}



/**
 * 绑定模拟AJAX表单提交事件
 * 
 */
function bindform(formname) {
	$(document).ready(function() {
		var ajaxframeid = '_FSS_' + (new Date().getTime());
		var ajaxframe;
		var io;
		var formaction = $('#' + formname)[0].action;
		var __form = $('#' + formname);
		
		try {
			__test_frame = $('<iframe id="__test_' + ajaxframeid + '" name="__test_' + ajaxframeid + '" src="" width="100" height="100" />');
			__test_frame.appendTo('body');
			__test_io = __test_frame[0];
			__test_doc = __test_io.contentWindow ? __test_io.contentWindow.document: __test_io.contentDocument ? __test_io.contentDocument: __test_io.document;
			data = $(__test_doc.XMLDocument ? __test_doc.XMLDocument: __test_doc);
			__test_frame.remove();
		}catch(err) { __test_frame.remove(); return;}

		$('#' + formname).submit(function() {
			ajaxframe = $('<iframe id="' + ajaxframeid + '" name="' + ajaxframeid + '" src="about:blank;" onload="(jQuery(this).data(\'ajaxform-onload\'))()" />');
			ajaxframe.appendTo('body');
			ajaxframe.data('ajaxform-onload', hanlde_data);

			ajaxframe.css({
				position: 'absolute',
				top: '-1000px',
				left: '-1000px'
			});
			io = ajaxframe[0];
			document.getElementById(formname).target = ajaxframeid;
			document.getElementById(formname).action = formaction + '&inajax=1&submit_time=' + (new Date().getTime());

			function hanlde_data() {
				var data, doc;
				doc = io.contentWindow ? io.contentWindow.document: io.contentDocument ? io.contentDocument: io.document;
				var isXml = doc.XMLDocument || $.isXMLDoc(doc);
				if (isXml) {
					data = $(doc.XMLDocument ? doc.XMLDocument: doc);

					if (data.find("message").text() != '') {
						$('#ajax_status_display').html('<span style="color:red;font-weight:bold;">' + data.find("message").text() + '</span>');
					}

					if (data.find("status").text().toUpperCase() == 'OK') {
						ajaxform_ok(data);
					} else if (data.find("status").text().toUpperCase() == 'FAILED') {
						ajaxform_failed(data);
					} else if (data.find("status").text().toUpperCase() == 'NEWCOMMENT') {
						ajaxform_newcomment(data);
					} else if (data.find("status").text().toUpperCase() == 'NEWRECOMMENT') {
						ajaxform_newrecomment(data);
					} else {
						error_trace(data[0].innerHTML);
					}
				} else {
					try {
						data = doc.body ? doc.body.innerHTML: null;
						if (data == "") {
							error_trace('服务器没有任何返回结果。');
						} else {
							error_trace(data);
						}
					} catch(err) {
						error_trace("服务器内部错误！");
					}
					
				}
				setTimeout(function() {
					ajaxframe.removeData('ajaxform-onload');
					ajaxframe.remove();
					data = null;
				},
				100);
			}
		});
		return false;
	});
}

/**
 * 模拟AJAX提交后，如果出现错误，则显示错误信息
 * @param data
 */
function error_trace(data) {
	 $('#ajax_status_display').html("<div id=\"div_error_trace\" style=\"background:#E1E1E1; padding:5px; width:600px; z-index:10000;\"><div style=\"background:#fff; border:#A7A6A6 1px solid; text-align:left; padding:10px;\">"+data+"</div></div>");
}

/**
 * 多级联动菜单
 * 
 */
function createmultiselect(select_id, select_name, select_content, select_parent, select_init_val) {
	var selector = 1;
	$('#' + select_id).attr('name', select_name);
	$('#' + select_id).bind('change', function() {
		creat(this.id);
	});
	function creat(id) {
		var originalrid = $('#' + id + '').val();
		var csid = id.split("_")[1];
		var newinnercontent = '';
		for (var i in select_content) {
			if (select_content[i].upid == originalrid) {
				newinnercontent += "<option value=\"" + select_content[i].catid + "\"" + ((typeof(upid) != "undefined" && select_content[i].catid == upid) || (typeof(value) != "undefined" && select_content[i].catid == value) ? " selected=\"selected\"": "") + ">" + select_content[i].name + "</option>";
			}
		}
		var selectlength = $('#' + select_parent + ' select').length;
		if (selectlength > 1) {
			for (var i = selectlength; i > 0; i--) {
				var cid = $('#' + select_parent + ' select:nth-child(' + i + ')').attr('id');
				if (cid.split("_")[1] > csid) {
					$('#' + select_parent + ' select:nth-child(' + i + ')').remove();
				}
			}

		}
		if (newinnercontent != '') {
			$("#" + id + "").after('<select><option value="-1">' + select_init_val + '</option>' + newinnercontent + '</select>');
			$("#" + id + "").removeAttr("name");
			$("#" + id + " + select").attr('name', select_name);
			$("#" + id + " + select").attr('id', 'selector' + select_name + '_' + selector);
			$("#" + id + " + select").bind('change', function() {
				creat(this.id);
			});
		} else {
			$("#" + id).attr('name', select_name);
		}
		selector = $('#' + select_parent + ' select').length;
		if (selector == 1) {
			$('#' + select_id).attr('name', select_name);
		}
	}
}
function groupbuy_userdel(itemid) {
	return;
}

function show_comment_score_area() {
	show = $("#comment_score_area").css('display');
	if(show == 'none') {
		$("#comment_score_area").show();
		$("#ico_opt").attr('src','static/image/ico_dec.png');
		$("#ico_opt").attr('title','收起');
	} else {
		$("#comment_score_area").hide();
		$("#ico_opt").attr('src','static/image/ico_add.png');
		$("#ico_opt").attr('title','展开');
	}
}


function resize_image(img, w, h) {
	if (img.width <= w && img.height <= h) return;

	img_wh = img.width/img.height;

	if (img_wh < (w/h)) {
		if (img.width > w) {
			img.width = w;
			img.height = w / img_wh;
		} else {
			img.width = img_wh * h;
			img.height = h;
		}
	}
}
