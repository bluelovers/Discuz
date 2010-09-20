<?php

/**
 *      [品牌空間] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: tpl.func.php 4473 2010-09-15 04:04:13Z fanshengshuai $
 */

if(!defined('IN_BRAND')) {
	exit('Acess Denied');
}

@set_time_limit(0);

function cplang($name, $replace = array(), $output = false) {
	global $_G, $lang;
	$ret = '';
	
	$_G['lang'] = $lang;

	$ret = $_G['lang'][$name];

	$ret = $ret ? $ret : ($replace === false ? '' : $name);
	if($replace && is_array($replace)) {
		$s = $r = array();
		foreach($replace as $k => $v) {
			$s[] = '{'.$k.'}';
			$r[] = $v;
		}
		$ret = str_replace($s, $r, $ret);
	}
	$output && print($ret);
	return $ret;
}

function cpurl($type = 'parameter', $filters = array('sid', 'frames')) {
	parse_str($_SERVER['QUERY_STRING'], $getarray);
	$extra = $and = '';
	foreach($getarray as $key => $value) {
		if(!in_array($key, $filters)) {
			@$extra .= $and.$key.($type == 'parameter' ? '%3D' : '=').rawurlencode($value);
			$and = $type == 'parameter' ? '%26' : '&';
		}
	}
	return $extra;
}


function showheader($key, $url) {
	echo '<li><em><a href="javascript:;" id="header_'.$key.'" hidefocus="true" onclick="toggleMenu(\''.$key.'\', \''.$url.'\');">'.lang('header_'.$key).'</a></em></li>';
}

function shownav($header = '', $menu = '', $nav = '') {
	global $_G, $action, $operation, $BASESCRIPT, $_SC;

	$title = 'cplog_'.$action.($operation ? '_'.$operation : '');
	if(in_array($action, array('home', 'custommenu'))) {
		$customtitle = '';
	} elseif(lang($title, false)) {
		$customtitle = $title;
	} elseif(lang('nav_'.($header ? $header : 'index'), false)) {
		$customtitle = 'nav_'.$header;
	} else {
		$customtitle = rawurlencode($nav ? $nav : ($menu ? $menu : ''));
	}

	echo '<script type="text/JavaScript" charset="'.$_G['charset'].'">if (parent.$(\'#admincpnav\')[0]) parent.$(\'#admincpnav\')[0].innerHTML=\''.lang('nav_'.($header ? $header : 'index')).
		($menu ? '&nbsp;&raquo;&nbsp;'.lang($menu) : '').
		($nav ? '&nbsp;&raquo;&nbsp;'.lang($nav) : '').'\';'.
	'</script>';
}

function showmenu($key, $menus) {
	global $_G, $BASESCRIPT;
	echo '<ul id="menu_'.$key.'" style="display: none">';
	if(is_array($menus)) {
		foreach($menus as $menu) {
			if($menu[0] && $menu[1]) {
				echo '<li><a href="'.(substr($menu[1], 0, 4) == 'http' ? $menu[1] : $BASESCRIPT.'?action='.$menu[1]).'" hidefocus="true" target="'.($menu[2] ? $menu[2] : 'main').'"'.($menu[3] ? $menu[3] : '').'>'.lang($menu[0]).'</a></li>';
			}
		}
	}
	echo '</ul>';
}

function cpmsg($message, $url = '', $type = '', $extra = '', $halt = true, $goback = false, $checkresults = array(), $redirecttime = 2000) {
	global $_G;
	extract($GLOBALS, EXTR_SKIP);
	include_once (B_ROOT.'./language/admin.lang.php');
	$vars = explode(':', $message);
	if(count($vars) == 2 && isset($scriptlang[$vars[0]][$vars[1]])) {
		@eval("\$message = \"".str_replace('"', '\"', $scriptlang[$vars[0]][$vars[1]])."\";");
	} elseif(!empty($messsage)) {
		@eval("\$message = \"".(isset($lang[$message]) ? $lang[$message] : $message)."\";");
	}
	if($_G['inajax'] == 1) {
		showxmlheader($_G['charset']);
		echo '<root>';
		if(!empty($checkresults)) {
			echo '<status>FAILED</status>';
			foreach($checkresults as $error) {
				echo showarraytoxml($error, $_G['charset'], 1);
			}
		} else {
			echo '<status>OK</status>';
			echo '<url><![CDATA['.$url.']]></url>';
		}
		echo '<message><![CDATA['.lang($message).']]></message>';
		echo '</root>';
		exit;
	} else {
		switch($type) {
			case 'succeed': $classname = 'infotitle2';break;
			case 'error': $classname = 'infotitle3';break;
			case 'loading': $classname = 'infotitle1';break;
			default: $classname = 'marginbot normal';break;

		}
		$message = "<h4 class=\"$classname\">".cplang($message)."</h4>";
		$url .= !empty($scrolltop) ? '&scrolltop='.intval($scrolltop) : '';

		if($type == 'form') {
			$message = "<form method=\"post\" action=\"$url\"><input type=\"hidden\" name=\"formhash\" value=\"".FORMHASH."\">".
				"<br />$message$extra<br />".
				"<p class=\"margintop\"><input type=\"submit\" class=\"btn\" name=\"confirmed\" value=\"$lang[ok]\"> &nbsp; \n".
				"<input type=\"button\" class=\"btn\" value=\"$lang[cancel]\" onClick=\"history.go(-1);\"></p></form><br />";
		} elseif($type == 'loadingform') {
			$message = "<form method=\"post\" action=\"$url\" id=\"loadingform\"><input type=\"hidden\" name=\"formhash\" value=\"".FORMHASH."\"><br />$message$extra<img src=\"static/image/admin/ajax_loader.gif\" class=\"marginbot\" /><br />".
				'<p class="marginbot"><a href="'.$url.'" onclick="$(\'loadingform\').submit();return false;" class="lightlink">'.lang('message_redirect').'</a></p></form><br /><script type="text/JavaScript">setTimeout("$(\'loadingform\').submit();", 2000);</script>';
		} else {
			$message .= $extra.($type == 'loading' ? '<img src="static/image/admin/ajax_loader.gif" class="marginbot" />' : '');
			if($url) {
				if($type == 'button') {
					$message = "<br />$message<br /><p class=\"margintop\"><input type=\"submit\" class=\"btn\" name=\"submit\" value=\"$lang[start]\" onclick=\"location.href='$url'\" />";
				} else {
					$message .= '<p class="marginbot"><a href="'.$url.'" class="lightlink">'.lang('message_redirect').'</a></p>';
				}
				$message .= "<script type=\"text/JavaScript\">setTimeout(\"redirect('$url');\", $redirecttime);</script>";
			} elseif(strpos($message, $lang['return'])) {
				$message .= '<p class="marginbot"><a href="javascript:history.go(-1);" class="lightlink">'.lang('message_return').'</a></p>';
			}
			$message .= $goback ? ("<script>setTimeout(\"history.go(-1);\", $redirecttime);</script>") : '';
		}

		if($halt) {
			echo '<h3>'.lang('pk_message').'</h3><div class="infobox">'.$message.'</div>';
			cpfooter();
			exit();
		} else {
			echo '<div class="infobox">'.$message.'</div>';
		}
	}
}

function cpheader() {
	global $_G, $_SC, $_SSCONFIG, $_SGLOBAL, $BASESCRIPT;

	if(!defined('BRAND_CP_HEADER_OUTPUT')) {
		define('BRAND_CP_HEADER_OUTPUT', true);
	} else {
		return true;
	}

	$frame = (isset($frame) && $frame == 'no') ? 0 : 1;
	echo <<< EOT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="static/image/admin/admincp.css" rel="stylesheet" type="text/css" />
<link href="static/image/admin/calendar.css" rel="stylesheet" type="text/css" />
</head>
<body>
<script type="text/JavaScript">
var admincpfilename = '$BASESCRIPT', IN_ADMIN = true, ISFRAME = $frame;
</script>
<script charset="utf-8" src="static/js/common.js" type="text/javascript"></script>
<script charset="utf-8" src="static/js/jquery.js" type="text/javascript"></script>
<script charset="utf-8" src="static/image/admin/admincp.js" type="text/javascript"></script>
<script charset="utf-8" src="static/image/admin/calendar.js" type="text/javascript"></script>
<script type="text/javascript">
if(ISFRAME && !parent.document.getElementById('leftmenu')) {
	redirect(admincpfilename + '?frames=yes&' + document.URL.substr(document.URL.indexOf(admincpfilename) + 10));
}
</script>
<div id="append_parent"></div>
<div class="container" id="cpcontainer">
EOT;

}

function showsubmenu($title, $menus = array(), $right = '') {
	global $_G, $BASESCRIPT;
	if(empty($menus)) {
		$s = '<div class="itemtitle" id="tabbar-div">'.$right.'<h3>'.lang($title).'</h3></div>';
	} elseif(is_array($menus)) {
		$s = '<div class="itemtitle" id="tabbar-div">'.$right.'<h3>'.lang($title).'</h3>';
		if(is_array($menus)) {
			$s .= '<ul class="tab1">';
			foreach($menus as $k => $menu) {
				if(is_array($menu[0])) {
					$s .= '<li id="addjs'.$k.'" class="'.($menu[2] ? ' current' : 'hasdropmenu').'" onmouseover="dropmenu(this);"><a href="#"><span>'.lang($menu[0]['menu']).'<em>&nbsp;&nbsp;</em></span></a><div id="addjs'.$k.'child" class="dropmenu" style="display:none;">';
					if(is_array($menu[0]['submenu'])) {
						foreach($menu[0]['submenu'] as $submenu) {
							if ($submenu[1]) {
								$url = $BASESCRIPT.'?action='.$submenu[1];
							} else {
								$url = "";
							}
							$s .= '<a href="$url">'.lang($submenu[0]).'</a>';
						}
					}
					$s .= '</div></li>';
				} else {
					if($menu[4]) {
						$s .= '<li'.($menu[2] ? ' class="current"' : '').'><a id="'.$menu[4].'"onclick="selecttab(this.id)"><span>'.lang($menu[0]).'</span></a></li>';
					} else {
						if ($menu[1]) {
							$url = $BASESCRIPT . '?action='. $menu[1];
						} else {
							$url ="";
						}
						$s .= '<li'.($menu[2] ? ' class="current"' : '').'><a href="' . $url . '"'.($menu[3] ? ' target="_blank"' : '').'><span>'.lang($menu[0]).'</span></a></li>';
					}
				}
			}
			$s .= '</ul>';
		}
		$s .= '</div>';
	}
	echo !empty($menus) ? '<div class="floattop">'.$s.'</div><div class="floattopempty"></div>' : $s;
}

function showsubmenusteps($title, $menus = array()) {
	$s = '<div class="itemtitle">'.($title ? '<h3>'.lang($title).'</h3>' : '');
	if(is_array($menus)) {
		$s .= '<ul class="stepstat">';
			$i = 0;
		foreach($menus as $menu) {
			$i++;
			$s .= '<li'.($menu[1] ? ' class="current"' : '').' id="step'.$i.'">'.$i.'.'.lang($menu[0]).'</li>';
		}
		$s .= '</ul>';
	}
	$s .= '</div>';
	echo $s;
}

function showsubmenuanchors($title, $menus = array(), $right = '') {
	global $_G, $BASESCRIPT;
	if(!$title || !$menus || !is_array($menus)) {
		return;
	}
	$s = '<div class="itemtitle">'.$right.'<h3>'.lang($title).'</h3>';
	$s .= '<ul class="tab1" id="submenu">';
	foreach($menus as $menu) {
		if($menu && is_array($menu)) {
			$s .= '<li'.(!$menu[3] ? ' id="nav_'.$menu[1].'" onclick="showanchor(this)"' : '').($menu[2] ? ' class="current"' : '').'><a href="'.($menu[3] ? $BASESCRIPT.'?action='.$menu[1] : '#').'"><span>'.lang($menu[0]).'</span></a></li>';
		}
	}
	$s .= '</ul>';
	$s .= '</div>';
	echo !empty($menus) ? '<div class="floattop">'.$s.'</div><div class="floattopempty"></div>' : $s;
}

function showtips($tips, $id = 'tips', $display = true) {
	extract($GLOBALS, EXTR_SKIP);
	if(lang($tips, false)) {
		eval('$tips = "'.str_replace('"', '\\"', $lang[$tips]).'";');
	}
	$tmp = explode('</li><li>', substr($tips, 4, -5));
	if(count($tmp) > 4) {
		$tips = '<li>'.$tmp[0].'</li><li>'.$tmp[1].'</li><li id="'.$id.'_more" style="border: none; background: none; margin-bottom: 6px;"><a href="###" onclick="var tiplis = document.getElementById(\''.$id.'lis\').getElementsByTagName(\'li\');for(var i = 0; i < tiplis.length; i++){tiplis[i].style.display=\'\'}document.getElementById(\''.$id.'_more\').style.display=\'none\';">'.lang('tips_all').'...</a></li>';
		foreach($tmp AS $k => $v) {
			if($k > 1) {
				$tips .= '<li style="display: none">'.$v.'</li>';
			}
		}
	}
	unset($tmp);
	showtableheader('tips', '', 'id="'.$id.'"'.(!$display ? ' style="display: none;"' : ''), 0);
	showtablerow('', 'class="tipsblock"', '<ul id="'.$id.'lis">'.$tips.'</ul>');
	showtablefooter();
}

function showformheader($action, $extra = '', $name = 'cpform') {
	global $_G, $BASESCRIPT;
	echo '<form name="'.$name.'" method="post" target="_self" action="'.$BASESCRIPT.'?action='.$action.'" id="'.$name.'"'.($extra == 'enctype' ? ' enctype="multipart/form-data"' : " $extra").'>'.
		'<input type="hidden" name="formhash" value="'.formhash().'" />'.
		'<input type="hidden" id="formscrolltop" name="scrolltop" value="" />'.
		'<input type="hidden" name="anchor" value="'.htmlspecialchars($GLOBALS['anchor']).'" />';
}

function showhiddenfields($hiddenfields = array()) {
	if(is_array($hiddenfields)) {
		foreach($hiddenfields as $key => $val) {
			$val = is_string($val) ? htmlspecialchars($val) : $val;
			echo "\n<input type=\"hidden\" name=\"$key\" value=\"$val\">";
		}
	}
}

