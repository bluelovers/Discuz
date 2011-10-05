<?php

/**
 * @author bluelovers
 * @copyright 2011
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if (!$_G['uid'] || $_G['adminid'] != 1) {
	showmessage('admin_nopermission', NULL);
}

$authoridnew = $_G['gp_authoridnew'];

if (!submitcheck('modsubmit')) {
	include template('forum/topicadmin_action');
}

?>