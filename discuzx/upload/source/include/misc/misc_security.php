<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc_security.php 6757 2010-03-25 09:01:29Z cnteacher $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if($attackevasive & 1 || $attackevasive & 4) {
	$_G['cookie']['lastrequest'] = authcode($_G['cookie']['lastrequest'], 'DECODE');
	dsetcookie('lastrequest', authcode(TIMESTAMP, 'ENCODE'), TIMESTAMP + 816400, 1, true);
}

if($attackevasive & 1) {
	if(TIMESTAMP - $_G['cookie']['lastrequest'] < 1) {
		securitymessage('attachsave_1_subject', 'attachsave_1_message');
	}
}

if(($attackevasive & 2) && ($_SERVER['HTTP_X_FORWARDED_FOR'] ||
	$_SERVER['HTTP_VIA'] || $_SERVER['HTTP_PROXY_CONNECTION'] ||
	$_SERVER['HTTP_USER_AGENT_VIA'] || $_SERVER['HTTP_CACHE_INFO'] ||
	$_SERVER['HTTP_PROXY_CONNECTION'])) {
		securitymessage('attachsave_2_subject', 'attachsave_2_message', FALSE);
}

if($attackevasive & 4) {
	if(empty($_G['cookie']['lastrequest']) || TIMESTAMP - $_G['cookie']['lastrequest'] > 300) {
		securitymessage('attachsave_4_subject', 'attachsave_4_message');
	}
}

if($attackevasive & 8) {
	list($questionkey, $questionanswer, $questiontime) = explode('|', authcode($_G['cookie']['secqcode'], 'DECODE'));
	loadcache('secqaa');
	if(!$questionanswer || !$questiontime || $_G['cache']['secqaa'][$questionkey]['answer'] != $questionanswer) {

		if(empty($_POST['secqsubmit']) || (!empty($_POST['secqsubmit']) && $_G['cache']['secqaa'][$questionkey]['answer'] != md5($_POST['answer']))) {
			$questionkey = array_rand($_G['cache']['secqaa']);
			dsetcookie('secqcode', authcode($questionkey.'||'.TIMESTAMP, 'ENCODE'), TIMESTAMP + 816400, 1, true);
			securitymessage($_G['cache']['secqaa'][$questionkey]['question'], '<input type="text" name="answer" size="8" maxlength="150" /><input class="button" type="submit" name="secqsubmit" value=" Submit " />', FALSE, TRUE);
		} else {
			dsetcookie('secqcode', authcode($questionkey.'|'.$_G['cache']['secqaa'][$questionkey]['answer'].'|'.TIMESTAMP, 'ENCODE'), TIMESTAMP + 816400, 1, true);
		}
	}

}

function securitymessage($subject, $message, $reload = TRUE, $form = FALSE) {
	global $_G;
	$scuritylang = array(
		'attachsave_1_subject' => '&#x9891;&#x7e41;&#x5237;&#x65b0;&#x9650;&#x5236;',
		'attachsave_1_message' => '&#x60a8;&#x8bbf;&#x95ee;&#x672c;&#x7ad9;&#x901f;&#x5ea6;&#x8fc7;&#x5feb;&#x6216;&#x8005;&#x5237;&#x65b0;&#x95f4;&#x9694;&#x65f6;&#x95f4;&#x5c0f;&#x4e8e;&#x4e24;&#x79d2;&#xff01;&#x8bf7;&#x7b49;&#x5f85;&#x9875;&#x9762;&#x81ea;&#x52a8;&#x8df3;&#x8f6c;&#x20;&#x2e;&#x2e;&#x2e;',
		'attachsave_2_subject' => '&#x4ee3;&#x7406;&#x670d;&#x52a1;&#x5668;&#x8bbf;&#x95ee;&#x9650;&#x5236;',
		'attachsave_2_message' => '&#x672c;&#x7ad9;&#x73b0;&#x5728;&#x9650;&#x5236;&#x4f7f;&#x7528;&#x4ee3;&#x7406;&#x670d;&#x52a1;&#x5668;&#x8bbf;&#x95ee;&#xff0c;&#x8bf7;&#x53bb;&#x9664;&#x60a8;&#x7684;&#x4ee3;&#x7406;&#x8bbe;&#x7f6e;&#xff0c;&#x76f4;&#x63a5;&#x8bbf;&#x95ee;&#x672c;&#x7ad9;&#x3002;',
		'attachsave_4_subject' => '&#x9875;&#x9762;&#x91cd;&#x8f7d;&#x5f00;&#x542f;',
		'attachsave_4_message' => '&#x6b22;&#x8fce;&#x5149;&#x4e34;&#x672c;&#x7ad9;&#xff0c;&#x9875;&#x9762;&#x6b63;&#x5728;&#x91cd;&#x65b0;&#x8f7d;&#x5165;&#xff0c;&#x8bf7;&#x7a0d;&#x5019;&#x20;&#x2e;&#x2e;&#x2e;'
	);

	$subject = $scuritylang[$subject] ? $scuritylang[$subject] : $subject;
	$message = $scuritylang[$message] ? $scuritylang[$message] : $message;
	if($_GET['inajax']) {
		ajaxshowheader();
		echo '<div id="attackevasive_1" class="popupmenu_option"><b style="font-size: 16px">'.$subject.'</b><br /><br />'.$message.'</div>';
		ajaxshowfooter();
	} else {
		echo '<html>';
		echo '<head>';
		echo '<title>'.$subject.'</title>';
		echo '</head>';
		echo '<body bgcolor="#FFFFFF">';
		if($reload) {
			echo '<script language="JavaScript">';
			echo 'function reload() {';
			echo '	document.location.reload();';
			echo '}';
			echo 'setTimeout("reload()", 1001);';
			echo '</script>';
		}
		if($form) {
			echo '<form action="'.$G['PHP_SELF'].'" method="post" autocomplete="off">';
		}
		echo '<table cellpadding="0" cellspacing="0" border="0" width="700" align="center" height="85%">';
		echo '  <tr align="center" valign="middle">';
		echo '    <td>';
		echo '    <table cellpadding="10" cellspacing="0" border="0" width="80%" align="center" style="font-family: Verdana, Tahoma; color: #666666; font-size: 11px">';
		echo '    <tr>';
		echo '      <td valign="middle" align="center" bgcolor="#EBEBEB">';
		echo '     	<br /><br /> <b style="font-size: 16px">'.$subject.'</b> <br /><br />';
		echo $message;
		echo '        <br /><br />';
		echo '      </td>';
		echo '    </tr>';
		echo '    </table>';
		echo '    </td>';
		echo '  </tr>';
		echo '</table>';
		if($form) {
			echo '</form>';
		}
		echo '</body>';
		echo '</html>';
	}
	exit();
}


function ajaxshowheader() {
	global $_G;
	ob_end_clean();
	@header("Expires: -1");
	@header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
	@header("Pragma: no-cache");
	header("Content-type: application/xml");
	echo "<?xml version=\"1.0\" encoding=\"".CHARSET."\"?>\n<root><![CDATA[";
}

function ajaxshowfooter() {
	echo ']]></root>';
	exit();
}

?>