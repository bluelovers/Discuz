<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
	global $_G;
	global $file_dir;

	$file_dir = array();
	$filemd5 = array();
	$query = DB::query("SELECT * FROM ".DB::table('forum_imgpoll'));
	$opts = 0;
	while($options = DB::fetch($query)) {
		$file_dir[] = md5($options['tid']);
		$opts++;
	}
	$dirs=getAllDirs("./{$_G['setting']['attachurl']}forum/imgpoll/");
	foreach($dirs as $dir){
		if(!in_array($dir,$file_dir)){
			deleteDir("./{$_G['setting']['attachurl']}forum/imgpoll/".$dir."/");
		}
	}
	echo "清除冗余图片成功!";
/**
* 遍历函数 getAllDirs()
* @param   string  $filedir    要遍历的目录
* @return  array   遍历的结果
*/	
function getAllDirs($filedir) {
	$allDirs = array(); //文件名数组
	if (is_dir($filedir)) {//判断要遍历的是否是目录
		if ($dh = opendir($filedir)) {//打开目录并赋值一个目录句柄(directory handle)
			while (FALSE !== ($filestring = readdir($dh))) {//读取目录中的文件名
				if ($filestring != '.' && $filestring != '..') {//如果不是.和..(每个目录下都默认有.和..)
					if (is_dir($filedir . $filestring)) {//该文件名是一个目录时
						$allDirs[] = $filestring; 
					} 
				}
			}
		} else {//打开目录失败
			exit('Open the directory failed');
		}
		closedir($dh);//关闭目录句柄
		return $allDirs;//返回文件名数组
	} else {//目录不存在
		exit('The directory is not exist');
	}

}

function deleteDir($filedir) {
	if (is_dir($filedir)) {//判断要遍历的是否是目录
		if ($dh = opendir($filedir)) {//打开目录并赋值一个目录句柄(directory handle)
			while (FALSE !== ($filestring = readdir($dh))) {//读取目录中的文件名
				if ($filestring != '.' && $filestring != '..') {//如果不是.和..(每个目录下都默认有.和..)
					$file = $filedir . $filestring;
					if (is_dir($file)) {//该文件名是一个目录时
						deleteDir($file);
					} else {
						if(@unlink($file)) echo "删除文件".$file."成功<hr>";
					}
				}
			}
		} else {//打开目录失败
			exit('Open the directory failed');
		}
		closedir($dh);//关闭目录句柄
		if(@rmdir($filedir)) echo "清除目录".$filedir."成功<hr>";
	} else {//目录不存在
		exit('The directory is not exist');
	}

}

/**
* 遍历函数 getAllFiles()
* @param   string  $filedir    要遍历的目录
* @return  array   遍历的结果
*/
function getAllFiles($filedir) {
	$allfiles = array(); //文件名数组
	$tempArr = array(); //临时文件名数组
	if (is_dir($filedir)) {//判断要遍历的是否是目录
		if ($dh = opendir($filedir)) {//打开目录并赋值一个目录句柄(directory handle)
			while (FALSE !== ($filestring = readdir($dh))) {//读取目录中的文件名
				if ($filestring != '.' && $filestring != '..') {//如果不是.和..(每个目录下都默认有.和..)
					if (is_dir($filedir . $filestring)) {//该文件名是一个目录时
						$tempArr = getAllFiles($filedir . $filestring . '/');//继续遍历该子目录
						$allfiles = array_merge($allfiles, $tempArr); //把临时文件名和临时文件名组合
					} else if (is_file($filedir . $filestring)) {
						$allfiles[] = $filedir . $filestring;
					}
				}
			}
		} else {//打开目录失败
			exit('Open the directory failed');
		}
		closedir($dh);//关闭目录句柄
		return $allfiles;//返回文件名数组
	} else {//目录不存在
		exit('The directory is not exist');
	}

}

?>
