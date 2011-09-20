<?php
/*
 * Kilofox Services
 * SimStock v1.0
 * Plug-in for Discuz!
 * Last Updated: 2011-07-19
 * Author: Glacier
 * Copyright (C) 2005 - 2011 Kilofox Services Studio
 * www.Kilofox.Net
 */
class Tools
{
	public function kfsmReset()
	{
		global $baseScript;
		$kfsclass = new kfsclass;
		$kfsclass::kfsmReset();
		$baseScript .= '&mod=tools';
		cpmsg('股市重新启动成功', $baseScript, 'succeed');
	}
	public function udRank()
	{
		global $baseScript;
		$kfsclass = new kfsclass;
		$kfsclass::updateRank();
		$baseScript .= '&mod=tools';
		cpmsg('大赛榜单更新成功', $baseScript, 'succeed');
	}
}
?>
