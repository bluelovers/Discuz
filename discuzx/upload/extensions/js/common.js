/*
	$Id: $
*/

(function(_jQueryInit){
	jQuery.fn.init = function(selector, context) {
		return (jQuery._this = new _jQueryInit(selector, context));
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

	$.cssHooks[var_name_x] = {
	    get: function (element, computed, extra) {
	        var x = get_coordinate(1, $element);

	        if (!x) return;

	        return x;
	    },

	    set: function (element, x) {
	        var $element = $(element);

	        var y = get_coordinate(2, $element);

	        if (!y) return;

	        $element.css('background-position', get_position([x, y]));
	    }
	};

	$.cssHooks[var_name_y] = {
	    get: function (element, computed, extra) {
	        var y = get_coordinate(2, $element);

	        if (!y) return;
	    },

	    set: function (element, y) {
	        var $element = $(element);

	        var x = get_coordinate(1, $element);

	        if (!x) return;

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