function showtableheader($title = '', $classname = '', $extra = '', $titlespan = 15) {
	$classname = str_replace(array('nobottom', 'notop'), array('nobdb', 'nobdt'), $classname);
	echo "\n".'<table class="tb tb2 '.$classname.'"'.($extra ? " $extra" : '').'>';
	if($title) {
		$span = $titlespan ? 'colspan="'.$titlespan.'"' : '';
		echo "\n".'<tr><th '.$span.' class="partition">'.lang($title).'</th></tr>';
	}
}

function showtagheader($tagname, $id, $display = false, $classname = '') {
	echo '<'.$tagname.($classname ? " class=\"$classname\"" : '').' id="'.$id.'"'.($display ? '' : ' style="display: none"').'>';
}

function showtitle($title, $extra = '') {
	echo "\n".'<tr'.($extra ? " $extra" : '').'><th colspan="15" class="partition">'.cplang($title).'</th></tr>';
}

function showsubtitle($title = array(), $rowclass='header') {
	if(is_array($title)) {
		$subtitle = "\n<tr class=\"$rowclass\">";
		foreach($title as $v) {
				if($v !== NULL) {
						$_title_len=explode('_||_',$v);
						if($_title_len[1] && intval($_title_len[1])>0){
								$subtitle .= '<th style="width:'.$_title_len[1].'px;">'.lang($_title_len[0]).'</th>';
						}else{
								$subtitle .= '<th>'.lang($v).'</th>';
						}
			}
		}
		$subtitle .= '</tr>';
		echo $subtitle;
	}
}

function showtablerow($trstyle = '', $tdstyle = array(), $tdtext = array(), $return = false) {
	if(!preg_match('/class\s*=\s*[\'"]([^\'"<>]+)[\'"]/i', $trstyle, $matches)) {
		$rowswapclass = is_array($tdtext) && count($tdtext) > 2 ? ' class="hover"' : '';
	} else {
		if(is_array($tdtext) && count($tdtext) > 2) {
			$rowswapclass = " class=\"{$matches[1]} hover\"";
			$trstyle = preg_replace('/class\s*=\s*[\'"]([^\'"<>]+)[\'"]/i', '', $trstyle);
		}
	}
	$cells = "\n".'<tr'.($trstyle ? ' '.$trstyle : '').$rowswapclass.' >';
	if(isset($tdtext)) {
		if(is_array($tdtext)) {
			foreach($tdtext as $key => $td) {
					$cells .= '<td'.(is_array($tdstyle) && !empty($tdstyle[$key]) ? ' '.$tdstyle[$key] : '').'>'.$td.'</td>';
			}
		} else {
			$cells .= '<td'.(!empty($tdstyle) && is_string($tdstyle) ? ' '.$tdstyle : '').' >'.$tdtext.'</td>';
		}
	}
	$cells .= '</tr>';
	if($return) {
		return $cells;
	}
	echo $cells;
}

function showsetting($setname, $varname, $value, $type = 'radio', $disabled = '', $hidden = 0, $comment = '', $extra = '', $required = '', $other = '') {

	$s = "\n";
	$check = array();
	$check['disabled'] = $disabled ? ' disabled' : '';

	if($type == 'radio') {
		$value ? $check['true'] = "checked" : $check['false'] = "checked";
		$value ? $check['false'] = '' : $check['true'] = '';
		$check['hidden1'] = $hidden ? ' onclick="$(\'hidden_'.$setname.'\').style.display = \'\';"' : '';
		$check['hidden0'] = $hidden ? ' onclick="$(\'hidden_'.$setname.'\').style.display = \'none\';"' : '';
		$s .= '<ul onmouseover="altStyle(this);">'.
			'<li'.($check['true'] ? ' class="checked"' : '').'><input class="radio" style="border:none;" type="radio" name="'.$varname.'" value="1" '.$check['true'].$check['hidden1'].$check['disabled'].'>&nbsp;'.lang('yes').'</li>'.
			'<li'.($check['false'] ? ' class="checked"' : '').'><input class="radio" style="border:none;" type="radio" name="'.$varname.'" value="0" '.$check['false'].$check['hidden0'].$check['disabled'].'>&nbsp;'.lang('no').'</li>'.
			'</ul>';
	} elseif($type == 'radio_a') {
		$value == 1 ? $check['access'] = "checked" : "";
		$value == 0 ? $check['default'] = "checked" : '';
		$value == -1 ? $check['noaccess'] = "checked" : '';
		$check['hidden1'] = $hidden ? ' onclick="$(\'hidden_'.$setname.'\').style.display = \'\';"' : '';
		$check['hidden0'] = $hidden ? ' onclick="$(\'hidden_'.$setname.'\').style.display = \'none\';"' : '';
		$s .= '<ul onmouseover="altStyle(this);">'.
			'<li'.($value == 0 ? ' class="checked"' : '').'><input class="radio" type="radio" style="border:none;" name="'.$varname.'" value="0" '.$check['default'].$check['hidden0'].$check['disabled'].'>&nbsp;'.lang('default').'</li>'.
			'<li'.($value == 1 ? ' class="checked"' : '').'><input class="radio" type="radio" style="border:none;" name="'.$varname.'" value="1" '.$check['access'].$check['hidden1'].$check['disabled'].'>&nbsp;'.lang('enable_access').'</li>'.
			'<li'.($value == -1 ? ' class="checked"' : '').'><input class="radio" type="radio" style="border:none;" name="'.$varname.'" value="-1" '.$check['noaccess'].$check['hidden1'].$check['disabled'].'>&nbsp;'.lang('disable_noaccess').'</li>'.
			'</ul>';
	} elseif($type == 'text' || $type == 'password' || $type == 'number') {
		$s .= '<input id="'.$varname.'" name="'.$varname.'" value="'.shtmlspecialchars($value).'" type="'.$type.'" class="txt" '.$check['disabled'].' '.$extra.' />'.$other;
	} elseif($type == 'file') {
		$s .= '<input id="'.$varname.'" name="'.$varname.'" type="file" class="txt uploadbtn marginbot" '.$check['disabled'].' '.$extra.' /><input name="'.$varname.'_value" type="hidden" id="'.$varname.'_value" value="'.shtmlspecialchars($value).'" style="display:none;"/>';
	} elseif($type == 'textarea') {
		$readonly = $disabled ? 'readonly' : '';
		$s .= "<textarea $readonly rows=\"6\" ondblclick=\"textareasize(this, 1)\" onkeyup=\"textareasize(this, 0)\" name=\"$varname\" id=\"$varname\" cols=\"50\" class=\"tarea\" $extra>".shtmlspecialchars($value)."</textarea>";
	} elseif($type == 'select') {
		$s .= '<select name="'.$varname[0].'" '.$extra.'>';
		foreach($varname[1] as $option) {
			$selected = $option[0] == $value ? 'selected="selected"' : '';
			$s .= "<option value=\"$option[0]\" $selected>".$option[1]."</option>\n";
		}
		$s .= '</select>';
	} elseif($type == 'mradio') {
		if(is_array($varname)) {
			$radiocheck = array($value => ' checked');
			$s .= '<ul'.(empty($varname[2]) ?  ' class="nofloat"' : '').' onmouseover="altStyle(this);">';
			foreach($varname[1] as $varary) {
				if(is_array($varary) && !empty($varary)) {
					$onclick = '';
					if(!empty($varary[2])) {
						foreach($varary[2] as $ctrlid => $display) {
							$onclick .= '$(\'#'.$ctrlid.'\').css(\'display\',\''.$display.'\');';
						}
					}
					$onclick && $onclick = ' onclick="'.$onclick.'"';
					$s .= '<li'.($radiocheck[$varary[0]] ? ' class="checked"' : '').'><input class="radio" style="border:none;" type="radio" name="'.$varname[0].'" value="'.$varary[0].'"'.$radiocheck[$varary[0]].$check['disabled'].$onclick.'>&nbsp;'.$varary[1].'</li>';
				}
			}
			$s .= '</ul>';
		}
	} elseif($type == 'mcheckbox') {
		$s .= '<ul class="nofloat" onmouseover="altStyle(this);">';
		foreach($varname[1] as $varary) {
			if(is_array($varary) && !empty($varary)) {
				$onclick = !empty($varary[2]) ? ' onclick="$(\''.$varary[2].'\').style.display = $(\''.$varary[2].'\').style.display == \'none\' ? \'\' : \'none\';"' : '';
				$checked = is_array($value) && in_array($varary[0], $value) ? ' checked' : '';
				$s .= '<li'.($checked ? ' class="checked"' : '').'><input class="checkbox" style="border:none;" type="checkbox" name="'.$varname[0].'[]" value="'.$varary[0].'"'.$checked.$check['disabled'].$onclick.'>&nbsp;'.$varary[1].'</li>';
			}
		}
		$s .= '</ul>';
	} elseif($type == 'binmcheckbox') {
		$checkboxs = count($varname[1]);
		$value = sprintf('%0'.$checkboxs.'b', $value);$i = 1;
		$s .= '<ul class="nofloat" onmouseover="altStyle(this);">';
		foreach($varname[1] as $key => $var) {
			$s .= '<li'.($value{$checkboxs - $i} ? ' class="checked"' : '').'><input class="checkbox" style="border:none;" type="checkbox" name="'.$varname[0].'['.$i.']" value="1"'.($value{$checkboxs - $i} ? ' checked' : '').' '.(!empty($varname[2][$key]) ? $varname[2][$key] : '').'>&nbsp;'.$var.'</li>';
			$i++;
		}
		$s .= '</ul>';
	} elseif($type == 'mselect') {
		$s .= '<select name="'.$varname[0].'" multiple="multiple" size="10" '.$extra.'>';
		foreach($varname[1] as $option) {
			$selected = is_array($value) && in_array($option[0], $value) ? 'selected="selected"' : '';
			$s .= "<option value=\"$option[0]\" $selected>".$option[1]."</option>\n";
		}
		$s .= '</select>';
	} elseif($type == 'color') {
		global $_G, $stylestuff;
		$preview_varname = str_replace('[', '_', str_replace(']', '', $varname));
		$code = explode(' ', $value);
		$css = '';
		for($i = 0; $i <= 1; $i++) {
			if($code[$i] != '') {
				if($code[$i]{0} == '#') {
					$css .= strtoupper($code[$i]).' ';
				} elseif(preg_match('/^http:\/\//i', $code[$i])) {
					$css .= 'url(\''.$code[$i].'\') ';
				} else {
					$css .= 'url(\''.$stylestuff['imgdir']['subst'].'/'.$code[$i].'\') ';
				}
			}
		}
		$background = trim($css);
		$colorid = ++$GLOBALS['coloridcount'];
		$s .= "<input id=\"c{$colorid}_v\" type=\"text\" class=\"txt\" style=\"float:left; width:200px;\" value=\"$value\" name=\"$varname\" onchange=\"updatecolorpreview('c{$colorid}')\">\n".
			"<input id=\"c$colorid\" onclick=\"c{$colorid}_frame.location='static/image/admin/getcolor.htm?c{$colorid}';showMenu({'ctrlid':'c$colorid'})\" type=\"button\" class=\"colorwd\" value=\"\" style=\"background: $background\"><span id=\"c{$colorid}_menu\" style=\"display: none\"><iframe name=\"c{$colorid}_frame\" src=\"\" frameborder=\"0\" width=\"166\" height=\"186\" scrolling=\"no\"></iframe></span>\n$extra";
	} elseif($type == 'calendar') {
		$s .= "<input type=\"text\" class=\"txt\" name=\"$varname\" value=\"".shtmlspecialchars($value)."\" onclick=\"showcalendar(event, this".($extra ? ', 1' : '').")\">\n";
	} elseif(in_array($type, array('multiply', 'range', 'daterange'))) {
		$onclick = $type == 'daterange' ? ' onclick="showcalendar(event, this)"' : '';
		$s .= "<input type=\"text\" class=\"txt\" name=\"$varname[0]\" value=\"".shtmlspecialchars($value[0])."\" style=\"width: 108px; margin-right: 5px;\"$onclick>".($type == 'multiply' ? ' X ' : ' -- ')."<input type=\"text\" class=\"txt\" name=\"$varname[1]\" value=\"".shtmlspecialchars($value[1])."\"class=\"txt\" style=\"width: 108px; margin-left: 5px;\"$onclick>";
	} elseif($type == 'p') {
		$s .= '<p id="'.$varname.'" name="'.$varname.'">'.shtmlspecialchars($value).'</p>';
	} elseif($type == 'p_pre') {
		$s .= '<div style="">'.$value.'</div>';
	} else {
		$s .= $type;
	}
	if($hidden) {
		showtagheader('tbody', 'hidden_'.$setname, $value, 'sub');
	}
	showtablerow('', 'colspan="2" class="td27"', lang($setname).$required);
	$spanid = is_array($varname) ? $varname[0] : $varname;
	showtablerow('class="noborder"', array('class="vtop rowform"', 'class="vtop tips2" id="span_'.$spanid.'"'), array(
		$s,
		($type == 'p' || $type == 'p_pre' ? '' : ($comment ? $comment : (lang($setname.'_comment')==$setname.'_comment'?'':lang($setname.'_comment')))).($type == 'textarea' ? '<br />'.lang('tips_textarea') : '').
		($disabled ? '<br /><span class="smalltxt" style="color:#FF0000">'.lang($setname.'_disabled', 0).'</span>' : NULL)
	));
	if($hidden) {
		showtagfooter('tbody');
	}

}

function mradio($name, $items = array(), $checked = '', $float = true) {
	$list = '<ul'.($float ?  '' : ' class="nofloat"').' onmouseover="altStyle(this);">';
	if(is_array($items)) {
		foreach($items as $value => $item) {
			$list .= '<li'.($checked == $value ? ' class="checked"' : '').'><input type="radio" style="border:none;" name="'.$name.'" value="'.$value.'" class="radio"'.($checked == $value ? ' checked="checked"' : '').' /> '.$item.'</li>';
		}
	}
	$list .= '</ul>';
	return $list;
}

