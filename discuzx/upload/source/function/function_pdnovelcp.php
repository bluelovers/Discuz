<?php

function pdnovelcache($cachename, $identifier = '') {

	global $_G;
	$cachearray = array('pdnovelcategory', 'pdnovelcreditrule');
	$cachename = in_array($cachename, $cachearray) ? $cachename : '';	
	if($cachename == 'pdnovelcategory') {	
		$data = array();
		$query = DB::query("SELECT * FROM ".DB::table('pdnovel_category')." ORDER BY displayorder,catid");	
		while($value = DB::fetch($query)) {
			$value['catname'] = dhtmlspecialchars($value['catname']);
			$data[$value['catid']] = $value;
		}
		foreach($data as $key => $value) {
			$upid = $value['upid'];
			$data[$key]['level'] = 0;
			if($upid && isset($data[$upid])) {
				$data[$upid]['children'][] = $key;
				while($upid && isset($data[$upid])) {
					$data[$key]['level'] += 1;
					$upid = $data[$upid]['upid'];
				}
			}
		}
		save_syscache('pdnovelcategory', $data);	
	}
	
}

function pdnovelcategoryrow($key, $level = 0, $last = '') {
	global $_G;

	loadcache('pdnovelcategory');
	$value = $_G['cache']['pdnovelcategory'][$key];
	$return = '';

	if($level == 1) {
		$return = '<tr class="hover" id="cat'.$value['catid'].'">'.
		'<td>&nbsp;</td><td class="td25"><input type="text" class="txt" name="order['.$value['catid'].']" value="'.$value['displayorder'].'" /></td>'.
		'<td><div class="board"><input type="text" class="txt" name="name['.$value['catid'].']" value="'.$value['catname'].'" /></div></td>'.
		'<td class="txt170"><input type="text" class="txt" name="caption['.$value['catid'].']" value="'.$value['caption'].'" /></td>'.
		'<td class="txt170"><input type="text" class="txt" name="keyword['.$value['catid'].']" value="'.$value['keyword'].'" /></td>'.
		'<td class="txt170"><input type="text" class="txt" name="summary['.$value['catid'].']" value="'.$value['description'].'" /></td>'.
		'<td><a href="'.ADMINSCRIPT.'?action=pdnovel&operation=category&do=delete&catid='.$value['catid'].'">'.cplang('delete').'</a></td>'.
		'</tr>';
	} else {
		$childrennum = count($_G['cache']['pdnovelcategory'][$key]['children']);
		$toggle = $childrennum > 25 ? ' style="display:none"' : '';
		$return = '<tbody><tr class="hover" id="cat'.$value['catid'].'">'.
		'<td onclick="toggle_group(\'group_'.$value['catid'].'\')"><a id="a_group_'.$value['catid'].'" href="javascript:;">'.($toggle ? '[+]' : '[-]').'</a></td>'.
		'<td class="td25"><input type="text" class="txt" name="order['.$value['catid'].']" value="'.$value['displayorder'].'" /></td>'.
		'<td><div class="parentboard"><input type="text" class="txt" name="name['.$value['catid'].']" value="'.$value['catname'].'" /></div></td>'.
		'<td class="txt170"><input type="text" class="txt" name="caption['.$value['catid'].']" value="'.$value['caption'].'" /></td>'.
		'<td class="txt170"><input type="text" class="txt" name="keyword['.$value['catid'].']" value="'.$value['keyword'].'" /></td>'.
		'<td class="txt170"><input type="text" class="txt" name="summary['.$value['catid'].']" value="'.$value['description'].'" /></td>'.
		'<td><a href="'.ADMINSCRIPT.'?action=pdnovel&operation=category&do=delete&catid='.$value['catid'].'">'.cplang('delete').'</a></td>'.
		'</tr></tbody>'.
		'<tbody id="group_'.$value['catid'].'"'.$toggle.'>';
		for($i=0,$L=count($value['children']); $i<$L; $i++) {
			$return .= pdnovelcategoryrow($value['children'][$i], 1, '');
		}
		$return .= '</tdoby><tr><td>&nbsp;</td><td colspan="6"><div class="lastboard"><a href="###" onclick="addrow(this, 1, '.$value['catid'].')" class="addtr">'.cplang('category_addsubcategory').'</a></div></td></tr>';
	}
	return $return;
}

