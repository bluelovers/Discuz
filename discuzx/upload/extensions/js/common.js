/*
	$Id: $
*/

(function(_jQueryInit){
	jQuery.fn.init = function(selector, context) {
		return (jQuery._this = new _jQueryInit(selector, context));
	};
})(jQuery.fn.init);
