<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_smilies_js.php 21292 2011-03-22 08:19:48Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_smilies_js() {
	global $_G;

	$query = DB::query("SELECT typeid, name, directory FROM ".DB::table('forum_imagetype')." WHERE type='smiley' AND available='1' ORDER BY displayorder");
	$fastsmiley = (array)unserialize(DB::result_first("SELECT svalue FROM ".DB::table('common_setting')." WHERE skey='fastsmiley'"));
	$return_type = 'var smilies_type = new Array();';
	$return_array = 'var smilies_array = new Array();var smilies_fast = new Array();';
	$spp = $_G['setting']['smcols'] * $_G['setting']['smrows'];
	$fpre = '';
	while($type = DB::fetch($query)) {
		$return_data = array();
		$return_datakey = '';
		$squery = DB::query("SELECT id, code, url FROM ".DB::table('common_smiley')." WHERE type='smiley' AND code<>'' AND typeid='$type[typeid]' ORDER BY displayorder");
		if(DB::num_rows($squery)) {
			$i = 0;$j = 1;$pre = '';
			$return_type .= 'smilies_type[\'_'.$type['typeid'].'\'] = [\''.str_replace('\'', '\\\'', $type['name']).'\', \''.str_replace('\'', '\\\'', $type['directory']).'\'];';
			$return_datakey .= 'smilies_array['.$type['typeid'].'] = new Array();';
			while($smiley = DB::fetch($squery)) {
				if($i >= $spp) {
					$return_data[$j] = 'smilies_array['.$type['typeid'].']['.$j.'] = ['.$return_data[$j].'];';
					$j++;$i = 0;$pre = '';
				}
				if($size = @getimagesize(DISCUZ_ROOT.'./static/image/smiley/'.$type['directory'].'/'.$smiley['url'])) {
					$smiley['code'] = str_replace('\'', '\\\'', $smiley['code']);
					$smileyid = $smiley['id'];
					$s = smthumb($size, $_G['setting']['smthumb']);
					$smiley['w'] = $s['w'];
					$smiley['h'] = $s['h'];
					$l = smthumb($size);
					$smiley['lw'] = $l['w'];
					unset($smiley['id'], $smiley['directory']);
					$return_data[$j] .= $pre.'[\''.$smileyid.'\', \''.$smiley['code'].'\',\''.str_replace('\'', '\\\'', $smiley['url']).'\',\''.$smiley['w'].'\',\''.$smiley['h'].'\',\''.$smiley['lw'].'\']';
					if(is_array($fastsmiley[$type['typeid']]) && in_array($smileyid, $fastsmiley[$type['typeid']])) {
						$return_fast .= $fpre.'[\''.$type['typeid'].'\',\''.$j.'\',\''.$i.'\']';
						$fpre = ',';
					}
					$pre = ',';
				}
				$i++;
			}
			$return_data[$j] = 'smilies_array['.$type['typeid'].']['.$j.'] = ['.$return_data[$j].'];';
		}
		$return_array .= $return_datakey.implode('', $return_data);
	}
	$cachedir = DISCUZ_ROOT.'./data/cache/';
	if(@$fp = fopen($cachedir.'common_smilies_var.js', 'w')) {
		fwrite($fp, 'var smthumb = \''.$_G['setting']['smthumb'].'\';'.$return_type.$return_array.'var smilies_fast=['.$return_fast.'];');
		fclose($fp);
	} else {
		exit('Can not write to cache files, please check directory ./data/ and ./data/cache/ .');
	}

}

?>