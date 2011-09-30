<?php

/**
 * @author bluelovers
 * @copyright 2011
 */

class _sco_dx_tag {

	function tagarray($tags) {
		$tags = self::_fix_tags($tags);
		if(strexists($tags, ',')) {
			$tagarray = array_unique(explode(',', $tags));
		} else {
			$langcore = lang('core');
			$tags = str_replace($langcore['fullblankspace'], ' ', $tags);
			$tagarray = array_unique(explode(' ', $tags));
		}

		return $tagarray;
	}

	function _fix_tags($tags) {
		// 分別為 GBK, BIG5, UTF8 的全形"，"
		$tags = str_replace(array(chr(0xa3).chr(0xac), chr(0xa1).chr(0x41), chr(0xef).chr(0xbc).chr(0x8c)), ',', censor($tags));

		// bluelovers
		// GBK, BIG5, UTF8 的全形"　"
		$tags = str_replace(array(chr(0xa1).chr(0xa1), chr(0xa1).chr(0x40), chr(0xe3).chr(0x80).chr(0x80)), ' ', $tags);

		$tags = self::str_f2h($tags);

		$tags = str_replace(array(
			"\r\n", "\n", '、', '：', ':', '。'
		), ',', $tags);

		$tags = preg_replace('/[\s\t\r\n]+/', ' ', $tags);

		$tags = trim($tags);
		// bluelovers

		return $tags;
	}

	function check($tagname) {
		$ret = false;
		$tagname = trim($tagname, ',:&#-');

		/*
		$ret = preg_match('/^([\x7f-\xff_-]|\w|\s){3,20}$/', $tagname);
		*/
		// bluelovers
		$ret = preg_match('/^([\x7f-\xff_-\d\w\s;@\・\&\:]+)$/i', $tagname);

		if ($ret) {
			$_strlen = self::strlen($tagname);

			global $_G;

			if (
				$_strlen > 20
				|| (
					$_strlen < 2
					&& $_G['adminid'] != 1
					&& $_G['adminid'] != 2
				)
			) {
				$ret = false;
			}
		}

		/*
		debug(array(
			$tagname,
			$_strlen,
			$ret,
			self::utf8_strlen($tagname),
		));
		*/

		// bluelovers

		return $ret;
	}

	function strlen($str) {
		if(strtolower(CHARSET) == 'utf-8') return self::utf8_strlen($str);

		return dstrlen($str);
	}

	function utf8_strlen($str) {
		return preg_match_all('/[\x00-\x7F\xC0-\xFD]/', $str, $dummy);
	}

	/**
	 * 全形字轉半形字用
	 */
	function str_f2h($str, $h2f = 0) {
		/**
		 * 全形英數字及符號
		 */
		$f = array ('　', '０', '１', '２', '３', '４', '５', '６', '７', '８', '９', 'Ａ', 'Ｂ', 'Ｃ', 'Ｄ', 'Ｅ', 'Ｆ', 'Ｇ', 'Ｈ', 'Ｉ', 'Ｊ', 'Ｋ', 'Ｌ', 'Ｍ', 'Ｎ', 'Ｏ', 'Ｐ', 'Ｑ', 'Ｒ', 'Ｓ', 'Ｔ', 'Ｕ', 'Ｖ', 'Ｗ', 'Ｘ', 'Ｙ', 'Ｚ', 'ａ', 'ｂ', 'ｃ', 'ｄ', 'ｅ', 'ｆ', 'ｇ', 'ｈ', 'ｉ', 'ｊ', 'ｋ', 'ｌ', 'ｍ', 'ｎ', 'ｏ', 'ｐ', 'ｑ', 'ｒ', 'ｓ', 'ｔ', 'ｕ', 'ｖ', 'ｗ', 'ｘ', 'ｙ', 'ｚ', '～', '！', '＠', '＃', '＄', '％', '^', '＆', '＊', ' （', '）', '＿', '＋', '｜', '‘', '－', '＝', '＼', '｛', '｝', '〔', '〕', '：', '”', '；', '’', ' ＜', '＞', '？', '，', '．', '／', '︿',);
		/**
		 * 半形英數字及符號
		 */
		$h = array (' ', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '~', '!', '@', '#', '$', '%', '^', '&', '*', ' (', ')', '_', '+', '|', '`', '-', '=', '\\', '{', '}', '[', ']', ':', '"', ';', '\'', '<', '>', '?', ',', '.', '/', '^',);

		return $h2f ? str_replace($h, $f, $str) : str_replace($f, $h, $str);
	}

}

?>