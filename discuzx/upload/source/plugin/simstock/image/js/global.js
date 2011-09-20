/*
 * Kilofox Services
 * SimStock v1.0
 * Plug-in for Discuz!
 * Last Updated: 2011-07-14
 * Author: Glacier
 * Copyright (C) 2005 - 2011 Kilofox Services Studio
 * www.Kilofox.Net
 */
var translate = {
	SellBuy	: {
		0	: "买入",	1 : "卖出"
	},
	Transaction_type	: {
		1	: "委托成交",  2 : "强制平仓", 3 : "自动配股",  4 : "系统卖出"
	},
	statement_type	: {
		1	: "分红",   2 :  "买入" , 3 : "卖出", 4 : "系统卖出", 5 : "送股", 6 : "自动配股", 7 : "派息", 8 : "强制平仓"
	},
	IfDealt	: {
		0	: "未成交", 1 : "成交", 2 : "用户撤销", 3 : "系统撤销"
	}
};
var urlInfo = "plugin.php?id=simstock:index";
function formatNum( n )
{
	return n > 9 ? ( "" +n ) : ("0" + n );
}
function msg( str )
{
	$("#alertWindow #alertContent").html( str );
	$("#alertWindow").show();
}
function isMsg()
{
	return $("#alertWindow").css("display") != "none";
}
function msgArray( arr )
{
	msg( arr.join("<br/>") );
}
function f2( condition, amt, decimal, unit )
{
	amt = parseFloat( amt );
	return ( isNaN( amt ) )  ? "--" : ( ( decimal === undefined ? amt : amt.toFixed( decimal ) ) + (unit || "") );
}
function getZeroCls( amt )
{
	amt = parseFloat( amt );
	if ( isNaN(amt) )
		return  "fgray";
	return amt > 0 ? "fred" : ( amt == 0 ? "fgray" : "fgreen" );
}
function getValidNum( a, b )
{
	return ( isNaN( a ) || parseFloat( a ) == 0 ) ? ( parseFloat( b ) || 0) : parseFloat( a );
}
$( function(){
	//退出
	var c = new LoginComponent({
		logout	: ".logoutbtn",
		onLogoutSuccess	: function(){window.location.href = urlInfo;},
		onLogoutFailed	: function(){if (msg) msg( "退出失败" );}
	});
	LoginManager.init()
		.add( c )
		.startMonitor();
} );
//--------------------------------  赛区选择器  --------------------------------------------
var ContestSelector = function( config ){
	$.extend( this, config );
	return this.init();
}
ContestSelector.prototype = {
	el		: "#contests",
	callback: function(){},
	init	: function(){
		//切换赛区
		this.el = $( this.el );
		var _self = this;
		this.el.find("a").click( function( e ){
			e.preventDefault();
			if ( !$(this).parent().hasClass("selected") )
				_self.onClick( $(this).attr("cid") );
			return false;
		} );
		//获取存储的赛区信息
		var cid = Request.QueryString( "cid" ) ||  $.cookie("contests");
		//没有对应的赛区时，改用第一个赛区
		var elements = $("#contests a[cid='" + cid +"']");
		if ( elements.length == 0 )
			cid = $("#contests").find("a:first").attr("cid");
		this.onClick( cid );
		return this;
	},
	onClick			: function( id ){
		this.el.children("span").removeClass("selected");
		this.el.find("a[cid='" + id + "']").parent().addClass("selected");
		this.callback( this.createContest( id ) );
		$.cookie("contests", id );
	},
	createContest	: function( id ){
		return {
			id : id,
			title : "",
			percent : id == "5" ? 0.3 : 1			//自由赛区30％限制
		}
	}
};
var kfsAccount = function( config ){
	$.extend( this, config );
	return this.init();
}
kfsAccount.prototype = {
	uid		: '',
	nick	: '',
	init	: function(){
		this.uid = u.uid;
		this.nick = u.uname;
		return this;
	}
};

