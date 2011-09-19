<?php
// English by Valery Votintsev at sources.ru
$datadir = DISCUZ_ROOT.'./source/';

showtips('<li><strong>'.lang('update_permissions').'</strong></li><li><strong>'.lang('update_forum_too').'</strong></li><li>'.lang('update_choose_process').'</li><li>'.lang('update_more_space').'</li>');//vot

if(is_dir($datadir)) {

	$cdir = dir($datadir);
	show_table_header();
	show_table_row(array(
			lang('source_version'),//vot
			lang('target_version'),//vot
			array('width="50%"', lang('introduction')),//vot
			array('width="5%"', lang('description')),//vot
			array('width="5%"', lang('settings')),//vot
			array('width="5%"', ''),
		), 'header title');
	while(($entry = $cdir->read()) !== false) {
		if(($entry != '.' && $entry != '..') && is_dir($datadir.$entry)) {
			$settingfile = $datadir.$entry.'/setting.ini';
			$readmefile = $datadir.$entry.'/readme.txt';

			$readme = file_exists($readmefile) ? '<a target="_blank" href="source/'.$entry.'/readme.txt">'.lang('view_readme').'</a>' : '';//vot

			if(file_exists($settingfile) && $setting = loadsetting($entry)) {
				$trclass = $trclass == 'bg1' ? 'bg2' : 'bg1';
				show_table_row(
					array(
						$setting['program']['source'],
						$setting['program']['target'],
						$setting['program']['introduction'],
						array('align="center"', $readme),
						array('align="center"', '<a href="index.php?a=setting&source='.rawurlencode($entry).'">'.lang('change').'</a>'),//vot
						array('align="center"', '<a href="index.php?a=config&source='.rawurlencode($entry).'">'.lang('start').'</a>'),//vot
					), $trclass
				);
			}
		}
	}
	$cdir->close();
	show_table_footer();
} else {
	showmessage('config_child_error');
}