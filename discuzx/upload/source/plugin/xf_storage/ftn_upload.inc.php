<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: ftn_upload.inc.php 29021 2012-03-22 09:35:55Z songlixin $
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$formhash = formhash();
if($_G['gp_formhash'] == $formhash && $_G['gp_inajax']){
	$iframeurl = make_iframe_url(ftn_formhash());
} else {
	$iframeurl = '';
}

include template('xf_storage:upload');

?>