/*
 * Kilofox Services
 * StockIns v9.4
 * Plug-in for Discuz!
 * Last Updated: 2011-05-28
 * Author: Glacier
 * Copyright (C) 2005 - 2011 Kilofox Services Studio
 * www.Kilofox.Net
 */
function genRandomNum()
{
	r = Math.round(999*Math.random());
	return r;
}
function foxajax()
{
	if ( typeof window.external == 'object' && typeof document.all=='object' )
	{
		r = new ActiveXObject("Microsoft.XMLHTTP");
	}
	else
	{
		r = new XMLHttpRequest();
	}
	return r;
}
// 检查股票名称
function checkdata(old)
{
	if ( old == 1 )
	{
		var stname = document.form1.stname.value;
		var stnameold = '';
	}
	else
	{
		var stname = document.form1.stname.value;
		var stnameold = document.form1.stnameold.value;
	}
	r = genRandomNum();
	s = 'stname=' + stname + '&stnameold=' + stnameold + '&r=' + r;
	chkd.open('POST','plugin.php?id=stock_dzx:index&mod=ajax&section=esnamecheck');
	chkd.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	chkd.onreadystatechange = showresult;
	chkd.send(s);
}
function showresult()
{
	if ( chkd.readyState == 4 )
	{
		dataReturn = chkd.responseText.toString();
		document.getElementById('dispmsg').innerHTML = dataReturn;
	}
}
// 分红
function selectCutType(id)
{
	if ( id == 1 )
	{
		document.getElementById('cut_type1').style.display = 'inline';
		document.getElementById('cut_type2').style.display = 'none';
	}
	else if ( id == 2 )
	{
		document.getElementById('cut_type1').style.display = 'none';
		document.getElementById('cut_type2').style.display = 'inline';
	}
}
function cutCheck1( stockNum, myFunds )
{
	cutNum = document.formcam.cutnum1.value;
	var needFunds = cutNum * parseInt(stockNum/100);
	var msgs = '本次分红将派送资金 <span class="xi1">' + needFunds + '</span> 元';
	if ( needFunds > myFunds )
		msgs += '，您只有 <span class="s1">' + myFunds + '</span> 元，不足以分红';
	document.getElementById('show_msgs').innerHTML = msgs;
}
function cutCheck2( totalNum, myStockNum )
{
	cutNum = document.formcam.cutnum2.value;
	var needNum = cutNum * parseInt(totalNum/100);
	var msgs = '本次分红将派送股票 <span class="xi1">' + needNum + '</span> 股';
	if ( needNum > myStockNum )
		msgs += '，您只有 <span class="s1">' + myStockNum + '</span> 股，不足以分红';
	document.getElementById('show_msgs').innerHTML = msgs;
}
function melonFormCheck( totalNum, myStockNum, myFunds )
{
	var j = 0;
	for ( i=0; i<document.formcam.cuttype.length; i++ )
	{
		if ( document.formcam.cuttype[i].checked )
			j = i + 1;
	}
	if ( j == 0 )
	{
		msgs = '请选择分红方式！';
		alert(msgs);
		return false;
	}
	else if ( j == 1 )
	{
		var cutNum = document.formcam.cutnum1.value;
		if ( isNaN(cutNum) || cutNum < 1 )
		{
			msgs = '请正确填写派送资金！';
			alert(msgs);
			return false;
		}
		var needFunds = cutNum * parseInt(totalNum/100);
		if ( needFunds > myFunds )
		{
			msgs = '您只有 ' + myFunds + ' 元，不足以分红';
			alert(msgs);
			return false;
		}
	}
	else if ( j == 2 )
	{
		var cutNum = document.formcam.cutnum2.value;
		if ( isNaN(cutNum) || cutNum < 1 )
		{
			msgs = '请正确填写送股数量！';
			alert(msgs);
			return false;
		}
		var needNum = cutNum * parseInt(totalNum/100);
		if ( needNum > myStockNum )
		{
			msgs = '您只有 ' + myStockNum + ' 股，不足以分红';
			alert(msgs);
			return false;
		}
	}
	return true;
}
function changeTwoDecimal(x)
{
	var f_x = parseFloat(x);
	if ( isNaN(f_x) )
	{
		alert('错误：非数字计算');
		return false;
	}
	var f_x = Math.round(x*100)/100;
	var s_x = f_x.toString();
	var pos_decimal = s_x.indexOf('.');
	if ( pos_decimal < 0 )
	{
		pos_decimal = s_x.length;
		s_x += '.';
	}
	while ( s_x.length <= pos_decimal + 2 )
	{
		s_x += '0';
	}
	return s_x;
}
// 首页时间显示
function foxTime()
{
	foxdate = new Date();
	year	= foxdate.getFullYear();
	month	= foxdate.getMonth() + 1;
	day		= foxdate.getDate();
	hour	= foxdate.getHours();
	minute	= foxdate.getMinutes();
	second	= foxdate.getSeconds();
	if ( minute < 10 )
	{
		minute = "0" + minute;
	}
	if ( second < 10 )
	{
		second = "0" + second;
	}
	document.getElementById('foxsmtime').innerHTML = year + '年' + month + '月' + day + '日 ' + hour + ':' + minute + ':' + second;
	setTimeout('foxTime()',1000)
}
function isIntNum(str)
{
	if ( str.match(/^[\d]+$/) )
		return true;
	else
		return false;
}
// 购买股票计算费用
function calFeesBuy(tradecharge, stampduty, usercash, numMax)
{
	var price	= document.getElementById('price_buy').value;
	var num		= document.getElementById('num_buy').value;
	var showMsg	= '';
	if ( !price || isNaN(price) || price <= 0 )
		showMsg = '股票价格输入错误！';
	else if ( !num || isNaN(num) || num <= 0 )
		showMsg = '股票数量输入错误！';
	else
	{
		if ( num > numMax )
		{
			showMsg = '买入数量不能大于 ' + numMax + ' 股';
		}
		else
		{
			worth		= changeTwoDecimal(price * parseInt(num));
			needFees	= worth * tradecharge / 100;
			needFees	= changeTwoDecimal(needFees >= stampduty ? needFees : stampduty);
			moneyLeft	= changeTwoDecimal(usercash - worth - needFees);
			if ( moneyLeft < 0 )
				showMsg = '您的帐户中没有足够的资金用来购买股票！';
			else
				showMsg = '股票金额 <span class="xi1">' + worth + '</span> 元<br/>印花税金 <span class="xi1">' + needFees + '</span> 元<br/>买后帐户结余 <span class="xi1">' + moneyLeft + '</span> 元';
		}
	}
	document.getElementById('feesNeedBuy').innerHTML = showMsg;
}
// 卖出股票计算费用
function calFeesSell(tradecharge, stampduty, usercash, numMax)
{
	var price	= document.getElementById('price_sell').value;
	var num		= document.getElementById('num_sell').value;
	var showMsg = '';
	if ( price == '' || isNaN(price) || price <= 0 )
		showMsg = '卖出价格输入错误！';
	else if ( !num || isNaN(num) || num <= 0 )
		showMsg = '卖出数量输入错误！';
	else
	{
		if ( num > numMax )
		{
			showMsg = '卖出数量不能大于 ' + numMax + ' 股';
		}
		else
		{
			worth		= changeTwoDecimal(price * parseInt(num));
			needFees	= worth * tradecharge / 100;
			needFees	= changeTwoDecimal(needFees >= stampduty ? needFees : stampduty);
			moneyLeft	= changeTwoDecimal(usercash - needFees);
			showMsg = '印花税金 <span class="xi1">' + needFees + '</span> 元<br/>卖后帐户结余 <span class="xi1">' + moneyLeft + '</span> 元';
		}
	}
	document.getElementById('feesNeedSell').innerHTML = showMsg;
}
// 公司上市申请根据“组数”计算股票发行数量
function showTotalNum(n,base)
{
	if ( isNaN(n) || isNaN(base) )
		result = 0;
	else
		result = n * base;
	document.getElementById('totalnum').innerHTML = result;
}
// 公司上市申请检查表单输入
function applyFormCheck(obj, nameLenMin, nameLenMax, numMin, introLenMax)
{
	if ( obj.stname.value.length < nameLenMin || obj.stname.value.length > nameLenMax )
	{
		alert('股票名称长度不能小于 ' + nameLenMin + ' 字节或者大于 ' + nameLenMax + ' 字节');
		obj.stname.focus();
		return false;
	}
	if ( isNaN(obj.stprice.value) || obj.stprice.value < 2 || obj.stprice.value > 99 )
	{
		alert('股票发行单价不能小于 2 元或者大于 99 元');
		obj.stprice.focus();
		return false;
	}
	if ( isNaN(obj.stnum.value) || obj.stnum.value < 1 )
	{
		alert('股票发行数量不能小于 ' + numMin + ' 股');
		obj.stnum.focus();
		return false;
	}
	if ( obj.comintro.value.length < 10 || obj.comintro.value.length > introLenMax )
	{
		alert('公司简介长度请控制在 10 个字与 ' + Math.floor(introLenMax/2) + ' 个字之间');
		obj.comintro.focus();
		return false;
	}
	return true;
}
// 股票交易检查表单输入
function tradeFormCheck(obj, priceMin, priceMax, numMin, numMax, trade_type)
{
	var price_buy	= document.getElementById('price_buy').value;
	var num_buy		= document.getElementById('num_buy').value;
	var price_sell	= document.getElementById('price_sell').value;
	var num_sell	= document.getElementById('num_sell').value;
	if ( trade_type == 'b' )
	{
		if ( isNaN(price_buy) || price_buy < priceMin || price_buy > priceMax )
		{
			alert('买入价格不能小于 ' + priceMin + ' 元或者大于 ' + priceMax + ' 元');
			obj.price_buy.focus();
			return false;
		}
		if ( isNaN(num_buy) || num_buy < numMin || num_buy > numMax )
		{
			alert('买入数量不能小于 ' + numMin + ' 股或者大于 ' + numMax + ' 股');
			obj.num_buy.focus();
			return false;
		}
	}
	else if ( trade_type == 's' )
	{
		if ( isNaN(price_sell) || price_sell < priceMin || price_sell > priceMax )
		{
			alert('卖出价格不能小于 ' + priceMin + ' 元或者大于 ' + priceMax + ' 元');
			obj.price_sell.focus();
			return false;
		}
		if ( isNaN(num_sell) || num_sell < numMin || num_sell > numMax )
		{
			alert('卖出数量不能小于 ' + numMin + ' 股或者大于 ' + numMax + ' 股');
			obj.num_sell.focus();
			return false;
		}
	}
	else
	{
		alert('交易类型错误');
		return false;
	}
	return true;
}