function category_showselect($name='catid', $current='') {
	global $_G;
	loadcache('pdnovelcategory');
	$category = $_G['cache']['pdnovelcategory'];

	$select = "<select id=\"$name\" name=\"$name\" class=\"ps vm\">";
	$select .= '<option value="">'.cplang('pdnovel_category_select').'</option>';
	foreach ($category as $value) {
		if($value['level'] == 0) {
			$select .= "<option value=\"$value[catid]\" disabled=\"disabled\">$value[catname]</option>";
			if(!$value['children']) {
				continue;
			}
			foreach ($value['children'] as $catid) {
				$disabled = ($current && $current==$catid) ? 'disabled="disabled"' : '';
				$select .= "<option value=\"{$category[$catid][catid]}\" $disabled>-- {$category[$catid][catname]}</option>";
			}
		}
	}
	$select .= "</select>";
	return $select;
}

function removeDir($dirName){
	if(!is_dir($dirName)){
		@unlink($dirName);
		return false;
	}
	$handle = @opendir($dirName);
	while(($file = @readdir($handle)) !== false){
		if($file != '.' && $file != '..'){
			$dir = $dirName . '/' . $file;
			is_dir($dir) ? removeDir($dir) : @unlink($dir);
		}
	}
	closedir($handle);
	return rmdir($dirName) ;
}

function show_input ($value, $name) {
	$select = '<ul onmouseover="altStyle(this);"><li'.($value==0 ? ' class="checked"' : '').'><input class="radio" type="radio" name="'.$name.'" value="0"'.($value==0 ? ' checked' : '').'>&nbsp;'.cplang('pdnovel_collect_'.$name.'_no').'</li><li'.($value==1 ? ' class="checked"' : '').'><input class="radio" type="radio" name="'.$name.'" value="1"'.($value==1 ? ' checked' : '').'>&nbsp;'.cplang('pdnovel_collect_'.$name.'_yes').'</li>';
	return $select;
}

//采集所用到的函数
function get_contents($url, $gz=0) {
	if($gz){
		$gzcontent = gzfile($url);
		$contents = '';
		foreach ($gzcontent as $value) {
			$contents .= $value;
		}
		$contents = iconv("UTF-8", "GB2312//IGNORE", urldecode($contents));
	}else{
		$contents = @file_get_contents($url);
	}
	return $contents;
}

function get_matchone($pregstr, $source, $txt=1){
	preg_match($pregstr, $source, $match);
	if (!is_array($match)){
		return false;
	}
	if($txt){
		$match[1] = trim(strip_tags($match[1]));
	}
	return $match[1];
}

function get_matchall($pregstr, $source){
	preg_match_all($pregstr, $source, $match, PREG_OFFSET_CAPTURE + PREG_SET_ORDER);
	if (!is_array($match)){
		return false;
	}
	foreach ($match as $var){
		if (is_array($var)){
			$matchvar[] = $var[count($var)-1];
		}else{
			$matchvar[] = $var;
		}
	}
	return $matchvar;
}

function get_txtvar($content){
	$pregstr = array("/(\r|\n)\s+/", "/\r|\n/", "/\<br[^\>]*\>/i", "/\<[\s]*\/p[\s]*\>/i", "/\<[\s]*p[\s]*\>/i", "/\<script[^\>]*\>.*\<\/script\>/is", "/\<[\/\!]*[^\<\>]*\>/is", "/&(quot|#34);/i", "/&(amp|#38);/i", "/&(lt|#60);/i", "/&(gt|#62);/i", "/&(nbsp|#160);/i", "/&#(\d+);/", "/&([a-z]+);/i", "/(\r\n){2,}/i");
	$replacestr = array(" ", "", "\r\n", "", "\r\n", "", "", "\"", "&", "<", ">", " ", "", "", "\r\n");
	$content = preg_replace($pregstr, $replacestr, $content);
	$content = strip_tags($content);
	$content = str_replace('  ', '　', $content);
	$content = rtrim($content);
	return $content;
}

