<?php
/*
 * Kilofox Services
 * StockIns v9.4
 * Plug-in for Discuz!
 * Last Updated: 2011-06-10
 * Author: Glacier
 * Copyright (C) 2005 - 2011 Kilofox Services Studio
 * www.Kilofox.Net
 */
class Help
{
	public function __construct()
	{
		global $baseScript, $kfsclass, $hkimg, $htype, $_G, $db_smname, $db_wavemax, $db_tradenummin, $db_dutyrate, $db_dutymin;
		$htype = $_G['gp_htype'];
		$db_dutymin = number_format($db_dutymin,2);
		include template('stock_dzx:help');
	}
}
?>
