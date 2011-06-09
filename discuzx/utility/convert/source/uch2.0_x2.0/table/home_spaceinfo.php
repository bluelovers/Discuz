<?php

/**
 * DiscuzX Convert
 *
 * $Id: home_space.php 17194 2010-09-26 06:05:16Z zhengqingpeng $
 */

$curprg = basename(__FILE__);

$oldpre = $db_source->tablepre;
$newpre = $db_target->tablepre;
$table_source = $db_source->tablepre.'members';
$table_target = $db_target->tablepre.'common_member';

$limit = $setting['limit']['space'] ? $setting['limit']['space'] : 500;
$nextid = 0;

$start = getgpc('start');
$home = load_process('home');

$limit = 500;

$query = $db_source->query("SELECT DISTINCT sf.uid
	FROM {$oldpre}spaceinfo sf, {$oldpre}space s
	WHERE sf.uid = s.uid and sf.uid>'$start'
	ORDER BY sf.uid
	LIMIT $limit");

while ($space = $db_source->fetch_array($query)) {

	$nextid = $space['uid'];

	// 只匯入已經存在於目標資料庫內的用戶
	// 適用於以論壇為主的升級轉換
	if (!$_temp_ = $db_target->fetch_first("SELECT * FROM {$table_target} WHERE uid = '$space[uid]'", 'SILENT')) {
		continue;
	}

//	showmessage(123);

	$space['types'] = $space['values'] = array();

	$query2 = $db_source->query("SELECT sf.*
		FROM {$oldpre}spaceinfo sf
		WHERE sf.uid = '{$space[uid]}'
		ORDER BY sf.type, sf.subtype, sf.infoid ASC");
	while ($spaceinfo = $db_source->fetch_array($query2)) {

//		$spaceinfo = array_map('trim', $spaceinfo);

		$spaceinfo['title'] = s_trim($spaceinfo['title'], '\\/');
		$spaceinfo['subtitle'] = s_trim($spaceinfo['subtitle'], '\\/');

		if (empty($spaceinfo['title'])) {
			continue;
		}

		$type = $spaceinfo['type'];
		$subtype = $spaceinfo['subtype'] ? $spaceinfo['subtype'] : 'null';;

		$spaceinfo['type_key'] = $type_key = $type.'_'.$subtype;

//		$subtype == 'trainwith' && showmessage($spaceinfo['type_key'].$spaceinfo['title']);

		$space['types'][$type_key][] = $spaceinfo;
	}

	$space['types']  = daddslashes($space['types'], 1);

	foreach(array(
		'info_trainwith',
		'info_interest',

		// nnone
		'info_book',
		'info_movie',
		'info_tv',
		'info_music',
		'info_game',

		// 喜歡的運動
		'info[sport',

		// 偶像
		'info[idol',

		// 座右銘
		'info_motto',

		// 最近心願
		'info_wish',

		// 我的簡介
		'info_intro',

	) as $_k_) {
		if ($space['types'][$_k_]) {
			$space['values'][$_k_] = $space['types'][$_k_][0]['title'];

//				showmessage(123);
//				exit();

			if (!s_trim($space['values'][$_k_], "\\/@＠?!#&mp;%\*\^_.-+=0123456789asd") || s_valid_email($space['values'][$_k_]) || s_valid_url($space['values'][$_k_])) {
				$space['values'][$_k_] = '';
			}

		}
	}

	$update = array(
		'status' => array(
//			'regip' => $row['regip'],
//			'lastip' => $row['regip'],
//			'lastvisit' => $row['lastvisit'],
//			'lastactivity' => $row['lastactivity'],
//			'lastpost' => $row['lastpost'],
//			'buyercredit' => $row['buyercredit'],
//			'sellercredit' => $row['sellercredit'],
		),
		'count' => array(
//			'posts' => $row['posts'],
//			'threads' => $row['threads'],
//			'digestposts' => $row['digestposts'],
//			'extcredits1' => $row['extcredits1'],
//			'extcredits2' => $row['extcredits2'],
//			'extcredits3' => $row['extcredits3'],
//			'extcredits4' => $row['extcredits4'],
//			'extcredits5' => $row['extcredits5'],
//			'extcredits6' => $row['extcredits6'],
//			'extcredits7' => $row['extcredits7'],
//			'extcredits8' => $row['extcredits8'],
//			'oltime' => $row['oltime'],
		),
		'profile' => array(
//			'birthyear' => $year,
//			'birthmonth' => $month,
//			'birthday' => $day,
//			'gender' => $row['gender'],
//			'site' => $rowfield['site'],
//			'alipay' => $rowfield['alipay'],
//			'icq' => $rowfield['icq'],
//			'qq' => $rowfield['qq'],
//			'yahoo' => $rowfield['yahoo'],
//			'msn' => $rowfield['msn'],
//			'taobao' => $rowfield['taobao'],
//			'address' => $rowfield['location'],
//			'bio' => $rowfield['bio'],
//
//			// bluelovers
//			'nickname' => $rowfield['nickname'],
////			'customstatus' => $rowfield['customstatus'],
//			// bluelovers

			'lookingfor' => $space['values']['info_trainwith'],
			'interest' => $space['values']['info_interest'],

		),
		'field_forum' => array(
//			'customshow' => $row['customshow'],
//			'customstatus' => $rowfield['customstatus'],
//			'medals' => $rowfield['medals'],
//			'sightml' => $rowfield['sightml'],
//			'groupterms' => $rowfield['groupterms'],
//			'authstr' => $rowfield['authstr'],
		)
	);

	foreach($update as $table => $trow) {
		$trow = array_filter($trow);

		if ($trow) {
			$data = implode_field_value($trow, ',', db_table_fields($db_target, $table_target.'_'.$table));

//			showmessage("UPDATE {$table_target}_$table SET $data WHERE uid='$space[uid]'");
//			exit();

			$data && $db_target->query("UPDATE {$table_target}_$table SET $data WHERE uid='$space[uid]'");
		}
	}

	$update = array(
		'status' => array(
		),
		'count' => array(
		),
		'profile' => array(
			'bio' => $space['values']['info_intro'],

		),
		'field_forum' => array(
		)
	);

	foreach($update as $table => $trow) {
		$trow = array_filter($trow);

		if ($trow) {
			$_temp_ = $data = array();

			$_temp_ = $db_target->fetch_first("SELECT * FROM {$table_target}_{$table} WHERE uid = '$space[uid]'", 'SILENT');

			foreach($trow as $_k_ => $_v_) {
				if (empty($_temp_[$_k_])) {
					$data[$_k_] = $_v_;
				} elseif ($_temp_[$_k_] != $_v_) {
					$data[$_k_] = $_temp_[$_k_]."\n\n--------------------\n\n".$_v_;
				}
			}

			$data = implode_field_value($data, ',', db_table_fields($db_target, $table_target.'_'.$table));

//			showmessage("UPDATE {$table_target}_$table SET $data WHERE uid='$space[uid]'");
//			exit();

			$data && $db_target->query("UPDATE {$table_target}_$table SET $data WHERE uid='$space[uid]'");
		}
	}

}

//showmessage($data);
//exit();

if($nextid) {
	showmessage("繼續轉換數據表 {$oldpre}spaceinfo uid> $nextid", "index.php?a=$action&source=$source&prg=$curprg&start=$nextid");
}

function getupdatesql($setarr) {
	$updatearr = array();
	foreach ($setarr as $key => $value) {
		$updatearr[] = "`$key`='$value'";
	}
	return implode(',', $updatearr);
}

?>