<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: class_template.php 12140 2010-06-30 01:49:41Z monkey $
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

	function parse_template($tplfile, $tpldir, $file, $cachefile) {
		$basefile = basename(DISCUZ_ROOT.$tplfile, '.htm');
		$file == 'common/header' && defined('CURMODULE') && CURMODULE && $file = 'common/header_'.CURMODULE;
		$this->file = $file;

		if(!@$fp = fopen(DISCUZ_ROOT.$tplfile, 'r')) {
			$tpl = $tpldir.'/'.$file.'.htm';
			$tplfile = $tplfile != $tpl ? $tpl.'", "'.$tplfile : $tplfile;
			error('template_notfound', array('$tplfile' => $tplfile));
		}

		$template = @fread($fp, filesize(DISCUZ_ROOT.$tplfile));
		fclose($fp);

		$var_regexp = "((\\\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(\-\>)?[a-zA-Z0-9_\x7f-\xff]*)(\[[a-zA-Z0-9_\-\.\"\'\[\]\$\x7f-\xff]+\])*)";
		$const_regexp = "([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)";

		//note 解析支持子模板，代码循环执行，为的是可以解析子模板当中的子模板，默认 3 层嵌套
		$headerexists = preg_match("/{(sub)?template\s+[\w\/]+?header\}/", $template);
		$this->subtemplates = array();
		for($i = 1; $i <= 3; $i++) {
			if(strexists($template, '{subtemplate')) {
				$template = preg_replace("/[\n\r\t]*(\<\!\-\-)?\{subtemplate\s+([a-z0-9_:\/]+)\}(\-\-\>)?[\n\r\t]*/ies", "\$this->loadsubtemplate('\\2')", $template);
			}
		}

		$template = preg_replace("/([\n\r]+)\t+/s", "\\1", $template);
		$template = preg_replace("/\<\!\-\-\{(.+?)\}\-\-\>/s", "{\\1}", $template);
		$template = preg_replace("/\{lang\s+(.+?)\}/ies", "\$this->languagevar('\\1')", $template);
		$template = preg_replace("/[\n\r\t]*\{block\/(\d+?)\}[\n\r\t]*/ie", "\$this->blocktags('\\1')", $template);
		$template = preg_replace("/[\n\r\t]*\{blockdata\/(\d+?)\}[\n\r\t]*/ie", "\$this->blockdatatags('\\1')", $template);
		$template = preg_replace("/[\n\r\t]*\{ad\/(.+?)\}[\n\r\t]*/ie", "\$this->adtags('\\1')", $template);
		$template = preg_replace("/[\n\r\t]*\{date\((.+?)\)\}[\n\r\t]*/ie", "\$this->datetags('\\1')", $template);
		$template = preg_replace("/[\n\r\t]*\{avatar\((.+?)\)\}[\n\r\t]*/ie", "\$this->avatartags('\\1')", $template);
		$template = preg_replace("/[\n\r\t]*\{eval\s+(.+?)\s*\}[\n\r\t]*/ies", "\$this->evaltags('\\1')", $template);
		$template = preg_replace("/[\n\r\t]*\{csstemplate\}[\n\r\t]*/ies", "\$this->loadcsstemplate('\\1')", $template);
		//noteX 去除 $template = preg_replace("/\{faq\s+(.+?)\}/ies", "faqvar('\\1')", $template);
		$template = str_replace("{LF}", "<?=\"\\n\"?>", $template);
		$template = preg_replace("/\{(\\\$[a-zA-Z0-9_\-\>\[\]\'\"\$\.\x7f-\xff]+)\}/s", "<?=\\1?>", $template);
		$template = preg_replace("/$var_regexp/es", "template::addquote('<?=\\1?>')", $template);
		$template = preg_replace("/\<\?\=\<\?\=$var_regexp\?\>\?\>/es", "\$this->addquote('<?=\\1?>')", $template);

		if(!empty($this->subtemplates)) {
			$headeradd .= "\n0\n";
			foreach($this->subtemplates as $fname) {
				$headeradd .= "|| checktplrefresh('$tplfile', '$fname', ".time().", '$templateid', '$cachefile', '$tpldir', '$file')\n";
			}
			$headeradd .= ';';
		} else {
			$headeradd = '';
		}

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
			error('directory_notfound', array('$dir' => dirname(DISCUZ_ROOT.$cachefile)));
		}

		$template = preg_replace("/\"(http)?[\w\.\/:]+\?[^\"]+?&[^\"]+?\"/e", "\$this->transamp('\\0')", $template);
		$template = preg_replace("/\<script[^\>]*?src=\"(.+?)\"(.*?)\>\s*\<\/script\>/ies", "\$this->stripscriptamp('\\1', '\\2')", $template);
		$template = preg_replace("/[\n\r\t]*\{block\s+([a-zA-Z0-9_\[\]]+)\}(.+?)\{\/block\}/ies", "\$this->stripblock('\\1', '\\2')", $template);

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
			return $langvar[$var];
		} else {
			return '!'.$var.'!';
		}
	}

	function adtags($parameter) {
		$parameter = stripslashes($parameter);
		$i = count($this->replacecode['search']);
		$this->replacecode['search'][$i] = $search = "<!--AD_TAG_$i-->";
		$this->replacecode['replace'][$i] = "<?php echo adshow(\"$parameter\"); ?>";
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

	function loadcsstemplate() {
		global $_G;
		$scriptcss = '<link rel="stylesheet" type="text/css" href="data/cache/style_{STYLEID}_common.css?{VERHASH}" />'.
			'<link rel="stylesheet" id="extstyle_css" type="text/css" href="{if $_G[uid] && isset($_G[cookie][extstyle])}$_G[cookie][extstyle]/style.css{elseif $_G[style][defaultextstyle]}$_G[style][defaultextstyle]/style.css{/if}" />';
		$content = $this->csscurmodules = '';
		$content = @implode('', file(DISCUZ_ROOT.'./data/cache/style_'.STYLEID.'_module.css'));
		$content = preg_replace("/\[(.+?)\](.*?)\[end\]/ies", "\$this->cssvtags('\\1','\\2')", $content);
		if($this->csscurmodules) {
			$this->csscurmodules = preg_replace(array('/\s*([,;:\{\}])\s*/', '/[\t\n\r]/', '/\/\*.+?\*\//'), array('\\1', '',''), $this->csscurmodules);;
			if(@$fp = fopen(DISCUZ_ROOT.'./data/cache/style_'.STYLEID.'_'.$_G['basescript'].'_'.CURMODULE.'.css', 'w')) {
				fwrite($fp, $this->csscurmodules);
				fclose($fp);
			} else {
				exit('Can not write to cache files, please check directory ./data/ and ./data/cache/ .');
			}
			$scriptcss .='<link rel="stylesheet" type="text/css" href="data/cache/style_{STYLEID}_'.$_G['basescript'].'_'.CURMODULE.'.css?{VERHASH}" />';
		}
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
		//note $expr = str_replace("\\\"", "\"", preg_replace("/\<\?\=(\\\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\"\'\[\]\$\x7f-\xff]*)\?\>/s", "\\1", $expr));
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

}

?>