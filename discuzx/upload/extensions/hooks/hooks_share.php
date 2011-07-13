<?php

/**
 * @author bluelovers
 */

if (!discuz_core::$plugin_support['Scorpio_Event']) return false;

/* function_share.php */

Scorpio_Hook::add('Func_mkshare:Before', '_eFunc_mkshare_Before');

function _eFunc_mkshare_Before($_EVENT, $share = array()) {
	$_lang_template = empty($share['lang_template']) ? array() : unserialize($share['lang_template']);
	if (is_array($_lang_template)) {
		foreach ($_lang_template as $_k_ => $_v_) {
			$share[$_k_] = is_array($_v_) ? call_user_func_array('lang', $_v_) : lang('feed', $_v_);
		}
	}
}

Scorpio_Hook::add('Func_mkshare:After', '_eFunc_mkshare_After');

function _eFunc_mkshare_After($_EVENT, &$share, &$searchs, &$replaces) {
	$share['title_template'] = str_replace($searchs, $replaces, $share['title_template']);
}

/* function_feed.php */

Scorpio_Hook::add('Func_mkfeed:Before', '_eFunc_mkfeed_Before');

function _eFunc_mkfeed_Before($_EVENT, &$feed) {

	$_lang_template = empty($feed['lang_template']) ? array() : unserialize($feed['lang_template']);
	if (is_array($_lang_template)) {
		foreach ($_lang_template as $_k_ => $_v_) {
			$feed[$_k_] = is_array($_v_) ? call_user_func_array('lang', $_v_) : lang('feed', $_v_);

			if ($feed['icon'] == 'share' && !strexists($feed[$_k_], '{actor}')) {
				$feed[$_k_] = '{actor} '.$feed[$_k_];
			}
		}
	}

	if ($feed['icon'] == 'share') {
		$feed['title_data'] = $feed['body_data'];
	}

//	dexit($feed);
}

Scorpio_Hook::add('Func_feed_add:Before_feedarr_addslashes', '_eFunc_feed_add_Before_feedarr_addslashes');

function _eFunc_feed_add_Before_feedarr_addslashes($_EVENT, $conf) {
	extract($conf, EXTR_REFS);

	$_lang_template = array();

	list($feedarr['title_template'], $_lang_template['title_template']) = _feed_add($feedarr['title_template'], $feedarr['icon']);
	list($feedarr['body_template'], $_lang_template['body_template']) = _feed_add($feedarr['body_template'], $feedarr['icon']);
	list($feedarr['body_general'], $_lang_template['body_general']) = _feed_add($feedarr['body_general'], $feedarr['icon']);

	if ($_lang_template = array_filter($_lang_template)) {
		$feedarr['lang_template'] = $_lang_template ? serialize($_lang_template) : '';
	}

	if (empty($feedarr['image_1']) && $_body_data = $feedarr['body_data']) {
		$_body_data = is_array($_body_data) ? $_body_data : empty($_body_data) ? array() : unserialize($_body_data);

		if (!empty($_body_data['imgurl'])) $feedarr['image_1'] = $_body_data['imgurl'];
	}
}

/* spacecp_share.php */

Scorpio_Hook::add('Dz_module_spacecp_share:Before_share', '_eDz_module_spacecp_share_Before_share');

function _eDz_module_spacecp_share_Before_share($_EVENT, $conf) {
	$conf['arr'] = _share_add($conf['arr']);
}

Scorpio_Hook::add('Dz_module_spacecp_share:Before_share_insert', '_eDz_module_spacecp_share_Before_share_insert');

function _eDz_module_spacecp_share_Before_share_insert($_EVENT, $conf) {
	$conf['setarr'] = DB::table_field_value('home_share', $conf['setarr']);
}

Scorpio_Hook::add('Dz_module_spacecp_share:Before_notification', '_eDz_module_spacecp_share_Before_notification');

function _eDz_module_spacecp_share_Before_notification($_EVENT, $conf) {
	extract($conf, EXTR_REFS);

	if ($feedid) {
		$sid = DB::update('home_feed', array(
			'id' => $sid,
			'idtype' => 'sid',
		), array(
			'feedid' => $feedid,
		));
	}
}

Scorpio_Hook::add('Dz_module_spacecp_share:Before_notification', '_eDz_module_spacecp_share_Before_notification');

