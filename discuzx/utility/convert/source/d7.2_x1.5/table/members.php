<?php

/**
 * DiscuzX Convert
 *
 * $Id: members.php 15719 2010-08-25 23:51:36Z monkey $
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

$query = $db_source->query("SELECT * FROM $table_source WHERE uid>'$start' ORDER BY uid LIMIT $limit");
while ($row = $db_source->fetch_array($query)) {

	if($row['adminid'] == 1) {
		$adminrow = array('uid' => $row['uid'], 'cpgroupid' => '0', 'customperm' => '');
		$data = implode_field_value($adminrow);
		$db_target->query("INSERT INTO {$table_target_admincp} SET $data");
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
			'lastip' => $row['regip'],
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
	for ($i=1; $i++; $i<=8) {
		if (isset($rowfield['field_'.$i]) && !empty($rowfield['field_'.$i])) {
			$update['profile']['field'.$i] = $rowfield['field'.$i] = $rowfield['field_'.$i];
		}
	}
	// bluelovers

	foreach($update as $table => $trow) {
		$data = implode_field_value($trow, ',', db_table_fields($db_target, $table_target.'_'.$table));
		$db_target->query("UPDATE {$table_target}_$table SET $data WHERE uid='$row[uid]'");
	}

	// bluelovers
	if ($rowfield['authstr'] == '') $row['emailstatus'] = 1;
	// bluelovers

	$data = implode_field_value($row, ',', db_table_fields($db_target, $table_target));

	$db_target->query("INSERT INTO $table_target SET $data");
}

if($nextid) {
	showmessage("繼續轉換數據表 ".$table_source." uid > $nextid", "index.php?a=$action&source=$source&prg=$curprg&start=$nextid");
}

?>