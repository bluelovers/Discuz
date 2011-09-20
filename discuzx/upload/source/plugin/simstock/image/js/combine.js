/*
 * Kilofox Services
 * SimStock v1.0
 * Plug-in for Discuz!
 * Last Updated: 2011-08-10
 * Author: Glacier
 * Copyright (C) 2005 - 2011 Kilofox Services Studio
 * www.Kilofox.Net
 */
( function( $ ){
	function List( config ){
		$.extend( this, config);
		return this.init();
	};
	List.prototype = {
		el		: null,
		overCls : false,
		zebra	: false,
		zebraCls: "",
		combine	: false, //由HQ得到的是一个数组
		combineVar : "", //数组名
		combineLen : 0,  //数组长度
		init	: function(){
			this.el = $( this.el );
			this.items	 = [];
			this.params	 = this.params || [];
			this.defaults = this.defaults || {};
			this.codes = [];
			var n = this.combineLen || this.params.length;
			for (var i=0; i< n; i++) {
				this.items.push(
					new ListItem( $.extend( { index : i, list : this, bgCls : this.zebra && i % this.zebra == 0 ? this.zebraCls : false }, this.defaults, this.params[i] ) )
				);
				if ( this.params[i] && this.params[i].code )
					this.codes.push( this.params[i].code );
			}
			return this;
		},
		setData	: function( arr ){
			this.arr = arr;
			var body = $("<tbody>");
			var n = this.combineLen || arr.length;
			for (var i=0; i<n; i++)
			{
				body.append( this.items[i].setData( arr[i] ).create() );
			}
			this.el.find("tbody").replaceWith( body );
		},
		loadData: function(){}
	};
	var ListItem = function( config ){
		$.extend( this, config || {});
		return this.init();
	};
	ListItem.prototype = {
		code	: null,
		link	: false,
		title	: "",
		titleIndex	: null, //股票名称所在位置
		current	: 3, 	//当前价
		rate	: null, //涨跌幅
		compare : null, //昨收价
		digi	: 2, 	//小数位
		bgCls	: false,
		priceCls: "blue",
		colCls	: "cor_blue",
		upCls	: "fred",
		downCls : "fgreen",
		rsymbol : "@code@",   //待替换的标识符
		rvar	: "code",     //待替换的变量名
		target  : "_self",
		unit	: "%",
		pre		: "+",		//上涨时添加
		index	: 0, 	//数组中的位置
		list	: null, //List对象
		init	: function(){
			this.data = [];
			return this;
		},
		setData	: function( data ){
			this.data = data;
			return this;
		},
		getLink	: function( i ){
			if ( this.rsymbol && (this[ this.rvar ] || this.data[ this.rvar ] ) )
				return this.link.replace( this.rsymbol , this[ this.rvar ] || this.data[ this.rvar ] );
			else
				return this.link;
		},
		getTitle: function(){
			if ( !this.title && this.titleIndex != null )
				this.title = this.data[ this.titleIndex ];
			return this.title;
		},
		getPrice: function(){
			var p = parseFloat(this.data[ this.current ]);
			if ( isNaN(p) || p == 0 )
				p = "--";
			else
				p = p.toFixed( this.digi );
			return p;
		},
		getRate	: function(){
			var r;
			if ( this.rate )
				r = parseFloat( this.data[ this.rate ] );
			else
				r = 100 * (parseFloat(this.data[ this.current ]) - parseFloat(this.data[ this.compare ])) / parseFloat(this.data[ this.compare ]);
			if ( isNaN(r) )
				r = "--";
			else
				r = r.toFixed( this.digi );
			return r;
		}
	};
	$.HQList = List;
	$.HQListMgr = function( config ){
		$.extend( this, config || {});
		return this.init();
	};
	$.HQListMgr.prototype = {
		inter	: 1000 * 30,
		timer	: null,
		suspend	: false,
		init	: function(){
			this.items = [];
			return this;
		},
		push		: function( list ){
			this.items.push( list );
			return this;
		},
		remove	: function( list ){
			var i = $.inArray(list, this.items);
			if ( i > -1 )
				this.items.splice( i, 1);
			return this;
		},
		start	: function(){
			this.suspend = false;
			if ( this.timer )
				clearInterval( this.timer );
			var codes = [];
			for (var i=0; i<this.items.length; i++) {
				codes = codes.concat( this.items[i].combineVar || this.items[i].codes );
			}
			//开始循环
			var _self = this;
			var loop = function(){
				if (codes.length > 0) {
					$.getScript("http://hq.sinajs.cn/_=" + (+new Date()) + "&list=" + codes.join(","), function(){
						if (!_self.suspend) {
							for (var i = 0; i < _self.items.length; i++) {
								var l = _self.items[i], arr = [];
								if (l.combine) {
									arr = eval(eval("hq_str_" + l.combineVar));
								}
								else {
									for (var j = 0; j < l.codes.length; j++) {
										arr.push(eval("hq_str_" + l.codes[j] + ".split(',')"));
									}
								}
								l.setData(arr);
							}
						}
					});
				}
			};
			this.timer = setInterval( loop, this.inter );
			loop();
			return this;
		},
		stop	: function(){
			if ( this.timer )
				clearInterval( this.timer );
			this.suspend = true;
			return this;
		}
	};
} )( jQuery );
$ = function(t){
	return document.getElementById(t)
};
!function($)
{
	function getScript(argUrl,argCallback)
	{
	    var _script = document.createElement('script');
	    _script.type = 'text/javascript';
	    _script.src = argUrl;
	    var _head = $('head')[0];
	    var _done = false;
	    _script.onload = _script.onreadystatechange = function()
	    {
	        if(!_done &&(!this.readyState || this.readyState === "loaded" || this.readyState === "complete"))
	        {
	            _done = true;
	            argCallback();
	            _script.onload = _script.onreadystatechange = null;
	            setTimeout(function()
	            {
	                _head.removeChild(_script);
	            },1);
	        }
	    };
	    _head.appendChild(_script);
	};
}(jQuery);
