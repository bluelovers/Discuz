<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: bbcode.class.php 10469 2010-05-11 09:12:14Z monkey $
 */

class bbcode {

	var $search_exp = array();
	var $replace_exp = array();
	var $search_str = array();
	var $replace_str = array();
	var $html_s_exp = array();
	var $html_r_exp = array();
	var $html_s_str = array();
	var $html_r_str = array();

	function &instance() {
		static $object;
		if(empty($object)) {
			$object = new bbcode();
		}
		return $object;
	}

	function bbcode() {
	}

	function bbcode2html($message, $parseurl=0) {
		if(empty($this->search_exp)) {
			$this->search_exp = array(
				"/\s*\[quote\][\n\r]*(.+?)[\n\r]*\[\/quote\]\s*/is",
				"/\[url\]\s*(https?:\/\/|ftp:\/\/|gopher:\/\/|news:\/\/|telnet:\/\/|rtsp:\/\/|mms:\/\/|callto:\/\/|ed2k:\/\/){1}([^\[\"']+?)\s*\[\/url\]/i",
				"/\[em:(.+?):\]/i",
			);
			$this->replace_exp = array(
				"<div class=\"quote\"><blockquote>\\1</blockquote></div>",
				"<a href=\"\\1\\2\" target=\"_blank\">\\1\\2</a>",
				" <img src=\"".STATICURL."image/smiley/comcom/\\1.gif\" class=\"vm\"> "
			);
			$this->search_str = array('[b]', '[/b]','[i]', '[/i]', '[u]', '[/u]');
			$this->replace_str = array('<b>', '</b>', '<i>','</i>', '<u>', '</u>');
		}

		if($parseurl==2) {
			$this->search_exp[] = "/\[img\]\s*([^\[\<\r\n]+?)\s*\[\/img\]/ies";
			$this->replace_exp[] = '$this->bb_img(\'\\1\')';
			$message = bbcode::parseurl($message);
		}

		@$message = str_replace($this->search_str, $this->replace_str,preg_replace($this->search_exp, $this->replace_exp, $message, 20));
		return nl2br(str_replace(array("\t", '   ', '  '), array('&nbsp; &nbsp; &nbsp; &nbsp; ', '&nbsp; &nbsp;', '&nbsp;&nbsp;'), $message));
	}

	function parseurl($message) {
		return preg_replace("/(?<=[^\]a-z0-9-=\"'\\/])((https?|ftp|gopher|news|telnet|mms|rtsp):\/\/)([a-z0-9\/\-_+=.~!%@?#%&;:$\\()|]+)/i", "[url]\\1\\3[/url]", ' '.$message);
	}

	function html2bbcode($message) {

		if(empty($this->html_s_exp)) {
			$this->html_s_exp = array(
					"/\<div class=\"quote\"\>\<span class=\"q\"\>(.*?)\<\/span\>\<\/div\>/is",
					"/\<div class=\"quote\"\>\<blockquote\>(.*?)\<\/blockquote\>\<\/div\>/is",
					"/\<a href=\"(.+?)\".*?\<\/a\>/is",
					"/(\r\n|\n|\r)/",
					"/<br.*>/siU",
					"/[ \t]*\<img src=\"static\/image\/home\/face\/(.+?).gif\".*?\>[ \t]*/is",
					"/\s*\<img src=\"(.+?)\".*?\>\s*/is"
				);
				$this->html_r_exp = array(
					"[quote]\\1[/quote]",
					"[quote]\\1[/quote]",
					"\\1",
					'',
					"\n",
					"[em:\\1:]",
					"\n[img]\\1[/img]\n"
			);
			$this->html_s_str = array('<b>', '</b>', '<i>','</i>', '<u>', '</u>', '&nbsp; &nbsp; &nbsp; &nbsp; ', '&nbsp; &nbsp;', '&nbsp;&nbsp;', '&lt;', '&gt;', '&amp;');
			$this->html_r_str = array('[b]', '[/b]','[i]', '[/i]', '[u]', '[/u]', "\t", '   ', '  ', '<', '>', '&');
		}

		@$message = str_replace($this->html_s_str, $this->html_r_str,
		preg_replace($this->html_s_exp, $this->html_r_exp, $message));

		$message = htmlspecialchars($message);

		return trim($message);
	}

