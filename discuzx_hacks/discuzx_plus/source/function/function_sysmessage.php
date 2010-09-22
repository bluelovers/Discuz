<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_sysmessage.php 7312 2010-04-06 05:21:28Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

/**
 * 顯示系統消息（簡易模式，無需加載 Style)
 * 依賴：function_core
 * @param string $message 消息，語言包 message
 * @param string $title 標題， 支持語言包 message， 默認 system message
 * @param array $msgvar 消息語言的拓展變量參數
 */
function show_system_message($message, $title = '', $msgvar = array()) {
	$message = lang('message', $message, $msgvar);
	$title = $title ? lang('message', $title) : lang('error', 'System Message');
	$charset = CHARSET;
	echo <<<EOT
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>$title</title>
<meta name="keywords" content="" />
<meta name="description" content="System Message - Discuz! Board" />
<meta name="generator" content="Discuz! " />
<meta name="author" content="Discuz! Team and Comsenz UI Team" />
<meta name="copyright" content="2001-2010 Comsenz Inc." />
<meta name="MSSmartTagsPreventParsing" content="True" />
<meta http-equiv="MSThemeCompatible" content="Yes" />
</head>
<body bgcolor="#FFFFFF">
<table cellpadding="0" cellspacing="0" border="0" width="700" align="center" height="85%">
  <tr align="center" valign="middle">
    <td>
    <table cellpadding="20" cellspacing="0" border="0" width="80%" align="center" style="font-family: Verdana, Tahoma; color: #666666; font-size: 12px">
    <tr>
      <td valign="middle" align="center" bgcolor="#EBEBEB">
        <b style="font-size: 16px">$title</b>
        <br /><br />$message
        <br /><br />
      </td>
    </tr>
    </table>
    </td>
  </tr>
</table>
</body>
</html>
EOT;
	die();
}

?>