function mcheckbox($name, $items = array(), $checked = array()) {
	$list = '<ul class="dblist" onmouseover="altStyle(this);">';
	if(is_array($items)) {
		foreach($items as $value => $item) {
			$list .= '<li'.(empty($checked) || in_array($value, $checked) ? ' class="checked"' : '').'><input type="checkbox" name="'.$name.'[]" value="'.$value.'" class="checkbox"'.(empty($checked) || in_array($value, $checked) ? ' checked="checked"' : '').' /> '.$item.'</li>';
		}
	}
	$list .= '</ul>';
	return $list;
}

function showsubmit($name = '', $value = 'submit', $before = '', $after = '', $floatright = '', $nexttask = '') {
	$str = '<tr>';
	$str .= $name && in_array($before, array('del', 'select_all', 'td')) ? '<td class="td25">'.($before != 'td' ? '<input type="checkbox" name="chkall" id="chkall" class="checkbox" onclick="checkall(this.form, \'delete\')" /><label for="chkall">'.lang($before) : '').'</label></td>' : '';
	$str .= '<td colspan="15">';
	$str .= $floatright ? '<div class="cuspages right">'.$floatright.'</div>' : '';
	$str .= '<div class="fixsel"><div id="ajax_status_display"></div>';
	$str .= $before && !in_array($before, array('del', 'select_all', 'td')) ? $before.' &nbsp;' : '';
	$str .= $name ? '<input type="submit" class="btn" id="submit_'.$name.'" name="'.$name.'" title="'.lang('submit_tips').'" value="'.lang($value).'" />' : '';
	$after = $after == 'more_options' ? '<input class="checkbox" type="checkbox" value="1" onclick="document.getElementById(\'advanceoption\').style.display = document.getElementById(\'advanceoption\').style.display == \'none\' ? \'\' : \'none\'; this.value = this.value == 1 ? 0 : 1; this.checked = this.value == 1 ? false : true" id="btn_more" /><label for="btn_more">'.lang('more_options').'</label>' : $after;
	$str = $after ? $str.(($before && $before != 'del') || $name ? ' &nbsp;' : '').$after : $str;
	$str .= $nexttask && $value == 'submitnext' ? ' <a style="" href="'.$nexttask.'">'.lang('task_skip_next').'</a>' :  '';
	$str .= '</div>';
	$str .= '</td></tr>';
	echo $str;
}

function showtagfooter($tagname) {
	echo '</'.$tagname.'>';
}

function showtablefooter() {
	echo '</table>'."\n";
}

function showformfooter() {
	global $_G, $scrolltop;
	echo '</form>'."\n";
	if($scrolltop) {
		echo '<script type="text/JavaScript">_attachEvent(window, \'load\', function () { scroll(0,'.intval($scrolltop).') }, document);</script>';
	}
}

function cpfooter() {
	global $_G, $nexttask, $taskmessage, $_SC;

	if($taskmessage) {
		echo '
		<div id="backhold" style="width:3000px;z-index:999;top:0px;left:0px;position: absolute; display: block; background-color: #666666; opacity: 0.4;filter:alpha(opacity=50);  height: 3000px;"></div>
		<div id="newtask" style="z-index:1000;">
				<h3 style=""><span>'.$taskmessage[0].'</span></h3>
				<a style="" class="closetask" onclick="closetask();return false;" href="#">'.lang('close').'</a>
					<div id="taskmessage" style="background-color:#FFFFFF;margin-bottom:0px;">
						<div class="messagecont">'.$taskmessage[1].'</div>
						<div id="taskaction">
							<ul>
							<li style="display:inline;"><a class="dotask" onclick="closetask();return false;" href="#"><span>'.lang('task_start').'</span></a></li>
							<li style="display:inline;"><a style="overflow:hidden;float:left;height:32px;line-height:30px;margin-left:20px;width:40px;" href="'.$nexttask.'">'.lang('task_skip').'</a></li></ul>
						</div>
					</div>
				</div>
				';
	}
	echo '<script type="text/JavaScript">
			function closetask() {
					document.getElementById("newtask").style.display= "none";
					document.getElementById("backhold").style.display= "none";
				}
			</script>';
	if(!empty($_GET['highlight'])) {
		$kws = explode(' ', $_GET['highlight']);
		echo '<script type="text/JavaScript">';
		foreach($kws as $kw) {
			echo 'parsetag(\''.$kw.'\');';
		}
		echo '</script>';
	}
echo "</body></html>";

	if(defined('IN_ADMIN') && $_GET['action'] == 'index') {
		$newsurl =  'ht'.'tp:/'.'/cus'.'tome'.'r.disc'.'uz.n'.'et/n'.'ews'.'.p'.'hp?'.brandinformation();

		//$newsurl = 'http://localhost/n'.'ews'.'.p'.'hp?'.brandinformation();
		echo '
		<script type="text/javascript">
		var newhtml = "";
		newhtml += \'<table class="tb tb2"><tr><th class="partition edited">您當前使用的 Discuz! 程序版本有重要更新，請參照以下提示進行及時升級</th></tr>\';
		newhtml += \'<tr><td class="tipsblock"><a href="http://faq.comsenz.com/checkversion.php?product=Brand&version='.B_VER.'&release='.B_RELEASE.'&charset='.$_G['charset'].'&dbcharset='.$_SC['dbcharset'].'" target="_blank"><img src="'.$newsurl.'" onload="showbrandnews()" /></a></td></tr></table>\';
		$("#brandnews").css({display: "none"});
		$("#brandnews").html(newhtml);
		function showbrandnews() {
			$("#brandnews").css({display: ""});
		}
		</script>';
	}

exit();
	//updatesession();
}

function brandinformation() {

	global $_G, $_SGLOBAL, $_SERVER;

	if(empty($_G['setting']['siteuniqueid']) || bstrlen($_G['setting']['siteuniqueid']) < 8 || strpos($_G['setting']['siteuniqueid'], 'PK')!==0) {
		$_G['setting']['siteuniqueid'] = DB::result_first('SELECT value FROM '.tname('settings')." WHERE variable='siteuniqueid'");
		if(empty($_G['setting']['siteuniqueid']) || bstrlen($_G['setting']['siteuniqueid']) < 8 || strpos($_G['setting']['siteuniqueid'], 'PK')!==0) {
			$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
			$_G['setting']['siteuniqueid'] = 'PK'.$chars[date('y')%60].$chars[date('n')].$chars[date('j')].$chars[date('G')].$chars[date('i')].$chars[date('s')].substr(md5($_G['clientip'].$_G['username'].$_G['timestamp']), 0, 4).random(4);
			DB::query('REPLACE INTO '.tname('settings')." (variable, value) VALUES ('siteuniqueid', '$_G[setting][siteuniqueid]')");
			require_once(B_ROOT.'./source/function/cache.func.php');
			updatesettingcache();
		}
	}

	$update = array('id' => $_G['setting']['siteuniqueid'], 'version' => B_VER, 'release' => B_RELEASE, 'php' => PHP_VERSION, 'mysql' => DB::version(), 'charset' => $_G['charset'], 'siteurl' => $_G['setting']['siteurl'], 'sitename' => $_G['setting']['wwwname'].'->'.$_G['setting']['sitename'], 'email' => $_G['member']['email']);

	$updatetime = @filemtime(B_ROOT.'./data/updatetime.lock');
	if(empty($updatetime) || ($_G['timestamp'] - $updatetime > 3600 * 4)) {
		@touch(B_ROOT.'./data/updatetime.lock');
		$update['members'] = DB::result_first('SELECT COUNT(*) FROM '.tname('members'));
		$update['shops'] = DB::result_first('SELECT COUNT(*) FROM '.tname('shopitems'));
		$update['discounts'] = DB::result_first('SELECT COUNT(*) FROM '.tname('shopitems')." WHERE isdiscount='1'");
		$update['goods'] = DB::result_first('SELECT COUNT(*) FROM '.tname('gooditems'));
		$update['notices'] = DB::result_first('SELECT COUNT(*) FROM '.tname('noticeitems'));
		$update['consumes'] = DB::result_first('SELECT COUNT(*) FROM '.tname('consumeitems'));
		$update['albums'] = DB::result_first('SELECT COUNT(*) FROM '.tname('albumitems'));
		$update['albumsbbs'] = DB::result_first('SELECT COUNT(*) FROM '.tname('albumitems')." WHERE frombbs='1'");
		$update['photos'] = DB::result_first('SELECT COUNT(*) FROM '.tname('photoitems'));
		$update['comments'] = DB::result_first('SELECT COUNT(*) FROM '.tname('spacecomments'));
		$update['commentscores'] = DB::result_first('SELECT COUNT(*) FROM '.tname('commentscores'));
		$update['links'] = DB::result_first('SELECT COUNT(*) FROM '.tname('brandlinks'));
		$update['reportlog'] = DB::result_first('SELECT COUNT(*) FROM '.tname('reportlog'));
		foreach(array('shop', 'good', 'notice', 'consume', 'album') as $value) {
			$update[$value.'cates'] = count($_SGLOBAL[$value.'cates']);
		}
	}

	$data = '';
	foreach($update as $key => $value) {
		$data .= $key.'='.rawurlencode($value).'&';
	}

	return 'os=pk&update='.rawurlencode(base64_encode($data)).'&md5hash='.substr(md5($_SERVER['HTTP_USER_AGENT'].implode('', $update).$_G['timestamp']), 8, 8).'&timestamp='.$_G['timestamp'];
}

