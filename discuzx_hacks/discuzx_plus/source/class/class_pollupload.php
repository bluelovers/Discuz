<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: class_pollupload.php 638 2010-09-10 08:50:26Z yexinhao $
 */

class poll_upload {

	var $uid;
	var $aid;
	var $simple;
	var $statusid;
	var $attach;
	var $user;
	var $imageexts;
	var $attachextensions;
	var $maxattachsize;
	var $pollid;

	function poll_upload() {
		global $_G;

		$this->uid = intval($_G['gp_uid']);
		$swfhash = md5(substr(md5($_G['config']['security']['authkey']), 8).$this->uid);

		if(!$_FILES['Filedata']['error'] && $_G['gp_hash'] == $swfhash && $this->uid) {

			$this->aid = 0;
			$this->simple = 0;

			$this->user = getuserbyuid($this->uid);
			if(empty($this->user['adminid'])) {
				$this->uploadmsg(9);
			}

			$_G['uid'] = $this->uid;

			$this->pollid = !empty($_G['gp_pollid']) ? intval($_G['gp_pollid']) :0;

			if($this->pollid <= 0 || !intval(DB::result_first("SELECT contenttype FROM ".DB::table('poll_item')." WHERE itemid='{$this->pollid}'"))) {
				$this->uploadmsg(9);
			}

			$attach = upload_images($_FILES['Filedata'], 'poll', 176, 176);

			$caption = dhtmlspecialchars(trim($attach['name']));
			$caption = substr($caption, 0, -(strlen(fileext($caption)) + 1));
			$data = array(
				'itemid' => $this->pollid,
				'caption' => $caption,
				'displayorder' => 0,
				'imageurl' => $attach['attachment'],
				'aid' => $attach['aid']
			);
			DB::insert('poll_choice', $data);

			$this->aid = $this->pollid;
			$this->uploadmsg(0);

		}
	}

	function uploadmsg($statusid) {
		global $_G;
		if($this->simple == 1) {
			echo 'DISCUZUPLOAD|'.$statusid.'|'.$this->aid.'|'.$this->attach['isimage'];
		} elseif($this->simple == 2) {
			echo 'DISCUZUPLOAD|'.($_G['gp_type'] == 'image' ? '1' : '0').'|'.$statusid.'|'.$this->aid.'|'.$this->attach['isimage'].'|'.$this->attach['attachment'].'|'.$this->attach['name'];
		} else {
			echo $this->aid;
		}
		exit;
	}
}

?>