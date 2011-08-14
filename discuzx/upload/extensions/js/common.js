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

(function($) {

	$.fn.scoScale = function (options) {
		var agv = $.extend(true, {}, $.fn.scoScale.defaults, options);

//		alert($(this).length);

		var elems = this.filter('img');
		elems.each(function(){
			var _this = $(this);
			var _p = agv.scale;

			var _o = _this.scoRealsize();
			var _e = {
				height: _o.height * _p,
				width: _o.width * _p,
			};

			var _is_height		= (_o.height >= _o.width) ? 1 : 0;

			if (agv.mode == 'fill' || agv.mode == 'fill2') {
				_is_height = !_is_height;
			} else if (!agv.height && _is_height && agv.width) {
				_is_height = 0;
			} else if (agv.height && !_is_height && !agv.width) {
				_is_height = 1;
			}

			if (_is_height) {
				if (agv.height && _e.height > agv.height) {
					_p = (agv.height / _e.height);
				} else if (agv.mode == 'fit' && _e.height < agv.height) {
					_p = (agv.height / _e.height);
				}
			} else {
				if (agv.width && _e.width > agv.width) {
					_p = (agv.width / _e.width);
				} else if (agv.mode == 'fit' && _e.width < agv.width) {
					_p = (agv.width / _e.width);
				}
			}

			_e = _scale_size(_e, _p);

//			_this.after(', <b>mode: ' + agv.mode + '</b>');
//			_this.after(', <b>agv: ' + agv.width + ' x ' + agv.height + '</b>');
//			_this.after('<br><b>p: ' + _p + ', ' + _e.width + ' x ' + _e.height + '</b>');

			if (agv.mode == 'fill') {
				var _cm = _pos(_e, agv, agv.pos);

				var newimg = _this.clone()
//					$('<img/>').attr('src', _this.attr('src'))
					.css(_e).css(agv.img.css).attr(agv.img.attr).css({'margin-top': 0 - _cm.top, 'margin-left': 0 - _cm.left});

				var newdiv = $('<div></div>').height(agv.height).width(agv.width)
					.css(agv.div.css).attr(agv.div.attr).css({'display': _this.css('display') == 'inline' ? 'inline-block' : 'block', 'overflow': 'hidden'})
					.append(
						newimg
					);

				_this.after(
					newdiv
				);

				_this.hide();
			}

			_this.height(_e.height).width(_e.width).css(agv.img.css).attr(agv.img.attr);
		});

		return this;
	};

	$.fn.scoScale.defaults = {
		mode: 'default',
		scale: 1,
		mode_replace: 'self',
		pos: 5,
		div: {attr:{},css:{}},
		img: {attr:{},css:{}}
	};

	$.fn.scoRealsize = function() {
		var _this = this;
		var img = $('<img/>').attr('src', _this.attr('src'));

		var _o = {
			height: _this.height(),
			width: _this.width()
		};

		var _oo = img.ready(function(){
			return {
				height: img.height(),
				width: img.width()
			};
		});

		_o = $.extend(_o, _oo[0]);

		return _o;
	}

	function _pos(_e, agv, options) {
		var _p = {
			top: 0,
			left: 0,
			bottom: 0,
			right: 0
		};

		if ($.type(options) == 'array') {

			_p.top = options[0];
			_p.left = options[1];
			_p.bottom = options[2];
			_p.right = options[3];

		} else if ($.type(options) == 'object') {

			_p = $.extend(_p, options);

		} else {
			var w = _e.width - agv.width;
			var h = _e.height - agv.height;

			switch(options) {
				case 7:
					break;
				case 8:
					_p.left = w / 2;
					break;
				case 9:
					_p.left = w;
					break;

				case 1:
					_p.top = h;
					break;
				case 2:
					_p.top = h;
					_p.left = w / 2;
					break;
				case 3:
					_p.top = h;
					_p.left = w;
					break;

				case 4:
					_p.top = h / 2;
					break;
				case 6:
					_p.top = h / 2;
					_p.left = w;
					break;

				case 5:
				default:
					_p.top = h / 2;
					_p.left = w / 2;

					break;
			}
		}

		_p.top = Math.floor(_p.top);
		_p.left = Math.floor(_p.left);
		_p.bottom = Math.floor(_p.bottom);
		_p.right = Math.floor(_p.right);

		return _p;
	}

	function _scale_size(attr, p) {
		if (!p) p = 1;

		attr.height = Math.floor(attr.height * p);
		attr.width = Math.floor(attr.width * p);

		return attr;
	};

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

				_this.attr({
					'onclick' : 'void(0)',
					'onload' : 'void(0)',
				});

				if (_a_init) {

					var _div = $('<div/>')
						.css({
							'max-width' : 120,
							'max-height' : 120,
							'overflow' : 'hidden',
						})
						.attr({
							'class' : 'cl',
						})
					;

					_a
						.attr({
							'rel' : 'clearbox[gallery=bbcode_img]',
							'href' : _src,
							'tnhref' : _src,
							'target' : '_blank',
							'class' : 'clearbox',
						})
						.append(_div)
					;

					_this
						.appendTo(_div)
						.load(function(){
							$(this)
								.attr({
									lazyloaded : true
								})
								.scoScale({
									width : 120,
									height : 120,
									mode : 'fill2',
								})
								.css({
									'margin-left' : (120 - $(this).width()) / 2,
									'margin-top' : (120 - $(this).height()) / 2,
								})
							;
						})
					;
				}
			});

			jQuery.getScript(CB_ScriptDir+'/js/clearbox_jquery.js', function(data, textStatus){

				jQuery.log(textStatus);

				$.clearbox.init({
					path : {
						base : 'extensions/js/clearbox',
						js : 'extensions/js/clearbox/js',
					},
					CB_PicDir : 'extensions/js/clearbox/pic'
				});

				jQuery('<a>SHOW BBCODE IMG - PICS: ' + bbcode_imgs.size() + '</a>')
					.attr({
						'href' : 'javascript:void(0);',
						'class' : 'notice_green',
					})
					.css({
						'text-decoration' : 'none',
					})
					.click(function(){
						bbcode_imgs.first().click();
					})
					.appendTo(jQuery('<div/>'))
					.parent()
					.css({

					})
					.attr({
						'class' : 'notice notice_green',
					})
					.appendTo(jQuery('<div/>'))
					.parent()
					.prependTo(bbcode_imgs.parents('td[id].t_f').first())
					.css({
						'padding-top' : 5,
						'padding-bottom' : 5,
					})
				;
			});
		}
	});

})(jQuery);

function _hack_zoom(obj, zimg, nocover, pn) {

}