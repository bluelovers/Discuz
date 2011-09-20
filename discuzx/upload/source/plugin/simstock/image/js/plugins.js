/*
 * Kilofox Services
 * SimStock v1.0
 * Plug-in for Discuz!
 * Last Updated: 2011-06-30
 * Author: Glacier
 * Copyright (C) 2005 - 2011 Kilofox Services Studio
 * www.Kilofox.Net
 */
( function(){
var jsc = now(),
	rscript = /<script(.|\s)*?\/script>/g,
	rselectTextarea = /select|textarea/i,
	rinput = /text|hidden|password|search/i,
	jsre = /=\?(&|$)/,
	rquery = /\?/,
	rts = /(\?|&)_=.*?(&|$)/,
	rurl = /^(\w+:)?\/\/([^\/?#]+)/,
	r20 = /%20/g;
jQuery.fn.extend({
	// Keep a copy of the old load
	_load: jQuery.fn.load,
	load: function( url, params, callback ) {
		if ( typeof url !== "string" ) {
			return this._load( url );
		}
		var off = url.indexOf(" ");
		if ( off >= 0 ) {
			var selector = url.slice(off, url.length);
			url = url.slice(0, off);
		}
		// Default to a GET request
		var type = "GET";
		// If the second parameter was provided
		if ( params ) {
			// If it's a function
			if ( jQuery.isFunction( params ) ) {
				// We assume that it's the callback
				callback = params;
				params = null;
			// Otherwise, build a param string
			} else if ( typeof params === "object" ) {
				params = jQuery.param( params );
				type = "POST";
			}
		}
		// Request the remote document
		jQuery.ajax({
			url: url,
			type: type,
			dataType: "html",
			data: params,
			context:this,
			complete: function(res, status){
				// If successful, inject the HTML into all the matched elements
				if ( status === "success" || status === "notmodified" ) {
					// See if a selector was specified
					this.html( selector ?
						// Create a dummy div to hold the results
						jQuery("<div />")
							// inject the contents of the document in, removing the scripts
							// to avoid any 'Permission Denied' errors in IE
							.append(res.responseText.replace(rscript, ""))
							// Locate the specified elements
							.find(selector) :
						// If not, just inject the full result
						res.responseText );
				}
				if ( callback ) {
					this.each( callback, [res.responseText, status, res] );
				}
			}
		});
		return this;
	},
	serialize: function() {
		return jQuery.param(this.serializeArray());
	},
	serializeArray: function() {
		return this.map(function(){
			return this.elements ? jQuery.makeArray(this.elements) : this;
		})
		.filter(function(){
			return this.name && !this.disabled &&
				(this.checked || rselectTextarea.test(this.nodeName) ||
					rinput.test(this.type));
		})
		.map(function(i, elem){
			var val = jQuery(this).val();
			return val == null ?
				null :
				jQuery.isArray(val) ?
					jQuery.map( val, function(val, i){
						return {name: elem.name, value: val};
					}) :
					{name: elem.name, value: val};
		}).get();
	}
});
// Attach a bunch of functions for handling common AJAX events
jQuery.each( "ajaxStart,ajaxStop,ajaxComplete,ajaxError,ajaxSuccess,ajaxSend".split(","), function(i,o){
	jQuery.fn[o] = function(f){
		return this.bind(o, f);
	};
});
jQuery.extend({
	get: function( url, data, callback, type ) {
		// shift arguments if data argument was omited
		if ( jQuery.isFunction( data ) ) {
			type = type || callback;
			callback = data;
			data = null;
		}
		return jQuery.ajax({
			type: "GET",
			url: url,
			data: data,
			success: callback,
			dataType: type
		});
	},
	getScript: function( url, callback ) {
		return jQuery.get(url, null, callback, "script");
	},
	getJSON: function( url, data, callback ) {
		return jQuery.get(url, data, callback, "json");
	},
	post: function( url, data, callback, type ) {
		// shift arguments if data argument was omited
		if ( jQuery.isFunction( data ) ) {
			type = type || callback;
			callback = data;
			data = {};
		}
		return jQuery.ajax({
			type: "POST",
			url: url,
			data: data,
			success: callback,
			dataType: type
		});
	},
	ajaxSetup: function( settings ) {
		jQuery.extend( jQuery.ajaxSettings, settings );
	},
	ajaxSettings: {
		url: location.href,
		global: true,
		type: "GET",
		contentType: "application/x-www-form-urlencoded",
		processData: true,
		async: true,
		/*
		timeout: 0,
		data: null,
		username: null,
		password: null,
		*/
		// Create the request object; Microsoft failed to properly
		// implement the XMLHttpRequest in IE7, so we use the ActiveXObject when it is available
		// This function can be overriden by calling jQuery.ajaxSetup
		xhr: function(){
			return window.ActiveXObject ?
				new ActiveXObject("Microsoft.XMLHTTP") :
				new XMLHttpRequest();
		},
		accepts: {
			xml: "application/xml, text/xml",
			html: "text/html",
			script: "text/javascript, application/javascript",
			json: "application/json, text/javascript",
			text: "text/plain",
			_default: "*/*"
		}
	},
	// Last-Modified header cache for next request
	lastModified: {},
	etag: {},
	ajax: function( s ) {
		// Extend the settings, but re-extend 's' so that it can be
		// checked again later (in the test suite, specifically)
		s = jQuery.extend(true, s, jQuery.extend(true, {}, jQuery.ajaxSettings, s));
		var jsonp, status, data,
			callbackContext = s.context || window,
			type = s.type.toUpperCase();
		// convert data if not already a string
		if ( s.data && s.processData && typeof s.data !== "string" ) {
			s.data = jQuery.param(s.data);
		}
		// Handle JSONP Parameter Callbacks
		if ( s.dataType === "jsonp" ) {
			if ( type === "GET" ) {
				if ( !jsre.test( s.url ) ) {
					s.url += (rquery.test( s.url ) ? "&" : "?") + (s.jsonp || "callback") + "=?";
				}
			} else if ( !s.data || !jsre.test(s.data) ) {
				s.data = (s.data ? s.data + "&" : "") + (s.jsonp || "callback") + "=?";
			}
			s.dataType = "json";
		}
		// Build temporary JSONP function
		if ( s.dataType === "json" && (s.data && jsre.test(s.data) || jsre.test(s.url)) ) {
			jsonp = "jsonp" + jsc++;
			// Replace the =? sequence both in the query string and the data
			if ( s.data ) {
				s.data = (s.data + "").replace(jsre, "=" + jsonp + "$1");
			}
			s.url = s.url.replace(jsre, "=" + jsonp + "$1");
			// We need to make sure
			// that a JSONP style response is executed properly
			s.dataType = "script";
			// Handle JSONP-style loading
			window[ jsonp ] = function(tmp){
				data = tmp;
				success();
				complete();
				// Garbage collect
				window[ jsonp ] = undefined;
				try{ delete window[ jsonp ]; } catch(e){}
				if ( head ) {
					head.removeChild( script );
				}
			};
		}
		if ( s.dataType === "script" && s.cache === null ) {
			s.cache = false;
		}
		if ( s.cache === false && type === "GET" ) {
			var ts = now();
			// try replacing _= if it is there
			var ret = s.url.replace(rts, "$1_=" + ts + "$2");
			// if nothing was replaced, add timestamp to the end
			s.url = ret + ((ret === s.url) ? (rquery.test(s.url) ? "&" : "?") + "_=" + ts : "");
		}
		// If data is available, append data to url for get requests
		if ( s.data && type === "GET" ) {
			s.url += (rquery.test(s.url) ? "&" : "?") + s.data;
		}
		// Watch for a new set of requests
		if ( s.global && ! jQuery.active++ ) {
			jQuery.event.trigger( "ajaxStart" );
		}
		// Matches an absolute URL, and saves the domain
		var parts = rurl.exec( s.url );
		// If we're requesting a remote document
		// and trying to load JSON or Script with a GET
		if ( s.dataType === "script" && type === "GET" && parts
			&& ( parts[1] && parts[1] !== location.protocol || parts[2] !== location.host )) {
			var head = document.getElementsByTagName("head")[0] || document.documentElement;
			var script = document.createElement("script");
			script.src = s.url;
			if ( s.scriptCharset ) {
				script.charset = s.scriptCharset;
			}
			// Handle Script loading
			if ( !jsonp ) {
				var done = false;
				// Attach handlers for all browsers
				script.onload = script.onreadystatechange = function(){
					if ( !done && (!this.readyState ||
							this.readyState === "loaded" || this.readyState === "complete") ) {
						done = true;
						success();
						complete();
						// Handle memory leak in IE
						script.onload = script.onreadystatechange = null;
						if ( head && script.parentNode ) {
							head.removeChild( script );
						}
					}
				};
			}
			// Use insertBefore instead of appendChild  to circumvent an IE6 bug.
			// This arises when a base node is used (#2709 and #4378).
			head.insertBefore( script, head.firstChild );
			// We handle everything using the script element injection
			return undefined;
		}
		var requestDone = false;
		// Create the request object
		var xhr = s.xhr();
		// Open the socket
		// Passing null username, generates a login popup on Opera (#2865)
		if ( s.username ) {
			xhr.open(type, s.url, s.async, s.username, s.password);
		} else {
			xhr.open(type, s.url, s.async);
		}
		// Need an extra try/catch for cross domain requests in Firefox 3
		try {
			// Set the correct header, if data is being sent
			if ( s.data ) {
				xhr.setRequestHeader("Content-Type", s.contentType);
			}
				// Set the If-Modified-Since and/or If-None-Match header, if in ifModified mode.
				if ( s.ifModified ) {
					if ( jQuery.lastModified[s.url] ) {
						xhr.setRequestHeader("If-Modified-Since", jQuery.lastModified[s.url]);
					}
					if ( jQuery.etag[s.url] ) {
						xhr.setRequestHeader("If-None-Match", jQuery.etag[s.url]);
					}
				}
			// Set header so the called script knows that it's an XMLHttpRequest
			xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
			// Set the Accepts header for the server, depending on the dataType
			xhr.setRequestHeader("Accept", s.dataType && s.accepts[ s.dataType ] ?
				s.accepts[ s.dataType ] + ", */*" :
				s.accepts._default );
		} catch(e){}
		// Allow custom headers/mimetypes and early abort
		if ( s.beforeSend && s.beforeSend.call(callbackContext, xhr, s) === false ) {
			// Handle the global AJAX counter
			if ( s.global && ! --jQuery.active ) {
				jQuery.event.trigger( "ajaxStop" );
			}
			// close opended socket
			xhr.abort();
			return false;
		}
		if ( s.global ) {
			trigger("ajaxSend", [xhr, s]);
		}
		// Wait for a response to come back
		var onreadystatechange = function(isTimeout){
			// The request was aborted, clear the interval and decrement jQuery.active
			if ( !xhr || xhr.readyState === 0 ) {
				if ( ival ) {
					// clear poll interval
					clearInterval( ival );
					ival = null;
					// Handle the global AJAX counter
					if ( s.global && ! --jQuery.active ) {
						jQuery.event.trigger( "ajaxStop" );
					}
				}
			// The transfer is complete and the data is available, or the request timed out
			} else if ( !requestDone && xhr && (xhr.readyState === 4 || isTimeout === "timeout") ) {
				requestDone = true;
				// clear poll interval
				if (ival) {
					clearInterval(ival);
					ival = null;
				}
				status = isTimeout === "timeout" ?
					"timeout" :
					!jQuery.httpSuccess( xhr ) ?
						"error" :
						s.ifModified && jQuery.httpNotModified( xhr, s.url ) ?
							"notmodified" :
							"success";
				if ( status === "success" ) {
					// Watch for, and catch, XML document parse errors
					try {
						// process the data (runs the xml through httpData regardless of callback)
						data = jQuery.httpData( xhr, s.dataType, s );
					} catch(e) {
						status = "parsererror";
					}
				}
				// Make sure that the request was successful or notmodified
				if ( status === "success" || status === "notmodified" ) {
					// JSONP handles its own success callback
					if ( !jsonp ) {
						success();
					}
				} else {
					jQuery.handleError(s, xhr, status);
				}
				// Fire the complete handlers
				complete();
				if ( isTimeout ) {
					xhr.abort();
				}
				// Stop memory leaks
				if ( s.async ) {
					xhr = null;
				}
			}
		};
		if ( s.async ) {
			// don't attach the handler to the request, just poll it instead
			var ival = setInterval(onreadystatechange, 13);
			// Timeout checker
			if ( s.timeout > 0 ) {
				setTimeout(function(){
					// Check to see if the request is still happening
					if ( xhr && !requestDone ) {
						onreadystatechange( "timeout" );
					}
				}, s.timeout);
			}
		}
		// Send the data
		try {
			xhr.send( type === "POST" || type === "PUT" ? s.data : null );
		} catch(e) {
			jQuery.handleError(s, xhr, null, e);
		}
		// firefox 1.5 doesn't fire statechange for sync requests
		if ( !s.async ) {
			onreadystatechange();
		}
		function success(){
			// If a local callback was specified, fire it and pass it the data
			if ( s.success ) {
				s.success.call( callbackContext, data, status );
			}
			// Fire the global callback
			if ( s.global ) {
				trigger( "ajaxSuccess", [xhr, s] );
			}
		}
		function complete(){
			// Process result
			if ( s.complete ) {
				s.complete.call( callbackContext, xhr, status);
			}
			// The request was completed
			if ( s.global ) {
				trigger( "ajaxComplete", [xhr, s] );
			}
			// Handle the global AJAX counter
			if ( s.global && ! --jQuery.active ) {
				jQuery.event.trigger( "ajaxStop" );
			}
		}
		function trigger(type, args){
			(s.context ? jQuery(s.context) : jQuery.event).trigger(type, args);
		}
		// return XMLHttpRequest to allow aborting the request etc.
		return xhr;
	},
	handleError: function( s, xhr, status, e ) {
		// If a local callback was specified, fire it
		if ( s.error ) {
			s.error.call( s.context || window, xhr, status, e );
		}
		// Fire the global callback
		if ( s.global ) {
			(s.context ? jQuery(s.context) : jQuery.event).trigger( "ajaxError", [xhr, s, e] );
		}
	},
	// Counter for holding the number of active queries
	active: 0,
	// Determines if an XMLHttpRequest was successful or not
	httpSuccess: function( xhr ) {
		try {
			// IE error sometimes returns 1223 when it should be 204 so treat it as success, see #1450
			return !xhr.status && location.protocol === "file:" ||
				// Opera returns 0 when status is 304
				( xhr.status >= 200 && xhr.status < 300 ) ||
				xhr.status === 304 || xhr.status === 1223 || xhr.status === 0;
		} catch(e){}
		return false;
	},
	// Determines if an XMLHttpRequest returns NotModified
	httpNotModified: function( xhr, url ) {
		var lastModified = xhr.getResponseHeader("Last-Modified"),
			etag = xhr.getResponseHeader("Etag");
		if ( lastModified ) {
			jQuery.lastModified[url] = lastModified;
		}
		if ( etag ) {
			jQuery.etag[url] = etag;
		}
		// Opera returns 0 when status is 304
		return xhr.status === 304 || xhr.status === 0;
	},
	httpData: function( xhr, type, s ) {
		var ct = xhr.getResponseHeader("content-type"),
			xml = type === "xml" || !type && ct && ct.indexOf("xml") >= 0,
			data = xml ? xhr.responseXML : xhr.responseText;
		if ( xml && data.documentElement.nodeName === "parsererror" ) {
			throw "parsererror";
		}
		// Allow a pre-filtering function to sanitize the response
		// s is checked to keep backwards compatibility
		if ( s && s.dataFilter ) {
			data = s.dataFilter( data, type );
		}
		// The filter can actually parse the response
		if ( typeof data === "string" ) {
			// If the type is "script", eval it in global context
			if ( type === "script" ) {
				jQuery.globalEval( data );
			}
			// Get the JavaScript object, if JSON is used.
			if ( type === "json" ) {
				if ( typeof JSON === "object" && JSON.parse ) {
					data = JSON.parse( data );
				} else {
					data = (new Function("return " + data))();
				}
			}
		}
		return data;
	},
	// Serialize an array of form elements or a set of
	// key/values into a query string
	param: function( a ) {
		var s = [],
			param_traditional = jQuery.param.traditional;
		function add( key, value ){
			// If value is a function, invoke it and return its value
			value = jQuery.isFunction(value) ? value() : value;
			s[ s.length ] = encodeURIComponent(key) + '=' + encodeURIComponent(value);
		}
		// If an array was passed in, assume that it is an array
		// of form elements
		if ( jQuery.isArray(a) || a.jquery )
			// Serialize the form elements
			jQuery.each( a, function() {
				add( this.name, this.value );
			});
		else
			// Encode parameters from object, recursively. If
			// jQuery.param.traditional is set, encode the "old" way
			// (the way 1.3.2 or older did it)
			jQuery.each( a, function buildParams( prefix, obj ) {
				if ( jQuery.isArray(obj) )
					jQuery.each( obj, function(i,v){
						// Due to rails' limited request param syntax, numeric array
						// indices are not supported. To avoid serialization ambiguity
						// issues, serialized arrays can only contain scalar values. php
						// does not have this issue, but we should go with the lowest
						// common denominator
						add( prefix + ( param_traditional ? "" : "[]" ), v );
					});
				else if ( typeof obj == "object" )
					if ( param_traditional )
						add( prefix, obj );
					else
						jQuery.each( obj, function(k,v){
							buildParams( prefix ? prefix + "[" + k + "]" : k, v );
						});
				else
					add( prefix, obj );
			});
		// Return the resulting serialization
		return s.join("&").replace(r20, "+");
	}
});
} );
/*
 * Get the value of a cookie with the given name.
 *
 * @example $.cookie('the_cookie');
 * @desc Get the value of a cookie.
 *
 * @param String name The name of the cookie.
 * @return The value of the cookie.
 */
jQuery.cookie = function(name, value, options) {
    if (typeof value != 'undefined') { // name and value given, set cookie
        options = options || {};
        if (value === null) {
            value = '';
            options.expires = -1;
        }
        var expires = '';
        if (options.expires && (typeof options.expires == 'number' || options.expires.toUTCString)) {
            var date;
            if (typeof options.expires == 'number') {
                date = new Date();
                date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000));
            } else {
                date = options.expires;
            }
            expires = '; expires=' + date.toUTCString(); // use expires attribute, max-age is not supported by IE
        }
        // CAUTION: Needed to parenthesize options.path and options.domain
        // in the following expressions, otherwise they evaluate to undefined
        // in the packed version for some reason...
        var path = options.path ? '; path=' + (options.path) : '';
        var domain = options.domain ? '; domain=' + (options.domain) : '';
        var secure = options.secure ? '; secure' : '';
        document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join('');
    } else { // only name given, get cookie
        var cookieValue = null;
        if (document.cookie && document.cookie != '') {
            var cookies = document.cookie.split(';');
            for (var i = 0; i < cookies.length; i++) {
                var cookie = jQuery.trim(cookies[i]);
                // Does this cookie string begin with the name we want?
                if (cookie.substring(0, name.length + 1) == (name + '=')) {
                    cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
                    break;
                }
            }
        }
        return cookieValue;
    }
};
( function($){
	$.extend({
		getJSONP : function( url, data, callback ){
			var fnName = "SINAFINANCE" + (+new Date()) + parseInt( Math.random() * 100000 ) ;
			window[ fnName ] = function(){
				callback.apply( this, arguments );
				try {
					delete window[ fnName ];
				}
				catch (e) {
				}
			};
			$.ajax({
				url: url,
				data:data,
				dataType:"script"
			});
		}
	});
} )(jQuery);
/**
 * @author Tongye
 */
