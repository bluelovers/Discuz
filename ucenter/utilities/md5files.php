<?php
/*
發佈前運行此程序生成 control/admin/ucfiles.md5 文件
*/
$md5data = array();
chdir($argv[1]);
checkfiles('./', '\.php', 0, '\.php|\.xml');
checkfiles('control/', '\.php');
checkfiles('model/', '\.php');
checkfiles('lib/', '\.php');
checkfiles('view/', '\.php|\.htm');
checkfiles('js/', '\.js');

$savedatanew = '';
foreach($md5data as $file => $md5) {
	$savedatanew .= $md5.' *'.$file."\r\n";
}

$fp = fopen('control/admin/ucfiles.md5', 'w');fwrite($fp, $savedatanew);fclose($fp);

function checkfiles($currentdir, $ext = '', $sub = 1, $skip = '') {
	global $md5data, $savedata;
	$currentdir = $currentdir;
	$dir = opendir($currentdir);
	$exts = '/('.$ext.')$/i';
	$skips = explode(',', $skip);

	while($entry = readdir($dir)) {
		$file = $currentdir.$entry;
		if($entry != '.' && $entry != '..' && (preg_match($exts, $entry) || $sub && is_dir($file)) && !in_array($entry, $skips)) {
			if($sub && is_dir($file)) {
				checkfiles($file.'/', $ext, $sub, $skip);
			} else {
				$md5data[$file] = md5_file($file);
			}
		}
	}
}

echo "control/admin/ucfiles.md5 生成完畢！";

?>