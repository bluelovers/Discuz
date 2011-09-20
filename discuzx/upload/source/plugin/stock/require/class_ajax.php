<?php
/*
 * Kilofox Services
 * StockIns v9.5
 * Plug-in for Discuz!
 * Last Updated: 2011-06-20
 * Author: Glacier
 * Copyright (C) 2005 - 2011 Kilofox Services Studio
 * www.Kilofox.Net
 */
class Ajax
{
	public function __construct($section)
	{
		method_exists($this,$section) && $this->$section();
	}
	private function esnamecheck()
	{
		global $db_esnamemin, $db_esnamemax, $_G;
		$stname		= $_G['gp_stname'];
		$stnameold	= $_G['p_gstnameold'];
		$msg = '';
		if ( empty($stname) )
		{
			$msg .= "请输入股票名称";
		}
		else
		{
			$stname = mb_convert_encoding($stname,'gbk','utf-8');
			$stnameold = mb_convert_encoding($stnameold,'gbk','utf-8');
			if ( strlen($stname) < $db_esnamemin )
			{
				$msg .= "股票名称长度不能小于 {$db_esnamemin} 字节";
			}
			else if ( strlen($stname) > $db_esnamemax )
			{
				$msg .= "股票名称长度不能大于 {$db_esnamemax} 字节";
			}
			else if ( $stname <> $stnameold )
			{
				$rs = DB::result_first("SELECT stockname FROM ".DB::table('kfsm_stock')." WHERE stockname='$stname'");
				if ( $rs )
				{
					$msg .= '您输入的股票名称已经存在';
				}
				else
				{
					$esrs = DB::result_first("SELECT stockname FROM ".DB::table('kfsm_apply')." WHERE stockname='$stname' AND state<>2");
					if ( $esrs )
					{
						$msg .= '您输入的股票名称已经存在';
					}
				}
			}
		}
		if ( !$msg )
		{
			$msg = "股票名称<span class=\"xi1\">{$stname}</span>已通过验证，可以使用";
		}
		echo $msg;
	}
}
?>