function show_style_picker($id, $color) {
	return '
	<input style="display: none;" type="text" onchange="updatecolorpreview(\'c'.$id.'\')" name="fontcolor'.$id.'" value="" id="c'.$id.'_v"/>
	<input type="button"  value="" class="colorwd" onclick="c'.$id.'_frame.location=\'static/image/admin/getcolor.htm?'.$id.'\';showMenu({\'ctrlid\':\'c'.$id.'\'})" id="c'.$id.'"/>
	<span style="display: none;" id="c'.$id.'_menu">
	<iframe width="166" scrolling="no" height="186" frameborder="0" src="" name="c'.$id.'_frame"></iframe>
	</span>

	<img src="static/image/admin/ti.gif" /><input type="checkbox" style="border:none;" name="em'.$id.'" id="em'.$id.'" value="italic" onClick="set_style(\''.$id.'\');" />
	<img src="static/image/admin/tb.gif" /><input type="checkbox" style="border:none;" name="strong'.$id.'" id="strong'.$id.'" value="bold" onClick="set_style(\''.$id.'\');" />
	<img src="static/image/admin/tu.gif" /><input type="checkbox" style="border:none;" name="underline'.$id.'" id="underline'.$id.'" value="underline" onClick="set_style(\''.$id.'\');" />
	<img src="static/image/delStyle.gif" onclick="reset_style(\'' . $id . '\')" style="cursor:pointer;" title="'.cplang('del_style').'" />
	<script language="javascript" type="text/javascript">
		load_style(\''.$id.'\', \''.$color.'\');
		var theboj = $(\''.$id.'\');
	</script>	';
}

// 標題
function showstyletitle($mname, $color) {
	$lang_title = lang($mname.'_stytletitle');
	$lang_fontcolor = lang($mname.'_fontcolor');
	echo <<<EOF
	<tr><td class="td27" colspan="2">{$lang_title}</td></tr>
	<tr class="noborder">
		<td class="vtop rowform" colspan="2">
			<span style="float: left;display:none;">{$lang_fontcolor}</span>
EOF;
echo show_style_picker('subject', $color);
echo <<<EOF
		</td>
	</tr>


EOF;
}

function showmapsetting($mname, $mapapikey, $mapapimark) {
	global $_G, $editvalue, $_SC;
	//$shopaddress = !empty($mapapimark) || empty($editvalue['address']) ?'<input id="inputmap" name="mapapimark" value="{$mapapimark}" type="hidden" />':'<input id="inputmap" name="mapapimark" value="" type="hidden" /><input type="hidden" id="address" value="'.$editvalue['address'].'" />';
	//$mapapimark = empty($mapapimark)?'(39.917,116.397)':$mapapimark;
	$shopaddress = '<input type="hidden" name="inputmap" id="inputmap" value="'.$mapapimark.'" /><input type="hidden" name="local" id="local" value="'.$_SC['local'].'" /><input type="hidden" name="address" id="address" value="'.$editvalue['address'].'" />';
	$lang_mark = lang($mname.'_mapapimark');
	$lang_mark_comment = lang($mname.'_mapapimark_comment');
	echo <<<EOF
<tr>
	<td colspan="2">
		{$shopaddress}
		<div id="divmapapimark" style="width:500px; height:350px;"></div>

		<script src="http://maps.google.com/maps?file=api&v=2&key={$mapapikey}&hl=zh-CN&sensor=false" charset="utf-8" type="text/javascript">
		</script>
		<script type="text/javascript" charset="utf-8">
        $(function(){
            maploading();
            var center;
            var marker;
            var zoomnum = 14;
            var needgeoagain = false;
            var adressagain = '';
            var map = new GMap2(document.getElementById("divmapapimark"), {
                                                googleBarOptions: {
                                                                onIdleCallback : function(e){
                                                                    //alert("onIdleCallback");
                                                                },
                                                                onSearchCompleteCallback  : function(e){
                                                                    //alert("onSearchCompleteCallback ");
                                                                },
                                                                onMarkersSetCallback  : function(e){
                                                                    //alert("onMarkersSetCallback ");
                                                                },
                                                                onGenerateMarkerHtmlCallback  : function(m,h,r){
                                                                    map.setCenter(m.getLatLng());
                                                                    setmarker(m.getLatLng(),1)
                                                                    return h.innerHTML;
                                                                }
                                                }
            });
            map.enableScrollWheelZoom();
            map.addControl(new GLargeMapControl());
            map.addControl(new GMapTypeControl());
            map.enableGoogleBar();
            if($('#inputmap').length > 0 && $('#inputmap').val().length>0) {
                eval('var latlng = new GLatLng'+$('#inputmap').val());
                setmarker(latlng,1);
            } else {

                 if($('#address').length > 0 && $('#address').val().length>0) {
                    if($('#local').length > 0 && $('#local').val().length>0) {
                        needgeoagain = true;
                        adressagain = $('#address').val();
                        getlocations($('#local').val()+$('#address').val());
                    } else {
                        getlocations($('#address').val());
                    }
                 } else {
                    if($('#local').length > 0 && $('#local').val().length>0) {
                        getlocations($('#local').val());
                    } else {
                        setmarker_bj();
                    }
                 }


            }
            $('#resm').click(function() {
                map.setCenter(center, zoomnum);
                marker.setLatLng(center);
                document.getElementById("inputmap").value = center;


            });


            function setmarker(latlng, status) {

				map.setCenter(latlng, zoomnum);
				marker = new GMarker(latlng, {draggable: true});
                if(status == 1) {
                    document.getElementById("inputmap").value = latlng;
                    center = latlng;
                }
                marker.setLatLng(latlng);
                map.clearOverlays();
                map.addOverlay(marker);
                map.panTo(latlng);
                maploaded();
                GEvent.addListener(marker, "dragstart", function() {
                        map.closeInfoWindow();
                        });

                GEvent.addListener(marker, "dragend", function() {
                        map.panTo(marker.getLatLng());
                        document.getElementById("inputmap").value = marker.getLatLng();
                        });


                var myEventListener = GEvent.bind(map, "click", map, function(markere,latlng) {
                    if (latlng) {
                        marker.setLatLng(latlng);
                        map.clearOverlays();
                        map.addOverlay(marker);
                        document.getElementById("inputmap").value = latlng;
                        map.panTo(latlng);

                    }
                });
            }
            function setmarker_bj() {
                latlng = new GLatLng(39.917,116.397);
                setmarker(latlng, 1);
            }
            function getlocations(adress) {
                if(adressagain != '') {
                    if($('#local').val() == adress) {
                        needgeoagain = false;
                    }
                    if(needgeoagain == true && adressagain == adress) {
                        adressagain = $('#local').val();
                    }

                }
                var GL = new GClientGeocoder();
                GL.getLocations(adress, function(e){
                    geocoder(e);
                });
            }
            function geocoder(e) {
                var s = e.Status;
                if(s.code == 200) {
                    var p = e.Placemark;
                    var po = p[0].Point;
                    var cc = (po.coordinates);
                    var ccc = new GLatLng(cc[1],cc[0]);
                    setmarker(ccc, 1);
                    return true;
                } else {
                    if (needgeoagain == true) {
                        getlocations(adressagain);
                    } else {
                        setmarker_bj();
                    }
                    return false;

                }
            }
            function maploading() {
                //半透明層覆蓋頁面，顯示loading
                $("#loading").css('display', 'block');
                $("#background").css({'height':$(document).height(),'width':$(document).width()});
                $("#background").css('display', 'block');

            }
            function maploaded() {
                //隱藏半透明層、loading
                $("#loading").css('display', 'none');
                $("#background").css('display', 'none');
            }
        });
		</script>
	</td>
</tr>
<tr><td class="td27" colspan="2"><span style=" font-weight: normal;">{$lang_mark_comment}</span></td></tr>
EOF;
}

function showbasicfield($mname, $editvalue, $_SSCONFIG, $categorylist, $file='admin') {
	global $_G, $_SGLOBAL, $item;

	//編輯頁面基本字段
	$required = '<span style="color:red">*</span>';
	pklabel(array('type'=>'input', 'other'=>'style="'.pktitlestyle($editvalue['styletitle']).'"', 'alang'=>$mname.'_subject', 'name'=>'subject', 'value'=>$editvalue['subject'], 'required'=>$required));
	if($mname == 'notice' || $mname == 'shop') {
		showstyletitle($mname, substr($editvalue['styletitle'], 0, 7));
	}
	if(in_array($mname, array('good', 'album', 'consume', 'notice', 'groupbuy')) || (pkperm('isadmin') && $mname == 'shop')) {
		if(pkperm('isadmin') && $mname == 'shop') {
			$categorylist = getmodelcategory($mname);
			showsetting('syncfid', 'syncfid', ''.(empty($editvalue['syncfid'])?'':$editvalue['syncfid']), 'text');
			echo '<tr><td class="td27" colspan="2">'.lang('category_'.$mname).'<span style="color:red">*</span></td></tr><tr><td colspan="2" class="vtop rowform" id="'.$showarr['name'].'div">';
			echo InteractionCategoryMenu($categorylist,'catid',$editvalue['catid'],1);
			echo '<span id="span_catid"></span></td></tr>';
			
		} else {
			$categorylist = mymodelcategory($mname);
			echo '<tr><td class="td27" colspan="2">'.lang('category_'.$mname).'<span style="color:red">*</span></td></tr><tr><td class="vtop rowform" id="catiddiv" colspan="2">';
			echo InteractionCategoryMenu($categorylist,'catid',$editvalue['catid'],1);
			echo '<span id="span_catid"></span></td></tr>';
		}
		if($editvalue['attr_ids']) {
			require_once( B_ROOT.'/batch.attribute.php');
			$itemattrupdate = getattributesettingsupdate($editvalue['catid'],$editvalue['attr_ids']);
		} else {
			$itemattrupdate = '';
		}
		echo '<tr><td colspan="2" style="border-top:none;"><div id="attributes">'.$itemattrupdate.'</div></td></tr>';

	} elseif(!pkperm('isadmin') &&$mname == 'shop') {
		$categorylist = getmodelcategory($mname);
		$editvalue['catid'] = $categorylist[$editvalue['catid']]['name'];
		showsetting($mname.'_catid', 'catid', $editvalue['catid'], 'p');
	}
	if($mname != 'consume') {
		pklabel(array('type'=>'file', 'alang'=>$mname.'_subjectimage', 'name'=>'subjectimage', 'value'=>$editvalue['subjectimage'], 'fileurl'=>A_URL.'/'.$editvalue['subjectimage']));
	}
	// 過期時間
	if(in_array($mname,array('shop', 'good', 'consume', 'notice', 'groupbuy'))){
		if(empty($editvalue['validity_start'])){
			$editvalue['validity_start'] = date('Y-m-d', $_G['timestamp']);
		} else {
			$editvalue['validity_start'] = date('Y-m-d', $editvalue['validity_start']);
		}
		if(!pkperm('isadmin') && ($_G['myshopstatus'] == 'verified') && $mname == 'shop') {
			showsetting('validity_start','validity_start', $editvalue['validity_start'], 'p');
		} else {
			showsetting('validity_start','validity_start', $editvalue['validity_start'], 'calendar', '', 0, '', '', $value['required']);
		}
		if(empty($editvalue['validity_end'])) {
			$editvalue['validity_end'] = mktime(0,0,0,date('m',$_G['timestamp']),date('d',$_G['timestamp']), (date('Y',$_G['timestamp']) + 10));
			if($mname == 'consume'){
				$editvalue['validity_end'] = mktime(0,0,0,(date('m',$_G['timestamp']) + 10),date('d',$_G['timestamp']), date('Y',$_G['timestamp']));
			}
		}

		$editvalue['validity_end'] = date('Y-m-d', $editvalue['validity_end']);
		if(!pkperm('isadmin') && ($_G['myshopstatus'] == 'verified') && $mname == 'shop') {
			showsetting('validity_end','validity_end', $editvalue['validity_end'], 'p');
		} else {
			showsetting('validity_end', 'validity_end', $editvalue['validity_end'], 'calendar', '', 0, '', '', $value['required']);
		}
	}
	if($mname == 'consume') {
		pklabel(array('type'=>'textarea', 'alang'=>'consume_message', 'name'=>'message', 'value'=>$editvalue['message']));
		pklabel(array('type'=>'textarea', 'alang'=>'consume_exception', 'name'=>'exception', 'value'=>$editvalue['exception']));
		if($_G['setting']['allowcreateimg']) {
			$createimgradio = array(0, lang('createimg'), array('createimg' => '', 'uploadimg' => 'none'));
		}
		if($_SGLOBAL['panelinfo']['group']['consumemaker'] == 1 || ckfounder($_G['uid'])) {
			$uploadimgradio = array(1, lang('uploadimg'), array('createimg' => 'none', 'uploadimg' => ''));
		}
		if(!empty($createimgradio) || !empty($uploadimgradio)) {
			showconsumemaker(array($uploadimgradio, $createimgradio), $file);
		}
	}
	/* 顯示商品描述 */
	if($mname == 'good') {
		pklabel(array('type'=>'textarea', 'alang'=>'good_intro', 'name'=>'intro', 'value'=>$editvalue['intro']));
	}
	
	if(pkperm('isadmin') && $mname != 'shop') {
		/*
		if($_GET['action'] == 'add') {
			showtablerow('', 'colspan="2" class="td27"', lang($mname.'_shopid').$required);
			showtablerow('class="noborder"', array('class="vtop rowform"', 'class="vtop tips2"'), array(
				show_cat_shop_linkarea(),
				lang($mname.'_shopid_comment')
			));
		} elseif($_GET['action'] == 'edit') {
			showhiddenfields(array('shopid' => $editvalue['shopid']));
		}*/
	} elseif($mname != 'shop') {
		showhiddenfields(array('shopid' => $editvalue['shopid']));
	} else {
		pkregion(array('alang'=>$mname.'_region', 'name'=>'region', 'options'=>getmodelcategory('region'), 'value'=>$editvalue['region'], 'required'=>$required));
	}
	if($mname == 'shop') {
		pklabel(array('type'=>'input', 'alang'=>'global_seokeywords', 'name'=>'keywords', 'value'=>$editvalue['keywords']));
		pklabel(array('type'=>'textarea', 'alang'=>'global_seodescription', 'name'=>'description', 'value'=>$editvalue['description']));
		pklabel(array('type'=>'textarea', 'alang'=>$mname.'_message', 'name'=>'message', 'value'=>$editvalue['message']));
		pklabel(array('type'=>'input', 'alang'=>'shop_letter', 'name'=>'letter', 'value'=>$editvalue['letter']));
	} elseif($mname=='good' || $mname=='notice' || $mname=='groupbuy') {
		$editvalue['message'] = bbcode2html($editvalue['message']);
		pklabel(array('type'=>'edit', 'alang'=>$mname.'_message', 'name'=>'message', 'value'=>$editvalue['message']));
	}
	/*
	if($editvalue['grade'] > 1) {
		showsetting($mname.'_onshow', array('grade', array(
				array(3, lang($mname.'_onshow_true')),
				array(2, lang($mname.'_onshow_false'))
		)), $editvalue['grade'], 'select', '', 0, '', '', $required);
	}*/
}

//顯示用戶名註冊字段
function showusernamefield() {
	//$required = '<span style="color:red">*</span>';
	showsetting('ucreg_username', 'ucreg_username', '', 'text');
	showsetting('ucreg_password', 'ucreg_password', '', 'password');
	showsetting('ucreg_rtpassword', 'ucreg_rtpassword', '', 'password');
	showsetting('ucreg_email', 'ucreg_email', '', 'text');
}

function showlistrow($mname, $value) {
	global $_G, $_SERVER, $_SGLOBAL, $_SSCONFIG, $alang, $cats;
	$mlist = '';
	$value['url'] = 'store.php?id='.($mname=='shop'?$value['itemid']:$value['shopid'].'&action='.$mname.'&xid='.$value['itemid']);
	$value['subject'] = cutstr($value['subject'], 40);
	$tdclassarr = array(
		0 => '',
		1 => '',
		2 => 'class="td25"'
	);
	if(pkperm('isadmin')) {
		$rowarr = array(
			0 => "<input class=\"checkbox\" type=\"checkbox\" name=\"item[]\" value=\"$value[itemid]\" checked/>".(isset($value['shopid'])?'<input type="hidden" name="item_shopid['.$value[itemid].']" value="'.$value[shopid].'"/>':''),
			1 => "<div style=\"margin-top:3px\">$value[itemid]</div>",
			2 => "<input name=\"display[{$value[itemid]}]\" type=\"text\" size=\"2\" value=\"".( pkperm('isadmin')  ? $value['displayorder']:$value['displayorder_s'])."\" />",
			3 => "<a href=\"$value[url]\" target=\"_blank\" title=\"$value[subject]\"> ".cutstr($value[subject],50)." </a>",
			4 => "<a href=\"{$_SERVER[SCRIPT_NAME]}?action=list&m={$mname}&groupid={$value['groupid']}&filtersubmit=GO\" title=\"$value[title]\">".(empty($value[title])?lang('no_groupid'):cutstr($value[title],24))."</a>",
			5 => !empty($value['catid'])?"<a href=\"{$_SERVER[SCRIPT_NAME]}?action=list&m={$mname}&catid={$value['catid']}&filtersubmit=GO\">".$cats[$value['catid']]['name']."</a>":lang($mname.'_nocatid'),
			6 => empty($value['isdiscount'])?lang('shop_discount_no'):lang('shop_discount_yes'),
			7 => empty($value['allowreply'])?lang('no'):lang('yes'),
			8 => "<a href=\"{$_SERVER[SCRIPT_NAME]}?action=list&m={$mname}&uid={$value[uid]}&filtersubmit=GO\" title=\"$value[username]\">".cutstr($value[username],8)."</a>",
			9 => date('Y-m-d', $value['dateline']),
			10 =>  ($value['updateverify'] == 1?$_SGLOBAL['shopgrade'][5]:$_SGLOBAL['shopgrade'][$value['grade']]),
			11 => "<a href=\"{$_SERVER[SCRIPT_NAME]}?action=edit&m=$mname&itemid={$value[itemid]}".(in_array($mname, array('good','notice','groupbuy','consume','album'))?'&shopid='.$value['shopid']:'')."\">$alang[edit]</a>".
				((pkperm('isadmin') && $mname=='shop') ? " <a href=\"{$_SERVER[SCRIPT_NAME]}?action=add&m=good&shopid={$value[itemid]}\">$alang[publish]</a>" : '').
				((pkperm('isadmin') && in_array($mname, array('shop','good','notice','consume','album', 'groupbuy')) && $value['grade']==0)?"&nbsp;<a style=\"color:red;\" href=\"{$_SERVER[SCRIPT_NAME]}?action=edit&m=$mname&itemid={$value[itemid]}&op=adminview&optpass=1\">$alang[update]</a>":'').
				(pkperm('isadmin') && $value['updateverify'] == 1?"&nbsp;<a style=\"color:red;\" href=\"{$_SERVER[SCRIPT_NAME]}?action=edit&m=$mname&itemid={$value[itemid]}&op=adminview&updatepass=1\">$alang[update]</a>":'')
		);
	} else {
		$rowarr = array(
			0 => "<input class=\"checkbox\" type=\"checkbox\" name=\"item[]\" value=\"$value[itemid]\" />",
			1 => "<div style=\"margin-top:3px\">$value[itemid]</div>",
			2 => "<input name=\"display[{$value[itemid]}]\" type=\"text\" size=\"2\" value=\"".( pkperm('isadmin')  ? $value['displayorder']:$value['displayorder_s'])."\" />",
			3 => "<a href=\"$value[url]\" target=\"_blank\" title=\"$value[subject]\"> ".cutstr($value[subject],50)." </a>",
			5 => !empty($value['catid'])?$cats[$value['catid']]['name']:lang($mname.'_nocatid'),
			6 => date('Y-m-d', $value['dateline']),
			7 => $_SGLOBAL['shopgrade'][$value['grade']],
			8 => "<a href=\"{$_SERVER[SCRIPT_NAME]}?action=edit&m=$mname&itemid={$value[itemid]}\">$alang[edit]</a>".((pkperm('isadmin') && $mname=='shop') ? " <a href=\"{$_SERVER[SCRIPT_NAME]}?action=add&m=good&shopid={$value[itemid]}\">$alang[publish]</a>" : '').((pkperm('isadmin') && $mname=='shop' && $value['grade']==0)?"&nbsp;<a style=\"color:red;\" href=\"{$_SERVER[SCRIPT_NAME]}?action=edit&m=$mname&itemid={$value[itemid]}&op=adminview&optpass=1\">$alang[update]</a>":'').(pkperm('isadmin') && $value['updateverify'] == 1?"&nbsp;<a style=\"color:red;\" href=\"{$_SERVER[SCRIPT_NAME]}?action=edit&m=$mname&itemid={$value[itemid]}&op=adminview&updatepass=1\">$alang[update]</a>":'').($value['updateverify'] == 1?' '.$_SGLOBAL['shopgrade'][5]:'')
		);
	}
	if ($mname!='shop' && pkperm('isadmin')) {
		$rowarr[4] = "<a href=\"{$_SERVER[SCRIPT_NAME]}?action=list&m={$mname}&shopid={$value['shopid']}&filtersubmit=GO\" title=\"$value[title]\">".cutstr($value[title],24)."</a>";
		$rowarr[5] = !empty($value['catid'])?$cats[$value['catid']]['name']:lang($mname.'_nocatid');
		unset($rowarr[6],$rowarr[7]);
	}
	$mlist = showtablerow('', $tdclassarr, $rowarr, true);
	return $mlist;
}

function showlistsearch($mname) {
	global $_G, $categorylist, $_GET, $_SERVER, $_SGLOBAL, $catstr, $opcheckstr, $gradestr;
	$catstr = $opcheckstr = $gradestr = '';
	foreach($_SGLOBAL['shopgrade'] as $key=>$value) {
		$opcheckstr .= '&nbsp; <input class="radio" type="radio" name="opcheck" value="'.$key.'"'.(pkperm('isadmin')?' onClick="showchecktxt('.$key.');"':'').'> '.$value.' &nbsp;';
		$gradestr .= '<option value="'.$key.'" '.($_GET['grade']==$key?'selected="selected"':'').'>'.$value.'</option>';
	}
	//搜索框顯示
	echo '<form method="get" name="listform" id="theform" action="'.$_SERVER['SCRIPT_NAME'].'">';
	echo '<input type="hidden" name="action" value="list" /><input type="hidden" name="m" value="'.$mname.'" />';
	showtableheader($mname.'_search', 'notop search_header');
	echo '
			<tr><td><table class="noborder">
			<tr><td style="text-align:right;vertical-align:middle;">'.lang($mname.'_subject').lang('colon').'</td>
			<td style="width:100px;"><input type="text" name="subject" value="'.$_GET['subject'].'" size="10" /></td>'.
			(($mname=='photo')?'':($mname=='album'?
				('<td style="text-align:right;vertical-align:middle;">'.lang($mname.'_type').lang('colon').'</td>'.'
				<td style="width:70px;"><select name="type"><option value="user" selected="selected">'.lang('album_user').'</option><option value="import">'.lang('album_import').'</option><option value="default">'.lang('album_default').'</option></select></td>'):
				('<td style="text-align:right;vertical-align:middle;">'.lang($mname.'_grade').lang('colon').'</td>'.'
				<td style="width:70px;"><select name="grade"><option value="-1">'.lang('please_select').'</option>'.$gradestr.'</select></td>')
			)).'
			<td style="text-align:right;vertical-align:middle;">'.lang($mname.'_itemid').lang('colon').' </td>
			<td style="width:50px;"><input type="text" name="itemid" value="'.(empty($_GET['itemid'])?'':$_GET['itemid']).'" size="3" /></td>
			<td style="text-align:right;vertical-align:middle;">'.lang('order').lang('colon').' </td>
			<td style="width:80px;">
			<select id="order" name="order">
			<option value="itemid">'.lang($mname.'_dateline').'</option>
			<option value="lastpost">'.lang('lastpost').'</option>'.
			'<option value="viewnum">'.lang('viewnum').'</option>'.
			'<option value="replynum">'.lang('replynum').'</option>'.
			'</select></td>'.
			'<td style="text-align:right;vertical-align:middle;">'.lang('sc').lang('colon').'</td>'.
			'<td style="width:70px;"><select id="sc" name="sc">'.
			'<option value="ASC">'.lang('ASC').'</option>'.
			'<option value="DESC" selected>'.lang('DESC').'</option>'.
			'</select></td>'.
			'<td><input class="btn" type="submit" name="filtersubmit" value="'.lang('search').'" /></td></tr></table>
			<style>
			.noborder td{border:none;vertical-align:middle;}
			</style>
			';
	showtablefooter();
	showformfooter();

}

function showlistsearch_report() {
	global $_G, $_SGLOBAL;

	$typelist=array('shop', 'good', 'consume', 'notice', 'album', 'groupbuy');
	foreach($typelist as $key=>$value) {
		$typestr .= '<option value="'.$value.'">'.lang('report_'.$value).'</option>';
	}
	echo '<form method="POST" name="reportform" id="reportform" action="panel.php?action=report">';
	showtableheader('report_search', 'notop search_header');
	echo '
			<tr><td><table class="noborder">
			<tr><td style="text-align:right;vertical-align:middle;">'.lang('reportusername').lang('colon').'</td>
			<td style="width:100px;"><input type="text" name="username" value="'.$_POST['username'].'" size="10" /></td>
			<td style="text-align:right;vertical-align:middle;">'.lang('reporttype').lang('colon').'</td>
			<td style="width:80px;">
			<select id="reporttype" name="reporttype">
			<option value="" selected="selected">'.lang('please_select').'</option>
			'.$typestr.
			'</select></td>'.
			'<td><input class="btn" type="submit" name="filtersubmit" value="'.lang('search').'" /></td></tr></table>
			<style>
			.noborder td{border:none;vertical-align:middle;}
			</style>
			';
	showtablefooter();
	showformfooter();

}

function showlistsearch_brandlinks() {
	global $_G, $_SGLOBAL;

	echo '<form method="POST" name="brandlinkform" id="brandlinkform" action="panel.php?action=brandlinks">';
	showtableheader('brandlinks_search', 'notop search_header');
	echo '
			<tr><td><table class="noborder">
			<tr><td style="text-align:right;vertical-align:middle;">'.lang('brandlinks_linkid').lang('colon').'</td>
			<td style="width:100px;"><input type="text" name="linkid" value="'.$_POST['linkid'].'" size="10" /></td>
			<td style="text-align:right;vertical-align:middle;">'.lang('brandlinks_name').lang('colon').'</td>
			<td style="width:100px;"><input type="text" name="name" value="'.$_POST['name'].'" size="10" /></td>
			<td><input class="btn" type="submit" name="filtersubmit" value="'.lang('search').'" /></td></tr></table>
			<style>
			.noborder td{border:none;vertical-align:middle;}
			</style>
			';
	showtablefooter();
	showformfooter();

}

function showshop_byletter() {
	global $_G, $_SGLOBAL;

	$letter = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
	$i = 1;
	$shopidarr = array();
	$shopletter = '';
	$shopletter .= '<tr>';
	foreach($letter as $value) {
		$num = DB::result_first("SELECT COUNT(itemid) FROM ".tname("shopitems")." WHERE letter='$value'");
		if($num > 0) {
			$shopletter .= '<td>'.$value.':';
			$query = DB::query("SELECT itemid, subject FROM ".tname("shopitems")." WHERE letter='$value' ORDER BY itemid ASC;");
			while($result = DB::fetch($query)) {
				$shopletter .= '&nbsp;&nbsp;&nbsp;&nbsp;<a href="'.$_SERVER['SCRIPT_NAME'].'?action=edit&m=shop&itemid='.$result['itemid'].'">'.$result['subject'].'</a>';
				$shopidarr[] = $result['itemid'];
			}
			$shopletter .= '</td>';
			if($i % 2 == 0) {
				$shopletter .= '</tr><tr>';
			}
			$i++;
		}
	}
	$shopletter .= '</tr>';
	$shopidstr = !empty($shopidarr) ? implode(',', $shopidarr) : '';
	if(!empty($shopidstr)) {
		$othernum = DB::result_first("SELECT COUNT(itemid) FROM ".tname("shopitems")." WHERE itemid NOT IN ($shopidstr)");
		if($othernum > 0) {
			$shopletter .= '<tr><td>'.lang('other').': ';
			$query = DB::query("SELECT itemid, subject FROM ".tname("shopitems")." WHERE itemid NOT IN ($shopidstr) ORDER BY itemid ASC;");
			while($result = DB::fetch($query)) {
				$shopletter .= '&nbsp;&nbsp;&nbsp;&nbsp;<a href="'.$_SERVER['SCRIPT_NAME'].'?action=edit&m=shop&itemid='.$result['itemid'].'">'.$result['subject'].'</a>';
			}
			$shopletter .= '</td></tr>';
		}
	}
	echo $shopletter;
}

function show_searchfrom_webmaster($mname) {
	global $_G, $_GET, $_COOKIE, $_SC, $_SERVER, $_SGLOBAL, $catstr, $opcheckstr, $gradestr,$cats;

	$catstr = $opcheckstr = $gradestr = '';
	$typelist=array('shop', 'good', 'consume', 'notice');
	$query = DB::query("SELECT * FROM ".tname("shopgroup")." ORDER BY id ASC;");
	while($result = DB::fetch($query)) {
		$catstr .= '<option value="'.$result['id'].'">'.$result['title'].'</option>';
	}
	foreach($_SGLOBAL['shopgrade'] as $key=>$value) {
		$opcheckstr .= '&nbsp; <input class="radio" type="radio" name="opcheck" value="'.$key.'" onClick="showchecktxt('.$key.');"> '.$value.' &nbsp;';
		$gradestr .= '<option value="'.$key.'" '.($_GET['grade']==$key?'selected="selected"':'').'>'.$value.'</option>';
	}

	//搜索框顯示
	echo '<form method="get" name="listform" id="theform" action="'.$_SERVER['SCRIPT_NAME'].'">';
	echo '<style>input {width:250px;}</style><input type="hidden" name="action" value="list" /><input type="hidden" name="m" value="'.$mname.'" />';
	showtableheader($mname.'_search', 'notop');
	$search_items[] = $mname.'_subject'.'::<input type="text" name="subject" value="'.$_GET['subject'].'" size="10" />';
	$search_items[] = $mname.'_username'.'::<input type="text" name="username" value="'.$_GET['username'].'" size="6" />';
	$search_items[] = $mname.'_itemid'.'::<input type="text" name="itemid" value="'.(empty($_GET['itemid'])?'':$_GET['itemid']).'" size="3" />';
	if($mname == 'shop') {
		if(pkperm('isadmin') && !ckfounder($_G['uid'])) {
			foreach($cats as $key=>$value) {
				if(!in_array($key, explode(",", $_SGLOBAL['adminsession']['cpgroupshopcats']))) {
					unset($cats[$key]);
				}
			}
		}
		//所屬用戶組
		$search_items[] = $mname.'_groupid'.'::<select name="groupid" id="shop_incat"><option value="0">'.lang('please_select').'</option>'.$catstr.'</select>';
		//所屬分類
		$search_items[] = $mname.'_catid'.'::'.InteractionCategoryMenu($cats,'catid',null,null);
	} else {
		if($mname=='album' && $_GET['from']=='addphoto') {
			//相冊所屬店舖
			$search_items[] = $mname.'_shopid'.'::<select name="shopid" id="shop_incat"><option value="'.$_G['cookie']['shopid'].'">'.$_G['cookie']['shopid'].'</option></select>';
		} else {
			//所屬店舖
			$search_items[] = 'search_shopid'.'::<input type="text" name="shopid" value="'.(empty($_GET['shopid'])?'':$_GET['shopid']).'" size="6" />';
		}
		if($mname != 'photo') {
			//所屬分類
			$search_items[] = $mname.'_catid'.'::'.InteractionCategoryMenu($cats,'catid',null,null);
		}
	}
	if($mname=='album') {
		//相冊類型默認相冊還是自定義相冊
		$search_items[] = $mname.'_type'.'::<select name="type"><option value="user" selected="selected">'.lang('album_user').'</option><option value="import">'.lang('album_import').'</option><option value="default">'.lang('album_default').'</option></select>';
	} elseif($mname=='photo') {
		//none
	} elseif($mname=="shop") {
		$search_items[] = 'mod_recommend'.
			'::<select id="recommend" name="recommend">'.
			'<option value="" selected="selected">'.lang('all').'</option>'.
			'<option value="yes">'.lang('yes').'</option>'.
			'<option value="no">'.lang('no').'</option>'.
			'</select>';
	} else {
		$search_items[] = $mname.'_grade'.'::<select name="grade"><option value="-1">'.lang('please_select').'</option>'.$gradestr.'</select>';
	}
	$search_items[] = 'order'.
			'::<select id="order" name="order">'.
			'<option value="itemid">'.lang($mname.'_dateline').'</option>'.
			'<option value="lastpost">'.lang('lastpost').'</option>'.
			'<option value="viewnum">'.lang('viewnum').'</option>'.
			'<option value="replynum">'.lang('replynum').'</option>'.
			'</select>';
	$search_items[] = 'sc'.
			'::<select id="sc" name="sc">'.
			'<option value="ASC">'.lang('ASC').'</option>'.
			'<option value="DESC" selected>'.lang('DESC').'</option>'.
			'</select>';
	
	if($mname == 'shop') {
		showshop_byletter();
	}
	foreach($search_items as $k=>$v){
		$tmp = explode('::',$v);
		showsetting($tmp[0], '', '',$tmp[1]);
	}
	// 顯示搜索按鈕
	showsetting('', '', '', '<input style="width:50px;" class="btn" type="submit" name="filtersubmit" value="'.lang('search').'" />');
	showtablefooter();
	showformfooter();

}

function show_searchform_report() {
	global $_GET, $_SERVER, $_SGLOBAL, $catstr, $cats;

	$catstr = $typestr = '';
	$typelist=array('shop', 'good', 'consume', 'notice', 'album', 'groupbuy');
	foreach($typelist as $key=>$value) {
		$typestr .= '<option value="'.$value.'">'.lang('report_'.$value).'</option>';
	}
	$query = DB::query("SELECT * FROM ".tname("reportreasons")." ORDER BY rrid ASC;");
	while($result = DB::fetch($query)) {
		$reasonidstr .= '<option value="'.$result['rrid'].'">'.$result['content'].'</option>';
	}
	$query = DB::query("SELECT * FROM ".tname("shopgroup")." ORDER BY id ASC;");
	while($result = DB::fetch($query)) {
		$catstr .= '<option value="'.$result['id'].'">'.$result['title'].'</option>';
	}
	echo '<form method="get" name="reportform" id="reportform" action="admin.php?action=report">';
	echo '<style>input {width:250px;}</style><input type="hidden" name="action" value="report" />';
	showtableheader('report_search', 'notop');
	$search_items[]=lang('reportusername').'::<input type="text" name="username" value="" size="6" />';
	$search_items[]=lang('search_shopid').'::<input type="text" name="shopid" value="'.(empty($_GET['shopid'])?'':$_GET['shopid']).'" size="6" />';
	$search_items[]=lang('reporttype').'::<select name="reporttype"><option value="" selected="selected">'.lang('please_select').'</option>'.$typestr.'</select>';
	$search_items[]=lang('reasonid').'::<select name="reasonid"><option value="" selected="selected">'.lang('please_select').'</option>'.$reasonidstr.'</select>';

	foreach($search_items as $k=>$v){
		$tmp = explode('::',$v);
		showsetting($tmp[0], '', '',$tmp[1]);
	}
	echo '<input type="hidden" name="formhash" value="'.formhash().'" />';
	showsetting('', '', '','<input style="width:50px;" class="btn" type="submit" name="filtersubmit" value="'.lang('search').'" />');
	showtablefooter();
	showformfooter();

}

function show_searchform_managelog() {
	global $_GET, $_SERVER, $_SGLOBAL, $catstr, $cats;

	$catstr = $typestr = '';
	$typelist=array('shop', 'good', 'consume', 'notice', 'album', 'groupbuy');
	foreach($typelist as $key=>$value) {
		$typestr .= '<option value="'.$value.'">'.lang('managelog_'.$value).'</option>';
	}
	$query = DB::query("SELECT * FROM ".tname("shopgroup")." ORDER BY id ASC;");
	while($result = DB::fetch($query)) {
		$catstr .= '<option value="'.$result['id'].'">'.$result['title'].'</option>';
	}
	echo '<form method="get" name="managelogform" id="managelogform" action="admin.php?action=managelog">';
	echo '<style>input {width:250px;}</style><input type="hidden" name="action" value="managelog" />';
	showtableheader('managelog_search', 'notop');
	$search_items[]=lang('search_shopid').'::<input type="text" name="shopid" value="'.(empty($_GET['shopid'])?'':$_GET['shopid']).'" size="6" />';
	$search_items[]=lang('managelogtype').'::<select name="managelogtype"><option value="" selected="selected">'.lang('please_select').'</option>'.$typestr.'</select>';

	foreach($search_items as $k=>$v){
		$tmp=explode('::',$v);
		showsetting($tmp[0], '', '',$tmp[1]);
	}
	echo '<input type="hidden" name="formhash" value="'.formhash().'" />';
	showsetting('', '', '','<input style="width:50px;" class="btn" type="submit" name="filtersubmit" value="'.lang('search').'" />');
	showtablefooter();
	showformfooter();

}

function show_searchform_brandlinks() {
	global $_GET, $_SERVER, $_SGLOBAL, $catstr, $cats;

	$catstr = $typestr = '';
	$query = DB::query("SELECT * FROM ".tname("shopgroup")." ORDER BY id ASC;");
	while($result = DB::fetch($query)) {
		$catstr .= '<option value="'.$result['id'].'">'.$result['title'].'</option>';
	}
	echo '<form method="get" name="brandlinksform" id="brandlinksform" action="'.$_SERVER['SCRIPT_NAME'].'">';
	echo '<style>input {width:250px;}</style><input type="hidden" name="action" value="brandlinks" />';
	showtableheader('brandlinks_search', 'notop');
	$search_items[]=lang('brandlinks_linkid').'::<input type="text" name="linkid" value="'.(empty($_GET['linkid'])?'':$_GET['linkid']).'" size="3" />';
	$search_items[]=lang('brandlinks_name').'::<input type="text" name="name" value="" size="6" />';
	$search_items[]=lang('search_shopid').'::<input type="text" name="shopid" value="'.(empty($_GET['shopid'])?'':$_GET['shopid']).'" size="6" />';

	foreach($search_items as $k=>$v){
		$tmp=explode('::',$v);
		showsetting($tmp[0], '', '',$tmp[1]);
	}
	echo '<input type="hidden" name="formhash" value="'.formhash().'" />';
	showsetting('', '', '','<input style="width:50px;" class="btn" type="submit" name="filtersubmit" value="'.lang('search').'" />');
	showtablefooter();
	showformfooter();

}

function showlistnormal($mname, $mlist, $multipage) {
		showformheader('batchmod&m='.$mname);//批量操作的form表頭
		if($mname=='shop'){$len_cat=60;$len_opt=115;$len_status=60;}else{$len_cat=160;$len_opt=80;$len_status=60;}

	//數據顯示
	showtableheader($mname.'_listresult', 'notop');
	if(pkperm('isadmin')) {
		$subtitlearr = array(
			0 => '<input type="checkbox" onclick="checkall(this.form, \'item\')" name="chkall" checked>_||_30',
			1 => lang($mname.'_itemid').'_||_30',
			2 => lang($mname.'_displayorder').'_||_40',
			3 => lang($mname.'_subject'),
			4 => lang($mname.'groupid').'_||_'.$len_cat,
			5 => lang($mname.'_catid').'_||_'.$len_cat,
			6 => lang($mname.'_isdiscount').'_||_80',
			7 => lang($mname.'_allowreply').'_||_80',
			8 => lang($mname.'_username').'_||_60',
			9 => lang($mname.'_dateline').'_||_80',
			10 => lang($mname.'_stats').'_||_'.$len_status,
			11 => lang($mname.'_operation'.'_||_'.$len_opt)
		);
	}else{
		$subtitlearr = array(
			0 => '<input type="checkbox" onclick="checkall(this.form, \'item\')" name="chkall">_||_30',
			1 => lang($mname.'_itemid').'_||_30',
			2 => lang($mname.'_displayorder').'_||_40',
			3 => lang($mname.'_subject'),
			4 => lang($mname.'catid').'_||_80',
			5 => lang($mname.'_dateline').'_||_80',
			6 => lang($mname.'_stats').'_||_'.$len_status,
			7 => lang($mname.'_operation'.'_||_'.$len_opt)
		);
	}
	if($mname!='shop' && pkperm('isadmin')) {
		unset($subtitlearr[5],$subtitlearr[7]);
		$subtitlearr[4] = lang($mname.'_shopid').'_||_'.$len_cat;
		$subtitlearr[6] = lang($mname.'_catid').'_||_60';
	}
	showsubtitle($subtitlearr);
	echo $mlist;
	showtablefooter();
	echo $multipage;
}

function showlistmod($mname) {
	global $_G, $catstr, $opcheckstr, $gradestr, $_SGLOBAL, $_SC;
	//下拉框拼湊
	$opcheckstr="";
	foreach($_SGLOBAL['shopgrade'] as $key=>$value) {
		if(($_G['myshopstatus'] == 'verified') && ($key == 0 || $key == 5 || (!pkperm('isadmin') && $key == 1))) {
		} else {
			$opcheckstr .= '&nbsp; <input class="radio" type="radio" name="opcheck" value="'.$key.'"'.(pkperm('isadmin')?' onClick="showchecktxt('.$key.');"':'').'> '.$value.' &nbsp;';
			$gradestr .= '<option value="'.$key.'" '.($_GET['grade']==$key?'selected="selected"':'').'>'.$value.'</option>';
		}
	}
	//批量操作方法
	$opt_master_pass = ($_GET['optpass'] == 1) ? true : false; //快速操作，管理員點擊待審核列表時，只出現更改審核狀態的設置
	$update_master_pass = ($_GET['updatepass'] == 1) ? true : false; //審核通過店舖，修改信息後站長審核頁面。
	showtableheader(lang('operation_form'), 'nobottom');
	if(!$opt_master_pass && !$update_master_pass) {
		showsubtitle(array('', 'operation', 'option'));
	}

	$checktextjavascript = '
		<script type="text/javascript" charset="'.$_G['charset'].'">
		function showchecktxt(cktxtid) {
			if($("#newgroupid").length>0) {
				$("#newgroupfield").css("display","none");
			}
			if(cktxtid==1) {
				$("#check_trid").css("display",""); $("#check_txtid").text("'.lang('mod_checktxt_fail').'");
			} else if(cktxtid==2) {
				$("#check_trid").css("display",""); $("#check_txtid").text("'.lang('mod_checktxt_close').'");
			} else if(cktxtid==3) {
				if($("#newgroupid").length>0) {
					$("#newgroupfield").css("display","");
				} else {
					var newgroupid = $("#groupid").clone();
					newgroupid[0].id= "newgroupid";
					newgroupid[0].name= "newgroupid";
					$("#newgroupselect").before(newgroupid);
					$("#newgroupfield").css("display","");
				}
				$("#check_trid").css("display",""); $("#check_txtid").text("'.lang('mod_checktxt_pass').'");
			} else if(cktxtid==4) {
				$("#check_trid").css("display",""); $("#check_txtid").text("'.lang('mod_checktxt_recommend').'");
			} else {
				$("#check_trid").css("display","none"); $("#check_txtid").text("");
			}
			}
			$(function() {
				$("#submit_listsubmit").click(function() {
					var operations = $(":radio[name=\'operation\']");
					if(operations.length>0) {
						for(var i = 0; i < operations.length; i++) {
							if(operations[i].checked) {
								return true;
							}
						}
					}
					alert("'.lang("operation_mustselected").'");
					return false;
				});
			});
		</script>';

	// 如果進入的是待審核快速操作選項
	if($opt_master_pass) {

		if($mname == 'shop') {
			showtablerow('', array('style="display:none;"', 'class="rowform" style="width:auto;"'), array(
						'<input class="radio" type="radio" checked name="operation" value="check">',
						'&nbsp; <input type="radio" onclick="showchecktxt(3);" value="3" name="opcheck" class="radio">'.lang('pass_update').'&nbsp;&nbsp; <input type="radio" onclick="showchecktxt(1);" value="1" name="opcheck" class="radio">'.lang('del_update')
			));

			showtablerow('id="check_trid" style="display:none;"', array('class="rowform" style="width:auto;"'), array(
						'&nbsp;<textarea rows="6" name="check_txt" id="check_txtid" cols="50" class="tarea"></textarea> <span class="vtop tips2">'.lang('mod_check_textarea_comment').'</span>'
			));

			$catstr = '<select name="newgroupid" id="newgroupid">';
			$query = DB::query("SELECT * FROM ".tname("shopgroup")." ORDER BY id ASC;");
			while($result = DB::fetch($query)) {
				$catstr .= '<option value="'.$result['id'].'">'.$result['title'].'</option>';
			}
			showtablerow('id="newgroupfield" style="display:none;"', array('class="rowform" style="width:auto;"'), array(
						$catstr.'</select> <span id="newgroupselect" class="vtop tips2">'.lang('mod_check_newgroupid_comment').'</span>'
			));
			echo $checktextjavascript;
		} else {
			showtablerow('', array('style="display:none;"', 'class="rowform" style="width:auto;"'), array(
						'<input class="radio" type="radio" checked="checked" name="operation" value="passcheck">',
						'&nbsp; <input type="radio" onclick="showchecktxt(3);" checked="checked" value="3" name="opcheck" class="radio">'.lang('pass_update').' &nbsp; <input type="radio" onclick="showchecktxt(1);" value="1" name="opcheck" class="radio">'.lang('del_update')
			));
			showtablerow('id="check_trid" style="display:;"', array('class="rowform" style="width:auto;"'), array(
						'&nbsp;<textarea rows="6" name="check_txt" id="check_txtid" cols="50" class="tarea">'.lang('mod_update_pass_'.$mname).'</textarea> <span class="vtop tips2">'.lang('mod_check_textarea_comment_'.$mname).'</span>'
			));
			echo $checktextjavascript_ = '<script type="text/javascript" charset="'.$_G['charset'].'">function showchecktxt(cktxtid) {
				if(cktxtid==1) {
					$("#check_trid").css("display",""); $("#check_txtid").text("'.lang('mod_checktxt_refuse_'.$mname).'");
				} else if(cktxtid==3) {
					$("#check_trid").css("display",""); $("#check_txtid").text("'.lang('mod_checktxt_pass_'.$mname).'");
				}
			}
				</script>
			';
		}

	} elseif($update_master_pass) {
		if($mname == 'shop') {
			showtablerow('', array('style="display:none;"', 'class="rowform" style="width:auto;"'), array(
						'<input class="radio" type="radio" checked name="operation" value="passupdate">',
						'&nbsp; <input class="radio" type="radio" checked="checked" name="update" value="1" />'.lang('pass_update').' &nbsp; <input class="radio" type="radio" name="update" value="0" />'.lang('del_update')
			));
			showtablerow('id="check_trid" style="display:;"', array('class="rowform" style="width:auto;"'), array(
						'&nbsp;<textarea rows="6" name="check_txt" id="check_txtid" cols="50" class="tarea">'.lang('mod_update_pass_'.$mname).'</textarea> <span class="vtop tips2">'.lang('mod_check_textarea_comment').'</span>'
			));
		} else {
			showtablerow('', array('style="display:none;"', 'class="rowform" style="width:auto;"'), array(
						'<input class="radio" type="radio" checked name="operation" value="passupdate">',
						'&nbsp; <input class="radio" type="radio" checked="checked" name="update" value="1" />'.lang('pass_update').' &nbsp; <input class="radio" type="radio" name="update" value="0" />'.lang('del_update')
			));
			showtablerow('id="check_trid" style="display:;"', array('class="rowform" style="width:auto;"'), array(
						'&nbsp;<textarea rows="6" name="check_txt" id="check_txtid" cols="50" class="tarea">'.lang('mod_update_pass_'.$mname).'</textarea> <span class="vtop tips2">'.lang('mod_check_textarea_comment_'.$mname).'</span>'
			));

		}
		echo '<script type="text/javascript" charset="'.$_G['charset'].'">
				$(function(e){
					$(":radio[name=\'update\']").click(function(e){
						var update = e.target.value;
						if(update == 0) {
							$("#check_txtid").text("'.lang('mod_update_refuse_'.$mname).'");
						} else {
							$("#check_txtid").text("'.lang('mod_update_pass_'.$mname).'");
						}
					});
				});
		</script>';
	} else {
		// 調整顯示順序,除了圖片外都有顯示順序
		if($mname != 'photo') {
			showtablerow('', array('class="td25"', 'class="td24"', 'class="rowform" style="width:auto;"'), array(
					'<input class="radio" type="radio" name="operation" value="display">',
					lang('mod_displayorder'),
					''
			));
		}
	//一般情況的批量操作選項
		if($mname == 'album') {
			$mycats = array();
			if(pkperm('isadmin')) {
				$mycats = getmodelcategory('album');
			} else {
				$mycats = mymodelcategory('album');
			}
			$please_select = '<select name="catid" id="album_catid" style="width:140px;"><option value="0" selected="selected">'.lang('please_select').'</option>';
			foreach($mycats as $value) {
				$please_select .= '<option value="'.$value['catid'].'" >'.$value['name'].'</option>';
			}
			$please_select .= '</select>';
			showtablerow('', array('class="td25"', 'class="td24"', 'class="rowform" style="width:auto;"'), array(
						'<input class="radio" type="radio" name="operation" value="album_movecat" >',
						lang('mod_album_movecat'),
						InteractionCategoryMenu(getmodelcategory('album'),'catid', null, 1)
			));
		}


		//站長修改店舖組和分類
		if(pkperm('isadmin') && $mname=='shop') {
			echo '<tr class="hover">
					<td class="td25"><input type="radio" value="changecat" name="operation" class="radio"></td>
					<td class="td24">'.lang("modallshopcat").'</td>
					<td style="width: auto;" class="rowform">
					<div id="catdiv" style="width: 700px;">
				';
			$catelist = getmodelcategory('shop');
			echo '<div id="'.shopcat.'div" colspan="2">';
			echo InteractionCategoryMenu(getmodelcategory('shop'),'shopcat', null, null);
			echo '</div></div></td></tr>';
			$catstr = '<select name="groupid" id="groupid">';
			$query = DB::query("SELECT * FROM ".tname("shopgroup")." ORDER BY id ASC;");
			while($result = DB::fetch($query)) {
				$catstr .= '<option value="'.$result['id'].'">'.$result['title'].'</option>';
			}
			showtablerow('', array('class="td25"', 'class="td24"', 'class="rowform" style="width:auto;"'), array(
					'<input class="radio" type="radio" name="operation" value="movecat">',
					lang('mod_shop_changegroup'),
					$catstr.'</select>'
			));
		}

		//站長修改信息所屬店舖
		if(pkperm('isadmin') && $mname!='shop' && $mname!='photo'){
			showtablerow('', array('class="td25"', 'class="td24"', 'class="rowform" style="width:auto;"'), array(
					'<input class="radio" type="radio" name="operation" value="moveshop">',
					lang('mod_'.$mname.'_moveshop'),
					'<input class="number" type="number" name="opshopid">'.lang('mod_moveshop_id')
			));
		}

		//更改店舖狀態
		showtablerow('', array('class="td25"', 'class="td24"', 'class="rowform" style="width:auto;"'), array(
				'<input class="radio" type="radio" name="operation" value="check">',
				lang('mod_check'),
				$opcheckstr
		));

		if($mname=='shop') {
			//店舖狀態短信通知
			showtablerow('id="check_trid" style="display:none;"', array('class="td25"', 'class="td24"', 'class="rowform" style="width:auto;"'), array(
						'',
						lang('mod_check_textarea'),
						'&nbsp;<textarea rows="6" name="check_txt" id="check_txtid" cols="50" class="tarea"></textarea> <span class="vtop tips2">'.lang('mod_check_textarea_comment').'</span>'
			));
			showtablerow('id="newgroupfield" style="display:none;"', array('class="td25"', 'class="td24"', 'class="rowform" style="width:auto;"'), array(
						'','<font color="red">'.lang('shop_newgroupid').'</font>',' <span id="newgroupselect" class="vtop tips2">'.lang('mod_check_newgroupid_comment').'</span>'
			));
			//店舖狀態短信通知js
			echo $checktextjavascript;

			//是否首頁推薦
			showtablerow('', array('class="td25"', 'class="td24"', 'class="rowform" style="width:auto;"'), array(
						'<input class="radio" type="radio" name="operation" value="recommend">',
						lang('mod_recommend'),
						'&nbsp; <input class="radio" type="radio" name="opallowreply" value="1"> '.lang('yes').' &nbsp; &nbsp; <input class="radio" type="radio" name="opallowreply" value="0"> '.lang('no')
						));
			//會員卡
			showtablerow('', array('class="td25"', 'class="td24"', 'class="rowform" style="width:auto;"'), array(
						'<input class="radio" type="radio" name="operation" value="discount">',
						lang('mod_discount'),
						'&nbsp; <input class="radio" type="radio" name="opdiscount" value="1"> '.lang('mod_discount_yes').' &nbsp; &nbsp; <input class="radio" type="radio" name="opdiscount" value="0"> '.lang('mod_discount_no')
			));
			//店舖所有者
			showtablerow('', array('class="td25"', 'class="td24"', 'class="rowform" style="width:auto;"'), array(
						'<input class="radio" type="radio" name="operation" value="owner">',
						lang('mod_owner'),
						'<input class="number" type="number" name="opowner" value="" /> <span style="color:#999;">&nbsp;'.lang('mod_owner_inputuid').'</span>'
			));
		}

		//是否允許評論
		if($mname != 'photo' && $mname != 'album') {
			showtablerow('', array('class="td25"', 'class="td24"', 'class="rowform" style="width:auto;"'), array(
						'<input class="radio" type="radio" name="operation" value="allowreply">',
						lang('mod_allowreply'),
						'&nbsp; <input class="radio" type="radio" name="opallowreply" value="1"> '.lang('mod_allowreply_yes').' &nbsp; &nbsp; <input class="radio" type="radio" name="opallowreply" value="0"> '.lang('mod_allowreply_no')
						));
		}

		//刪除信息
		showtablerow('', array('class="td25"', 'class="td24"', 'class="rowform" style="width:auto;"'), array(
				'<input class="radio" type="radio" name="operation" value="delete">',
				lang('mod_delete'),
				($mname=='shop')?('<input class="checkbox" type="checkbox" name="opdelete" id="opdelete" value="1" checked="checked" /><label for="opdelete"> '.lang('mod_delete_all').'</label>'):''
		));

	}

	showsubmit('listsubmit', 'submit', '');
	showtablefooter();
	showformfooter();
}

function show_cat_shop_linkarea($name='shopid', $string=1) {
	//三級聯動菜單選擇店舖
	global $_G, $_SGLOBAL, $mname, $_SC;
	$catstr = '';
	$query = DB::query('SELECT * FROM '.tname('shopgroup').' WHERE type=\'shop\'');
	while($rs = DB::fetch($query)) {
		$arr_cat[$rs['id']] = $rs['title'];
	}
	foreach($arr_cat as $key=>$value) {
			$catstr .= '<option value="'.$key.'" '.($_GET['catid']==$key?'selected="selected"':'').'>'.$value.'</option>';
	}
	$str = '<select id="cat_up" name="groupid"><option value="0">'.lang('please_select').'</option>'.$catstr.'</select>';
	$str.= '<select name="'.$name.'" id="shop_incat" style="width:140px;"><option value="0" selected="selected">'.lang('please_select').'</option></select>';

	if($string) {
		$str .='
		<script type="text/javascript" charset="'.$_G['charset'].'">
			function changeShopList() {
				$("#shop_incat").load("admin.php?action=ajax&opt=getshop&catid="+$("#cat_up").val());
			}
			if($("#cat_up")[0]) {
				$("#cat_up").change( function(){
					changeShopList();
					if($("#'.$mname.'_catid")[0]) { changeCatList(); }
				});
				if($("#cat_up").val()>0) {
					changeShopList();
				}
			}
			function changeCatList() {
				$("#'.$mname.'_catid").load("admin.php?action=ajax&opt=getCat&type='.$mname.'&catid="+$("#cat_up").val());
			}
		</script>
';
		return $str;
	} else {
		echo $str;
	}
}

//批量修改店舖分類
function show_shop_changecat() {
	global $_G, $_SGLOBAL;

	$arr_cat = getmodelcategory('shop');
	echo '
	<div style="width: 700px;" id="catdiv">
		<select id="catid_0" name="catid">
			<option value="-1" selected="selected">'.lang('attend_cats_select').'</option>';
			foreach($_G['categorylist'] as $cat) {
				if($cat['upid'] == 0) {
					echo '<option value="'.$cat['catid'].'" >'.$cat['pre'].$cat['name'].'</option>';
				}
			}
	echo '
		</select>
		<script type="text/javascript" charset="utf-8">
		$(function() {
			var arr_cats = '.json_encode_region($arr_cat).';
			createmultiselect("catid_0", "catid", arr_cats, "catdiv", "'.lang('attend_cats_select').'");});
		</script>
	</div>';
}

function show_searchfrom_comment($subtype) {
	global $_G, $categorylist, $_GET, $_SERVER, $_SGLOBAL, $catstr, $opcheckstr, $gradestr;
	$catstr = $opcheckstr = $gradestr = '';
	$typelist=array('shop', 'good', 'consume', 'notice', 'groupbuy');
	//下拉框拼湊
	foreach($typelist as $key=>$value) {
		$catstr .= '<option value="'.$value.'">'.lang($subtype.'_'.$value).'</option>';
	}
	//搜索框顯示
	echo '<form method="get" name="listform" id="theform" action="'.$_SERVER['SCRIPT_NAME'].'">';
	echo '<style>input {width:250px;}</style><input type="hidden" name="action" value="'.$subtype.'" /><input type="hidden" name="m" value="'.$mname.'" />';
	showtableheader($subtype.'_search', 'notop');
	$search_items[]=lang($subtype.'_message').'::<input type="text" name="message" value="'.$_GET['message'].'" size="6" />';
	$search_items[]=lang($subtype.'_username').'::<input type="text" name="author" value="'.$_GET['author'].'" size="6" />';
	echo '<input type="hidden" name="formhash" value="'.formhash().'" />';
	if($subtype == 'comment') {
		$search_items[]=lang($subtype.'_type').'::<select name="type"><option value="" selected="selected">'.lang('please_select').'</option>'.$catstr.'</select>';
	}
	$search_items[]=lang('order').
			'::<select id="order" name="order">'.
			'<option value="itemid">'.lang($subtype.'_dateline').'</option>'.
			'</select>';
	$search_items[]=lang('sc').
			'::<select id="sc" name="sc">'.
			'<option value="ASC">'.lang('ASC').'</option>'.
			'<option value="DESC" selected>'.lang('DESC').'</option>'.
			'</select>';
	$search_items[]='::<input style="width:50px;" class="btn" type="submit" name="filtersubmit" value="'.lang('search').'" />';

	foreach($search_items as $k=>$v){
		$tmp=explode('::',$v);
		showsetting($tmp[0], '', '',$tmp[1]);
	}
	showtablefooter();
	showformfooter();
}

function showlistcomment($mlist, $multipage, $subtype) {
	showformheader($subtype);//批量操作的form表頭

	//數據顯示
	showtableheader($subtype.'_listresult', 'notop');
	$subtitlearr = array(
			0 => '<input type="checkbox" onclick="checkall(this.form, \'cid\')" name="chkall" checked>',
			2 => lang($subtype.'_username').'_||_80',
			1 => lang($subtype.'_message'),
			3 => lang($subtype.'_dateline').'_||_80',
			);
	showsubtitle($subtitlearr);
	echo $mlist;
	showtablefooter();
	echo $multipage;
}
function showcommentrow($mname, $value) {
	global $_G, $_SERVER, $_SGLOBAL, $_SSCONFIG;

	$mlist = '';
	$tdclassarr = array(
		0 => 'class="td25"',
		1 => '',
		2 => 'class="td25"'
	);

	$rowarr = array(
		0 => "<input class=\"checkbox\" type=\"checkbox\" name=\"cid[]\" value=\"$value[cid]\" checked/>",
		/*1 => "$value[cid]",
		2 => lang($value[type]),*/
		2 => "$value[author]",
		1 => "$value[message]",
		3 => date('Y-m-d', $value['dateline']),
	);
	$mlist = showtablerow('', $tdclassarr, $rowarr, true);
	return $mlist;
}
function showcommentmod($display=true) {
	global $_G, $catstr, $opcheckstr, $gradestr,$_SGLOBAL,$buffurl;

	showtableheader(lang('operation_form'), 'nobottom');

	showtablerow('', array('width="50px"', ''), array(
				'<input class="radio" type="radio" name="operation" value="delete"><input type="hidden" name="page" value="'.$_GET['page'].'"><input type="hidden" name="buffurl" value="'.$buffurl.'">',
				lang('mod_delete'),
				));
	showsubmit('deletesubmit', 'submit', '');
	showtablefooter();
	showformfooter();
}

function showcronrow($cron) {
	global $_G, $_SERVER, $_SGLOBAL, $_SSCONFIG;

	$mlist = '';
	$tdclassarr = array(
		0 => 'class="td25"',
		1 => '',
		2 => 'class="td25"'
	);

	$rowarr = array(
		0 => "<input class=\"checkbox\" type=\"checkbox\" name=\"cronid[]\" value=\"$cron[cronid]\" />",
		1 => "$cron[name]<br /><b>$cron[filename]</b>",
		2 => $cron['available']==1?lang('yes'):lang('no'),
		//3 => lang($cron[type]),
		4 => "$cron[time]",
		5 => "$cron[lastrun]",
		6 => "$cron[nextrun]",
		7 => "<a href=\"admin.php?action=tool&operation=cron&edit=$cron[cronid]\">".lang("cron_edit")."</a> / <a href=\"admin.php?action=tool&operation=cron&run=$cron[cronid]\">".lang("cron_run")."</a>"
	);
	$mlist = showtablerow('', $tdclassarr, $rowarr, true);
	return $mlist;

}

function showcronlist($cronlist) {
	showformheader('tool&operation=cron');//批量操作的form表頭

	//數據顯示
	showtableheader('cron_listresult', 'notop');
	$subtitlearr = array(
			0 => '<input type="checkbox" onclick="checkall(this.form, \'cronid\')" name="chkall">',
			2 => lang('cron_name').'_||_180',
			1 => lang('cron_available'),
			//3 => lang('cron_type').'_||_80',
			4 => lang('cron_time'),
			5 => lang('cron_lastrun').'_||_180',
			6 => lang('cron_nextrun').'_||_180'
			);
	showsubtitle($subtitlearr);
	echo $cronlist;
	echo '<tr><td>'.lang('add_new').'</td><td colspan="10"><input type="text" size="20" value="" name="newname" class="txt"></td></tr>';
	showtablefooter();
	echo $multipage;

}

/**
* 關聯信息JS
*/
function showrelatedinfojs($type, $groupid, $itemid, $shopid, $file = 'panel') {
	global $_GET;

	echo '
	<script language="JavaScript">
		function changetypelist() {
			$("#typecatid").load("'.$file.'.php?action=ajax&opt=getallCat&groupid='.$groupid.'&type="+$("#relatedtype").val());
		}
		function search() {
			if($("#relatedtype").val() == 0) {
				alert(\''.lang('related_select_relatedtype').'\');
				return false;
			}
			$("#source").load("'.$file.'.php?action=ajax&opt=search&type='.$type.'&itemid='.$itemid.'&shopid='.$shopid.'&catid="+$("#typecatid").val()+"&relatedtype="+$("#relatedtype").val()+"&keyword="+encodeURIComponent($("#keyword").val()));
		}
		if($("#relatedtype")[0]) {
			$("#relatedtype").change(function(){
				changetypelist();
			});
			if($("#relatedtype").val() != 0) {
				changetypelist();
			}
		}
		$(function(){
			//移到右邊
			$("#source_add").click(function() {
				$("#source option:selected").remove().appendTo("#related");
				return false;
			});
			//移到左邊
			$("#target_remove").click(function() {
				$("#related option:selected").remove().appendTo("#source");
				return false;
			});
			//雙擊選項
			$("#source").dblclick(function(){
				$("option:selected",this).remove().appendTo("#related");
				return false;
			});
			//雙擊選項
			$("#related").dblclick(function(){
				$("option:selected",this).remove().appendTo("#source");
				return false;
			});
			$("#submit_settingsubmit").click(function(){
				var sel = $("#related")[0].options;
				var values = \'\';
				var dot = \'\';
				for(var i = 0; i < sel.length; i++) {
					var value = sel[i].value;
					values += dot+value;
					dot = \',\';
				}
				$("#relatedobject")[0].value=values;
				return true;
			});
		});
	</script>';
}

/**
* 關聯信息表單
*/
function showrelatedinfo($type) {
	global $_G, $_SGLOBAL, $relatedarr;

	$relatedtypes = array('album', 'consume', 'good', 'notice', 'groupbuy');
	echo '<tr>
			<td class="td27" colspan="2">'.lang('relatedinfo').'</td>
		</tr>
		<tr>
			<td colspan="3">
			<select id="relatedtype" name="relatedtype">
				<option value="0">'.lang('related_type').'</option>';
	foreach($relatedtypes as $typename) {
		echo '<option value="'.$typename.'">'.lang('header_'.$typename).'</option>';
	}
	echo '
			</select>
			<select id="typecatid" name="typecatid">
				<option value="0">'.lang('relatedtype_cat').'</option>
			</select>
			<input type="text" name="keyword" id="keyword" /><input type="button" value="'.lang('related_button_search').'"  class="btn" onclick="search()" />
			</td>
		</tr>
		<tr>
			<th>'.lang('related_search_result').'</th>
			<th>'.lang($type.'_relatedobject').'</th>
		</tr>
		<tr>
			<td>
				<select multiple="true" name="source" id="source" style="width: 380px; height: 160px;">
				</select>
				<br>
				<a id="source_add" href="#">'.lang('related_add_relatedobject').'>></a>
			</td>
			<td>
				<select name="related" id="related" multiple style="width: 380px; height: 160px;">';
	if(!empty($relatedarr)) {
		foreach($relatedarr as $related) {
			echo '<option value="'.$related['type'].'@'.$related['itemid'].'">'.$related['subject'].'</option>\n';
		}
	}
	echo '
				</select>
				<br>
				<a id="target_remove" href="#">&lt;&lt;'.lang('related_delete_relatedobject').'</a>
			</td>
			<input type="hidden" id="relatedobject" name="relatedobject" value="">
		</tr>';
}

/**
* 添加消費卷系統生成和個人上傳表單
*/
function showconsumemaker($radio=array(), $file='admin') {
	global $_G, $_SGLOBAL, $_SSCONFIG, $editvalue;

	if(isset($editvalue['imagetype'])) {
		if($editvalue['imagetype'] == 1 && empty($radio[0])) {
			$editvalue['imagetype'] = 0;
		} elseif($editvalue['imagetype'] == 0 && empty($radio[1])) {
			$editvalue['imagetype'] = 1;
		}
	} else {
		$editvalue['imagetype'] = !empty($radio[0]) ? 1 : 0;
	}
	$editvalue['imagetype'] = isset($editvalue['imagetype']) ? $editvalue['imagetype'] : !empty($radio[0]) ? 1 : 0;
	showsetting('consume_subjectimage', array('imagetype', $radio, true), $editvalue['imagetype'], 'mradio');
	if(!empty($radio[0])) {
		showtagheader('tbody', 'uploadimg', $editvalue['imagetype'], 'sub');
		pklabel(array('type'=>'file', 'alang'=>'', 'name'=>'subjectimage', 'value'=>$editvalue['subjectimage'], 'fileurl'=>A_URL.'/'.$editvalue['subjectimage']));
		showtagfooter('tbody');
	}
	if(!empty($radio[1])) {
		if($_GET['action'] == 'add') {
			$shopid = $_SGLOBAL['panelinfo']['itemid'];
			$hotline = $_SGLOBAL['panelinfo']['tel'];
			$address = $_SGLOBAL['panelinfo']['address'];
		} else {
			$shopid = $editvalue['shopid'];
			$shopinfo = DB::fetch(DB::query("SELECT tel, address FROM ".tname('shopitems')." WHERE itemid='$shopid'"));
			$hotline = $shopinfo['tel'];
			$address = $shopinfo['address'];
		}
		showtagheader('tbody', 'createimg', !$editvalue['imagetype'], 'sub');
		echo '<tr style="display:none;">
				<td class="vtop rowform">
					<textarea class="tarea" cols="50" id="address" name="address" rows="6">'.$address.'</textarea>
				</td>
				<td class="vtop tips2">
				</td>
			</tr>';
		$dir = opendir(B_ROOT.'static/image/consume/thumb');
		echo '<tr class="noborder"><td colspan="2"><ul id="shop_album_list">';
		while($consumeimgtpl = readdir($dir)) {
			if(strtolower(fileext($consumeimgtpl)) == 'jpg') {
				$imgtplvalue = substr($consumeimgtpl, 0, strpos($consumeimgtpl, '.'));
				echo '<li>
							<a target="_blank" href="static/image/consume/'.$imgtplvalue.'.jpg'.'"><img style="width: 192px; height: 119px;" alt="'.lang('theme_'.$entry.'_name').'" src="static/image/consume/thumb/'.$imgtplvalue.'.jpg'.'"></a>
							<div class="album_desc">
								<input class="radio" type="radio" name="imgtplid" value="'.intval($imgtplvalue).'"';
				if(!empty($editvalue['imgtplid'])) {
					if($editvalue['imgtplid'] == $imgtplvalue) {
						echo 'checked';
					}
				} else {
					$checked = $imgtplvalue == 1 ? 'checked' : '';
					echo $checked;
				}
				echo '>
							</div>
						</li>
				';
			}
		}
		echo '</ul></td></tr>';
		echo '<tr>
				<td colspan="15">
					<script type="text/javascript" charset="'.$_G['charset'].'">
						function previewimg() {
							$("#previewimg").load("'.$file.'.php?action=ajax&opt=previewconsume&shopid='.$shopid.'&id="+$("*[name=\'imgtplid\']:checked").val()+"&coupon_title="+encodeURIComponent($("*[name=\'subject\']").val())+"&brief="+encodeURIComponent($("*[name=\'message\']").val())+"&exception="+encodeURIComponent($("*[name=\'exception\']").val())+"&begin_date="+$("*[name=\'validity_start\']").val()+"&end_date="+$("*[name=\'validity_end\']").val());
						}
					</script>
					<div class="fixsel">
						<input type="button" value="'.lang('preview').'" title="'.lang('consume_preview_title').'" name="previewsubmit" id="previewsubmit" class="btn" onclick="previewimg();">
					</div>
					<div id="previewimg">
					</div>
				</td>
			</tr>';
		showtagfooter('tbody');
	}
}

function bind_ajax_form($form = 'cpform'){
	echo '<script type="text/javascript" charset="'.$GLOBALS['_SC']['charset'].'">bindform("'.$form.'");</script>';
}
/**
 * 顯示該模型下允許使用的分類
 *
 * @param string $type 模型
 */
function showfieldform($type) {
	global $_G, $group;

	$categorylist = getcategory($type);
	foreach($categorylist as $cid=>$cate) {
		$catarr[$cate['upid']][$cid] = $cid;
	}
	$arr = getchilds($catarr, 0);
	echo '<tr><td class="td27" colspan="2" style="border:none;"><div class="fieldform" id="fieldform_'.$type.'"><div style="border-bottom: 1px dotted #DEEFFB; height:20px; line-height:20px;">'.lang('group_'.$type).'</div>'.showcatelist($arr,$categorylist,0,$group[$type.'_field'],$type).'</div>';
	echo '<style>.fieldform{padding-left:20px;} .fieldform li {padding:5px 0 5px 0;}</style>';
	echo '</td></tr>';
}

function getchilds($all,$cid) {
    if(!empty($all[$cid])&& is_array($all[$cid])) {
        foreach($all[$cid] as $cc) {
            $arr[$cc] = getchilds($all,$cc);
        }
        return $arr;
    } else {
        return false;
    }
}

function showcatelist($arr,$categorylist,$a=0,$selected,$type) {
    $str ='';
    if(is_array($arr)) {
       
        if(!empty($a)||is_array($arr[$a]))
            $str .= '<div><ul><li><span>'.$categorylist[$a]['pre'].'<input type="checkbox" name="'.$type.'_field[]" value="'.$categorylist[$a]['catid'].'"'.((in_array($categorylist[$a]['catid'], $selected) || $selected[0] == 'all' || $_GET['op']=='add')?' checked=true':'').' upid="'.$categorylist[$a]['upid'].'" /><a href="javascript:void(0)">+</a>'.$categorylist[$a]['name'].'</span><div style="display:none;"><ul>';
        foreach($arr as $cc=>$aaa) {
            $str .= showcatelist($aaa,$categorylist,$cc,$selected,$type);
        }
        if(!empty($a)||is_array($arr[$a]))
            $str .= '</ul></div></li></ul></div>';
    } else {
        if($categorylist[$a]['upid']==0)
            $str .= '<div><ul>';    
        $str .= '<li><span>'.$categorylist[$a]['pre'].'<input type="checkbox" name="'.$type.'_field[]" value="'.$categorylist[$a]['catid'].'"'.((in_array($categorylist[$a]['catid'], $selected) || $selected[0] == 'all' || $_GET['op']=='add')?' checked=checked':'').' upid="'.$categorylist[$a]['upid'].'" />'.$categorylist[$a]['name'].'</span></li>';
        if($categorylist[$a]['upid']==0)
            $str .= '</ul></div>';   
    }
    return $str;
}
function showjscatefield() {
	echo '<script>
$(".fieldform input").click(function() {
    var span = $(this).parent().parent().find(\'div\');
    if(span.length > 0) {
        //$(span[0]).toggle();
        var ips = $(span[0]).find(\'input\');
        if(this.checked == true) {
            var status = true;
            //$(span[0]).show();
        } else {
            var status = false;
            //$(span[0]).hide();
        }
        for(var i = 0; i < ips.length; i++) {
            ips[i].checked = status;
        }
    }
});
$(".fieldform input").change(function() {
        //alert(\'change\');
        var upid = $(this).attr("upid");
        if($(".fieldform input[upid="+upid+"][checked]").length == 0) {
           $(".fieldform input[value="+upid+"]").attr("checked",false);
        } else {
           $(".fieldform input[value="+upid+"]").attr("checked",true);
        }
        $(".fieldform input[value="+upid+"]").change();
});
$("a").click(function() {
    var span = $(this).parent().parent().find(\'div\');
    if(span.length > 0) {
        if($(span[0]).css(\'display\') == \'none\') {
            $(span[0]).show();
            $(this).text("-");
        } else {
            $(span[0]).hide();
            $(this).text("+");       
        }
        
    }
    return false;
});
</script>';
}
?>