function get_jsvar($content){
	$content = preg_replace("/(\r\n|\n|\r)/i", "<br>", $content);
	return "document.write('".addslashes($content)."');";
}

function get_initial($str){
	$asc=ord(substr($str,0,1));
	if ($asc<160) {
		if ($asc>=48 && $asc<=57){
			return '1';
		}elseif ($asc>=65 && $asc<=90){
			return chr($asc);
		}elseif ($asc>=97 && $asc<=122){
			return chr($asc-32);
		}else{
			return '1';
		}
	}else {
		$asc=$asc*1000+ord(substr($str,1,1));
		if ($asc>=176161 && $asc<176197){
			return 'A';
		}elseif ($asc>=176197 && $asc<178193){
			return 'B';
		}elseif ($asc>=178193 && $asc<180238){
			return 'C';
		}elseif ($asc>=180238 && $asc<182234){
			return 'D';
		}elseif ($asc>=182234 && $asc<183162){
			return 'E';
		}elseif ($asc>=183162 && $asc<184193){
			return 'F';
		}elseif ($asc>=184193 && $asc<185254){
			return 'G';
		}elseif ($asc>=185254 && $asc<187247){
			return 'H';
		}elseif ($asc>=187247 && $asc<191166){
			return 'J';
		}elseif ($asc>=191166 && $asc<192172){
			return 'K';
		}elseif ($asc>=192172 && $asc<194232){
			return 'L';
		}elseif ($asc>=194232 && $asc<196195){
			return 'M'; 
		}elseif ($asc>=196195 && $asc<197182){
			return 'N';
		}elseif ($asc>=197182 && $asc<197190){
			return 'O';
		}elseif ($asc>=197190 && $asc<198218){
			return 'P';
		}elseif ($asc>=198218 && $asc<200187){
			return 'Q';
		}elseif ($asc>=200187 && $asc<200246){
			return 'R';
		}elseif ($asc>=200246 && $asc<203250){
			return 'S';
		}elseif ($asc>=203250 && $asc<205218){
			return 'T';
		}elseif ($asc>=205218 && $asc<206244){
			return 'W';
		}elseif ($asc>=206244 && $asc<209185){
			return 'X';
		}elseif ($asc>=209185 && $asc<212209){
			return 'Y';
		}elseif ($asc>=212209){
			return 'Z';
		}else{
			return '1';
		}
	}
}

function get_next($message, $url) {
	$message = cplang($message);
	$url = ADMINSCRIPT.'?'.$url;
	$message = '<p class="ptop ptext">'.$message.'</p>';
	$message .= '<p class="pbottom"><a href="'.$url.'" class="lightlink">'.cplang('message_redirect').'</a></p>';
	$message .= "<script type=\"text/JavaScript\">setTimeout(\"redirect('$url');\", 200);</script>";
	echo $message;
	exit();
}

function category_get_num($type, $catid) {
	global $_G;
	if(! in_array($type, array('pdnovel'))) {
		return array();
	}
	loadcache($type.'category');
	$category = $_G['cache'][$type.'category'];

	$numkey = $type == 'pdnovel' ? 'articles' : 'num';
	if(! isset($_G[$type.'category_nums'])) {
		$_G[$type.'category_nums'] = array();
		$tables = array('pdnovel'=>'pdnovel_category');
		$query = DB::query("SELECT catid, $numkey FROM ".DB::table($tables[$type]));
		while($value=DB::fetch($query)) {
			$_G[$type.'category_nums'][$value['catid']] = intval($value[$numkey]);
		}
	}

	$nums = $_G[$type.'category_nums'];
	$num = intval($nums[$catid]);
	if($category[$catid]['children']) {
		foreach($category[$catid]['children'] as $id) {
			$num += category_get_num($type, $id);
		}
	}
	return $num;
}
?>