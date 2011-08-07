<?php

/**
 * DiscuzX Convert
 *
 * $Id: members.php 17836 2010-11-03 05:24:59Z cnteacher $
 */

$curprg = basename(__FILE__);

$table_source = $db_source->tablepre.'members';
$table_target = $db_target->tablepre.'common_member';
$table_target_admincp = $db_target->tablepre.'common_admincp_member';

$limit = $setting['limit']['members'] ? $setting['limit']['members'] : 2000;
$nextid = 0;

$start = getgpc('start');
if($start == 0) {
	$db_target->query("TRUNCATE $table_target");
	$db_target->query("TRUNCATE $table_target_admincp");
	$db_target->query("TRUNCATE {$table_target}_count");
	$db_target->query("TRUNCATE {$table_target}_field_forum");
	$db_target->query("TRUNCATE {$table_target}_field_home");
	$db_target->query("TRUNCATE {$table_target}_log");
	$db_target->query("TRUNCATE {$table_target}_profile");
	$db_target->query("TRUNCATE {$table_target}_status");
}

// bluelovers
include_once DISCUZ_ROOT.'./include/editor.func.php';
$_old = $_new = array();
// bluelovers

$query = $db_source->query("SELECT * FROM $table_source WHERE uid>'$start' ORDER BY uid LIMIT $limit");
while ($row = $db_source->fetch_array($query)) {

	if($row['adminid'] == 1) {
		$adminrow = array('uid' => $row['uid'], 'cpgroupid' => '0', 'customperm' => '');
		$data = implode_field_value($adminrow);
		$db_target->query("INSERT INTO {$table_target_admincp} SET $data");
		$row['allowadmincp'] = 1;
	}

	$rowfield = $db_source->fetch_first("SELECT * FROM ".$db_source->tablepre."memberfields WHERE uid='$row[uid]'");
	$rowfield = daddslashes($rowfield, 1);

	$nextid = $row['uid'];
	$emptyrow = array('uid' => $row['uid']);
	$data = implode_field_value($emptyrow);
	$db_target->query("INSERT INTO {$table_target}_count SET $data");
	$db_target->query("INSERT INTO {$table_target}_field_forum SET $data");
	$db_target->query("INSERT INTO {$table_target}_field_home SET $data");
	$db_target->query("INSERT INTO {$table_target}_log SET $data");
	$db_target->query("INSERT INTO {$table_target}_profile SET $data");
	$db_target->query("INSERT INTO {$table_target}_status SET $data");

//	$db_target->query("REPLACE INTO {$table_target}_count SET $data");
//	$db_target->query("REPLACE INTO {$table_target}_field_forum SET $data");
//	$db_target->query("REPLACE INTO {$table_target}_field_home SET $data");
//	$db_target->query("REPLACE INTO {$table_target}_log SET $data");
//	$db_target->query("REPLACE INTO {$table_target}_profile SET $data");
//	$db_target->query("REPLACE INTO {$table_target}_status SET $data");

	// bluelovers
	$rowfield['site'] = s_trim($rowfield['site'], "\\/");

	if (!s_valid_url($rowfield['site'])) {
		$rowfield['site'] = '';
	}

	$rowfield['sightml'] = html2bbcode($rowfield['sightml'], 1, 1);
	$rowfield['sightml'] = s_trim($rowfield['sightml'], "\\/", 1);

	$rowfield['bio'] = html2bbcode($rowfield['bio'], 1);

//	if ($nextid == 174) {
//			showmessage($rowfield['bio']);
//		}

	$_new[$nextid]['nickname'] = $rowfield['nickname'] = daddslashes(s_trim_nickname($_old[$nextid]['nickname'] = $rowfield['nickname']));
	$_new[$nextid]['customstatus'] = daddslashes(s_trim_nickname($_old[$nextid]['customstatus'] = $rowfield['customstatus']));

	foreach (array('bio') as $___k_) {
		$rowfield[$___k_] = s_trim($rowfield[$___k_], "\\/", 1);

		$_t_ = s_trim($rowfield[$___k_], "\\/@＠?!#&mp;%\*\^_~.-+=0123456789asd大安多照顧無");

		$_t_ = str_replace(array('家', '好', '帥', '大', '安', '我',
		'很', '謝', '無', '沒', '什', '麼', '介', '紹', '新', '手',
		'您', '喔', '拉', '唷', '嘿', '嗨', '&amp;'
		), '', $_t_);
//		$_t_ = preg_replace("/([0-9_\-@\#\?\!;\*\^\.asdASD\'\" \t　]+)/iu", '', $_t_);
//		$_t_ = preg_replace("/[0-9_\-=asdf\t \@\?\!\.\^\*\'\~\"\[\]]+/iU", '', $_t_);

		if (!$_t_
			|| s_valid_email($rowfield[$___k_]) || s_valid_url($rowfield[$___k_])
		) {
			$rowfield[$___k_] = '';
		}

//		$rowfield[$___k_] = dhtmlspecialchars($rowfield[$___k_], ENT_QUOTES, true, ENT_QUOTES);
		$rowfield[$___k_] = dhtmlspecialchars($rowfield[$___k_], ENT_QUOTES);

//		if ($nextid == 174) {
//			showmessage($rowfield[$___k_]);
//		}
	}

	foreach (array('icq', 'alipay', 'taobao', 'qq', 'yahoo', 'msn') as $___k_) {
		$rowfield[$___k_] = s_trim($rowfield[$___k_], "\\/");

		if (empty($rowfield[$___k_]) || !(s_valid_email($rowfield[$___k_]) || preg_match("/^[a-z0-9_-]+$/i", $rowfield[$___k_]))) {
			$rowfield[$___k_] = '';
		}
	}
	// bluelovers

	$row  = daddslashes($row, 1);

	$unset = array(
		'regip',
		'lastip',
		'lastvisit',
		'lastactivity',
		'lastpost',
		'posts',
		'threads',
		'digestposts',
		'pageviews',
		'extcredits1',
		'extcredits2',
		'extcredits3',
		'extcredits4',
		'extcredits5',
		'extcredits6',
		'extcredits7',
		'extcredits8',
		'bday',
		'sigstatus',
		'tpp',
		'ppp',
		'styleid',
		'dateformat',
		'timeformat',
		'pmsound',
		'showemail',
		'newsletter',
		'invisible',
		'prompt',
		'editormode',
		'customshow',
		'xspacestatus',
		'customaddfeed',
		'newbietaskid',
		'secques',
		'gender',
	);
	list($year, $month, $day) = explode('-', $row['bday']);
	$row['notifysound'] = $row['pmsound'];
	$update = array(
		'status' => array(
			'regip' => $row['regip'],
			'lastip' => $row['lastip'],
			'lastvisit' => $row['lastvisit'],
			'lastactivity' => $row['lastactivity'],
			'lastpost' => $row['lastpost'],
			'buyercredit' => $row['buyercredit'],
			'sellercredit' => $row['sellercredit'],
		),
		'count' => array(
			'posts' => $row['posts'],
			'threads' => $row['threads'],
			'digestposts' => $row['digestposts'],
			'extcredits1' => $row['extcredits1'],
			'extcredits2' => $row['extcredits2'],
			'extcredits3' => $row['extcredits3'],
			'extcredits4' => $row['extcredits4'],
			'extcredits5' => $row['extcredits5'],
			'extcredits6' => $row['extcredits6'],
			'extcredits7' => $row['extcredits7'],
			'extcredits8' => $row['extcredits8'],
			'oltime' => $row['oltime'],
		),
		'profile' => array(
			'birthyear' => $year,
			'birthmonth' => $month,
			'birthday' => $day,
			'gender' => $row['gender'],
			'site' => $rowfield['site'],
			'alipay' => $rowfield['alipay'],
			'icq' => $rowfield['icq'],
			'qq' => $rowfield['qq'],
			'yahoo' => $rowfield['yahoo'],
			'msn' => $rowfield['msn'],
			'taobao' => $rowfield['taobao'],
			'address' => $rowfield['location'],
			'bio' => $rowfield['bio'],

			// bluelovers
			'nickname' => $rowfield['nickname'],
//			'customstatus' => $rowfield['customstatus'],
			// bluelovers

		),
		'field_forum' => array(
			'customshow' => $row['customshow'],
			'customstatus' => $rowfield['customstatus'],
			'medals' => $rowfield['medals'],
			'sightml' => $rowfield['sightml'],
			'groupterms' => $rowfield['groupterms'],
			'authstr' => $rowfield['authstr'],
		)
	);
	foreach($unset as $k) {
		unset($row[$k]);
	}

	// bluelovers
	for ($i=1; $i<=8; $i++) {
		if (isset($rowfield['field_'.$i]) && !empty($rowfield['field_'.$i])) {
			$update['profile']['field'.$i] = $rowfield['field'.$i] = $rowfield['field_'.$i];
		}
	}

	if (!(s_valid_email($update['profile']['field1']) || preg_match("/^[a-z0-9_-]+$/i", $update['profile']['field1']))) {
		$update['profile']['field1'] = '';
	}
	// bluelovers

	foreach($update as $table => $trow) {
		$data = implode_field_value($trow, ',', db_table_fields($db_target, $table_target.'_'.$table));
		$db_target->query("UPDATE {$table_target}_$table SET $data WHERE uid='$row[uid]'");
	}

	// bluelovers
	if (!s_valid_email($row['email']) || empty($row['email']) || (empty($row['timeoffset']) && !($row['posts'] > 0 || $row['threads'] > 0 || $row['digestposts'] > 0)) || !empty($rowfield['authstr'])) {
		$row['emailstatus'] = 0;
	} elseif ($rowfield['authstr'] == '' && ($row['posts'] > 0 || $row['threads'] > 0 || $row['digestposts'] > 0)) {
		$row['emailstatus'] = 1;
	}
	// bluelovers

	$data = implode_field_value($row, ',', db_table_fields($db_target, $table_target));

	$db_target->query("INSERT INTO $table_target SET $data");
//	$db_target->query("REPLACE INTO $table_target SET $data");
}

