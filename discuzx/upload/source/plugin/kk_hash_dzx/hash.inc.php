<?php

if(!defined('IN_ADMINCP')) exit('Only For Discuz! Admin Control Panel');
if(!$_G['config']['plugindeveloper']) cpmsg(lang('plugin/kk_hash_dzx', 'you_are_not_developer'), '', 'error');
if($_G['gp_pluginid']){
	$pluginid = DB::result_first('SELECT identifier FROM '.DB::table('common_plugin')." WHERE identifier='{$_G[gp_pluginid]}'");
	if(!$pluginid) cpmsg('plugin_not_found', '', 'error');
	$xml_path = DISCUZ_ROOT."./source/plugin/{$pluginid}/validator.xml";
	@touch($xml_path);
	if(!is_writeable($xml_path)) cpmsg(lang('plugin/kk_hash_dzx', 'could_not_write_validator_xml'), '', 'error');
	if(submitcheck('submit', 1)){
		$file_array = $extra_file = $extra_arr = array();
		$md5 = '';
		require_once 'source/discuz_version.php';
		require_once libfile('class/xml');
		dsetcookie("hash_{$pluginid}", $_G['gp_extra_file'], 2592000);
		$extra = str_replace(array("\r\n", "\n", "\r"),'|' ,$_G['gp_extra_file']);
		$extra_arr = explode('|', $extra);
		foreach($extra_arr as $file){
			$path = DISCUZ_ROOT."./$file";
			if($file && file_exists($path)) $extra_file[] = $file;
		}
		$file_array = list_dir("source/plugin/{$pluginid}");
		$files = (array)array_merge($file_array, $extra_file);
		sort($files);
		foreach($files as $file) {
			$md5 .= md5_file($file);
		}
		$xml = array(
			'Title' => 'Discuz! Plugin Validator',
			'Version' => DISCUZ_VERSION,
			'Data' => $files,
		);
		$validator_xml = array2xml($xml);
		if(file_get_contents($xml_path) != $validator_xml){
			file_put_contents($xml_path, $validator_xml);
			cpmsg(lang('plugin/kk_hash_dzx', 'validator_xml_updated'), dreferer(), 'succeed');
		}
		cpmsg(lang('plugin/kk_hash_dzx', 'create_validator_succeed', array('hash' => md5(md5($md5).$pluginid))), '', 'succeed');
	}else{
		showtableheader(lang('plugin/kk_hash_dzx', 'generate_hash'));
		showformheader("plugins&operation=config&identifier=kk_hash_dzx&pmod=hash&pluginid={$_G[gp_pluginid]}");
		showsetting(lang('plugin/kk_hash_dzx', 'extra_file'), 'extra_file', $_G['cookie']["hash_{$pluginid}"], 'textarea', '', '', lang('plugin/kk_hash_dzx', 'extra_file_tips'));
		showsubmit('submit');
		showformfooter();
		showtablefooter();
	}
}else{
	showtableheader('plugins_list');
	$query = DB::query('SELECT * FROM '.DB::table('common_plugin')." WHERE available='1'");
	while($plugin = DB::fetch($query)){
		$btn_name = lang('plugin/kk_hash_dzx', 'generate_hash');
		showtablerow('', array('width="40px"', '', 'width="100px"'), array(
			"<img src=\"http://addons.discuz.com/logo/{$plugin[identifier]}.png\" onerror=\"this.src='http://addons.discuz.com/images/logo.png';this.onerror=null\" width=\"40\" height=\"40\" align=\"left\" style=\"margin-right:5px\">",
			"{$plugin[name]}<br>{$plugin[identifier]}",
			"<a href=\"?action=plugins&operation=config&identifier=kk_hash_dzx&pmod=hash&pluginid={$plugin[identifier]}\">{$btn_name}</a>"
		));
	}
	showtablefooter();
}


function list_dir($dir){
	$list_dir = @dir($dir);
	while($file = $list_dir->read()){
		if($file == '.' || $file == '..') continue;
		if(is_dir("{$dir}/{$file}")){
			$dir_array = list_dir("{$dir}/{$file}");
			$file_array=array_merge($file_array,$dir_array);
		}else{
			$file_array[]="{$dir}/{$file}";
		}
	}
	return (array)$file_array;
}
?>