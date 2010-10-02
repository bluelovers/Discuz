<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: class_template.php 16868 2010-09-16 05:06:28Z cnteacher $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class template {

	var $subtemplates = array();
	var $csscurmodules = '';
	var $replacecode = array('search' => array(), 'replace' => array());
	var $blocks = array();
	var $language = array();
	var $file = '';

	// bluelovers
	var $subtemplates2 = array();
	// bluelovers

	function parse_template($tplfile, $templateid, $tpldir, $file, $cachefile) {
		$basefile = basename(DISCUZ_ROOT.$tplfile, '.htm');
		$file == 'common/header' && defined('CURMODULE') && CURMODULE && $file = 'common/header_'.CURMODULE;
		$this->file = $file;

		if(!@$fp = fopen(DISCUZ_ROOT.$tplfile, 'r')) {
			$tpl = $tpldir.'/'.$file.'.htm';
			$tplfile = $tplfile != $tpl ? $tpl.'", "'.$tplfile : $tplfile;
			$this->error('template_notfound', $tplfile);
		}

		$template = @fread($fp, filesize(DISCUZ_ROOT.$tplfile));
		fclose($fp);

		$var_regexp = "((\\\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(\-\>)?[a-zA-Z0-9_\x7f-\xff]*)(\[[a-zA-Z0-9_\-\.\"\'\[\]\$\x7f-\xff]+\])*)";
		$const_regexp = "([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)";

		$headerexists = preg_match("/{(sub)?template\s+[\w\/]+?header\}/", $template);
		$this->subtemplates = array();
		for($i = 1; $i <= 3; $i++) {
			// bluelovers
			$template = preg_replace("/\<\!\-\-\{(.+?)\}\-\-\>/s", "{\\1}", $template);

			if(strexists($template, '{subtpl')) {
				$template = preg_replace("/[\n\r\t]*\{subtpl\s+([a-z0-9_:\/]+)\}[\n\r\t]*/ies", "\$this->loadsubtemplate2('\\1')", $template);
				if ($i >= 3) $i--;
			}
			// bluelovers
			if(strexists($template, '{subtemplate')) {
				$template = preg_replace("/[\n\r\t]*(\<\!\-\-)?\{subtemplate\s+([a-z0-9_:\/]+)\}(\-\-\>)?[\n\r\t]*/ies", "\$this->loadsubtemplate('\\2')", $template);
//				$template = preg_replace("/[\n\r\t]*\{subtemplate\s+([a-z0-9_:\/]+)\}[\n\r\t]*/ies", "\$this->loadsubtemplate('\\1')", $template);
				if ($i >= 3) $i--;
			}
		}

		$template = preg_replace("/([\n\r]+)\t+/s", "\\1", $template);
		$template = preg_replace("/\<\!\-\-\{(.+?)\}\-\-\>/s", "{\\1}", $template);
		$template = preg_replace("/\{lang\s+(.+?)\}/ies", "\$this->languagevar('\\1')", $template);

		// bluelovers
		$template = preg_replace("/\{lang:html\s+([^,]+?)(\s*,\s*ENT_QUOTES\s*)?\}/ies", "\$this->languagevar('\\1',1\\2)", $template);
		// bluelovers

		$template = preg_replace("/[\n\r\t]*\{block\/(\d+?)\}[\n\r\t]*/ie", "\$this->blocktags('\\1')", $template);
		$template = preg_replace("/[\n\r\t]*\{blockdata\/(\d+?)\}[\n\r\t]*/ie", "\$this->blockdatatags('\\1')", $template);
		$template = preg_replace("/[\n\r\t]*\{ad\/(.+?)\}[\n\r\t]*/ie", "\$this->adtags('\\1')", $template);
		$template = preg_replace("/[\n\r\t]*\{ad\s+([a-zA-Z0-9_\[\]]+)\/(.+?)\}[\n\r\t]*/ie", "\$this->adtags('\\2', '\\1')", $template);
		$template = preg_replace("/[\n\r\t]*\{date\((.+?)\)\}[\n\r\t]*/ie", "\$this->datetags('\\1')", $template);
		$template = preg_replace("/[\n\r\t]*\{avatar\((.+?)\)\}[\n\r\t]*/ie", "\$this->avatartags('\\1')", $template);
		$template = preg_replace("/[\n\r\t]*\{eval\s+(.+?)\s*\}[\n\r\t]*/ies", "\$this->evaltags('\\1')", $template);
		$template = preg_replace("/[\n\r\t]*\{csstemplate\}[\n\r\t]*/ies", "\$this->loadcsstemplate('\\1')", $template);
		$template = str_replace("{LF}", "<?=\"\\n\"?>", $template);
		$template = preg_replace("/\{(\\\$[a-zA-Z0-9_\-\>\[\]\'\"\$\.\x7f-\xff]+)\}/s", "<?=\\1?>", $template);
		$template = preg_replace("/\{hook\/(\w+?)(\s+(.+?))?\}/ie", "\$this->hooktags('\\1', '\\3')", $template);
		$template = preg_replace("/$var_regexp/es", "template::addquote('<?=\\1?>')", $template);
		$template = preg_replace("/\<\?\=\<\?\=$var_regexp\?\>\?\>/es", "\$this->addquote('<?=\\1?>')", $template);

		$headeradd = $headerexists ? "hookscriptoutput('$basefile');" : '';
		if(!empty($this->subtemplates)) {
			$headeradd .= "\n0\n";
			foreach($this->subtemplates as $fname) {
				$headeradd .= "|| checktplrefresh('$tplfile', '$fname', ".time().", '$templateid', '$cachefile', '$tpldir', '$file')\n";
			}
			$headeradd .= ';';
		}

		// bluelovers
		if(!empty($this->subtemplates2)) {
			$headeradd .= "\n/*\n";
			foreach($this->subtemplates2 as $fname) {
				$headeradd .= "subtpl_add: $fname\n";
			}
			$headeradd .= '*/;'."\n";
		}
		// bluelovers

		if(!empty($this->blocks)) {
			$headeradd .= "\n";
			$headeradd .= "block_get('".implode(',', $this->blocks)."');";
		}

		// bluelovers
		$find = $replace = array();

		$var = '(\$[a-zA-Z_][a-zA-Z0-9_\->\.\[\]\$]*)';

		$find[] = "/[\n\r\t]*\{for_option(:|\s+)(\S+?)\s+(\S+?)\s+(\S+?)\s+(\S+?)\}[\n\r\t]*(.+?)[\n\r\t]*\{\/for_option\}[\n\r\t]*/ies";
		$replace[] = "\$this->addquote('<? if(is_array(\\2)) foreach (\\2 as \$_k_ => \\4) { \$_s_ = ((\\4[\\3] == \\5 || @in_array(\\4[\\3], \\5)) ? \' selected class=\"tpl_select\"\':\'\'); ?>','\\6<? } ?>')";

		$find[] = "/[\n\r\t]*\{option(:|\s+)(\S+?)\s+(\S+?)\}[\n\r\t]*(.+?)[\n\r\t]*\{\/option\}[\n\r\t]*/ies";
		$replace[] = "\$this->addquote('<? if(is_array(\\2)) foreach (\\2 as \$_k_ => \$_v_) { \$_s_ = ((\$_k_ == \\3 || @in_array(\$_k_, \\3)) ? \' selected class=\"tpl_select\"\':\'\'); ?>','\\4<? } ?>')";

		$find[] = "/[\n\r\t]*\{for(:|\s+)(\S+?)\s+(\S+?)\s+(\S+?)\}[\n\r\t]*(.+?)[\n\r\t]*\{\/for\}[\n\r\t]*/ies";
		$replace[] = "\$this->addquote('<? for (\$_v_=\\2;\$_v_\\3;\$_v_\\4) { ?>','\\5<? } ?>')";

//		{$metakeywords:strip_tags() ''}
//		<\?=($metakeywords ? strip_tags($metakeywords) :  ''); ?\>
		$find[] = "/[\n\r\t]*\{\<\?\=$var_regexp\?\>\:(\S+?)\((.*?)\)(\s([^\{\}].*?))?\}[\n\r\t]*/ies";
		$replace[] = "\$this->_tpl_func('\\5', '\\1', '\\6', '\\7')";

//		{變量:default 默認值}
		$find[] = "/[\n\r\t]*\{\<\?\=$var_regexp\?\>\:default\s+([^\{\}].*?)\}[\n\r\t]*/ies";
		$replace[] = "\$this->addquote('<?= ((!isset(\\1) || empty(\\1)) ? \\5 : \\1) ?>')";

		$find[] = "/\s+href=(\"|\')(?:(?:javascript\:;)|\#+)\\1/is";
		$replace[] = " href=\\1javascript:void(0);\\1";

		$find[] = "/[\n\r\t]*\{js(?:\:|\s+)(.+?)\}[\n\r\t]*/ies";
		$replace[] = "\$this->stripvtags('<script src=\"<? echo \$_G[\'setting\'][\'jspath\']; ?>\\1?<?=VERHASH?>\" type=\"text/javascript\"></script>')";

		$find[] = "/[\n\r\t]*\{rem(?:\:|\s+)(.+?)\s*\}[\n\r\t]*/ies";
		$replace[] = (defined('DISCUZ_DEBUG') && DISCUZ_DEBUG) ? "\$this->stripvtags(\"\n\".'<!--REM: \\1 //-->'.\"\n\")" : '';

		/**
		 * {變量:float 格式}
		 * 按照指定的格式顯示浮點數
		 * 對於浮點數，本語法可以將變量按照格式所指定的位數設置進行顯示。
		 * 格式寫法為「M.D」，M 代表整數位，D 代表小數位。
		 * 格式允許用變量代替。
		 **/
		$find[] = "/[\n\r\t]*\{\<\?\=$var_regexp\?\>\:float\s+(.+?)\}[\n\r\t]*/ies";
		$replace[] = "\$this->addquote('<?= sprintf(\'%\\5f\', \\1);?>')";

		$find[] = "/[\n\r\t]*\{\<\?\=$var_regexp\?\>\:html\s*\}[\n\r\t]*/ies";
		$replace[] = "\$this->addquote('<?= dhtmlspecialchars(\\1);?>')";

		$find[] = "/[\n\r\t]*\{\<\?\=$var_regexp\?\>\:htmlchar\s*\}[\n\r\t]*/ies";
		$replace[] = "\$this->addquote('<?= dhtmlspecialchars(\\1);?>')";

		$find[] = "/[\n\r\t]*\{\<\?\=$var_regexp\?\>\:htmlstrip\s*\}[\n\r\t]*/ies";
		$replace[] = "\$this->addquote('<?= dhtmlspecialchars(strip_tags(\\1));?>')";

		$find[] = "/[\n\r\t]*\{\<\?\=$var_regexp\?\>\:html\s+(.+?)\}[\n\r\t]*/ies";
		$replace[] = "\$this->addquote('<?= dhtmlspecialchars(\\1, \\5);?>')";

		if ($find && $replace) {
			$template = preg_replace($find, $replace, $template);
		}

		$template = $this->remove_bom($template);
		// bluelovers

		$template = "<? if(!defined('IN_DISCUZ')) exit('Access Denied'); {$headeradd}?>\n$template";

		$template = preg_replace("/[\n\r\t]*\{template\s+([a-z0-9_:\/]+)\}[\n\r\t]*/ies", "\$this->stripvtags('<? include template(\'\\1\'); ?>')", $template);
		$template = preg_replace("/[\n\r\t]*\{template\s+(.+?)\}[\n\r\t]*/ies", "\$this->stripvtags('<? include template(\'\\1\'); ?>')", $template);
		$template = preg_replace("/[\n\r\t]*\{echo\s+(.+?)\}[\n\r\t]*/ies", "\$this->stripvtags('<? echo \\1; ?>')", $template);

		$template = preg_replace("/([\n\r\t]*)\{if\s+(.+?)\}([\n\r\t]*)/ies", "\$this->stripvtags('\\1<? if(\\2) { ?>\\3')", $template);
		$template = preg_replace("/([\n\r\t]*)\{elseif\s+(.+?)\}([\n\r\t]*)/ies", "\$this->stripvtags('\\1<? } elseif(\\2) { ?>\\3')", $template);
		$template = preg_replace("/\{else\}/i", "<? } else { ?>", $template);
		$template = preg_replace("/\{\/if\}/i", "<? } ?>", $template);

		$template = preg_replace("/[\n\r\t]*\{loop\s+(\S+)\s+(\S+)\}[\n\r\t]*/ies", "\$this->stripvtags('<? if(is_array(\\1)) foreach(\\1 as \\2) { ?>')", $template);
		$template = preg_replace("/[\n\r\t]*\{loop\s+(\S+)\s+(\S+)\s+(\S+)\}[\n\r\t]*/ies", "\$this->stripvtags('<? if(is_array(\\1)) foreach(\\1 as \\2 => \\3) { ?>')", $template);
		$template = preg_replace("/\{\/loop\}/i", "<? } ?>", $template);

		$template = preg_replace("/\{$const_regexp\}/s", "<?=\\1?>", $template);
		if(!empty($this->replacecode)) {
			$template = str_replace($this->replacecode['search'], $this->replacecode['replace'], $template);
		}
		$template = preg_replace("/ \?\>[\n\r]*\<\? /s", " ", $template);

		if(!@$fp = fopen(DISCUZ_ROOT.$cachefile, 'w')) {
			$this->error('directory_notfound', dirname(DISCUZ_ROOT.$cachefile));
		}

		$template = preg_replace("/\"(http)?[\w\.\/:]+\?[^\"]+?&[^\"]+?\"/e", "\$this->transamp('\\0')", $template);
		$template = preg_replace("/\<script[^\>]*?src=\"(.+?)\"(.*?)\>(\s*)\<\/script\>/ies", "\$this->stripscriptamp('\\1', '\\2', '\\3')", $template);
		$template = preg_replace("/[\n\r\t]*\{block\s+([a-zA-Z0-9_\[\]]+)\}(.+?)\{\/block\}/ies", "\$this->stripblock('\\1', '\\2')", $template);

		// bluelovers
		$template = preg_replace("/\r\n/s", "\n", $template);
//		$template = preg_replace("/[\r\n]{2,}/s", "\n", $template);
		$template = preg_replace("/\n{2,}/s", "\n", $template);
//		$template = preg_replace("/([\n\r])[\t ]+/s", "\\1", $template);
//		$template = preg_replace("/([\n\r])[\t]+/s", "\\1", $template);
		$template = preg_replace("/\n\t+/s", "\n", $template);
		// bluelovers

		flock($fp, 2);
		fwrite($fp, $template);
		fclose($fp);
	}

	// bluelovers
	function _tpl_func($func, $var, $arg = '', $def = '') {
		$ret = (!empty($arg) ? "$func($var,$arg)" : "$func($var)");
		if (!empty($def)) {
			$ret = "($var ? $ret : $def)";
		}

		return $ret ? $this->addquote('<?='.$ret.'; ?>', '') : '';
	}
	// bluelovers

	function languagevar($var, $html = 0, $quote_style = 0) {
		$vars = explode(':', $var);
		$isplugin = count($vars) == 2;
		if(!$isplugin) {
			!isset($this->language['inner']) && $this->language['inner'] = array();
			$langvar = &$this->language['inner'];
		} else {
			!isset($this->language['plugin'][$vars[0]]) && $this->language['plugin'][$vars[0]] = array();
			$langvar = &$this->language['plugin'][$vars[0]];
			$var = &$vars[1];
		}
		if(!isset($langvar[$var])) {
			$lang = array();
			@include DISCUZ_ROOT.'./source/language/lang_template.php';
			$this->language['inner'] = $lang;
			if(!$isplugin) {
				list($path) = explode('/', $this->file);
				@include DISCUZ_ROOT.'./source/language/'.$path.'/lang_template.php';
				$this->language['inner'] = array_merge($this->language['inner'], $lang);
			} else {
				$templatelang = array();
				@include DISCUZ_ROOT.'./data/plugindata/'.$vars[0].'.lang.php';
				$this->language['plugin'][$vars[0]] = $templatelang[$vars[0]];
			}
		}
		if(isset($langvar[$var])) {
//			return $langvar[$var];
			return $html ? dhtmlspecialchars($langvar[$var]) : $langvar[$var];
		} else {
			return '!'.$var.'!';
		}
	}

	function blocktags($parameter) {
		$bid = intval(trim($parameter));
		$this->blocks[] = $bid;
		$i = count($this->replacecode['search']);
		$this->replacecode['search'][$i] = $search = "<!--BLOCK_TAG_$i-->";
		$this->replacecode['replace'][$i] = "<?php block_display('$bid'); ?>";
		return $search;
	}

	function blockdatatags($parameter) {
		$bid = intval(trim($parameter));
		$this->blocks[] = $bid;
		$i = count($this->replacecode['search']);
		$this->replacecode['search'][$i] = $search = "<!--BLOCKDATA_TAG_$i-->";
		$this->replacecode['replace'][$i] = "";
		return $search;
	}

	function adtags($parameter, $varname = '') {
		$parameter = stripslashes($parameter);
		$i = count($this->replacecode['search']);
		$this->replacecode['search'][$i] = $search = "<!--AD_TAG_$i-->";
		$this->replacecode['replace'][$i] = "<?php ".(!$varname ? 'echo ' : '$'.$varname.'=')."adshow(\"$parameter\"); ?>";
		return $search;
	}

	function datetags($parameter) {
		$parameter = stripslashes($parameter);
		$i = count($this->replacecode['search']);
		$this->replacecode['search'][$i] = $search = "<!--DATE_TAG_$i-->";
		$this->replacecode['replace'][$i] = "<?php echo dgmdate($parameter); ?>";
		return $search;
	}

	function avatartags($parameter) {
		$parameter = stripslashes($parameter);
		$i = count($this->replacecode['search']);
		$this->replacecode['search'][$i] = $search = "<!--AVATAR_TAG_$i-->";
		$this->replacecode['replace'][$i] = "<?php echo avatar($parameter); ?>";
		return $search;
	}

	function evaltags($php) {
		$php = str_replace('\"', '"', $php);
		$i = count($this->replacecode['search']);
		$this->replacecode['search'][$i] = $search = "<!--EVAL_TAG_$i-->";
		$this->replacecode['replace'][$i] = "<?php $php ?>";
		return $search;
	}

	function hooktags($hookid, $key = '') {
		$i = count($this->replacecode['search']);
		$this->replacecode['search'][$i] = $search = "<!--HOOK_TAG_$i-->";

		// bluelovers
		$key_old = $key;
		// bluelovers

		$key = $key !== '' ? "[$key]" : '';
		$dev = '';//for Developer $dev = "echo '[".($key ? 'array' : 'string')." $hookid]';";

		if(defined('DISCUZ_DEBUG') && DISCUZ_DEBUG) {
			$dev = "?><?= '<hook>[".($key ? 'array' : 'string')." $hookid]</hook>';?><?";
		}

		/**
		 * Discuz!X 中開啟嵌入點的方法
		 *
		 * 刪除 //for Developer
		 * 留下 $dev = "echo '[".($key ? 'array' : 'string')." $hookid]';";
		 *
		 * 然後更新緩存即可看到頁面中的所有嵌入點
		 *
		 * string xx 標識返回值是 string
		 * array xx 表示返回值是 array
		 *
		 * 數組 key 的含義請參考相關模版
		 */

		$d1 = $d2 = '';
		$d1 = "Scorpio_Hook::execute('Tpl_Func_hooktags_Before', array(&\$_G['setting']['pluginhooks']['$hookid']$key, '$hookid', ".($key_old != '' ? $key_old : 'null')."), 1);";
		$d2 = "Scorpio_Hook::execute('Tpl_Func_hooktags_After', array(&\$_G['setting']['pluginhooks']['$hookid']$key, '$hookid', ".($key_old != '' ? $key_old : 'null')."), 1);";

		$this->replacecode['replace'][$i] = "<!--Hook: $hookid - Start--><? {$dev}{$d1}if(!empty(\$_G['setting']['pluginhooks']['$hookid']$key)) ?"."><"."?= \$_G['setting']['pluginhooks']['$hookid']$key;{$d2} ?"."><!--Hook: $hookid - End-->";
		return $search;
	}

	function stripphpcode($type, $code) {
		$this->phpcode[$type][] = $code;
		return '{phpcode:'.$type.'/'.(count($this->phpcode[$type]) - 1).'}';
	}

	function loadsubtemplate($file) {
		$tplfile = template($file, 0, '', 1);
		if($content = @implode('', file(DISCUZ_ROOT.$tplfile))) {
			$this->subtemplates[] = $tplfile;
			return $content;
		} else {
			return '<!-- Lost Tpl File: '.$file.' -->';
		}
	}

	// bluelovers
	function loadsubtemplate2($file) {
		$tplfile = template($file, 0, '', 1);
		if($content = @implode('', file(DISCUZ_ROOT.$tplfile))) {
			$this->subtemplates2[] = $tplfile;
			return "\n{rem $file; - Start}\n".$content."\n{eval \$GLOBAL['_subtpl_']['$file'] = 1;}\n{rem $file; - End}\n";
		} else {
			return '<!-- Lost Tpl File: '.$file.' -->';
		}
	}
	// bluelovers

	function loadcsstemplate() {
		global $_G;
		$scriptcss = '<link rel="stylesheet" type="text/css" href="data/cache/style_{STYLEID}_common.css?{VERHASH}" />';
		$content = $this->csscurmodules = '';
		$content = @implode('', file(DISCUZ_ROOT.'./data/cache/style_'.STYLEID.'_module.css'));
		$content = preg_replace("/\[(.+?)\](.*?)\[end\]/ies", "\$this->cssvtags('\\1','\\2')", $content);
		if($this->csscurmodules) {
//			$this->csscurmodules = preg_replace(array('/\s*([,;:\{\}])\s*/', '/[\t\n\r]/', '/\/\*.+?\*\//'), array('\\1', '',''), $this->csscurmodules);;
			$this->csscurmodules = preg_replace(array('/\s*([,;:\{\}])[ \t]*/', ((defined('DISCUZ_DEBUG') && DISCUZ_DEBUG) ? '/[\t]/' : '/[\t\n\r]/'), '/\/\*.+?\*\//'), array('\\1', '',''), $this->csscurmodules);;
			if(@$fp = fopen(DISCUZ_ROOT.'./data/cache/style_'.STYLEID.'_'.$_G['basescript'].'_'.CURMODULE.'.css', 'w')) {
//				fwrite($fp, $this->csscurmodules);
				fwrite($fp, str_replace('\"', '"', $this->csscurmodules));
				fclose($fp);
			} else {
				exit('Can not write to cache files, please check directory ./data/ and ./data/cache/ .');
			}
			$scriptcss .= '<link rel="stylesheet" type="text/css" href="data/cache/style_{STYLEID}_'.$_G['basescript'].'_'.CURMODULE.'.css?{VERHASH}" />';
		}
		$scriptcss .= '{if $_G[uid] && isset($_G[cookie][extstyle])}<link rel="stylesheet" id="css_extstyle" type="text/css" href="$_G[cookie][extstyle]/style.css" />{elseif $_G[style][defaultextstyle]}<link rel="stylesheet" id="css_extstyle" type="text/css" href="$_G[style][defaultextstyle]/style.css" />{/if}';
		return $scriptcss;
	}

	function cssvtags($param, $content) {
		global $_G;
		$modules = explode(',', $param);
		foreach($modules as $module) {
			$module .= '::'; //fix notice
			list($b, $m) = explode('::', $module);
			if($b && $b == $_G['basescript'] && (!$m || $m == CURMODULE)) {
				$this->csscurmodules .= $content;
				return;
			}
		}
		return;
	}

	function transamp($str) {
		$str = str_replace('&', '&amp;', $str);
		$str = str_replace('&amp;amp;', '&amp;', $str);
		$str = str_replace('\"', '"', $str);
		return $str;
	}

	function addquote($var) {
		return str_replace("\\\"", "\"", preg_replace("/\[([a-zA-Z0-9_\-\.\x7f-\xff]+)\]/s", "['\\1']", $var));
	}


	function stripvtags($expr, $statement = '') {
		$expr = str_replace("\\\"", "\"", preg_replace("/\<\?\=(\\\$.+?)\?\>/s", "\\1", $expr));
		$statement = str_replace("\\\"", "\"", $statement);
		return $expr.$statement;
	}

	function stripscriptamp($s, $extra, $text = '') {
		$extra = str_replace('\\"', '"', $extra);

		// bluelovers
		$text = str_replace('\\"', '"', $text);

		if (strpos($extra, "type=\"text/javascript\"") === false) {
			$extra = "type=\"text/javascript\"".$extra;
		}
		$text	= trim($text);
		$extra	= trim($extra);
		// bluelovers

//		$s = str_replace('&amp;', '&', $s);
//		return "<script src=\"$s\" type=\"text/javascript\"$extra></script>";

		$s = str_replace('&amp;', '&', trim($s));
		return "<script src=\"$s\" $extra>$text</script>";
	}

	function stripblock($var, $s) {
		$s = str_replace('\\"', '"', $s);
		$s = preg_replace("/<\?=\\\$(.+?);?\s*?\?>/", "{\$\\1}", $s);

		preg_match_all("/<\?=(.+?);?\s*?\?>/e", $s, $constary);
		$constadd = '';
		$constary[1] = array_unique($constary[1]);
		foreach($constary[1] as $const) {
			$constadd .= '$__'.md5(trim($const)).' = '.$const.';';
		}
		/*
		$s = preg_replace("/<\?=(.+?)\?>/", "{\$__\\1}", $s);
		*/
		$s = preg_replace("/<\?=(.+?);?\s*\?>/e", "\$this->_stripblock('\\1')", $s);
		$s = str_replace('?>', "\n\$$var .= <<<EOF\n", $s);
		$s = str_replace('<?', "\nEOF;\n", $s);
		return "<?\n$constadd\$$var = <<<EOF\n".$s."\nEOF;\n?>";
	}

	function error($message, $tplname) {
		require_once libfile('class/error');
		discuz_error::template_error($message, $tplname);
	}

	// bluelovers
	function _stripblock($var) {
		$var = trim(stripslashes($var));

		return '$__'.md5($var);
	}
	// bluelovers

	// bluelovers
	function remove_bom ($str, $mode = 0){
		switch ($mode) {
			case 1:
				$str = str_replace("\xef\xbb\xbf", '', $str);
			case 2:
				$str = preg_replace("/^\xef\xbb\xbf/", '', $str);
			default:
				if(substr($str, 0,3) == pack("CCC",0xef,0xbb,0xbf)) {
					$str = substr($str, 3);
				}
		}
		return $str;
	}
	// bluelovers

}

?>