if($nextid) {
	$_old = array_filter($_old);
	$_s = '';

	if($start == 0) {
		$fp = fopen('tmp.txt', 'w') or die("can't open file");
		fclose($fp);
	}

	foreach($_old as $_uid => $_row) {
//		$_k_ = 'nickname';

		foreach($_row as $_k_ => $_v_) {
			if ($_row[$_k_] != $_new[$_uid][$_k_]) {
				$_s .= '<_uid_> '.$_uid.'<_k_> '.$_k_.' <_;_> '.$_row[$_k_].' <_x_> '.$_new[$_uid][$_k_]."\n";
			}
		}
	}

	$fp = fopen('tmp.txt', 'aw+') or die("can't open file");
	fwrite($fp, $_s);
	fclose($fp);

	showmessage("繼續轉換數據表 ".$table_source." uid > $nextid", "index.php?a=$action&source=$source&prg=$curprg&start=$nextid");

} else {
	$db_target->query("UPDATE $table_target SET newpm='0'");

// bluelovers

	$db_target->query("REPLACE INTO {$db_target->tablepre}common_setting ( skey, svalue )
		VALUES (
		'lastmember', (
			SELECT username
			FROM {$db_target->tablepre}common_member
			ORDER BY regdate DESC
			LIMIT 1
		)
	)");

// bluelvoers

}

?>