	function bb_img($url) {
		$url = addslashes($url);
		return "<img src=\"$url\">";
	}

	// bluelovers
	function bbcode_fix($message) {
		for ($i=0; $i<10; $i++) {
			$message = preg_replace(array(
				'/(?:\[([a-z0-9]+)(?:=(?:[^\[\]\n]+))?\])(\s+)?(?:\[\/\\1\])/isSU'
				, '/(?:\[(size)(?:=3|2)?\])((?:[^\[]|\[(?!\/\\1\])).+)(?:\[\/\\1\])/isSU'
				, '/(?:\[(color)(?:=black|#0+|\(?0+,0+,0+\)?)?\])((?:[^\[]|\[(?!\/\\1\])).+)(?:\[\/\\1\])/isSU'
			), '\\2', $message);

			$message = preg_replace(array(
				'/(?:\[(color|size|align|indent|i|s|u|italic|font)(=[^\[\]\n]+)?\])((?:[^\[]|\[(?!\/\\1\])).+)(?:\[\/\\1\])(\s*)(?:\[\\1\\2\])((?:[^\[]|\[(?!\/\\1\])).+)(?:\[\/\\1\])/isSU'
				, '/(?:\[(quote|sell|free|code|php|html|js|xml|sql|mysql|css|style|c|prel)(=[^\[\]\n]+)?\])(\n*)((?:[^\[]|\[(?!\/\\1\])).+)(\s+)?(?:\[\/\\1\])/isSU'
				, '/^\n*(?:\[(font|size|italic|s|u)(=[^\[\]\n]+)?\])(\n*)((?:[^\[]|\[(?!\/\\1\])).+)(\s+)?(?:\[\/\\1\])\s*$/isSU'
				, '/(?:\[(italic)(=[^\[\]\n]+)?\])((?:[^\[]|\[(?!\/\\1\])).+)(?:\[\/\\1\])/isSU'
			), array(
				'[\\1\\2]\\3\\4\\5[/\\1]'
				, '[\\1\\2]\\4[/\\1]'
				, '\\4'
				, '[i\\2]\\3[/i]'
			), $message);

			$message = preg_replace(array(
				'/^(\[[a-z0-9]+(?:=[^\[\]\n]+)?\])\n+|\n+(\[\/[a-z0-9]+\])/isSU'
			), '\\1\\2', $message);
		}

		$message = $this->bbcode_media($message);

		$message = preg_replace('/[　 \t]+(\n|$)/iSuU', '\\1', $message);

		return $message;
	}

	function bbcode_media($message) {

		$tag = '(?:'.$this->_bbcode_media(0, 1).')';

		$_skip = array('youtube', 'audio', 'flash');
		$_regexval = '(?:(?:[^\[]|\[(?!\/\\1\]))+)';

		$message = preg_replace_callback(array(
			"/\[(?<tag>{$tag})(?:=(?<extra>[^\[\]]*))?\][\n\r]*(?<value>".$_regexval.")[\n\r]*\[\/\\1\]/is",
		), array($this, '_bbcode_media_callback'), $message);

		return $message;
	}

	function _bbcode_media($t = 0, $_regex = 0) {
		$tags = array(
			'media',

			'youtube',
			'wmv',
			'rmvb',
			'yamflv',
			'wretch',
			'gv',

			'flv',
		);

		$tags_ex = array();

		$tags_ex[0] = array('audio',
			'wma',
			'ra',
			'rm',
			'mp3',
			'yammp3',
		);

		$tags_ex[1] = array(
			'flash',
			'swf',
		);

		if (!$t) {
			$tags = array_merge($tags, $tags_ex[0], $tags_ex[1]);
		} elseif ($t == 2) {
			$tags = $tags_ex[0];
		} elseif ($t == 3) {
			$tags = $tags_ex[1];
		}

		if ($_regex) {
			array_walk($tags, 'preg_quote');
			return implode('|', $tags);
		} else {
			return $tags;
		}
	}

