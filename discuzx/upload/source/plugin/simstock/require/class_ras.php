<?php
/*
 * Kilofox Services
 * SimStock v1.0
 * Plug-in for Discuz!
 * Last Updated: 2011-07-07
 * Author: Glacier
 * Copyright (C) 2005 - 2011 Kilofox Services Studio
 * www.Kilofox.Net
 */
class Ras
{
	public function __construct($section)
	{
		method_exists($this,$section) && $this->$section();
	}
	private function ctl()
	{
		global $hkimg;
		$rs = DB::fetch_first("SELECT stockcode FROM ".DB::table('kfss_sminfo')." WHERE id=1");
		if ( $rs['stockcode'] )
		{
			$p = strpos($rs['stockcode'],'|');
			if ( $p === false )
			{
				$code = $rs['stockcode'];
				$newCodes = '';
			}
			else
			{
				$code = substr($rs['stockcode'], 0, $p);
				$newCodes = substr($rs['stockcode'], strpos($rs['stockcode'],'|')+1);
			}
			DB::query("UPDATE ".DB::table('kfss_sminfo')." SET stockcode='$newCodes' WHERE id=1");
			include template('simstock:ras');
		}
	}
}
?>
