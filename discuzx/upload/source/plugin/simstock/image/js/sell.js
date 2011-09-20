/*
 * Kilofox Services
 * SimStock v1.0
 * Plug-in for Discuz!
 * Last Updated: 2011-06-30
 * Author: Glacier
 * Copyright (C) 2005 - 2011 Kilofox Services Studio
 * www.Kilofox.Net
 */
var main = function(){
	var contest, 	//赛区
		digi = 2,	//小数点位数
		limitPercent = 0.1,	//委托价格限制 ST为0.05
		me, account, hold,	//账户信息
		symbol, loading = false, type ="sell",
		hq  //当前股票行情数据
		;
	var MSG_PRICE_EMPTY = "请输入卖出价格",
		MSG_COUNT_EMPTY = "卖出数量必须大于0",
		MSG_PRICE_SELL = "您输入的卖出价格不在规则允许范围，请输入涨跌停价格之间的报价",
		MSG_COUNT_SELL = "卖出数量必须在可卖数量范围内",
		MSG_STOCK_EMPTY = "请先选择股票，再点击下单";
	var selector = new ContestSelector({
		callback	: function( c ){
			contest = c;
			//获取数据
			getAccountInfo();
			//更改说明
			$(".detail").hide();
			$(".detail[cid='" + c.id + "']" ).show();
			resetForm();
		}
	});
	//重置表单
	function resetForm(){
		$("#stockForm").find( "input" ).val("");
	}
	//生成下拉菜单
	function createSelect(){
		//默认显示 选择股票
		$("#stockList").empty().append("<option value='false' >---选择股票---</option>");
		if ( hold )
		{
			for (var i = 0; i < hold.length; i++)
			{
				var item = hold[i];
				$("<option>").text( item.StockName + "(" + item.StockCode + ")" ).val( item.StockCode ).appendTo( "#stockList" ).data( "data", item );
			}
		}
	}
	//选择股票
	$("#stockList").change( function(){
		var v = $("#stockList").val();
		symbol = v;
		if ( v && v != "false" )
		{
			firstFill = false;
			loadStock( symbol );
			$("#amountMax").val( hold[ this.selectedIndex - 1].AvailSell );
		}
		else
		{
			dl.stop().remove()
			resetForm();
			setFive( [] );
			$("#priceCurrent").val("");
			$("#amountMax").val("");
			$("#price").val("");
		}
		$("#price").val("");
		$("#amount").val("");
	} );
	//获取股票行情
	var dl = new $.HQDataLoader();
	function loadStock( symbol ){
		dl.stop()
		 .remove( symbol )
		 .add(symbol, {codes:[ symbol ], inter:5000, random:true})
		 .on( symbol, showStock )
		 .start( symbol );
	}
	//显示股票行情
	var trs = $("#five").find("tr");
	var firstFill = false;
	function showStock( datas )
	{
		var data = datas[0] , digi = 2;
		if ( !data )
			return false;
		hq = data;	//保存下来
		$("#stockName").val( data[0] );
		//填充当前价
		if ( !firstFill )
		{
			firstFill = true;
			$("#price").val(hq[3] || hq[2] || 0.0);
		}
		//ST股
		if ( /ST/i.test( data[0] ) )
		{
			limitPercent = 0.05;
		}
		$("#priceCurrent").val( f2( data[3], data[3], 2 ) )
						 .addClass( getZeroCls( data[ 3 ] - data[2] ) );
		setFive( data );
	}
	function setFive ( data ){
		//五档盘口
		trs.eq( 5 ).children("td:eq(1)").html( f2( data[ 3 ],data[ 3 ], digi) ) //价格
					.addClass( getZeroCls( data[ 3 ] - data[2] ) );
		for (var i=0; i<5; i ++ ) {
			//更新买
			var tds = trs.eq( 6 + i ).children("td");
			tds.eq( 2 ).html( f2( data[3]+data[ 10 + i * 2 ] , parseInt( data[ 10 + i * 2 ] ) /100 , 0 ) ); //数量
			tds.eq( 1 ).html( f2( data[3]+data[ 10 + i * 2 + 1 ], data[ 10 + i * 2 + 1 ] , digi) )
						.addClass( getZeroCls( data[ 10 + i * 2 + 1 ] - data[2] ) ) ;	//价格
			//更新卖
			tds = trs.eq( 4 - i ).children("td");
			tds.eq( 2 ).html( f2( data[3]+data[ 20 + i * 2 ], parseInt( data[ 20 + i * 2 ] ) /100 , 0 ) ); //数量
			tds.eq( 1 ).html( f2( data[3]+data[ 20 + i * 2 + 1 ], data[ 20 + i * 2 + 1 ] , digi ) )
						.addClass( getZeroCls( data[ 20 + i * 2 + 1 ] - data[2] ) ) ;	//价格
		}
	}
	//微调买入价格
	function adjust( selector, target, range, check, d )
	{
		$( selector ).click(function(){
			var v = getFloat( target );
			if ( v >= 0 )
			{
				var n = v + range;
				n = check( n );
				$( target ).val( n );
				//更改最大可购买量
				onChangePrice();
			}
		});
	}
	adjust( "#pricemap area:first", "#price", 0.01, checkPrice, digi );
	adjust( "#pricemap area:last", "#price", -0.01, checkPrice, digi );
	adjust( "#amountmap area:first", "#amount", 100, checkAmount, 0 );
	adjust( "#amountmap area:last", "#amount", -100, checkAmount, 0 );
	//当价格更改时
	function onChangePrice()
	{
		var v = getFloat("#price");
		onChangeAmount();
	}
	//检查买入价格
	function checkPrice( p )
	{
		if ( !hq )
			return p;
		var c = parseFloat( hq[3] || hq[2] );
		//有当前价时，不能超过限制
		if ( c ) {
			var max = c * (1+ limitPercent ) , min = c * (1 - limitPercent);
			if ( p > max ) p = max;
			if ( p < min ) p = min;
		}
		return parseFloat(p).toFixed( digi );
	}
	//检查买入数量
	function checkAmount( n )
	{
		var c =	getFloat("#amountMax");
		if ( n > c )
			n = c;
		//取消整数限制
/*
		else {
			var n = parseInt( n / 100 ) * 100;
			if ( n < 100 )
				n = 100;
		}
*/
		if ( n < 0 )
			n = 0;
		return n;
	}
	//当数量更改时
	function onChangeAmount()
	{
		var v = getFloat("#price");
		var n = getFloat("#amount");
		if ( v && n != null ){
			$("#sum").val( (v * n).toFixed( digi ) );
		}else{
			$("#sum").val("");
		}
	}
	$("#price").blur( function(){
		var v = $(this).val();
		if (v) {
			v = parseFloat(v).toFixed( digi );
			$(this).val( v );
			var msgs= [];
			if ( v != checkPrice($(this).val()) ){
				msgs.push( MSG_PRICE_SELL );
			}
			if (msgs.length) {
				msgArray(msgs);
				//$(this).val("");
			}
			onChangePrice();
		}
	} );
	$("#amount").blur( function(){
		var v = $(this).val();
		if ( v ){
			v = parseFloat(v).toFixed( 0 );
			$(this).val( v );
			var msgs= [];
			if ( v != checkAmount($(this).val()) ){
				msgs.push( MSG_COUNT_SELL );
			}
			if (msgs.length) {
				msgArray(msgs);
				//$(this).val("");
			}
			onChangeAmount();
		}
	} );
	//获得浮点型数据
	function getFloat( selector )
	{
		var v = $( selector ).val();
		if ( v != "" && !isNaN( v ) )
		{
			return parseFloat( v );
		}
		return null;
	}
	//强制输入必须为数字类型
	$(".uinumber").keyup( function(){
		var v = this.value;
		var matches = /\d+(\.\d{0,2})?/.exec( v );
		if ( matches && matches[0] != undefined )
			this.value = matches[0];
		else
			this.value = "";
	} );
	$(".uinumberb").keyup( function(){
		var v = this.value;
		var matches = /\d*/.exec( v );
		if ( matches && matches[0] != undefined )
			this.value = matches[0];
		else
			this.value = "";
	} );
	//获取帐号信息
	function getAccountInfo()
	{
		me = new kfsAccount();
		if ( !me )
			window.location.href = urlInfo;
		var names = $(".username");
		names.text( me.nick || "用户" );
		loading = true;
		LoginManager.add( {
			onLoginSuccess: function(){
				$.get(urlInfo, {mod:'ajax', section:'account', uid:me.uid}, function(obj){
					if ( obj )
					{
						eval(obj);
						account	= o.account;
						hold	= o.stockHold;
					}
					else
					{
						msg("获取账户信息失败，请刷新页面后再操作！");
					}
					loading = false;
					createSelect();
				});
			}
		});
	}
	var submiting = false;
	$("#submit").click( function(){
		if ( submiting )
			return false;
		var p = $("#price").val(),
			n = $("#amount").val(),
			s = $("#stockName").val(),
			msgs = [];
		$("#amount").blur();
		$("#price").blur();
		if ( isMsg() )
			return false;
		if ( !symbol || !/^\w\w\d{6}$/.test( symbol ) )
			msgs.push( MSG_STOCK_EMPTY );
		if ( p == "" || p == 0 )
			msgs.push( MSG_PRICE_EMPTY );
		if ( n == "" || n == 0 )
			msgs.push( MSG_COUNT_EMPTY );
		if ( msgs.length > 0 )
		{
			msgArray(msgs);
		}
		else
		{
			submiting = true;
			$.get(urlInfo, {mod:'ajax', section:type, uid:me.uid, code:symbol, stockname:s, price:p, amount:n}, function( obj ){
				submiting = false;
				if ( obj )
				{
					eval(obj);
					if ( ret == '0' )
					{
						msg("提交成功！");
						$("#submit").unbind("click");
						setTimeout( function(){window.location.href = urlInfo+'&mod=member&act=trustsmng';}, 1000 );
					}
					else
					{
						msg(ret);
					}
				}
				else
				{
					msg("提交失败，请刷新页面重新操作！");
				}
			});
		}
		return false;
	});
};
main();
