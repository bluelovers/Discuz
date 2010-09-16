<?php

$config = loadconfig();
$db_target = new db_mysql($config['target']);
$db_target->connect();

$readme = DISCUZ_ROOT.'./source/'.$source.'/readme.txt';
if(file_exists($readme)) {
	$txt = file_get_contents($readme);
} else {
	$txt = lang('finish');
}

$txt = nl2br(htmlspecialchars($txt));
$txt = str_replace('  ', '&nbsp;&nbsp;', $txt);
$txt = str_replace("\t", '&nbsp;&nbsp;&nbsp;&nbsp;', $txt);

$process = load_process('main');
list($rday, $rhour, $rmin, $rsec) = remaintime(time() - $process['timestart']);
$stime = gmdate('Y-m-d H:i:s', $process['timestart'] + 3600* 8);
$etime = gmdate('Y-m-d H:i:s',time() + 3600* 8);
$timetodo = "您已經順利的完成了數據轉換!";
$timetodo .= "<br><br>本次升級開始時間: <strong>$stime</strong><br>本次升級結束時間: <strong>$etime</strong>";
$timetodo .= "<br>升級累計執行時間: <strong>$rday</strong>天 <strong>$rhour</strong>小時 <strong>$rmin</strong>分 <strong>$rsec</strong>秒";
$timetodo .= "<br><br>通常情況下，您可能還需要按照以下提示繼續進行升級，從而使您的新程序正常運行";

showtips($timetodo);

show_table_header();
show_table_row(array('最後的說明(readme)'), 'title');
show_table_row(array($txt));
show_table_footer();

?>