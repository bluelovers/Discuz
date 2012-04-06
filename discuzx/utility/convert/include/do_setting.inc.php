<?php

/**
 * DiscuzX Convert
 *
 * $Id: do_setting.inc.php 10469 2010-05-11 09:12:14Z monkey $
 */

showtips('setting_tips');
$sfile = DISCUZ_ROOT.'./source/'.$source.'/setting.ini';

if(!submitcheck('submit')) {
	show_form_header();
	show_table_header();
	show_table_row(array(array('colspan="3"', '編輯配置文件 source/'.$source.'/setting.ini')), 'title');

	foreach ($setting as $key => $value) {
		if(is_array($value) && !empty($value)) {
			show_table_row(array(array('colspan="3" align="left"', '<strong>['.$key.']<strong>')), 'bg1');
			$disabled = in_array($key, array('program', 'config', 'steps', 'start', 'tablecheck')) ? ' disabled readonly' : '';
			foreach ($value as $k => $v) {
				show_table_row(	array(
				lang($k),
				array('class="bg2" width="70%"', '<input type="text" size="40" name="newsetting['.$key.']['.$k.']" value="'.htmlspecialchars($v).'"'.$disabled.'>'.($disabled ? ' 只讀' : ''))
				), 'bg2'
				);
			}
		}
	}
	show_table_footer();
	show_form_footer('submit', lang('submit'));

} else {
	$newsetting = getgpc('newsetting');
	if(is_array($newsetting)) {
		$lines = file($sfile);
		foreach ($lines as $num => $line) {
			$lines[$num] = $line = trim($line);
			$check = substr($line, 0, 1);
			if($check !== '' && $check != ';') {
				if($check == '[') {
					$group = trim(str_replace(array('[', ']'), '', $line));
				} elseif ($group != '') {
					list($key, $val) = explode('=', $line);
					$key = trim($key);
					if(isset($newsetting[$group][$key])) {
						if(is_numeric($newsetting[$group][$key])) {
							$lines[$num] = "$key = {$newsetting[$group][$key]}";
						} else {
							$lines[$num] = "$key = \"{$newsetting[$group][$key]}\"";
						}
					}
				}
			}
		}
		if($fp = @fopen($sfile, 'w')) {
			fwrite($fp, implode("\n", $lines));
			fclose($fp);
			showmessage('設置已經更新完畢並成功保存', 'index.php', '', 1000);
		} else {
			showmessage('該設置文件為只讀文件，無法保存，請返回');
		}
	}
	showmessage('您沒有改變任何設置', 'index.php', '', 1000);
}