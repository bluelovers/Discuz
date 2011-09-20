/*
 * Kilofox Services
 * SimStock v1.0
 * Plug-in for Discuz!
 * Last Updated: 2011-07-14
 * Author: Glacier
 * Copyright (C) 2005 - 2011 Kilofox Services Studio
 * www.Kilofox.Net
 */
var main = function(){
	var contest, 	//赛区
		digi = 2,	//小数点位数
		limitPercent = 0.1,	//委托价格限制 ST为0.05
		me, account, hold,	//账户信息
		symbol, loading = false, type = "buy",
		orders,
		hq  //当前股票行情数据
		;
	var MSG_PRICE_EMPTY = "请输入买入价格",
	  MSG_COUNT_EMPTY = "买入数量需大于0",
		MSG_PRICE_BUY = "您输入的买入价格不在规则允许范围，请输入涨跌停价格之间的报价",
		MSG_COUNT_100 = "买入数量应为100及其整数倍",
		MSG_COUNT_BUY = "买入数量必须在最大可买范围内",
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
		$("#stockForm").find( "input" ).val( "" );
	}
	//选择股票
	var sg = new SuggestServer();
	sg.bind({ target : "", "input": "searchStock", "default": "代码/名称/拼音", "type": "stock", "link": "javascript:void(0)",
				callback : function(){
					var tr = this._X || $(sg._T).find("tr")[1],
						stockname = tr.id ? tr.id.split(",")[4] : "";
					symbol = tr.id ? tr.id.split(",")[3] : "";
					if (tr && symbol) {
						$("#searchStock").val( symbol );
						$("#stockName").val( stockname );
						$("#price").val( "" );
						$("#amount").val( "" );
						$("#amountMax").val( "" );
						firstFill = false;
						firstTip = false;
						loadStock( symbol );
					}
				}
			});
	sg.changeType( "111" );
	//获取股票行情
	var dl = new $.HQDataLoader();
	function loadStock( symbol )
	{
		dl.stop()
		 .remove( symbol )
		 .add( symbol, {
		 	codes : [ symbol ], inter : 5000, random : true
		 } )
		 .on( symbol, showStock )
		 .start( symbol );
	}
	function timeToInt( timestr )
	{
		var time_arr=(timestr || "0:0:0") .split(":");
		return time_arr[0]*10000+time_arr[1]*100+time_arr[2]*1;
	}
	//显示股票行情
	var trs = $("#five").find("tr");
	var firstFill = false, firstTip = false;
	function showStock( datas ){
		var data = datas[ 0 ] , digi = 2;
		if ( !data )
			return false;
		hq = data;	//保存下来
		//收盘后不再限制新股
		var tickettime = timeToInt(data[31]);
		if ( !firstTip && /^N/i.test( data[0] ) && tickettime <= 150005 ){
			firstTip = true;
			msg( "抱歉，新股暂不能操作。" );
			return false;
		}
		//填充当前价
		if (!firstFill)
		{
			firstFill = true;
			$("#price").val( getValidNum( hq[3] , hq[2] ).toFixed(2) || 0.0 );
			onChangePrice();
		}
		//ST股
		if ( /ST/i.test( data[0] ) )
		{
			limitPercent = 0.05;
		}
		//判断停牌
		if ( (!data[3] || parseFloat(data[3]) == 0) && (tickettime < 90000 || tickettime > 93000) )
		{
			$("#priceCurrent").val("停牌(" + f2(data[2], data[2], 2) + ")").attr( "className", "input " + (getZeroCls(0)) );
		}
		else
		{
			$("#priceCurrent").val(f2(data[3], data[3], 2)).attr( "className","input " + getZeroCls(data[3] - data[2]) );
		}
		//五档盘口
		trs.eq( 5 ).children("td:eq(1)").html( f2( data[ 3 ],data[ 3 ], digi) ) //价格
					.addClass( getZeroCls( data[ 3 ] - data[2] ) );
		for ( var i=0; i<5; i ++ )
		{
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
		$( selector ).click( function(){
			var v = getFloat( target );
			if ( v ){
				var n = v + range;
				n = check( n );
				$( target ).val( n );
				//更改最大可购买量
				onChangePrice();
			}
		} );
	}
	adjust( "#pricemap area:first", "#price", 0.01, checkPrice, digi );
	adjust( "#pricemap area:last", "#price", -0.01, checkPrice, digi );
	adjust( "#amountmap area:first", "#amount", 100, checkAmount, 0 );
	adjust( "#amountmap area:last", "#amount", -100, checkAmount, 0 );
	//当价格更改时
	function onChangePrice()
	{
		var v = getFloat("#price");
		if ( v )
		{
			//账户余额
			var remain = parseFloat( account.AvailableFund ) || 0 ;
			var spend = 0;
			var cost = v * 1.001 + ( /sh/i.test( symbol ) ? 0.001 : 0 );
			var n = parseInt( remain / cost );
			n = parseInt( n / 100 ) * 100;
			$("#amountMax").val(n);
		}
		else
		{
			$("#amountMax").val("");
		}
		onChangeAmount();
	}
	//检查买入价格
	function checkPrice( p )
	{
		if ( !hq )
			return p;
		//有当前价时取当前价，没有时取昨收
		var c = parseFloat( getValidNum( hq[3] , hq[2] ) );
		//有当前价时，不能超过限制
		if ( c )
		{
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
		return n;
	}
	//当数量更改时
	function onChangeAmount()
	{
		var v = getFloat("#price");
		var n = getFloat("#amount");
		if ( v && n != null )
		{
			$("#sum").val( (v * n).toFixed( digi ) );
		}
		else
		{
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
				msgs.push( MSG_PRICE_BUY );
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
				msgs.push( MSG_COUNT_BUY );
			}
			if ( v % 100 != 0 ){
				msgs.push( MSG_COUNT_100 );
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
			window.location.href = "plugin.php?id=simstock:index";
		loading = true;
		$("#searchStock").attr( "disabled", "true" );
		LoginManager.add({
			onLoginSuccess: function(){
				$.get(urlInfo, {mod:'ajax', section:'account', uid:me.uid}, function(obj){
					if ( obj )
					{
						eval(obj);
						account	= o.account;
						hold	= o.stockHold;
						orders	= o.order;
					}
					else
					{
						msg("获取账户信息失败，请刷新页面后再操作");
					}
					loading = false;
					$("#searchStock").removeAttr("disabled");
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
		else
		{
			if ( n != checkAmount( n ) )
			{
				msgs.push( MSG_COUNT_BUY );
			}
			if ( n % 100 != 0 ){
				msgs.push( MSG_COUNT_100 );
			}
		}
		if ( msgs.length > 0 )
		{
			msgArray(msgs);
		}
		else
		{
			submiting = true;
			$.get( urlInfo, {mod:'ajax', section:type, uid:me.uid, code:symbol, stockname:s, price:p, amount:n}, function( obj ){
				submiting = false;
				if ( obj )
				{
					eval(obj);
					if ( ret == '0' )
					{
						msg("提交成功！");
						$("#submit").unbind("click");
						setTimeout(function(){window.location.href=urlInfo+'&mod=member&act=trustsmng';}, 1000);
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
