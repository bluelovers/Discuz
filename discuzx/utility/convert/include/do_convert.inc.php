<?php

$config = loadconfig();
$db_source = new db_mysql($config['source']);
$db_source->connect();

$db_target = new db_mysql($config['target']);
$db_target->connect();

$db_uc = new db_mysql($config['ucenter']);
if($setting['config']['ucenter']) {
	$db_uc->connect();
}

$process = load_process('main');
if(empty($process)) {
	showmessage("請首先選擇轉換程序", "index.php?action=select&source=$source");
}

$prg = getgpc('prg');

$prg_dir['tables'] = DISCUZ_ROOT.'./source/'.$source.'/table/';
$prg_dir['start'] = DISCUZ_ROOT.'./source/'.$source.'/';
$prg_dir['steps'] = DISCUZ_ROOT.'./source/'.$source.'/';

$prg_done = 0;
$prg_next = '';
$prg_total = $prg_total = count($process['tables']) + count($process['start']) + count($process['steps']);

foreach (array('start', 'tables', 'steps') as $program) {
	if(!empty($process[$program]) && !$process[$program.'_is_end']) {
		foreach ($process[$program] as $k => $v) {
			if($v) {
				$prg_done ++;
			} elseif ($prg_next == '') {
				$prg_next = $k;
			}
		}
		if($prg_next) {
			if(empty($prg) || !file_exists($prg_dir[$program].$prg)) {
				$prg = $prg_next;
			}
			$prg_done ++;

			list($rday, $rhour, $rmin, $rsec) = remaintime(time() - $process['timestart']);
			$stime = gmdate('Y-m-d H:i:s', $process['timestart'] + 3600* 8);
			$timetodo = "升級開始時間：<strong>$stime</strong>, 升級程序已經執行了 <strong>$rday</strong>天 <strong>$rhour</strong>小時 <strong>$rmin</strong>分 <strong>$rsec</strong>秒";
			$timetodo .= "<br><br>目前正在執行轉換程序( $prg_done / $prg_total ) <strong>$prg</strong>，轉換過程中需要多次跳轉，請勿關閉瀏覽器。";
			$timetodo .= "<br><br>如果程序中斷或者需要重新開始當前程序，請點擊 (<a href=\"index.php?a=convert&source=$source&prg=$prg\">重新開始</a>)";

			showtips($timetodo);
			if(file_exists($prg_dir[$program].$prg)) {
				define('PROGRAM_TYPE', $program);
				require $prg_dir[$program].$prg;
				save_process_main($prg);
				showmessage("轉換程序 $prg 執行完畢， 現在跳轉到下一個程序", "index.php?a=convert&source=$source", null, 500);
			} else {
				showmessage('數據轉換中斷! 無法找到轉換程序 '.$prg);
			}
		} else {
			$process[$program.'_is_end'] = 1;
			save_process('main', $process);
		}
	} else {
		$prg_done = $prg_done + count($process[$program]);
	}
}

showmessage('轉換程序全部運行完畢', "index.php?action=finish&source=$source");

function save_process_main($prg = '') {
	global $process;
	if(defined('PROGRAM_TYPE')) {
		$prg = empty($prg) ? $GLOBALS['prg'] : $prg;
		$process[PROGRAM_TYPE][$prg] = 1;
	}
	save_process('main', $process);
}
?>