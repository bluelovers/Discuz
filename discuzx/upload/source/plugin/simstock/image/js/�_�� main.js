/*
 * Kilofox Services
 * SimStock v1.0
 * Plug-in for Discuz!
 * Last Updated: 2011-07-17
 * Author: Glacier
 * Copyright (C) 2005 - 2011 Kilofox Services Studio
 * www.Kilofox.Net
 */
function mainFunction()
{
	var contest, //赛区
		digi = 2, //小数点位数
		limitPercent = 0.1, //委托价格限制 ST为0.05
		me, account, hold, //账户信息
		symbol, loading = false, type = "buy", orders, from = 0, count = 6, orderby = "season_profit_ratio", ordertype = "desc", friends, follows, uid, home = false, nickname, hq //当前股票行情数据
		;
	var dl = new $.HQDataLoader();
	var selector = new ContestSelector({
		callback: function(c){
			contest = c;
			//获取数据
			getAccountInfo();
		}
	});
	//获取帐号信息
	function getAccountInfo()
	{
		me = new kfsAccount();
		if ( !me )
			window.location.href = urlInfo;
		uid = Request.QueryString("uid") || me.uid; //页面应该显示谁的信息
		loading = true;
		LoginManager.add({
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
					//handleTopNav();
					showAccountInfo();
					showHoldStock();
				});
			}
		});
	}
	function handleTopNav()
	{
		//显示用户名
		var names = $(".username");
		names.text((account && account.NickName) || me.nick || "用户").attr("href", "main.html?uid=" + uid);
		if (!me)
		{
			//未登录
			$("#visitor").show();
		}
		else
		{
			home = me.uid == uid;
			$(".onlyname").text( (account && account.NickName) || me.nick || "用户" );
			$(".onlyuid").each( function(){
				$(this).attr( "href", $(this).attr( "href" ) + uid );
			} );
			if (home) {
				//查看自己主页
				$("#myself").show();
			}
			else {
				//查看别人主页
				$("#neighbour").show();
				//处理关注标题
				$("#sub_tit span a").each(function(){
					$(this).text($(this).text().replace("我", "TA")).attr("href", $(this).attr("href") + "&uid=" + uid);
				});
				//处理帐户持仓等跳转
				$("#sub_tit01").hide();
				var a = $("#sub_tit02").show().find("span:last a");
				a.attr("href", a.attr("href").replace("${uid}", uid));
				//处理关注
				$.getJSONP(urlInfo, "Follow_Service.isFollowingOne", {
					uided: uid,
					uiding: me.uid
				}, function(obj){
					if (obj) {
						if (obj.retcode == "1") {
							$("#delFriend").show();
							$("#addFriend").hide();
						}
						else {
							$("#delFriend").hide();
							$("#addFriend").show();
						}
					}
				});
				//添加关注
				$("#addFriend").click(function(){
					$.getJSONP(urlInfo, "Follow_Service.writeFollowInfo", {
						uid: uid
					}, function(obj){
						if (obj && obj.retcode == "0") {
							$("#delFriend").show();
							$("#addFriend").hide();
						}
					});
					return false;
				});
				//删除关注
				$("#delFriend").click(function(){
					$.getJSONP(urlInfo, "Follow_Service.delFollowingData", {
						uid: uid
					}, function(obj){
						if (obj && obj.retcode == "0") {
							$("#delFriend").hide();
							$("#addFriend").show();
						}
					});
					return false;
				});
			}
		}
	}
	function showAccountInfo()
	{
		$("#InitFund").text(f2(account.InitFund, account.InitFund, 2, " 元"));
		$("#AvailableFund").text(f2(1, account.AvailableFund, 2, " 元"));
		$("#d5_profit_ratio").text(f2(1, account.d5ProfitRatio, 2, "%"));
		$("#profit_sell_ratio").text(f2(1, account.ProfitSellRatio, 2, "%"));
		$("#TotalRank").text(f2(1, account.TotalRank, 0));
		//显示星级
		var starlevel = account.StarLevel || 0;
		var level = $("#level").empty();
		var total = 8;
		for (var i = 0; i < starlevel; i++)
		{
			$('<em class="star"></em>').appendTo(level);
		}
		for (var i = starlevel; i < total; i++)
		{
			$('<em class="emptystar"></em>').appendTo(level);
		}
	}
	//计算股票市值
	function calcFund(datas)
	{
		// 股票市值
		var stockFund = 0;
		$(hold).each(function(i, n){
			var data = datas[i]; //hq 行情串
			stockFund += n.StockAmount * 1 * getValidNum(data[3], data[2]);
		});
		$("#StockFund").text(f2(1, stockFund, 2, " 元"));
		// 帐户总值
		var total = stockFund + account.AvailableFund * 1 + account.WarrantFund * 1;
		$("#TotalFund").text(f2(1, total, 2, " 元"));
		// 账户盈亏
		var profit = total - account.InitFund * 1;
		$("#StockProfit").text(f2(1, profit, 2, " 元"));
		// 今日收益率
		if ( !account.LastTotalFund || parseFloat(account.LastTotalFund) == 0 )
			account.LastTotalFund = account.InitFund;
		account.d1_profit_ratio = 100 * (total - account.LastTotalFund) / (account.LastTotalFund * 1);
		$("#d1_profit_ratio").text(f2(1, account.d1_profit_ratio, 2, "%"));
		// 整体收益率
		account.total_profit_ratio = 100 * (total - account.InitFund * 1) / (account.InitFund * 1);
		$("#total_profit_ratio").text(f2(1, account.total_profit_ratio, 2, "%"));
	}
	function processHoldStock()
	{
		var codes = [];
		$(hold).each(function(i, n){
			codes.push(n.StockCode);
		});
		// 没有持股时，暂停HQ
		if ( codes.length == 0 )
		{
			dl.stop().remove(contest.id);
			mergeHoldStock([]);
			return;
		}
		dl.stop().remove(contest.id).add(contest.id, {
			codes: codes,
			inter: 5000,
			random: true,
			loop: true
		}).on(contest.id, mergeHoldStock).start(contest.id);
	}
	var listBody = $("#holdList tbody");
	function mergeHoldStock(datas)
	{
		var trs = listBody.children("tr");
		$(datas).each(function(i, n){
			var tr = trs.eq(i);
			tr.children(".stockcode").text(hold[i].StockCode);
			tr.children(".stockname").text(hold[i].StockName);
			//没有开盘价时，用昨收
			var value = getValidNum(n[3], n[2]);
			tr.children(".currentvalue").text(f2(value, value * hold[i].StockAmount , 0)); //持股市值
			tr.children(".currentprice").text(f2(value, value, 2));	// 当前价
			tr.children(".costfund").text(hold[i].CostFund);
			tr.children(".stockamount").text(hold[i].StockAmount);
			tr.children(".availsell").text(hold[i].AvailSell);
			var fdyk = ( value - hold[i].CostFund ) * hold[i].StockAmount;	// 浮动盈亏=当前市值-持股总成本
			var ykbl = 100 * fdyk / ( hold[i].CostFund * hold[i].StockAmount );	// 盈亏比例=浮动盈亏/持股总成本
			tr.children(".fdyk").text(f2(1, fdyk, 2));	// 浮动盈亏
			tr.children(".ykbl").text(f2(1, ykbl, 2, "%"));	// 盈亏比例
			tr.attr("className", getZeroCls(fdyk));
		});
		//计算股票市值
		calcFund(datas);
	}
	function showHoldStock()
	{
		//$("#holdList tbody").empty();
		if (hold && hold.length > 0)
			$("#holdTemplate").tmpl(hold).appendTo($("#holdList tbody"));
		processHoldStock();
	}
	//关注区域
	$('#sub_tit span').mouseover(function(){
		$(this).addClass("selected").siblings().removeClass();
		$(".sub_cont > table").hide().eq($('#sub_tit span').index(this)).show();
		$("#getmorefriends").attr("href", $(this).children("a").attr("href"));
	});
	//搜索用户
	var s = $("#searchperson");
	var oriText = s.val();
	s.focus( function(){
		var v = $.trim( s.val() );
		if ( v == oriText )
			s.val( "" );
	} ).blur( function(){
		var v = $.trim( s.val() );
		if ( v == "" )
			s.val( oriText );
	} );
	$("#searchForm").submit( function( e ){
		var v = $.trim( s.val() );
		if ( v == "" || v == oriText ){
			e.preventDefault();
			return false;
		}
		$("#searchvalue").val( encodeURIComponent( v ) );
	} );
};
mainFunction();
