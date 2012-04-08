<?php
/**
 *      [Discuz! X] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: search_wap.class.php 29278 2012-03-31 09:02:13Z zhouxiaobo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class mobileplugin_cloudsearch {

	public function mobileplugin_cloudsearch() {
		global $_G, $searchparams;

		$cloudAppService = Cloud::loadClass('Service_App');
		$this->allow = $cloudAppService->getCloudAppStatus('search');
		if($this->allow) {
			include_once template('cloudsearch:module');

			if (!$searchparams) {
				$searchHelper = Cloud::loadClass('Cloud_Service_SearchHelper');
				$searchparams = $searchHelper->makeSearchSignUrl();
			}
		}
	}

	function global_header_mobile() {

		if (!$this->allow) {
			return;
		}

		global $_G, $searchparams;

		$srchotquery = '';
		if(!empty($searchparams['params'])) {
			foreach($searchparams['params'] as $key => $value) {
				$srchotquery .= '&' . $key . '=' . $value;
			}
		}

		return tpl_global_header_mobile($searchparams, $srchotquery);
	}

}