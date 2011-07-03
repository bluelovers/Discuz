<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: class_template.php 21577 2011-04-01 02:07:21Z monkey $
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
			// 修正部分可能產生 BUG 的情形
			$template = preg_replace("/\<\!\-\-\{(.+?)\}\-\-\>/s", "{\\1}", $template);

			// 增加 subtpl 子模板
			if(strexists($template, '{subtpl')) {
				$template = preg_replace("/[\n\r\t]*\{subtpl\s+([a-z0-9_:\/]+)\}[\n\r\t]*/ies", "\$this->loadsubtemplate2('\\1')", $template);
				if ($i >= 3) $i--;
			}
			// bluelovers

			if(strexists($template, '{subtemplate')) {
				$template = preg_replace("/[\n\r\t]*\{subtemplate\s+([a-z0-9_:\/]+)\}[\n\r\t]*/ies", "\$this->loadsubtemplate('\\1')", $template);
				// 忘記是做什麼的了
				if ($i >= 3) $i--;
			}
		}

		$template = preg_replace("/([\n\r]+)\t+/s", "\\1", $template);
		$template = preg_replace("/\<\!\-\-\{(.+?)\}\-\-\>/s", "{\\1}", $template);
		$template = preg_replace("/\{lang\s+(.+?)\}/ies", "\$this->languagevar('\\1')", $template);
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
		// 記錄載入的 subtpl
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
		// 擴充模板語法
		$find = $replace = array();

		// {rem 註解內容}
		$find[] = "/[\n\r\t]*\{rem(?:\:|\s+)(.+?)\s*\}[\n\r\t]*/ies";
		$replace[] = (defined('DISCUZ_DEBUG') && DISCUZ_DEBUG) ? "\$this->stripvtags(\"\n\".'<!--REM: \\1 //-->'.\"\n\")" : '';

		if ($find && $replace) {
			$template = preg_replace($find, $replace, $template);
		}

		// 移除 utf8 的 bom 防止出現不該有的空白或造成頁面錯誤
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
		$template = preg_replace("/\<script[^\>]*?src=\"(.+?)\"(.*?)\>\s*\<\/script\>/ies", "\$this->stripscriptamp('\\1', '\\2')", $template);
		$template = preg_replace("/[\n\r\t]*\{block\s+([a-zA-Z0-9_\[\]]+)\}(.+?)\{\/block\}/ies", "\$this->stripblock('\\1', '\\2')", $template);
		$template = preg_replace("/\<\?(\s{1})/is", "<?php\\1", $template);
		$template = preg_replace("/\<\?\=(.+?)\?\>/is", "<?php echo \\1;?>", $template);

		// bluelovers
		// add Event 'Class_template::parse_template:Before_fwrite'
		if (discuz_core::$plugin_support['Scorpio_Event']) {
			Scorpio_Event::instance('Class_'.__METHOD__.':Before_fwrite')
				->run(array(array(
					'template'			=> $template
					, 'cachefile'		=> $cachefile
				)), array(
					'template'			=> &$template
					, 'cachefile'		=> &$cachefile
			));
		}
		// bluelovers

		flock($fp, 2);
		fwrite($fp, $template);
		fclose($fp);
	}

	function languagevar($var) {
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

				if(defined('IN_MOBILE')) {
					list($path) = explode('/', str_replace('mobile/', '', $this->file));
				} else {
					list($path) = explode('/', $this->file);
				}

				@include DISCUZ_ROOT.'./source/language/'.$path.'/lang_template.php';
				$this->language['inner'] = array_merge($this->language['inner'], $lang);

				if(defined('IN_MOBILE')) {
					@include DISCUZ_ROOT.'./source/language/mobile/lang_template.php';
					$this->language['inner'] = array_merge($this->language['inner'], $lang);
				}
			} else {
				global $_G;
				if(empty($_G['config']['plugindeveloper'])) {
					loadcache('pluginlanguage_template');
				} elseif(!isset($_G['cache']['pluginlanguage_template'][$vars[0]]) && preg_match("/^[a-z]+[a-z0-9_]*$/i", $vars[0])) {
					if(@include(DISCUZ_ROOT.'./data/plugindata/'.$vars[0].'.lang.php')) {
						$_G['cache']['pluginlanguage_template'][$vars[0]] = $templatelang[$vars[0]];
					} else {
						loadcache('pluginlanguage_template');
					}
				}
				$this->language['plugin'][$vars[0]] = $_G['cache']['pluginlanguage_template'][$vars[0]];
			}
		}
		if(isset($langvar[$var])) {
			return $langvar[$var];
		} else {
			return '!'.$var.'!';
		}
	}

	function blocktags($parameter) {
		$bid = intval(trim($parameter));
		$this->blocks[] = $bid;
		$i = count($this->replacecode['search']);
		$this->replacecode['search'][$i] = $search = "<!--BLOCK_TAG_$i-->";
		$this->replacecode['replace'][$i] = "<?php block_display('$bid');?>";
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
		$this->replacecode['replace'][$i] = "<?php ".(!$varname ? 'echo ' : '$'.$varname.'=')."adshow(\"$parameter\");?>";
		return $search;
	}

	function datetags($parameter) {
		$parameter = stripslashes($parameter);
		$i = count($this->replacecode['search']);
		$this->replacecode['search'][$i] = $search = "<!--DATE_TAG_$i-->";
		$this->replacecode['replace'][$i] = "<?php echo dgmdate($parameter);?>";
		return $search;
	}

	function avatartags($parameter) {
		$parameter = stripslashes($parameter);
		$i = count($this->replacecode['search']);
		$this->replacecode['search'][$i] = $search = "<!--AVATAR_TAG_$i-->";
		$this->replacecode['replace'][$i] = "<?php echo avatar($parameter);?>";
		return $search;
	}

	function evaltags($php) {
		$php = str_replace('\"', '"', $php);
		$i = count($this->replacecode['search']);
		$this->replacecode['search'][$i] = $search = "<!--EVAL_TAG_$i-->";
		$this->replacecode['replace'][$i] = "<?php $php?>";
		return $search;
	}

	function hooktags($hookid, $key = '') {
		global $_G;
		$i = count($this->replacecode['search']);
		$this->replacecode['search'][$i] = $search = "<!--HOOK_TAG_$i-->";
		$key = $key !== '' ? "[$key]" : '';

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
		 **/
		//for Developer $dev = "echo '[".($key ? 'array' : 'string')." $hookid]';";
		$dev = '';

		if(
			isset($_G['config']['plugindeveloper']) && $_G['config']['plugindeveloper'] == 2
		) {
			$dev = "echo '<hook>[".($key ? 'array' : 'string')." $hookid]</hook>';";

		// bluelovers
		} elseif (defined('DISCUZ_DEBUG') && DISCUZ_DEBUG) {
			$dev = "echo '<hook>[".($key ? 'array' : 'string')." $hookid]</hook>';";
		// bluelovers

		}
		$this->replacecode['replace'][$i] = "<?php {$dev}if(!empty(\$_G['setting']['pluginhooks']['$hookid']$key)) echo \$_G['setting']['pluginhooks']['$hookid']$key;?>";

		// bluelovers
		$this->replacecode['replace'][$i] = "<!--Hook: $hookid - Start-->".$this->replacecode['replace'][$i]."<!--Hook: $hookid - End-->";
		// bluelovers

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
			return '<!-- '.$file.' -->';
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
			$this->csscurmodules = preg_replace(array('/\s*([,;:\{\}])\s*/', '/[\t\n\r]/', '/\/\*.+?\*\//'), array('\\1', '',''), $this->csscurmodules);

			// bluelovers
			// add Event 'Class_template::loadcsstemplate:Before_fwrite'
			if (discuz_core::$plugin_support['Scorpio_Event']) {
				Scorpio_Event::instance('Class_'.__METHOD__.':Before_fwrite')
					->run(array(array(
						'cssdata'			=> $this->csscurmodules
						, 'entry'		=> $_G['basescript'].'_'.CURMODULE,
					)), array(
						'cssdata'			=> &$this->csscurmodules
				));
			}
			// bluelovers

			if(@$fp = fopen(DISCUZ_ROOT.'./data/cache/style_'.STYLEID.'_'.$_G['basescript'].'_'.CURMODULE.'.css', 'w')) {
				fwrite($fp, $this->csscurmodules);
				fclose($fp);
			} else {
				exit('Can not write to cache files, please check directory ./data/ and ./data/cache/ .');
			}
			$scriptcss .= '<link rel="stylesheet" type="text/css" href="data/cache/style_{STYLEID}_'.$_G['basescript'].'_'.CURMODULE.'.css?{VERHASH}" />';
		}
		$scriptcss .= '{if $_G[uid] && isset($_G[cookie][extstyle]) && strpos($_G[cookie][extstyle], TPLDIR) !== false}<link rel="stylesheet" id="css_extstyle" type="text/css" href="$_G[cookie][extstyle]/style.css" />{elseif $_G[style][defaultextstyle]}<link rel="stylesheet" id="css_extstyle" type="text/css" href="$_G[style][defaultextstyle]/style.css" />{/if}';
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

	function stripscriptamp($s, $extra) {
		$extra = str_replace('\\"', '"', $extra);
		$s = str_replace('&amp;', '&', $s);
		return "<script src=\"$s\" type=\"text/javascript\"$extra></script>";
	}

	function stripblock($var, $s) {
		$s = str_replace('\\"', '"', $s);
		$s = preg_replace("/<\?=\\\$(.+?)\?>/", "{\$\\1}", $s);
		preg_match_all("/<\?=(.+?)\?>/e", $s, $constary);
		$constadd = '';
		$constary[1] = array_unique($constary[1]);
		foreach($constary[1] as $const) {
			$constadd .= '$__'.$const.' = '.$const.';';
		}
		$s = preg_replace("/<\?=(.+?)\?>/", "{\$__\\1}", $s);
		$s = str_replace('?>', "\n\$$var .= <<<EOF\n", $s);
		$s = str_replace('<?', "\nEOF;\n", $s);
		return "<?\n$constadd\$$var = <<<EOF\n".$s."\nEOF;\n?>";
	}

	function error($message, $tplname) {
		require_once libfile('class/error');
		discuz_error::template_error($message, $tplname);
	}

	// bluelovers
	function remove_bom($str, $mode = 0){
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