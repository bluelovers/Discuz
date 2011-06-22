<?php
/*
 * Kilofox Services
 * StockIns v9.4
 * Plug-in for Discuz!
 * Last Updated: 2011-06-18
 * Author: Glacier
 * Copyright (C) 2005 - 2011 Kilofox Services Studio
 * www.Kilofox.Net
 */
class Apply
{
	public function __construct($member, $section)
	{
		if ( empty($section) )
			$this->showApplyList($member);
		else if ( $section == 'apply' )
			$this->showApplyForm($member);
		else if ( $section == 'save' )
			$this->saveData($member);
	}
	private function showApplyList($user)
	{
		global $baseScript, $_G, $db_smname, $db_marketpp, $page, $hkimg;
		$apply_list = array();
		$cnt = DB::result_first("SELECT COUNT(*) FROM ".DB::table('kfsm_apply'));
		if ( $cnt > 0 )
		{
			$readperpage = is_numeric($db_marketpp) && $db_marketpp > 0 ? $db_marketpp : 20;
			$page = $_G['gp_page'];
			if ( $page < 1 )
			{
				$page = 1;
				$start = 0;
			}
			$numofpage = ceil($cnt/$readperpage);
			if ( $page > $numofpage )
			{
				$page = $numofpage;
				$start-=1;
			}
			$start = ( $page - 1 ) * $readperpage;
			$pages = foxpage($page,$numofpage,"$baseScript&mod=member&act=apply&");
			$i = 0;
			$query = DB::query("SELECT * FROM ".DB::table('kfsm_apply')." ORDER BY aid DESC LIMIT $start, $readperpage");
			while ( $rs = DB::fetch($query) )
			{
				$i++;
				$rs['i'] = $i;
				$rs['stockprice'] = number_format($rs['stockprice'],2);
				$rs['applytime'] = dgmdate($rs['applytime']);
				if ( $rs['state'] == 0 )
					$rs['state'] = '待审核';
				else if ( $rs['state'] == 1 )
					$rs['state'] = '已批准';
				else if ( $rs['state'] == 2 )
					$rs['state'] = '未通过';
				else if ( $rs['state'] == 3 )
					$rs['state'] = '已上市';
				else
					$rs['state'] = '异常';
				$applys[] = $rs;
			}
		}
		include template('stock_dzx:member_apply');
	}
	private function showApplyForm($user)
	{
		global $baseScript, $hkimg, $_G, $db_smname, $db_esifopen, $db_esnamemin, $db_esnamemax, $db_esminnum, $db_introducemax;
		if ( $db_esifopen == '1' )
		{
			$min_wealth = $db_esminnum * 10;
			if ( $user['capital_ava'] < $min_wealth )
			{
				$applyAllow = false;
				$msg = '您的股市帐户可用资金不足 <span class="xi1">'.number_format($min_wealth,2).'</span> 元，不能申请公司上市';
			}
			else
			{
				$applyAllow = true;
				/* Prepared for JavaScript Begin */
				if ( empty($db_esnamemin) || !is_numeric($db_esnamemin) || $db_esnamemin < 3 )
					$db_esnamemin = 3;
				if ( empty( $db_esnamemax) || !is_numeric( $db_esnamemax) ||  $db_esnamemax < 3 )
					 $db_esnamemax = 3;
				if ( empty($db_introducemax) || !is_numeric($db_introducemax) || $db_introducemax < 10 )
					$db_introducemax = 10;
				if ( empty($db_esminnum) || !is_numeric($db_esminnum) || $db_esminnum < 0 )
					$db_esminum = 0;
				/* Prepared for JavaScript End */
				$rndPrice = mt_rand(2,99);
			}
		}
		else
		{
			$applyAllow = false;
			$msg = '暂时停止接受公司上市的申请';
		}
		include template('stock_dzx:member_apply');
	}
	private function saveData($user)
	{
		global $baseScript, $db_esifopen, $db_esnamemin, $db_esnamemax, $db_esminnum, $db_esminfunds, $db_introducemax, $_G;
		if ( $user['id'] )
		{
			$stname		= $_G['gp_stname'];
			$stprice	= $_G['gp_stprice'];
			$stnum		= $_G['gp_stnum'];
			$minprice	= $_G['gp_minprice'];
			$maxprice	= $_G['gp_maxprice'];
			$comintro	= trim($_G['gp_comintro']);
			if ( empty($stname) || strlen($stname) < $db_esnamemin || strlen($stname) > $db_esnamemax )
				showmessage("股票名称长度不能小于 $db_esnamemin 字节或者大于 $db_esnamemax 字节");
			if ( empty($stprice) || !is_numeric($stprice) || $stprice < 2 )
				showmessage('请正确输入股票发行单价，股票发行单价不能小于 2 元');
			$stnum = $stnum * $db_esminnum;
			if ( empty($stnum) || !is_numeric($stnum) || $stnum < $db_esminnum )
				showmessage("请正确输入股票发行数量，股票发行数量不能小于 " . intval($db_esminnum) . " 股");
			if ( $user['capital_ava'] < $stprice * $stnum )
				showmessage('您没有足够的资金，请适当调整股票发行单价与发行数量');
			if ( empty($comintro) || strlen($comintro) < 10 || strlen($comintro) > $db_introducemax )
				showmessage("公司简介长度请控制在 10 字节与 $db_introducemax 字节之间");
			$rsName = DB::result_first("SELECT stockname FROM ".DB::table('kfsm_stock')." WHERE stockname='$stname'");
			$rsName && showmessage('您输入的股票名称已经存在');
			$rsaName = DB::result_first("SELECT stockname FROM ".DB::table('kfsm_apply')." WHERE stockname='$stname' AND state<>2");
			$rsaName && showmessage('您输入的股票名称已经存在');
			$capitalisation = $stprice * $stnum;
			DB::query("INSERT INTO ".DB::table('kfsm_apply')." (userid, username, stockname, stockprice, stocknum, surplusnum, capitalisation, comintro, applytime, state) VALUES('$user[id]', '$user[username]', '$stname', $stprice, '$stnum', '$stnum', '$capitalisation', '$comintro', '$_G[timestamp]', '0')");
			DB::query("UPDATE ".DB::table('kfsm_user')." SET capital_ava=capital_ava-$capitalisation WHERE uid='$user[id]'");
			DB::query("INSERT INTO ".DB::table('kfsm_smlog')." (type, username2, descrip, timestamp, ip) VALUES('用户管理', '$user[username]', '公司 $stname 申请上市，单价 " . number_format($stprice,2) . " 元，数量 $stnum 股', '$_G[timestamp]', '$_G[clientip]')");
			showmessage('您的公司上市申请资料已成功提交！请等待证监会的审批。', $baseScript);
		}
	}
}
?>