function _eDz_module_spacecp_share_Before_notification($conf) {
	extract($conf, EXTR_REFS);

	if ($feedid) {
		$sid = DB::update('home_feed', array(
			'id' => $sid,
			'idtype' => 'sid',
		), array(
			'feedid' => $feedid,
		));
	}
}

Scorpio_Hook::add('Dz_module_spacecp_share:Before_feed', '_eDz_module_spacecp_share_Before_feed');

function _eDz_module_spacecp_share_Before_feed($conf) {
	extract($conf, EXTR_REFS);

	if ($_body_data = $arr['body_data']) {
//		dexit($arr);

		$_body_data = is_array($_body_data) ? $_body_data : (empty($_body_data) ? array() : unserialize($_body_data));

		if (!empty($_body_data['imgurl'])) $arr['image_1'] = $_body_data['imgurl'];

		if ($arr['type'] == 'video' && !empty($flashvar)) {
			$i = 1;
			foreach ($flashvar['imagearray'] as $_imgurl) {
				$arr['image_'.$i] = $_imgurl;
				$arr['image'][] = $_imgurl;
				$i++;
			}
		}

		$_body_data['imgurl'] = $arr['image_1'] ? $arr['image_1'] : (is_array($arr['image']) ? $arr['image'][0] : $arr['image']);

		$_parse_url = scotext::parse_url($arr['data_index']);
//		$_body_data['headers'] = _url_exists($arr['data_index'], 1, &$_url);
		$_url = $arr['data_index'];

//		dexit(23);

//		$_url = 'http://www.discuz.net/';

		$curl_ret = curl($_url);

		if ($htmldom = htmldom($curl_ret['exec'])) {
			$code1 = $htmldom->find('meta[http-equiv=Content-Type]', 0)->content;
			$code1 = preg_replace('/^.*\bcharset\=([^;].+)\b;?.*$/i', '\\1', $code1);

			$code2 = preg_replace('/^.*\bcharset\=([^;].+)\b;?.*$/i', '\\1', $curl_ret['status']['content_type']);

			if ($code1 && $code1 == $code2) {
				$code = strtoupper($code2);
			} elseif ($html = $htmldom->plaintext) {
				$chklist = array(
					'UTF-8',
					'JIS',
					'SJIS',
					'EUC-JP',
					'BIG5',
					'GBK',
				);

				if ($code2) array_unshift($chklist, $code2);
				if ($code1) array_unshift($chklist, $code1);

				$code = mb_detect_encoding($html, $chklist, true);
			}

			$_body_data['charset'] = $code;

//			if ($title = @$htmldom->find('title', 0)->plaintext) {
//			}
			if ($title = @$htmldom->find('title', 0)->plaintext) {
				$title = trim(preg_replace('/\s+/i', ' ', mb_convert_encoding($title, 'utf-8', $code)));
				$titlelen = 0;

				if ($titlelen && mb_strlen($title, 'utf-8') > $titlelen) {
					$titlea = preg_split('/\s*(\||,)\s*/i', $title);
					$titlec = '';
					if (0 && is_array($titlea)) {
						foreach($titlea as $titleb) {
							if (mb_strlen($titlec.$titleb, 'utf-8') < $titlelen) {
								$titlec .= $c.$titleb;
								$c = ' | ';
							}
						}

						if (!$titlec) $titlec = $titleb;
						$title = $titlec.'...';
					} else {
						$title = mb_substr($title, 0, $titlelen, 'utf-8').'...';
					}
				}

				$title = dhtmlspecialchars($title);
				$link = $_url;

				$_body_data['link'] = "<a href=\"$link\" target=\"_blank\">$title</a>";

				$_body_data['title'] = $title;
			}

/*
//			$title .= '123456789zxcvbnm,./\"\';lkjhgfdsaqwertyuiop[]=-?0987654321`!@#$%^&*()_+}{":><';
//			$title .= '見知らぬ少女に叩き起こされ、見知らぬ部屋で目を覚ました主人公・星央彼方 （ほしお かなた）。
//少女に急かされるように学園へと連れて行かれるが、歩く道も学園の名前も覚えが無い。
//しかしやがて、昨夜彼女に自己紹介を受けたことを思い出す。
//樓主收集的夠全的，對這一快確實不清楚，一半寫正則什麼的驗證中文總是習慣
//性的使用\u4e00-\u9FA5
//「私の名前は三多野綾莉 （みたの あやり）。仕事はサンタクロース！」
//
//首を傾げたくなるような自己紹介だったが、思い出せないよりはずっといい。
//見知らぬ学園に着いた彼方は、自分がここに来た経緯と事実を自覚し、ひとり呟くことになる。
//本当に、あの家から逃げて来たんだな、と。
//
//期間内にご予約していただきましたお客様に抽選で100名様に、原画家・神藤みけこ氏の描き下ろしカラーイラストが印刷された色紙を神藤みけこ氏直筆サイン入りでプレゼントいたします !!
//応募方法は公式Webサイトをご覧ください。
//
//キャンペーン期間：2009年3月30日までㄅㄆㄇㄈ
//一二三
//
//ぁぅぇぉゃゅゎょっぃﾇヌﾓモュユ㈹７８９４５６１２３０．，＝＾＋－＊／％＞＜）（｛｝「」';


			foreach($_a = mb_str_split($title) as $_s) {
				if (!trim($_s) || scotext::is_ascii(scotext::transliterate_to_ascii(scotext::str_f2h($_s)))) continue;

				$_ss = $_s;
				$_ss .= ' '.utf8Encode($_ss);

				if (scotext::is_codepage($_s, 'big5')) {
					$_body_data['charset2']['big5'][] = $_ss;
				}
				if (preg_match('/^([\x81-\xfe](?:[\x40-\x7e]|[\xa1-\xfe])+)+$/', $_s)) {
					$_body_data['charset2']['big52'][] = $_ss;
				}
//				if (big5_isBig5($_s)) {
//					$_body_data['charset2']['big53'][] = $_ss;
//				}
				if (scotext::is_codepage($_s, 'gbk')) {
					$_body_data['charset2']['gbk'][] = $_ss;
				}
//				if (scotext::is_codepage($_s, 'GB2312-s1')) {
//					$_body_data['charset2']['GB2312-s1'][] = $_s;
//				}
//				if (scotext::is_codepage($_s, 'JIS')) {
//					$_body_data['charset2']['JIS'][] = $_s;
//				}
//				if (scotext::is_codepage($_s, 'SJIS')) {
//					$_body_data['charset2']['SJIS'][] = $_s;
//				}
//				if (scotext::is_codepage($_s, 'EUC_JP')) {
//					$_body_data['charset2']['EUC_JP'][] = $_s;
//				}
				if (preg_match('/^([\x7f-\xff])+$/', $_s)) {
					$_body_data['charset2']['gbk2'][] = $_ss;
				}
				if (preg_match('/^([\x81-\xfe][\x40-\xfe])+$/', $_s)) {
					$_body_data['charset2']['gbk3'][] = $_ss;
				}
				if (preg_match('/^([\x{4E00}-\x{9FA5}])+$/u', $_s)) {
					$_body_data['charset2']['gbk4'][] = $_ss;
				}
				if (preg_match('/^([\x{4E00}-\x{9FBF}])+$/u', $_s)) {
					$_body_data['charset2']['Kanji'][] = $_ss;
				}
				if (preg_match('/^([\x{3400}-\x{4DFF}])+$/u', $_s)) {
					$_body_data['charset2']['Kanji2'][] = $_ss;
				}
				if (preg_match('/^([\x{4E00}-\x{9FC3}])+$/u', $_s)) {
					$_body_data['charset2']['Kanji3'][] = $_ss;
				}
				if (preg_match('/^([\x{F900}-\x{FAD9}])+$/u', $_s)) {
					$_body_data['charset2']['Kanji4'][] = $_ss;
				}

				// 日文平假名
				if (preg_match('/^([\x{3040}-\x{309F}])+$/u', $_s)) {
					$_body_data['charset2']['Hiragana'][] = $_ss;
				}

				// 日文片假名
				if (preg_match('/^([\x{30A0}-\x{30FF}])+$/u', $_s)) {
					$_body_data['charset2']['Katakana'][] = $_ss;
				}
				if (preg_match('/^([\x{FF65}-\x{FF9F}])+$/u', $_s)) {
					$_body_data['charset2']['HalfKana'][] = $_ss;
				}
				if (preg_match('/^([\x{FF61}-\x{FF64}])+$/u', $_s)) {
					$_body_data['charset2']['HalfKana-symbol'][] = $_ss;
				}
				if (preg_match('/^([\x{32D0}-\x{32FE}])+$/u', $_s)) {
					$_body_data['charset2']['Dakuten'][] = $_ss;
				}

				// 日文片假名拼音擴展
				if (preg_match('/^([\x{31F0}-\x{31FF}])+$/u', $_s)) {
					$_body_data['charset2']['Ainu'][] = $_ss;
				}
				if (preg_match('/^([\xa1-\xa2][\xa0-\xfe])+$/', $_s)) {
					$_body_data['charset2']['EUC_JP-symbol'][] = $_ss;
				}
				if (preg_match('/^(\xa3[\xb0-\xb9])+$/', $_s)) {
					$_body_data['charset2']['EUC_JP-num'][] = $_ss;
				}
				if (preg_match('/^([\x{30A0}-\x{30FF}\x{3040}-\x{309F}\x{4E00}-\x{9FBF}])+$/u', $_s)) {
					$_body_data['charset2']['JIS2'][] = $_ss;
				}
				if (preg_match('/^([\x{3105}-\x{312D}])+$/u', $_s)) {
					$_body_data['charset2']['注音'][] = $_ss;
				}
				if (preg_match('/^([\x{31A0}-\x{31BF}])+$/u', $_s)) {
					$_body_data['charset2']['注音（閩南語、客家語擴展）'][] = $_ss;
				}
			}

			$_body_data['charset2']['FullKana'] = array_merge((array)$_body_data['charset2']['Hiragana'], (array)$_body_data['charset2']['Katakana']);
			$_body_data['charset2']['Kana'] = array_merge((array)$_body_data['charset2']['FullKana'], (array)$_body_data['charset2']['HalfKana'], (array)$_body_data['charset2']['HalfKana-symbol']);

			$_body_data['charset2']['big5'] = array_diff((array)$_body_data['charset2']['big5'], (array)$_body_data['charset2']['Kana']);

			$_body_data['charset2']['gbk'] = array_diff((array)$_body_data['charset2']['gbk'], (array)$_body_data['charset2']['Kana'], (array)$_body_data['charset2']['注音']);
			$_body_data['charset2']['gbk2'] = array_diff((array)$_body_data['charset2']['gbk2'], (array)$_body_data['charset2']['Kana'], (array)$_body_data['charset2']['注音']);

			foreach($_body_data['charset2'] as $_k => $_v) {
				$_body_data['charset2'][$_k] = array_unique((array)$_v);
//				sort($_body_data['charset2'][$_k], SORT_NUMERIC & SORT_LOCALE_STRING);
				sort($_body_data['charset2'][$_k]);
//				$_body_data['charset2'][$_k] = array_reverse($_body_data['charset2'][$_k]);
			}

			asort($_body_data['charset2'], SORT_STRING);
*/
		}

//		$_body_data[] = @$htmldom->plaintext;

		$_body_data = array_filter($_body_data);
		$arr['body_data'] = $_body_data;

//		echo '<pre>';
//		dexit($arr);
	}
}

