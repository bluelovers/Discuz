
/**
 *      [品牌空間] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: scroll.js 3787 2010-07-18 13:35:09Z fanshengshuai $
 */

$(function(){
	var movemen;
/*	if(document.getElementById('movementresult')){
		new simplescroll('movementresult', {start_delay:0, speed: 40, delay:2000, direction:1, scrollItemCount:1, movecount:1});
	}
	$(".subnav ul").each(function(){if($(this).height()>36) $(this).css("height","34px")})
	$("#movementresult strong").each(function(){if($(this).width() > 49) $(this).css("width","49px")})
*/
	$(".hotbrand dl").eq(0).addClass("mouseover");
	$(".hotbrand dl").each(function(i){
		$(this).find("dd").addClass("n_"+(i+1));
		$(this).find("sub").html(i+1);
	});
	$(".hotbrand dl").mouseover(function(){
		$(".hotbrand dl").eq(0).removeClass("mouseover");
		$(this).addClass("mouseover");
	});
	$(".hotbrand dl").mouseout(function(){
		$(this).removeClass("mouseover");
		$(".hotbrand dl").eq(0).addClass("mouseover");
	});
	/*$(".superstock dl").mouseover(function(){
		$(this).addClass("mouseover");
	});
	$(".superstock dl").mouseout(function(){
		$(this).removeClass("mouseover");
	});*/
	$(".movement li").mouseover(function(){
		$(this).addClass("mouseover");
	});
	$(".movement li").mouseout(function(){
		$(this).removeClass("mouseover");
	});
	$(".consumer dl").hover(function(){
		$(this).addClass("thison");
	},function(){
		$(this).removeClass("thison");
	});
	if($("#recommendbrand li").size() > 9){
		$("#recommendbrand li:lt(9)").wrapAll(document.createElement("div"));
		$("#recommendbrand li:gt(8)").wrapAll(document.createElement("div"));
		$("#recommendbrand div").eq(0).css({"z-index":"10"});
		setInterval(fadeanimate,6000);
	}
})
function fadeanimate(){
	var d_one = $("#recommendbrand div").eq(0);
	var d_two = $("#recommendbrand div").eq(1);
	if(d_one.css("z-index") > d_two.css("z-index")){
		d_one.fadeTo(0,0,function(){d_two.css({"z-index":"10","opacity":"1"});d_one.css({"z-index":"1","opacity":"0"})});
	}else{
		d_two.fadeTo(0,0,function(){d_one.css({"z-index":"10","opacity":"1"});d_two.css({"z-index":"1","opacity":"0"})});
	}
}
