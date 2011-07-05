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
	 */
	function get_background_position($element) {
	    var position = $element.css('background-position')

	    if (!position) position = 'auto'

	    return position
	}

	function get_coordinates(position) {
	    var coordinates = position.split(' ')

	    if (coordinates.length != 2) return

	    return coordinates
	}

	function get_position(coordinates) {
	    return coordinates.join(' ')
	}

	function get_coordinate(index, $element) {
	    var position = get_background_position($element)

	    if (position === 'auto') {
	        return 'auto'
	    }

	    var coordinates = get_coordinates(position)

	    if (!coordinates) return

	    return coordinates[index - 1]
	}

	$.cssHooks['background_position_x'] = {
	    get: function (element, computed, extra) {
	        var x = get_coordinate(1, $element)

	        if (!x) return

	        return x
	    },

	    set: function (element, x) {
	        var $element = $(element)

	        var y = get_coordinate(2, $element)

	        if (!y) return

	        $element.css('background-position', get_position([x, y]))
	    }
	}

	$.cssHooks['background_position_y'] = {
	    get: function (element, computed, extra) {
	        var y = get_coordinate(2, $element)

	        if (!y) return
	    },

	    set: function (element, y) {
	        var $element = $(element)

	        var x = get_coordinate(1, $element)

	        if (!x) return

	        $element.css('background-position', get_position([x, y]))
	    }
	}

	$.fx.step['background_position_x'] = function (fx) {
	    $.cssHooks['background_position_x'].set(fx.elem, fx.now + fx.unit);
	}

	$.fx.step['background_position_y'] = function (fx) {
	    $.cssHooks['background_position_y'].set(fx.elem, fx.now + fx.unit);
	}

})(jQuery);
