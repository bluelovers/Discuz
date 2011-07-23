<?php

if (!discuz_core::$plugin_support['Scorpio_Event']) return false;

class _bbcode_ {
	function codedisp($code, $brush = 'plain') {
		global $_G;
		$_G['forum_discuzcode']['pcodecount']++;
		/*
		$code = dhtmlspecialchars(str_replace('\\"', '"', preg_replace("/^[\n\r]*(.+?)[\n\r]*$/is", "\\1", $code)));
		$code = str_replace("\n", "<li>", $code);
		*/
		$code = str_replace(
			array(
				"\t",
				"\n",
				'\\"',
				'\\"'
			), array(
				'[tab][/tab]',
				'[br][/br]',
				'"',
				'"'
			)
			, $code);

		$_G['forum_discuzcode']['codehtml'][$_G['forum_discuzcode']['pcodecount']] = tpl_codedisp($code);
		$_G['forum_discuzcode']['codecount']++;
		return "[\tDISCUZ_CODE_".$_G['forum_discuzcode']['pcodecount']."\t]";
	}
}

?>