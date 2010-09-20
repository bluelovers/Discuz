/**
 * [品牌空間] (C)2001-2010 Comsenz Inc. This is NOT a freeware, use is subject to license terms
 * 
 * $Id: viewgoodspic.js 3776 2010-07-16 08:21:35Z yexinhao $
 */

$(function() {
	$(".goodlist li").wrapAll(document.createElement("div"));
	var total = Math.ceil(($(".goodlist div li").size()) / 6) * 112 * 6;
	$(".goodlist div").css( {
		position : "absolute",
		left : "0",
		width : total + "px"
	});

	if (($(".goodlist div li").size() <= 6))
		$(".next").fadeTo("1", 0.25).css( {
			cursor : "default"
		});
	$(".goodlist li").eq(0).addClass("curr_pic");
	var src_curr = $("#tarpic").attr("src");
	$(".goodlist li").each(function(i) {
		$(this).click(function() {
			changepic(i);
			return false;
		});
		var src_tmp = $(this).find("img").attr("midimg");
		if (src_tmp == src_curr) {
			$(".goodlist li").removeClass("curr_pic");
			$(this).addClass("curr_pic");
		}
	})
	$(".targetpic_prev a").click(function() {
		showsibpic(1);
		return false;
	});
	$(".targetpic_next a").click(function() {
		showsibpic(-1);
		return false;
	});
	$(".prev a").click(function() {
		animates(1);
		return false;
	});
	$(".next a").click(function() {
		animates(-1);
		return false;
	});
});

var state = 1;
$(".next").click(function() {
	var total = $(".goodlist div").css("width");
	var currentleft = parseInt($(".goodlist div").css("marginLeft"));
	var endpos = 682 - parseInt(total);
	if (state) {
		if (currentleft != endpos) {
			state = 0;
			animates(currentleft - 682);
		} else {
			return false;
		}
	} else {
		return false;
	}
	return false;
});
$(".prev").click(function() {
	var currentleft = parseInt($(".goodlist div").css("marginLeft"));
	if (state) {
		if (currentleft >= 0) {
			return false;
		} else {
			state = 0;
			animates(currentleft + 682);
		}
	} else {
		return false;
	}
	return false;
});
function animates(n) {
	if (state == 0) {
		return false;
	}
	state = 0;
	var m_left = parseInt($(".goodlist div").css("left"));
	var t_width = $(".goodlist div").width();
	if ((t_width - Math.abs(m_left)) > 682 && n == -1) {
		$(".prev a").fadeTo("1", 1).css( {
			cursor : "pointer"
		});
		$(".goodlist div")
				.animate(
						{
							left : (m_left - 682) + "px"
						},
						1200,
						function() {
							state = 1;
							if (Math.abs(parseInt($(".goodlist div")
									.css("left"))) == t_width - 682) {
								$(".next a").fadeTo("1", 0.25).css( {
									cursor : "default"
								});
							}
							;
							var tmp = Math.abs(parseInt($(".goodlist div").css(
									"left"))) / 682;
							changepic(tmp * 6)
						});
	} else if (m_left < 0 && n == 1) {
		$(".next a").fadeTo("1", 1).css( {
			cursor : "pointer"
		});
		$(".goodlist div").animate( {
			left : (m_left + 682) + "px"
		}, 1200, function() {
			state = 1;
			if (parseInt($(".goodlist div").css("left")) == 0) {
				$(".prev a").fadeTo("1", 0.25).css( {
					cursor : "default"
				});
			}
			var tmp = Math.abs(parseInt($(".goodlist div").css("left"))) / 682;
			changepic(tmp * 6 + 5);
		});
	} else {
		state = 1;
		return false;
	}
}

function showsibpic(n) {
	if (state == 0) {
		return false;
	}
	state = 0;
	var t;
	var i = $(".goodlist li").index($('.curr_pic')[0]);
	if ((i == 0 && n == 1) || i == ($(".goodlist li").size() - 1) && n == -1) {
		state = 1;
		return false;
	}
	if (n == -1) {
		t = i + 1;
	} else if (n == 1) {
		t = i - 1;
	}
	changepic(t);
	state = 1;
	if (((t + 1) % 6) == 0 && n == 1) {
		animates(1)
	}
	;
	if ((t % 6) == 0 && n == -1) {
		animates(-1);
	}
}

function changepic(n) {
	var imgsrc = $(".goodlist li").eq(n).find("img").attr("midimg");// big photo
	var discription = $(".goodlist li").eq(n).find("p").html();// photo
																// description
	$("#tarpic").attr("src", imgsrc);
	$(".targetpic_main").find("p").html(discription);
	$(".goodlist li").removeClass("curr_pic");
	$(".goodlist li").eq(n).addClass("curr_pic");
}