/*
	$Id: $
*/

(function(_jQueryInit){
	jQuery.fn.init = function(selector, context, rootjQuery) {
		return (jQuery._this = new _jQueryInit(selector, context, rootjQuery));
	};
})(jQuery.fn.init);

(function($){
	/**
	 * This script adds background-position-x and background-position-y Css properties to jQuery.
	 * Because FireFox fails to support these natively.
	 *
	 * Usage:
	 *
	 * this.$element.css
	 *	({
	 *		"background_position_x": 'left',
	 *		"background_position_y": 'top'
	 *	})
	 *
	 * Copyright (c) 2011 Nikolay Kuchumov
	 * Licensed under MIT (http://en.wikipedia.org/wiki/MIT_License)
	 *
	 * @author Kuchumov Nikolay
	 * @email kuchumovn@gmail.com
	 * @github kuchumovn
	 * @see http://forum.jquery.com/topic/jquery-css-background-position-firefox-opera-bug
	 *
	 * @author bluelovers
	 **/
	function get_background_position($element) {
	    var position = $element.css('background-position');

	    if (!position) position = 'auto';

	    return position;
	}

	function get_coordinates(position) {
	    var coordinates = position.split(' ');

	    if (coordinates.length != 2) return;

	    return coordinates;
	}

	function get_position(coordinates) {
	    return coordinates.join(' ');
	}

	function get_coordinate(index, $element) {
	    var position = get_background_position($element);

	    if (position === 'auto') {
	        return 'auto';
	    }

	    var coordinates = get_coordinates(position);

	    if (!coordinates) return;

	    return coordinates[index - 1];
	}

	var var_name_x = $.camelCase('background-position-x');
	var var_name_y = $.camelCase('background-position-y');

	var ralpha = /alpha\([^)]*\)/i,
		ropacity = /opacity=([^)]*)/,
		// fixed for IE9, see #8346
		rupper = /([A-Z]|^ms)/g,
		rnumpx = /^-?\d+(?:px)?$/i,
		rnum = /^[+\-]?\d+$/,
		rrelNum = /^[+\-]=/,
		rrelNumFilter = /[^+\-\.\de]+/g,

		cssShow = { position: "absolute", visibility: "hidden", display: "block" },
		cssWidth = [ "Left", "Right" ],
		cssHeight = [ "Top", "Bottom" ],
		_u;

	$.cssHooks[var_name_x] = {
	    get: function (element, computed, extra) {
	    	var $element = $(element);

	        var x = get_coordinate(1, $element);

	        if (!x) return;

	        return x;
	    },

	    set: function (element, x) {
	        var $element = $(element);

	        var y = get_coordinate(2, $element);

	        if (!y) return;

	        if (rnum.test(x)) x += 'px';

	        $element.css('background-position', get_position([x, y]));
	    }
	};

	$.cssHooks[var_name_y] = {
	    get: function (element, computed, extra) {
	    	var $element = $(element);

	        var y = get_coordinate(2, $element);

	        if (!y) return;

	        return y;
	    },

	    set: function (element, y) {
	        var $element = $(element);

	        var x = get_coordinate(1, $element);

	        if (!x) return;

	        if (rnum.test(y)) y += 'px';

	        $element.css('background-position', get_position([x, y]));
	    }
	};

	$.fx.step[var_name_x] = function (fx) {
	    $.cssHooks[var_name_x].set(fx.elem, fx.now + fx.unit);
	}

	$.fx.step[var_name_y] = function (fx) {
	    $.cssHooks[var_name_y].set(fx.elem, fx.now + fx.unit);
	}

})(jQuery);

(function($, undefined){

	jQuery.ajaxSetup({
		cache : true,
	});

	$(window).load(function(){
		if (jQuery('#controlpanel').size() > 0) {
			function _body_css() {
				var _not_loaded = true;
				try {
					if (_elem.css('background-position-y') != (jQuery("body").css('background-position-y') + h)) {
						_not_loaded = false;

						jQuery("body").css('background-position-y', '+=' + h);
					}
				} catch(e) {
					error_i++;
				}

				if (_not_loaded && error_i < 30) {
					setTimeout(function(){_body_css()}, 100);
					return;
				}
			}

			var _elem = $('<div />');

			var h = $('#controlpanel').outerHeight() - ($('#controlpanel').outerHeight() - $('#controlpanel').height()) - 3;

			if (h > 0) {
				jQuery("body").css('background-position-y', '+=' + h);
			}

			var error_i = 0;
			var spaceDiy = window.spaceDiy ? window.spaceDiy : undefined;

			if (spaceDiy && spaceDiy != undefined) {
				spaceDiy._changeStyle = spaceDiy.changeStyle;

				spaceDiy.changeStyle = function (t) {
					error_i = 0;

					jQuery("body").css('background-position', '');

					_elem.css('background-position-y', jQuery("body").css('background-position-y'));

					spaceDiy._changeStyle(t);

					setTimeout(function(){_body_css()}, 500);
				};
			}
		}

	});

	$(document).ready(function(){

		// 因不明原因的 BUG 只好採用如此複雜的 selector
		var bbcode_imgs = jQuery('body.pg_viewthread .t_f img.bbcode_img');
		if (bbcode_imgs.length > 0) {

			CB_ScriptDir = 'extensions/js/clearbox';

			jQuery('<link rel="stylesheet" href="' + CB_ScriptDir+'/css/clearbox.css' + '" type="text/css" rel="stylesheet" />')
				.appendTo(jQuery('head'));

			/*
			jQuery.getScript(CB_ScriptDir+'/config/default.js');
			*/

			bbcode_imgs.each(function(index, elem){
				// elem = this
				var _this = jQuery(this);

				var _src = (_this.attr('file') || _this.attr('src'));
				var _a_init = 0;

				if (_this.parent('a').length) {
					var _a = _this.parent('a');

					if (_a.attr('href') == _src) {
						_a_init = 1;
					}
				} else {
					var _a = jQuery('<a>');

					_a.insertBefore(_this);
					_this.appendTo(_a);

					_a_init = 1;
				}

				if (_a_init) {
					_a
						.attr({
							'rel' : 'clearbox[gallery=bbcode_img]',
							'href' : _src,
							'tnhref' : _src,
							'target' : '_blank',
							'class' : 'clearbox',
						})
					;
				}
			});

			jQuery.getScript(CB_ScriptDir+'/js/clearbox_jquery.js', function(data, textStatus){

				jQuery.log(textStatus);

				$.clearbox.init({
					CB_PicDir : 'extensions/js/clearbox/pic'
				});

				jQuery('<a>SHOW BBCODE IMG</a>')
					.attr({
						'href' : 'javascript:void(0);',
					})
					.click(function(){
						bbcode_imgs.first().click();
					})
					.appendTo(jQuery('<p/>'))
					.parent()
					.prependTo(bbcode_imgs.parents('td[id].t_f').first());
				;
			});
		}
	});

})(jQuery);

function _hack_zoom(obj, zimg, nocover, pn) {

}