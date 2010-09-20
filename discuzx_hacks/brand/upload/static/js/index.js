/**
 *      [品牌空間] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: index.js 4237 2010-08-20 09:10:33Z fanshengshuai $
 */

function showAuto(){
	n = n >= (count - 1) ? 0 : n + 1;
	$("#play_text span").eq(n).trigger('mouseover');
}

var t = n = count = 0;

if ($("#play_list")[0]) {
	$(function(){
		count = $("#play_list a").size();
		var listr = '';
		if(count > 0) {
		for(var i = 1; i < ( count + 1 ); i++) {
			listr += "<span>"+i+"</span>";
		}
		$('#play_text').append(listr);
		}
		$("#play_list a:not(:first-child)").hide();
		$("#play_text span:first-child").css({"background":"#FF9415",'color':'#FFF','height':'18px','width':'18px'});
		$("#play_text span").mouseover(function() {
			var _this = this,
			i = $(_this).text() - 1;
			n = i;
			if (i >= count) return;
			$(_this).css({"background":"#FF9415",'color':'#FFF','height':'18px','width':'18px'}).siblings().css({"background":"#FCF2CF",'color':'#D94B01','height':'16px','width':'16px'});
			setTimeout(function() {
				$("#play_list a").filter(":visible").fadeOut(500).parent().children().eq(i).fadeIn(1000);
				},1);
			});

		t = setInterval("showAuto()", 2000);
		$("#play").hover(function(){clearInterval(t); t = setInterval("showAuto()", 2000);});
	});
}