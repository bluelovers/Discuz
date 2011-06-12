<?php


//TIMESTAMP
if (version_compare(phpversion(), "5.2", "<=")) {
	exit('需要PHP5.2.X或以後版本');
}

if (get_cfg_var("zend_extension")||get_cfg_var("zend_optimizer.optimization_level")||get_cfg_var("zend_extension_manager.optimizer_ts")||get_cfg_var("zend_extension_ts")){
}else{
	if (version_compare(phpversion(), "5.3", "<=")) {
	exit('請安裝 Zend Optimizer v3.3.0或更高版本');
	} else {
		exit('請安裝Zend Guard Loader v3.3.0或更高版本');
	}	
}

$addon = DB::fetch_first("SELECT * FROM ".DB::table('common_addon')." WHERE `key`='S110420S3suD'");
if(!$addon)DB::insert('common_addon', array('key' => 'S110420S3suD','title' => '【CX】創新應用中心', 'sitename' => '【CX】創新應用中心', 'siteurl' => 'http://www.cxapp.com', 'description' => '【CX】創新應用以穩定，實用，負責為原則，為大家提供優質的插件和服務。', 'contact' => 'admin@cxapp.net', 'logo' => 'http://www.cxapp.com/images/logo.png', 'system' => 0,));


$cxdir = DISCUZ_ROOT.'./data/cache/robots/';
if(!is_dir($cxdir)) {
	@mkdir($cxdir, 0777);
}
if($fp = @fopen($cxdir.'index.htm', 'wb')) {
	if (fwrite($fp, 'testwrite') === FALSE) {
     exit('不能寫入緩存文件,為使插件正常使用,請設置目錄 (相對論壇根目錄)./data/cache/robots/ 為777可讀寫修改刪除屬性. ');
  }
	fclose($fp);		
} else {
//		echo "<script>alert('不能寫緩存文件,為使插件正常使用,請設置目錄 (相對論壇根目錄)./data/cache/robots/ 為777可讀寫屬性. ');</script>"; 
		exit('不能創建緩存文件,為使插件正常使用,請設置目錄 (相對論壇根目錄)./data/cache/robots/ 為777可讀寫修改刪除屬性. ');
}

if($fp = @fopen($cxdir.'index.htm', 'wb')) {
	if (fwrite($fp, 'testchange') === FALSE) {
     exit('不能更新緩存文件,為使插件正常使用,請設置目錄 (相對論壇根目錄)./data/cache/robots/ 為777可讀寫修改刪除屬性. ');
    }
		fclose($fp);		
}

if(!@unlink($cxdir.'index.htm')){
			exit('不能刪除緩存文件,為使插件正常使用,請設置目錄 (相對論壇根目錄)./data/cache/robots/ 為777可讀寫修改刪除屬性. ');
}

if(!is_dir($cxdir)) {
	@mkdir($cxdir, 0777);
}else{
	if(!$dh = @opendir($cxdir)) {
    exit('Can not open directory ./data/cache/robots/ . Please try again. ');
   }
	while (false !== ($obj = readdir($dh))) {
       if($obj == '.' || $obj == '..') {
        continue;
       }
       @unlink($cxdir . '/' . $obj);
   }
   closedir($dh);	
}

?>