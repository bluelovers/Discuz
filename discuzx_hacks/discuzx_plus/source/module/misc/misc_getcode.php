<?php

/**
 *      [Discuz! XPlus] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc_getcode.php 621 2010-09-09 03:32:19Z yexinhao $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$itemid = intval($_G['gp_itemid']);
$typearray = array('poll');
$type = (empty($_G['gp_type']) || !in_array($_G['gp_type'], $typearray)) ? 'poll' : $_G['gp_type'];
$iframeurl = !empty($_G['gp_iframeurl']) ? trim(daddslashes(urldecode($_G['gp_iframeurl']))) : "{$_G['siteurl']}{$type}.php?id={$itemid}&iframe=1";
$width = !empty($_G['gp_width']) ? intval($_G['gp_width']) : '630';
$height = !empty($_G['gp_height']) ? intval($_G['gp_height']) : '400';
$bgcolor = !empty($_G['gp_bgcolor']) ? dhtmlspecialchars(trim($_G['gp_bgcolor'])) : 'FFF';

include template('common/getcode');

?>