	function _bbcode_media_callback($m) {
		$retempty = 0;

		$_skip = array('youtube', 'audio', 'flash');

		if (
			preg_match("/(?:\.youtube\..+?\/watch\?v=|youtu\.be\/)(?<idkey>[0-9A-Za-z-_]{11})(?:[\?\&](?<e1>t=[\dmhs]+)\&?)?/", $m['value'], $_m)
			// [youtube]b5EFKNmeovM[/youtube]
			|| $m['tag'] == 'youtube' && preg_match("/^(?<idkey>[0-9A-Za-z-_]{11})$/", $m['value'], $_m)
		) {
			$c = '&';

			$extra = '';
			if (!empty($_m['e1'])) {
				$extra = $_m['e1'].$c;
			}

			if (!empty($extra)) $extra = $c.trim($extra, $c);

			return $this->bbcode_make('media', 'http://www.youtube.com/watch?v='.$_m['idkey'].$extra);

		} elseif (in_array($m['tag'], $this->_bbcode_media(1)) && $this->is_url($m['value'])) {
			return $this->bbcode_make('media', $m['value']);

		} elseif (in_array($m['tag'], $this->_bbcode_media(2)) && $this->is_url($m['value'])) {
			return $this->bbcode_make('audio', $m['value']);

		} elseif (in_array($m['tag'], $this->_bbcode_media(3)) && $this->is_url($m['value'])) {

			return $this->bbcode_make('flash', $m['value'], $m['extra']);

		} elseif (in_array($m['tag'], $this->_bbcode_media()) && $this->is_url($m['value'])) {

			$retempty = 1;

		} elseif (in_array($m['tag'], $_skip)) {

			$retempty = 1;

		}

		if ($retempty) {
			return $m['value'];
		}

		return $m[0];
	}

	function bbcode_make($tag, $value = '', $extra = '') {
		$extra = trim($extra);

		if ($tag == 'media' && empty($extra)) $extra = 'x,500,375';

		$r = ($extra !== '') ? '='.$extra : '';

		return '['.$tag.$r.']'.$value.'[/'.$tag.']';
	}

	/**
	 * Validate a URL.
	 *
	 * @param   string   URL
	 * @return  boolean
	 */
	function is_url($url) {
		// Based on http://www.apps.ietf.org/rfc/rfc1738.html#sec-5
		if ( ! preg_match(
			'~^

			# scheme
			[-a-z0-9+.]++://

			# username:password (optional)
			(?:
				    [-a-z0-9$_.+!*\'(),;?&=%]++   # username
				(?::[-a-z0-9$_.+!*\'(),;?&=%]++)? # password (optional)
				@
			)?

			(?:
				# ip address
				\d{1,3}+(?:\.\d{1,3}+){3}+

				| # or

				# hostname (captured)
				(
					     (?!-)[-a-z0-9]{1,63}+(?<!-)
					(?:\.(?!-)[-a-z0-9]{1,63}+(?<!-)){0,126}+
				)
			)

			# port (optional)
			(?::\d{1,5}+)?

			# path (optional)
			(?:/.*)?

			$~iDx', $url, $matches))
			return FALSE;

		// We matched an IP address
		if ( ! isset($matches[1]))
			return TRUE;

		// Check maximum length of the whole hostname
		// http://en.wikipedia.org/wiki/Domain_name#cite_note-0
		if (strlen($matches[1]) > 253)
			return FALSE;

		// An extra check for the top level domain
		// It must start with a letter
		$tld = ltrim(substr($matches[1], (int) strrpos($matches[1], '.')), '.');
		return ctype_alpha($tld[0]);
	}

	// bluelovers
}

?>