/*
 * HQDataLoader 用于循环获取HQ数据,停盘后不再重复获取,开盘时再自动开启
 *
 * 属性：
 *  inter       : 1000 * 5,     //可选。默认循环时间
 *  step		: 1000 * 120, 	//可选。停盘时，最短隔多长时间再去拿数据，以ms为单位
 *	activeWeek	: [1,2,3,4,5],  //可选。星期几开盘 1 -> 7
 *	activeHour	: ["9:15-11:30", "12:60-15:00"], //可选。每天开盘时间
 * 	extend		: 2, 			//可选。允许宽限的交易时间，以分钟为单位 9:13-11:32
 *
 * 方法：
 *  add( nick, config )	         添加数据源
 *  remove( nick )				 删除数据源
 *  update( nick, config )       更新数据源
 *
 *       string nick   		:	 每个数据源的别名
 *       object config
 *        	array  codes	:    必选。股票代码的数组 ["sh000006", "s_sh600004", ... ]
 *        	number inter	:    可选。循环时间,默认为HQDataLoader的时间
 *        	bool   loop		:    可选。是否需要循环,默认true
 *          bool   random   :    可选。是否增加随机数 默认false
 *
 *  on( nick, function )	     增加某数据源的监听者
 *  un( nick, function )         删除某数据源的监听者
 *  	回调函数接收的第一个参数为解析codes后的数组数据
 *
 *  start( [nick] )              开启nick数据源,当参数为空全部开启
 *  stop ( [nick] )              关闭nick数据源,当参数为空全部关闭
 *  status( nick )				 获取nick数据的状态, true为正在运行, false为停止, null为不存在
 *
 *  setTime( number )            设置当前时间，用于判断是否停盘
 * 如果需要更多功能可以继承 DataLoader 进行自定义
 *
*/
(function($){
/*
 * DataLoader 通过循环加载script方式获得数据，并负责分配
*/
$.DataLoader = function( config ){
	this.init( config );
	return this;
};
/*
 * Action 负责加载script并回调
 */
$.DataLoader.Action	= function( config ){
	this.init( config );
	return this;
};
$.DataLoader.Action.prototype = {
	immediate	: true,
	random		: false,
	onBeforeLoad: function(){},
	init		: function( config ){
		this.callbacks	= [];
		$.extend( this, config );
	},
	update		: function( config ){
		$.extend( this, config );
	},
	call		: function( s ){
		for (var i=0; i<this.callbacks.length; i++) {
			var item = this.callbacks[ i ];
			if ( item.fn )
				item.fn.call( item.scope || s, s );
		}
	},
	start		: function( force ){
		if (!this.timer) {
			var s = this;
			function fn(){
				if ( force ){
					s.onBeforeLoad( s );
				}else
					//触发事件 如果返回false则不加载script
					if ( s.onBeforeLoad( s ) === false )
						return;
					var url = s.random ? s.url.replace(/(\?|&)_=.*?(&|$)/, "$1_=" + (new Date()).getTime() + "$2")  : s.url;
					$.get( url, s.data, function(){
						//回调
						s.call( s );
					}, "script" );
			}
			if (this.loop) {
				this.timer = setInterval(  fn, this.inter );
				//立刻执行一次
				if ( this.immediate )
					fn();
			}else
				this.timer = setTimeout(  fn, this.inter );
		}
		return this;
	},
	stop		: function(){
		if ( this.timer ){
			if (this.loop) {
				clearInterval( this.timer );
			}else
				clearTimeout( this.timer );
			delete 	this.timer;
		}
		return this;
	},
	on			: function( fn, scope ){
		if ( this.indexOf( fn ) == -1 )
			this.callbacks.push( {
				fn		: fn,
				scope 	: scope
			} );
		return this;
	},
	indexOf		: function( fn ){
		for (var i=0; i<this.callbacks.length; i++) {
			if ( fn == this.callbacks[i].fn )
				return i;
		}
		return -1;
	},
	un			: function( fn ){
		var i = this.indexOf( fn );
		if ( i > -1 )
			this.callbacks.splice( i, 1 );
		return this;
	}
};
$.DataLoader.prototype = {
	defaults	: {
		loop	: false,
		inter	: 1000*10,
		url		: "",
		data	: null,
		nick	: null,
		immediate	: true,
		/*
		 * 每个group调用那个前会触发该事件
		 */
		onBeforeLoad: function(){}
	},
	item		:  $.DataLoader.Action,
	init		: function( config ){
		this.items	= {};
		$.extend( this, config );
	},
	/*
	 * 增加回调方法
	*/
	on			: function( nick, fn, scope ){
		var g = this.items[nick];
		if ( g ){
			g.on( fn, scope )
		}
		return this;
	},
	/*
	 * 删除回调方法
	*/
	un			: function( nick, fn ){
		var g = this.items[nick];
		if ( g ){
			g.un( fn )
		}
		return this;
	},
	/*
	 * 添加数据源
	*/
	add			: function( nick, config ){
		if (!this.items[nick]) {
			config = config || {};
			config.nick = nick;
			var g = new this.item($.extend({}, this.defaults, config));
			this.items[nick] = g;
		}
		return this;
	},
	/*
	 * 删除数据源 nick = string
	*/
	remove		: function( nick ){
		this.stop( nick );
		delete this.items[ nick ];
		return this;
	},
	/*
	 * 更新数据源 nick = string
	*/
	update		: function( nick, config ){
		var g = this.items[nick];
		if ( g )
			g.update( config );
		return this;
	},
	/*
	 * 开启数据源 当无参数时,全部开启
	*/
	start		: function( nick ){
		if (nick == undefined) {
			for( var nick in this.items ){
				this.items[ nick ].start();
			}
		}
		else {
			var g = this.items[nick];
			if (g)
				g.start();
		}
		return this;
	},
	/*
	 * 关闭数据源 当无参数时,全部关闭
	*/
	stop		: function( nick ){
		if (nick == undefined) {
			for( var nick in this.items ){
				this.items[ nick ].stop();
			}
		}
		else {
			var g = this.items[nick];
			if (g)
				g.stop();
		}
		return this;
	},
	status	: function( nick ){
		var g = this.items[nick];
		if ( g )
			return g.hasOwnProperty("timer");
		else
			return null;
	}
};
/*
 * Group 股票代码的集合
*/
$.DataLoader.Group	= function( config ){
	this.init( config );
	return this;
};
$.extend( $.DataLoader.Group.prototype, $.DataLoader.Action.prototype, {
	init	: function( config ){
		this.callbacks	= [];
		this.codes = [];
		$.extend( this, config );
	},
	call	: function(){
		for (var i=0; i<this.callbacks.length; i++) {
			var item = this.callbacks[ i ];
			if (item.fn) {
				var vars = [];
				for (var j=0; j<this.codes.length; j++) {
					var tmp = "";
					try {
						eval("tmp = window.hq_str_" + this.codes[j] );
					} catch (e) {}
					if ( tmp === undefined )
						tmp = "";
					if (/[[{]/.test(tmp)) {
						var obj;
						try {
							obj = eval(tmp);
						}
						catch (e) {	}
					}
					vars.push(obj ? obj : tmp.split(","));
				}
				item.fn.call(item.scope || this, vars, this);
			}
		}
	}
} );
/*
 * HpDataLoader 股票代码的集合
*/
$.HQDataLoader = function( config ){
	this.init( config );
	return this;
};
$.extend( $.HQDataLoader.prototype, $.DataLoader.prototype, {
	extend		: 2, //以分钟为单位
	step		: 1000 * 120, //当不在交易时，最短隔多长时间去拿数据 以ms为单位
	activeWeek	: [1,2,3,4,5], //星期几开盘 1 -> 7
	activeHour	: ["9:15-11:30", "12:60-15:00"], //每天开盘时间
	inter		: 1000 * 30,  //1000*5, //5秒
	defaults	: {
		mgr		: null,  //初始化时设置为HpDataLoader实例
		loop	: true,  //默认循环
		url		: "http://hq.sinajs.cn/",
		data	: null,
		nick	: null,
		onBeforeLoad: function( s ){
			var g = s.mgr;
			if ( !g.time )
				return;
			var d = g.time, diff = 0;
			var we= d.getDay();
			if ( we == 0 ) //如果是周天 则让we = 7，便于理解
				we = 7;
			if ( $.inArray( we, g.activeWeek ) > -1 ){
				var n = d.getTime(),
					sec = g.activeSection,
					am = sec[0].from.getTime(),
					pm = sec[1].from.getTime();
				if( d >= am && d <= sec[0].to ||
					d >= pm && d <= sec[1].to )
					diff = 0;
				else{
					if ( n > pm )
						diff = 1 * 3600 * 1000;
					else if( n < am )
						diff = am - n;
					else
						diff = pm - n;
				}
			}else
				diff = 1 * 3600 * 1000;
			//如果不是交易时间则先关闭,等待diff时间后再开启
			if (diff != 0) {
				s.stop();
				diff = Math.min( g.step, diff );
				setTimeout( function(){
					s.start( true )
				}, diff );
			}
			//返回boolean值
			return diff == 0;
		}
	},
	item		:  $.DataLoader.Group,
	init		: function( config ){
		this.defaults.mgr = this;
		this.defaults.inter = this.inter;
		this.items	= {};
		$.extend( this, config );
	},
	/*
	 * override add 自动拼接url config中得有codes属性
	*/
	add			: function( nick, config ){
		if (!this.items[nick]) {
			config = config || {};
			config.nick = nick;
			config.url = config.url || (this.defaults.url + "?_=123&list=" + (config.codes || []).join(","));
			var g = new this.item($.extend({}, this.defaults, config));
			this.items[nick] = g;
		}
		return this;
	},
	/*
	 * override update 更新url
	*/
	update		: function( nick, config ){
		var g = this.items[nick];
		if (g) {
			config.url = config.url || (this.defaults.url + "?_=123&list=" + (config.codes || []).join(","));
			g.update(config);
		}
		return this;
	},
	/*
	 * 解析时间为 [ { from, to }, { from, to }, .. ]
	*/
	setTime		: function( n ){
		this.time = new Date();
		this.time.setTime( n );
		//解析开盘时间
		var hour = this.activeHour, after = [], extend = this.extend;
		function cd(){
			var tmp = new Date();
			tmp.setTime( n );
			tmp.setHours.apply( tmp, arguments );
			return tmp;
		}
		for (var i=0; i<hour.length; i++) {
			var sec = hour[i].split("-");
			sec[0] = sec[0].split(":");
			sec[1] = sec[1].split(":");
			after.push( {
				from : cd( sec[0][0], parseInt(sec[0][1]) - extend, 0, 0 ),
				to	 : cd( sec[1][0], parseInt(sec[1][1]) + extend, 0, 0 )
			} );
		}
		this.activeSection = after;
		return this;
	}
} );
})( jQuery );
/**
 * @author tongye
 *
 * LoginManager 登录管理器，单例模式，无需new
 *
 * 参数：
 * entry,service : 统一登录用来做统计的 默认 finance
 * logon		 ：当前用户登录状态
 * user			 ：用户信息 未登录时为null
 * monitor		 : 是否开启监视器
 * inter		 ：循环检查时间间隔 默认为1s
 *
 * 方法：
 * startMonitor  : 开启监视器  第一个参数循环检查时间
 * stopMonitor	 ：停止监视器
 * checkImmediate： 立即检查
 * add			 ：添加LoginComponent模块 参数可为实例也可为配置参数
 */
var LoginManager = function(){
	return this;
};
LoginManager.prototype = {
	entry 	: 'finance',
	inter	: 1000,		//循环检查时间间隔
	logon	: false,
	user	: null,
	monitor : false,	//是否开启监视器
	actionType : true,	//post
	_timer	: null,
	_components	: [],		//存放待添加的模块
	init	: function( config ){
		$.extend( this, config );
		if ( !window.LoginComponent ){
			throw "could not find LoginComponent object";
			return false;
		}
		return this;
	},
	startMonitor	: function( inter ){
		if ( this._timer )		//只能开启一个监视器
			return;
		this.inter = inter || this.inter || 1000;
		//设置监视器
		if ( !isNaN(this.inter) && this.inter > 0) {
			this.monitor = true;
			this._setComponents();
			var t = this;
			this._timer = setInterval( function(){	//定时器
				t._loopCheck();
			}, this.inter );
		}
		return this;
	},
	stopMonitor	: function(){
		//清除监视器
		if ( this._timer ){
			clearInterval( this._timer );
			this._timer = null;
		}
		this._resumeComponents();
	},
	//主功能 循环检查cookie
	_loopCheck	: function(){
		var userinfo = u;
		if ( userinfo == null && this.logon ){
			this.logon = false;
			this.user = null;
			this._onLogout();
		}else if ( userinfo && !this.logon ) {
			this.logon = true;
			this.user = userinfo;
			this._onLogin( this.user );
		}
	},
	//立即检查
	checkImmediate 	: function(){
		this._loopCheck();
	},
	//当一个组件登录状态成功更改后，同时通知其他组件登录状态已经修改
	_setComponents	: function(){
		for (var i=0; i<this._components.length; i++) {
			this._setComponent( this._components[ i ] );
		}
	},
	_setComponent	: function( c ){
		//设置回调函数作用域
		var t = this;
		var callback = function(){
			t.checkImmediate();
		}
		c._onLoginSuccess = c.onLoginSuccess;
		c._onLogoutSuccess = c.onLogoutSuccess;
		//重新设置回调函数
		c.onLoginSuccess = callback;
		c.onLogoutSuccess = callback;
	},
	_resumeComponents	: function(){
		for (var i=0; i<this._components.length; i++) {
			var c = this._components[ i ];
			//重新设置回调函数
			c.onLoginSuccess = c._onLoginSuccess ;
			c.onLogoutSuccess = c._onLogoutSuccess;
		}
	},
	_onLogin		: function( user ){
		for (var i=0; i<this._components.length; i++) {
			var c = this._components[ i ];
			if ( c._onLoginSuccess )
				c._onLoginSuccess( this.user );
		}
	},
	_onLogout		: function(){
		for (var i=0; i<this._components.length; i++) {
			var c = this._components[ i ];
			if ( c._onLogoutSuccess )
				c._onLogoutSuccess( c );
		}
	},
	add			: function( component ){
		if ( !component )
			return this;
		//如果已经开启监视器
		if (this._timer) {
			this._setComponent(component);
			//如果已经登录了 调用onLoginSuccess
			if ( this.logon && component._onLoginSuccess )
				component._onLoginSuccess( this.user );
		}
		this._components.push( component );
		return this;
	},
	get			: function( i ){
		return this._components[ i ];
	}
};
//单例
LoginManager = new LoginManager();
/**
 * @author tongye
 *
 * LoginComponent 登录组件
 *
 * 参数：
 * name	: 用户名输入框
 * paw	: 密码输入框
 * remember : 是否记住登录状态 可为数字也可为DOM
 * login: 登录按钮
 * logout : 登出按钮
 * onSubmit : 可重写该方法 进行更多的检验  返回false验证不通过 / true 通过
 *
 * 方法：
 * onLoginSuccess	: 登录成功回调 第一个参数用户信息
 * onLoginFailed	: 登录失败回调 第一个参数失败原因
 * onLogoutSuccess	: 登出成功
 * onLogoutFailed	: 登出失败     第一个参数失败原因
 */
function LoginComponent( config ){
	$.extend( this, config );
	return this.init();
};
$.extend( LoginComponent.prototype ,{
	name	: null,		//dom,id
	psw		: null,		//dom,id
	remember: null,		//int, dom, id
	login	: null,		//dom,id
	days	: null,		//记住天数
	logout	: null,		//dom,id 可选 button
	onSubmit: function(){ return true; },	//form 提交 可覆盖支持验证码
	init	: function(  ){
		this.inited = true;
		var t = this;
		//登录
		this.login = $( this.login ).click( function( e ){
			t._check();
		} );
		this.name = $( this.name ).keyup( function( e ){
			if ( e.which == 13 )
				t._check();
		} );
		this.psw = $( this.psw ).keyup( function( e ){
			if ( e.which == 13 )
				t._check();
		} );
		return this;
	},
	_check	: function(){
		var ok = true;
		if ( $.isFunction( this.onSubmit ) ){
			ok = this.onSubmit( this.name );
		}
		if ( ok ){
			this.loginHandler();
		}
	},
	getDays	: function(){
		//int
		if ( this.remember != null && !isNaN( this.remember) ){
			//首先采用remember，没有则采用days
			this.days = this.remember;
		}else if ( this.remember ){
			//DOM, id
			this.remember = $( this.remember );
			if ( this.remember.is(":checkbox") && this.remember.is(":checked") ){
				this.days = this.remember.val();
			}else if ( this.remember.is(":selected") ){
				this.days = this.remember.val();
			}
		}
		return isNaN( this.days ) ? undefined : parseInt( this.days ) ;
	},
	logoutHandler	: function(){
		//sinaSSOController.logout();
	},
	onLoginSuccess	: function(){},
	onLoginFailed	: function(){},
	onLogoutSuccess	: function(){},
	onLogoutFailed	: function(){}
});
(function (a) { var r = a.fn.domManip, d = "_tmplitem", q = /^[^<]*(<[\w\W]+>)[^>]*$|\{\{\! /, b = {}, f = {}, e, p = { key: 0, data: {} }, i = 0, c = 0, l = []; function g(e, d, g, h) { var c = { data: h || (d ? d.data : {}), _wrap: d ? d._wrap : null, tmpl: null, parent: d || null, nodes: [], calls: u, nest: w, wrap: x, html: v, update: t }; e && a.extend(c, e, { nodes: [], parent: d }); if (g) { c.tmpl = g; c._ctnt = c._ctnt || c.tmpl(a, c); c.key = ++i; (l.length ? f : b)[i] = c } return c } a.each({ appendTo: "append", prependTo: "prepend", insertBefore: "before", insertAfter: "after", replaceAll: "replaceWith" }, function (f, d) { a.fn[f] = function (n) { var g = [], i = a(n), k, h, m, l, j = this.length === 1 && this[0].parentNode; e = b || {}; if (j && j.nodeType === 11 && j.childNodes.length === 1 && i.length === 1) { i[d](this[0]); g = this } else { for (h = 0, m = i.length; h < m; h++) { c = h; k = (h > 0 ? this.clone(true) : this).get(); a(i[h])[d](k); g = g.concat(k) } c = 0; g = this.pushStack(g, f, i.selector) } l = e; e = null; a.tmpl.complete(l); return g } }); a.fn.extend({ tmpl: function (d, c, b) { return a.tmpl(this[0], d, c, b) }, tmplItem: function () { return a.tmplItem(this[0]) }, template: function (b) { return a.template(b, this[0]) }, domManip: function (d, m, k) { if (d[0] && a.isArray(d[0])) { var g = a.makeArray(arguments), h = d[0], j = h.length, i = 0, f; while (i < j && !(f = a.data(h[i++], "tmplItem"))); if (f && c) g[2] = function (b) { a.tmpl.afterManip(this, b, k) }; r.apply(this, g) } else r.apply(this, arguments); c = 0; !e && a.tmpl.complete(b); return this } }); a.extend({ tmpl: function (d, h, e, c) { var i, k = !c; if (k) { c = p; d = a.template[d] || a.template(null, d); f = {} } else if (!d) { d = c.tmpl; b[c.key] = c; c.nodes = []; c.wrapped && n(c, c.wrapped); return a(j(c, null, c.tmpl(a, c))) } if (!d) return []; if (typeof h === "function") h = h.call(c || {}); e && e.wrapped && n(e, e.wrapped); i = a.isArray(h) ? a.map(h, function (a) { return a ? g(e, c, d, a) : null }) : [g(e, c, d, h)]; return k ? a(j(c, null, i)) : i }, tmplItem: function (b) { var c; if (b instanceof a) b = b[0]; while (b && b.nodeType === 1 && !(c = a.data(b, "tmplItem")) && (b = b.parentNode)); return c || p }, template: function (c, b) { if (b) { if (typeof b === "string") b = o(b); else if (b instanceof a) b = b[0] || {}; if (b.nodeType) b = a.data(b, "tmpl") || a.data(b, "tmpl", o(b.innerHTML)); return typeof c === "string" ? (a.template[c] = b) : b } return c ? typeof c !== "string" ? a.template(null, c) : a.template[c] || a.template(null, q.test(c) ? c : a(c)) : null }, encode: function (a) { return ("" + a).split("<").join("&lt;").split(">").join("&gt;").split('"').join("&#34;").split("'").join("&#39;") } }); a.extend(a.tmpl, { tag: { tmpl: { _default: { $2: "null" }, open: "if($notnull_1){_=_.concat($item.nest($1,$2));}" }, wrap: { _default: { $2: "null" }, open: "$item.calls(_,$1,$2);_=[];", close: "call=$item.calls();_=call._.concat($item.wrap(call,_));" }, each: { _default: { $2: "$index, $value" }, open: "if($notnull_1){$.each($1a,function($2){with(this){", close: "}});}" }, "if": { open: "if(($notnull_1) && $1a){", close: "}" }, "else": { _default: { $1: "true" }, open: "}else if(($notnull_1) && $1a){" }, html: { open: "if($notnull_1){_.push($1a);}" }, "=": { _default: { $1: "$data" }, open: "if($notnull_1){_.push($.encode($1a));}" }, "!": { open: ""} }, complete: function () { b = {} }, afterManip: function (f, b, d) { var e = b.nodeType === 11 ? a.makeArray(b.childNodes) : b.nodeType === 1 ? [b] : []; d.call(f, b); m(e); c++ } }); function j(e, g, f) { var b, c = f ? a.map(f, function (a) { return typeof a === "string" ? e.key ? a.replace(/(<\w+)(?=[\s>])(?![^>]*_tmplitem)([^>]*)/g, "$1 " + d + '="' + e.key + '" $2') : a : j(a, e, a._ctnt) }) : e; if (g) return c; c = c.join(""); c.replace(/^\s*([^<\s][^<]*)?(<[\w\W]+>)([^>]*[^>\s])?\s*$/, function (f, c, e, d) { b = a(e).get(); m(b); if (c) b = k(c).concat(b); if (d) b = b.concat(k(d)) }); return b ? b : k(c) } function k(c) { var b = document.createElement("div"); b.innerHTML = c; return a.makeArray(b.childNodes) } function o(b) { return new Function("jQuery", "$item", "var $=jQuery,call,_=[],$data=$item.data;with($data){_.push('" + a.trim(b).replace(/([\\'])/g, "\\$1").replace(/[\r\t\n]/g, " ").replace(/\$\{([^\}]*)\}/g, "{{= $1}}").replace(/\{\{(\/?)(\w+|.)(?:\(((?:[^\}]|\}(?!\}))*?)?\))?(?:\s+(.*?)?)?(\(((?:[^\}]|\}(?!\}))*?)\))?\s*\}\}/g, function (m, l, k, d, b, c, e) { var j = a.tmpl.tag[k], i, f, g; if (!j) throw "Template command not found: " + k; i = j._default || []; if (c && !/\w$/.test(b)) { b += c; c = "" } if (b) { b = h(b); e = e ? "," + h(e) + ")" : c ? ")" : ""; f = c ? b.indexOf(".") > -1 ? b + h(c) : "(" + b + ").call($item" + e : b; g = c ? f : "(typeof(" + b + ")==='function'?(" + b + ").call($item):(" + b + "))" } else g = f = i.$1 || "null"; d = h(d); return "');" + j[l ? "close" : "open"].split("$notnull_1").join(b ? "typeof(" + b + ")!=='undefined' && (" + b + ")!=null" : "true").split("$1a").join(g).split("$1").join(f).split("$2").join(d ? d.replace(/\s*([^\(]+)\s*(\((.*?)\))?/g, function (d, c, b, a) { a = a ? "," + a + ")" : b ? ")" : ""; return a ? "(" + c + ").call($item" + a : d }) : i.$2 || "") + "_.push('" }) + "');}return _;") } function n(c, b) { c._wrap = j(c, true, a.isArray(b) ? b : [q.test(b) ? b : a(b).html()]).join("") } function h(a) { return a ? a.replace(/\\'/g, "'").replace(/\\\\/g, "\\") : null } function s(b) { var a = document.createElement("div"); a.appendChild(b.cloneNode(true)); return a.innerHTML } function m(o) { var n = "_" + c, k, j, l = {}, e, p, h; for (e = 0, p = o.length; e < p; e++) { if ((k = o[e]).nodeType !== 1) continue; j = k.getElementsByTagName("*"); for (h = j.length - 1; h >= 0; h--) m(j[h]); m(k) } function m(j) { var p, h = j, k, e, m; if (m = j.getAttribute(d)) { while (h.parentNode && (h = h.parentNode).nodeType === 1 && !(p = h.getAttribute(d))); if (p !== m) { h = h.parentNode ? h.nodeType === 11 ? 0 : h.getAttribute(d) || 0 : 0; if (!(e = b[m])) { e = f[m]; e = g(e, b[h] || f[h]); e.key = ++i; b[i] = e } c && o(m) } j.removeAttribute(d) } else if (c && (e = a.data(j, "tmplItem"))) { o(e.key); b[e.key] = e; h = a.data(j.parentNode, "tmplItem"); h = h ? h.key : 0 } if (e) { k = e; while (k && k.key != h) { k.nodes.push(j); k = k.parent } delete e._ctnt; delete e._wrap; a.data(j, "tmplItem", e) } function o(a) { a = a + n; e = l[a] = l[a] || g(e, b[e.parent.key + n] || e.parent) } } } function u(a, d, c, b) { if (!a) return l.pop(); l.push({ _: a, tmpl: d, item: this, data: c, options: b }) } function w(d, c, b) { return a.tmpl(a.template(d), c, b, this) } function x(b, d) { var c = b.options || {}; c.wrapped = d; return a.tmpl(a.template(b.tmpl), b.data, c, b.item) } function v(d, c) { var b = this._wrap; return a.map(a(a.isArray(b) ? b.join("") : b).filter(d || "*"), function (a) { return c ? a.innerText || a.textContent : a.outerHTML || s(a) }) } function t() { var b = this.nodes; a.tmpl(null, null, null, this).insertBefore(b[0]); a(b).remove() } })(jQuery)
			Request = {
				QueryString: function(item){
					var svalue = location.search.match(new RegExp("[\?\&]" + item + "=([^\&]*)(\&?)", "i"));
					return svalue ? svalue[1] : svalue;
				}
			};