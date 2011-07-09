<?php

/**
 * 增加轉換使用者自訂風格 spacefield's theme, css 到 home_theme_diy
 **/

$curprg = basename(__FILE__);

$oldpre = $db_source->tablepre;
$newpre = $db_target->tablepre;

$table_source = $db_source->tablepre.'spacefield';
$table_target = $db_target->tablepre.'home_theme_diy';

$limit = 100;
$nextid = 0;

$start = intval(getgpc('start'));

if($start == 0) {
	$db_target->query("TRUNCATE $table_target");
}

$query = $db_source->query("SELECT uid, theme, css
	FROM {$table_source}
	WHERE uid > '$start' AND css <> ''
	ORDER BY uid
	LIMIT $limit");
while ($space = $db_source->fetch_array($query)) {

	// 只匯入已經存在於目標資料庫內的用戶
	// 適用於以論壇為主的升級轉換
	if (!($_temp_ = $db_target->fetch_first("SELECT * FROM {$newpre}common_member WHERE uid = '$space[uid]'", 'SILENT'))) {
		$nextid = $space['uid'];
		continue;
	}

	$nextid = $space['uid'];

	// 將自定義風格 css 升級到 home_theme_diy
	if ($space['css'] = trim($space['css'])) {
		$space['css'] = str_replace("\r\n", "\n", $space['css']);
		$_css_name = _getcssname($space['css']);

		$setarr = array(
			'theme_name'		=> $_css_name == 'No name' ? '' : $_css_name,
			'theme_css'			=> $space['css'],
			'theme_authorid'	=> $space['uid'],
		);

		$setarr  = daddslashes($setarr, 1);

		$data = implode_field_value($setarr, ',', db_table_fields($db_target, $table_target));
		$db_target->query("INSERT INTO {$table_target} SET $data");
	}
}

if($nextid) {
	showmessage("繼續轉換數據表 $table_target uid> $nextid", "index.php?a=$action&source=$source&prg=$curprg&start=$nextid");
}

/**
 * get css style name
 **/
function _getcssname($css) {
	if($css) {
		preg_match("/\[name\](.+?)\[\/name\]/i", trim($css), $mathes);
		if(!empty($mathes[1])) $name = dhtmlspecialchars($mathes[1]);
	} else {
		$name = 'No name';
	}
	return $name;
}

?>