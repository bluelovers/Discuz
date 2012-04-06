<?php

/**
 *      [Discuz! X] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: search.class.php 26709 2011-12-20 08:38:43Z zhouguoqiang $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}


class plugin_cloudsearch {

	protected $allow = FALSE;

	function plugin_cloudsearch() {
		global $_G;

		$my_search_data = unserialize($_G['setting']['my_search_data']);
		$cloud_apps = (array)unserialize($_G['setting']['cloud_apps']);
		$this->allow = $cloud_apps['search']['status'] == 'normal' && $my_search_data['status'] ? TRUE : FALSE;
		if($this->allow) {
			include_once template('cloudsearch:module');
		}
	}

	function global_footer() {

		$res = '';
		if($this->allow) {
			if(CURSCRIPT == 'forum' && CURMODULE == 'viewthread' && $GLOBALS['page'] == 1) {
				$searchparams = makeSearchSignUrl();
				$srchotquery = '';
				if(!empty($searchparams[1])) {
					foreach($searchparams[1] as $key => $value) {
						$srchotquery .= '&' . $key . '=' . $value;
					}
				}
				$res = tpl_cloudsearch_global_footer_related($searchparams[0], $srchotquery);
			}
		}

		return $res;
	}

}

class plugin_cloudsearch_forum extends plugin_cloudsearch {

	public function index_top_output() {
		if($this->allow) {
			$searchparams = makeSearchSignUrl();
			$recwords = $this->getRecWords();
			$srchotquery = '';
			if(!empty($searchparams[1])) {
				foreach($searchparams[1] as $key => $value) {
					$srchotquery .= '&' . $key . '=' . $value;
				}
			}
			return tpl_cloudsearch_index_top($recwords, $searchparams, $srchotquery);
		}

	}

	public function viewthread_postbottom_output() {
		global $_G;

		if($this->allow && $GLOBALS['page'] == 1 && $_G['forum_firstpid'] && $GLOBALS['postlist'][$_G['forum_firstpid']]['invisible'] == 0) {
			return (array)tpl_cloudsearch_viewthread_postbottom_output();
		}
	}


	public function getRecWords($needNum = 14) {
		global $_G;

		$sId = $_G['setting']['my_siteid'];
		$data = array();

		if($sId) {
			$kname = 'search_recommend_words_' . $sId;
			loadcache($kname);

			if(isset($_G['cache'][$kname]['ts']) && (TIMESTAMP - $_G['cache'][$kname]['ts'] <= 21600)) {
				$data = $_G['cache'][$kname]['result'];
			} else {
				$apiUrl = 'http://api.discuz.qq.com/search/recwords/get';
				$params = array(
					's_id' => $sId,
					'need_random' => false,
					'need_num' => $needNum,
					'response_format' => 'php',
					'version' => 1, // 1：返回数字下标的结果集、2：返回关联数组形式的结果集
				);

				$response = dfsockopen($apiUrl, 0, generateSiteSignUrl($params), '', false, $_G['setting']['cloud_api_ip']);
				$result = (array) unserialize($response);

				if(isset($result['status']) && $result['status'] === 0) {
					$data = $result['result'];

					if($data) {
						save_syscache($kname, array('ts' => TIMESTAMP, 'result' => $data));
					}
				}
			}
		}

		return $data;
	}

}


?>