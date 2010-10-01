<?php

/*
	$HeadURL:  $
	$Revision: $
	$Author: $
	$Date: $
	$Id:  $
*/

!defined('IN_UC') && exit('Access Denied');

include UC_ROOT.'./data/config.inc.php';
require_once UC_ROOT.'./lib/db.class.php';

$db = new ucserver_db;
$db->connect(UC_DBHOST, UC_DBUSER, UC_DBPW, UC_DBNAME, UC_DBCHARSET);

if ($mfavatar = $db->result_first("SELECT avatar FROM ".UC_DBTABLEPRE."memberfields WHERE uid='$uid' LIMIT 1")) {

	if($check) {
		echo 1;
		exit;
	} else {
		dheader_cache(1800, (empty($random) ? 2 : 1), null, 'avatar_'.$uid);
//		@header("HTTP/1.1 301 Moved Permanently");
	}

	header('Location: '.$mfavatar);
	exit();
}

function dheader_cache ($s = 0, $mode = 0, $lastmodified = 0, $etag = '') {

	global $_SERVER;

	$timestamp = time();

	$header = array();
	$ss = '';

//	$r = 'D, d M Y H:i:s T';
	$r = 'r';

	if ($s < 0) {
		$header['Expires'] = -1;
		$header['Cache-Control'] = 'no-store, private, post-check=0, pre-check=0, max-age=0';
		$header['Pragma'] = 'no-cache';
	} else {

		$lastmodified = $lastmodified > 0 ? $lastmodified : $timestamp;

		if ($mode) {

//			$ims = preg_replace('/;.*$/', '', $_SERVER['HTTP_IF_MODIFIED_SINCE'] );

			if (($strpos = strpos($_SERVER['HTTP_IF_MODIFIED_SINCE'], ';')) !== false) {
				$ims = substr($_SERVER['HTTP_IF_MODIFIED_SINCE'], 0, $strpos);
			} else {
				$ims = $_SERVER['HTTP_IF_MODIFIED_SINCE'];
			}

			if (empty($ims)) {
				// make Etag like Last-Modified
				if (($strpos = strpos($_SERVER['HTTP_IF_NONE_MATCH'], ';')) !== false) {
					$ims = substr($_SERVER['HTTP_IF_NONE_MATCH'], 0, $strpos);
				} else {
					$ims = $_SERVER['HTTP_IF_NONE_MATCH'];
				}
			}

			$ims = strtotime($ims);

			if ($mode > 1 && ($lastmodified - $ims) < $s) {
				header('HTTP/1.0 304 Not Modified', true);
				exit;
			} else {
				$ss .= '$ims: '.$ims."<br>";
				$ss .= '$ims date: '.gmdate($r, $ims)."<br>";
				$ss .= '$lastmodified: '.$lastmodified."<br>";
				$ss .= 'HTTP_IF_MODIFIED_SINCE: '.$_SERVER['HTTP_IF_MODIFIED_SINCE'].'<br>';
				$ss .= 'HTTP_IF_NONE_MATCH: '.$_SERVER['HTTP_IF_NONE_MATCH'].'<br>';

				$header['Last-Modified'] = gmdate($r, $lastmodified);
				$header['Etag'] = gmdate($r, $lastmodified).($etag ? ';'.$etag : '');
			}
		}

		if ($s) {
			$header['Cache-Control'] = 'max-age='.$s.', public';
			$header['Expires'] = gmdate($r, $lastmodified + $s);
		}
	}

	if ($header) {

		foreach ($header as $_k_ => $_v_) {
			header($_k_.': '.$_v_, true);
//			$ss .= $_k_.': '.$_v_."<br>";
		}

//		$headers = apache_request_headers();
//
//foreach ($headers as $header => $value) {
//    echo "$header: $value <br />\n";
//}
//
//echo "------------------<br>";
//
//		foreach ($_SERVER as $_k_ => $_v_) {
//			echo $_k_.': '.$_v_."<br>";
//		}
//
//echo "------------------<br>";
//
//		exit($ss);
	}
}

?>