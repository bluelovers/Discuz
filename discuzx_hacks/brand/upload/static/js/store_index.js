
/**
 *      [品牌空間] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: store_index.js 4303 2010-09-03 01:17:02Z fanshengshuai $
 */

function showCaptcha() {
	$("#hiddenCaptcha").attr("style", "display:block");
	if (!$("img#captcha").attr("src")) $("img#captcha").attr("src", "do.php?action=seccode&rand=" + new Date().getTime());
}

function getRemoteCaptcha() {
	$("img#captcha").attr("src", "do.php?action=seccode&rand=" + new Date().getTime());
	$('input#inputCaptcha').val('');
}

String.prototype.trim = function() {
	return this.replace(/(^\s*)|(\s*$)/g, "");
};

function showReplyForm(obj) {
	$(obj).parents('div').children('form').eq(0).show();
}

function showEditReplyForm(obj) {
	var textarea = $(obj).parents('div').children('form').children('textarea');
	var dl = $(obj).parents('div').children(dl).children('dd').next().children('div').html();

	$(textarea).val(dl);
	$(obj).parents('div').children('form').eq(0).show();
	textarea = null;
	dl = null;
}

function hideReplyForm(obj) {
	$(obj).parents('form').hide();
}

function submitReplyForm(obj) {
	replayString = $(obj).children('textarea').val().trim();
	if (replayString.length < 2 || replayString.length > 250) {
		$(obj).children('textarea').focus();
		$(obj).children('label.error').show();
		return false;
	} else {
		$(obj).children('label.error').hide();
		$(obj).submit();
	}
}

function deleteMsg(url) {
	setTimeout("deleteMsgBackend('" + url + "')", 200);
}

function deleteMsgBackend(url) {
	if (confirm('$lang[comment_confirm]')) {
		self.location.href = url;
	}
}


$("#publishnew").hide();
$("#menulist div").hide();
$("#menulist div").eq(0).show();
changeoptions($("#newmovementmenu"), "li", $("#menulist"), "div");

changeoptions($("#hotgoods"), "li", $("#productlist"), "ul");

$("#productlist ul").hide();
$("#productlist ul").eq(0).show();
changeoptions($("#newproductmenu"), "li", $("#productlist"), "ul");

$(".movement dl").hover(
function() {
	$(this).css("backgroundPosition", "0 -127px");
},
function() {
	$(this).css("backgroundPosition", "0 0 ");
});

var istate = ostate = 1;
$(".showpic h4").hover(
function() {
	if (istate == 0) return;
	istate = 0;
	$(".showpic div").animate({
		left: '532px'
	},
	"slow", function() {
		istate = 1;
	});
},
function() {
	if (ostate == 0) return;
	ostate = 0;
	$(".showpic div").animate({
		left: '690px'
	},
	"slow", function() {
		ostate = 1;
	});
});

function changeoptions(eventobj, echildnode, resultobj, rchildnode) {
	var eventobject = eventobj.children(echildnode);
	var reusultobject = resultobj.children(rchildnode);
	eventobject.each(function(i) {
		$(this).mouseover(function() {
			eventobject.removeClass("mouseover");
			$(this).addClass("mouseover");
			reusultobject.hide();
			reusultobject.eq(i).show();
			if (this.id == "coinauction") {
				$("#publishnewauction").show();
				$("#publishnewconsume").hide();
			} else if (this.id == 'coinconsume') {
				$('#moreConsumeLink').show();
				$("#publishnewconsume").show();
				$("#publishnewauction").hide();
			} else {
				$("#publishnew").hide();
				$("#publishnewauction").hide();
				$("#publishnewconsume").hide();
			}
			if (this.id == "allGoodsList") {
				$("#moreGoodsLink").show();
			} else {
				$("#moreGoodsLink").hide();
			}
		});
	});
}
$(function() {
	$('#shop_notice').cycle({
		fx: 'scrollUp',
		prev: '#shop_notice_prev',
		next: '#shop_notice_next',
		pause: true,
		timeout: 6000
	}).find("li").css({
		background: "none"
	});
});

/**
 * 顯示商家簡介
 */
function showMoreDesc() {
	shopDesc_info = $('#shopDesc').html();
	$('#shopDesc').html($('#shopDesc').attr('title') + "&nbsp;&nbsp;&nbsp;&nbsp;<a onclick=\"showLessDesc();\" style=\"display:block; text-align:right; color:red; cursor:pointer; \">收起</a>");
}

function showLessDesc() {
	$('#shopDesc').html(shopDesc_info);
}
