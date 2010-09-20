
/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: header.js 4326 2010-09-04 08:13:37Z fanshengshuai $
 */

search_w = $("#search_keywords").val();

var search = {};
search.searchRedirect = function() {
	$("#searchoption li a").click(function() {
		if ("" == $("#search_keywords").val() || search_w == $("#search_keywords").val()) {
			$("#search_keywords").focus();
			return false;
		}
		actionUrl = "";
		searchType = $(this).attr('search_type');
		switch (searchType) {
		case 'shopsearch':
			actionUrl = 'street.php?range=all';
			break;
		case 'consume':
			actionUrl = 'consume.php';
			break;
		case 'goodssearch':
			actionUrl = 'goodsearch.php';
			break;
		default:
			break;
		}
		if ("" == actionUrl) return false;
		$("#search_keywords").attr("name", "keyword");
		$("#form_search").attr("action", actionUrl);
		$("#form_search").submit();
		return false;
	});
};

function changeclass(obj, Otar, type) {
	if (type == 1) {
		$("#" + Otar).hide();
		$("#addto_supnav").unbind().bind("mouseover", function() {
			$("#brandspce > a").addClass("mouseover");
		}).bind("mouseout", function() {
			$("#brandspce > a").removeClass("mouseover");
		});
	} else {
		$("#" + Otar).show();
	}
}

$(function() {
	// 搜索框
	search.searchRedirect();
	$("#search_keywords").click(function() { if ($(this).val() == search_w) {$(this).val("");};});
	$("#shownavsearch").hover(function() {$("#searchoption").show();},function() {$("#searchoption").hide();});
});

$(function(){
	$("#nav_message_handle").hover(
		function() {
			//var msglist = $("#show_navmsg .nav_msglist");
			$(this).addClass("on");
			$("#show_navmsg").addClass("nav_msg_active");
			$("#show_navmsg").show();
			$("#show_navmsg").css({top:'30px'});
			
		},
		function() {
			$(this).removeClass("on");
			$("#show_navmsg").removeClass("nav_msg_active");
			$("#show_navmsg").hide();
	});
	$("#nav_myshops_handle").hover(
		function() {
			$(this).addClass("on");
			$("#show_myshops").show();
			
		},
		function() {
			$(this).removeClass("on");
			$("#show_myshops").hide();
	});
});