/* func */

function _share_add($share) {
	$_lang_template = array();
	list($share['title_template'], $_lang_template['title_template']) = _mkshare($share['title_template']);
	list($share['body_template'], $_lang_template['body_template']) = _mkshare($share['body_template']);
	list($share['body_general'], $_lang_template['body_general']) = _mkshare($share['body_general']);

	if ($_lang_template = array_filter($_lang_template)) {
		$share['lang_template'] = $_lang_template ? serialize($_lang_template) : '';
	}

	$share['image'] = !empty($share['image_1']) ? $share['image_1'] : (is_array($share['image']) ? $share['image'][0] : $share['image']);

	return $share;
}

function _feed_add($langkey, $icon = '') {
	$_lang_template = '';

	if (is_array($langkey) && count($langkey) >= 2) {
		if (lang($langkey[0], $langkey[1], null, true)) {
			$_lang_template = $langkey;
		}
		$langkey = call_user_func_array('lang', $langkey);
	} else {
		if ($langkey && lang('feed', $langkey, null, true)) {
			$_lang_template = $langkey;
		}
		$langkey = $langkey ? lang('feed', $langkey) : '';
	}

	if ($icon == 'share' && $_lang_template && $langkey && !strexists($langkey, '{actor}')) {
		$langkey = '{actor} '.$langkey;
	}

	return array($langkey, $_lang_template);
}

/*
function mb_str_split( $string ) {
    # Split at all position not after the start: ^
    # and not before the end: $
    return preg_split('/(?<!^)(?!$)/u', $string );
}
*/

?>