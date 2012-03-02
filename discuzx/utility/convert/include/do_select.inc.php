<?php

/**
 * DiscuzX Convert
 *
 * $Id: do_select.inc.php 20664 2011-03-01 08:30:19Z shanzongjun $
 */

$config = loadconfig();
$db_source = new db_mysql($config['source']);
$db_source->connect();

$db_target = new db_mysql($config['target']);
$db_target->connect();

$db_uc = new db_mysql($config['ucenter']);
if($setting['config']['ucenter']) {
	$db_uc->connect();
}

$prgdir = DISCUZ_ROOT.'./source/'.$source.'/table/';

$process = array(
'timestart' => time(),
'start' => array(),
'tables' => array(),
'steps' => array(),
);

if(submitcheck('submit')) {
	$prgs = getgpc('prgs');
	if(is_array($prgs)) {
		foreach ($prgs as $prg) {
			if(substr($prg, 0, 6) == 's_prg_') {
				$prg = substr($prg, 6);
				$process['steps'][$prg] = 0;
			}elseif(substr($prg, 0, 6) == 'c_prg_') {
				$prg = substr($prg, 6);
				$process['start'][$prg] = 0;
			} else {
				$process['tables'][$prg] = 0;
			}
		}
		save_process('main', $process);
		showmessage("您選擇了 （".count($prgs)."） 個轉換程序，下面開始轉換", "index.php?a=convert&s=$source");
	}
}

showtips("<li>通常情況下，您需要執行下面所有數據表的轉換，除非您在執行過程中出現了意外的中斷或者有特殊需求，才進行必要的選擇</li>");
show_form_header();
show_table_header();
show_table_row(array('<span style="float: left">配置轉換過程</span><label style="float: right"><input type="checkbox" class="checkbox" onclick="checkAll(\'prefix\', this.form, \'prgs\')" id="chkall" name="chkall" checked> 全選</label>'), 'title');

if($setting['start']) {
	show_table_row(array('轉換之前運行的程序'), 'bg1');
	echo "<tr class=bg2><td colspan=2>\n<ul id=\"ulist\">";
	foreach ($setting['start'] as $key => $value) {
		echo "<li><label><input type=\"checkbox\" class=\"checkbox\" name=\"prgs[]\" value=\"c_prg_$key.php\" checked>&nbsp;$key($value)</label></li>";
	}
	echo "</ul>\n</td></tr>";
}


$tablelist = array();
$cdir = dir($prgdir);
while(($entry = $cdir->read()) !== false) {
	if(($entry != '.' && $entry != '..') && is_file($prgdir.$entry)) {
		if(preg_match("/uc_/", $entry)) {
			$table_source = str_replace('.php', '', $db_source->tablepre.$entry);
			$source_exist = $db_source->result_first("SHOW TABLES LIKE '".substr($table_source, strpos($table_source, '.') + 1)."'");
			if(empty($source_exist)) {
				continue;
			}
		}
		$tablelist[] = $entry;
	}
}
$cdir->close();

if($tablelist) {
	sort($tablelist);
	show_table_row(array('<span style="float: left">數據表轉換程序</span>'), 'bg1');
	echo "<tr class=bg2><td colspan=2>\n<ul id=\"ulist\">";
	foreach ($tablelist as $entry) {
		echo "<li><label><input type=\"checkbox\" class=\"checkbox\" name=\"prgs[]\" value=\"$entry\" checked>&nbsp;".basename($entry, '.php')."</label></li>";
	}
	echo "</ul>\n</td></tr>";
}

if($setting['steps']) {
	show_table_row(array('其他輔助轉換程序'), 'bg1');
	echo "<tr class=bg2><td colspan=2>\n<ul id=\"ulist\">";
	foreach ($setting['steps'] as $key => $value) {
		echo "<li><label><input type=\"checkbox\" class=\"checkbox\" name=\"prgs[]\" value=\"s_prg_$key.php\" checked>&nbsp;$key($value)</label></li>";
	}
	echo "</ul>\n</td></tr>";
}
show_table_footer();
show_form_footer('submit', '開始轉換');
showfooter();

exit();