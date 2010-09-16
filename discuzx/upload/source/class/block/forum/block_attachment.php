<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_attachment.php 11884 2010-06-18 06:40:35Z xupeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class block_attachment {

	var $settings = array();

	function block_attachment() {
		$this->settings = array(
			'fids'	=> array(
				'title' => 'attachmentlist_fids',
				'type' => 'mselect',
				'value' => array()
			),
			'tids'	=> array(
				'title' => 'attachmentlist_tids',
				'type' => 'text'
			),
			'special' => array(
				'title' => 'attachmentlist_special',
				'type' => 'mcheckbox',
				'value' => array(
					array(1, 'attachmentlist_special_1'),
					array(2, 'attachmentlist_special_2'),
					array(3, 'attachmentlist_special_3'),
					array(4, 'attachmentlist_special_4'),
					array(5, 'attachmentlist_special_5'),
					array(0, 'attachmentlist_special_0'),
				)
			),
			'rewardstatus' => array(
				'title' => 'attachmentlist_special_reward',
				'type' => 'mradio',
				'value' => array(
					array(0, 'attachmentlist_special_reward_0'),
					array(1, 'attachmentlist_special_reward_1'),
					array(2, 'attachmentlist_special_reward_2')
				),
				'default' => 0,
			),
			'isimage' => array(
				'title' => 'attachmentlist_isimage',
				'type' => 'mradio',
				'value' => array(
					array(0, 'attachmentlist_isimage_0'),
					array(1, 'attachmentlist_isimage_1'),
					array(2, 'attachmentlist_isimage_2')
				),
				'default' => 0
			),
			'threadmethod' => array(
				'title' => 'attachmentlist_threadmethod',
				'type' => 'radio',
				'default' => 0
			),
			'digest' => array(
				'title' => 'attachmentlist_digest',
				'type' => 'mcheckbox',
				'value' => array(
					array(1, 'attachmentlist_digest_1'),
					array(2, 'attachmentlist_digest_2'),
					array(3, 'attachmentlist_digest_3'),
					array(0, 'attachmentlist_digest_0')
				),
			),
			'orderby' => array(
				'title' => 'attachmentlist_orderby',
				'type' => 'mradio',
				'value' => array(
					array('dateline', 'attachmentlist_orderby_dateline'),
					array('downloads', 'attachmentlist_orderby_downloads'),
				),
				'default' => 'dateline'
			),
			'dateline' => array(
				'title' => 'attachmentlist_dateline',
				'type' => 'mradio',
				'value' => array(
					array('', 'attachmentlist_dateline_nolimit'),
					array('3600', 'attachmentlist_dateline_hour'),
					array('86400', 'attachmentlist_dateline_day'),
					array('604800', 'attachmentlist_dateline_week'),
					array('2592000', 'attachmentlist_dateline_month'),
				),
				'default' => ''
			),
			'titlelength' => array(
				'title' => 'attachmentlist_titlelength',
				'type' => 'text',
				'default' => 40
			),
			'summarylength' => array(
				'title' => 'attachmentlist_summarylength',
				'type' => 'text',
				'default' => 80
			),
			'startrow' => array(
				'title' => 'attachmentlist_startrow',
				'type' => 'text',
				'default' => 0
			),
		);
	}

	function name() {
		return lang('blockclass', 'blockclass_attachment_script_attachment');
	}

	function blockclass() {
		return array('attachment',  lang('blockclass', 'blockclass_forum_attachment'));
	}

	function fields() {
		return array(
					'url' => array('name' => lang('blockclass', 'blockclass_attachment_field_url'), 'formtype' => 'text', 'datatype' => 'string'),
					'title' => array('name' => lang('blockclass', 'blockclass_attachment_field_title'), 'formtype' => 'title', 'datatype' => 'title'),
					'pic' => array('name' => lang('blockclass', 'blockclass_attachment_field_pic'), 'formtype' => 'pic', 'datatype' => 'pic'),
					'summary' => array('name' => lang('blockclass', 'blockclass_attachment_field_summary'), 'formtype' => 'summary', 'datatype' => 'summary'),
					'threadurl' => array('name' => lang('blockclass', 'blockclass_attachment_field_threadurl'), 'formtype' => 'text', 'datatype' => 'text'),
					'threadsubject' => array('name' => lang('blockclass', 'blockclass_attachment_field_threadsubject'), 'formtype' => 'text', 'datatype' => 'text'),
					'threadsummary' => array('name' => lang('blockclass', 'blockclass_attachment_field_threadsummary'), 'formtype' => 'text', 'datatype' => 'text'),
					'filesize' => array('name' => lang('blockclass', 'blockclass_attachment_field_filesize'), 'formtype' => 'text', 'datatype' => 'string'),
					'author' => array('name' => lang('blockclass', 'blockclass_attachment_field_author'), 'formtype' => 'text', 'datatype' => 'string'),
					'authorid' => array('name' => lang('blockclass', 'blockclass_attachment_field_authorid'), 'formtype' => 'text', 'datatype' => 'int'),
					'dateline' => array('name'=>lang('blockclass', 'blockclass_attachment_field_dateline'), 'formtype' => 'date', 'datatype'=>'date'),
					'downloads' => array('name'=>lang('blockclass', 'blockclass_attachment_field_downloads'), 'formtype' => 'text', 'datatype'=>'int'),
					'hourdownloads' => array('name'=>lang('blockclass', 'blockclass_attachment_field_hourdownloads'), 'formtype' => 'text', 'datatype'=>'int'),
					'todaydownloads' => array('name'=>lang('blockclass', 'blockclass_attachment_field_todaydownloads'), 'formtype' => 'text', 'datatype'=>'int'),
					'weekdownloads' => array('name'=>lang('blockclass', 'blockclass_attachment_field_weekdownloads'), 'formtype' => 'text', 'datatype'=>'int'),
					'monthdownloads' => array('name'=>lang('blockclass', 'blockclass_attachment_field_monthdownloads'), 'formtype' => 'text', 'datatype'=>'int'),
				);
	}

	function getsetting() {
		global $_G;
		$settings = $this->settings;

		loadcache('forums');
		$settings['fids']['value'][] = array(0, lang('portalcp', 'block_all_forum'));
		foreach($_G['cache']['forums'] as $fid => $forum) {
			$settings['fids']['value'][] = array($fid, ($forum['type'] == 'forum' ? str_repeat('&nbsp;', 4) : ($forum['type'] == 'sub' ? str_repeat('&nbsp;', 8) : '')).$forum['name']);
		}
		return $settings;
	}

	function cookparameter($parameter) {
		return $parameter;
	}

	function getdata($style, $parameter) {
		global $_G;

		$parameter = $this->cookparameter($parameter);

		loadcache('forums');
		$tids		= !empty($parameter['tids']) ? explode(',', $parameter['tids']) : array();
		$startrow	= isset($parameter['startrow']) ? intval($parameter['startrow']) : 0;
		$items		= isset($parameter['items']) ? intval($parameter['items']) : 10;
		$digest		= isset($parameter['digest']) ? $parameter['digest'] : 0;
		$special	= isset($parameter['special']) ? $parameter['special'] : array();
		$rewardstatus	= isset($parameter['rewardstatus']) ? intval($parameter['rewardstatus']) : 0;
		$titlelength	= !empty($parameter['titlelength']) ? intval($parameter['titlelength']) : 40;
		$summarylength	= !empty($parameter['summarylength']) ? intval($parameter['summarylength']) : 80;
		$orderby = isset($parameter['orderby']) ? (in_array($parameter['orderby'],array('dateline','downloads')) ? $parameter['orderby'] : 'dateline') : 'dateline';
		$dateline = isset($parameter['dateline']) ? intval($parameter['dateline']) : '8640000';
		$threadmethod = !empty($parameter['threadmethod']) ? 1 : 0;
		$isimage = isset($parameter['isimage']) ? intval($parameter['isimage']) : '';

		$fids = array();
		if(!empty($parameter['fids'])) {
			if($parameter['fids'][0] == '0') {
				unset($parameter['fids'][0]);
			}
			$fids = $parameter['fids'];
		}
		if(empty($fids)) {
			if(!empty($_G['setting']['allowviewuserthread'])) {
				$fids = $_G['setting']['allowviewuserthread'];
			} else {
				return $returndata;
			}
		} else {
			$fids = dimplode($fids);
		}

		$bannedids = !empty($parameter['bannedids']) ? explode(',', $parameter['bannedids']) : array();

		$datalist = $list = array();
		$sql = ($fids ? ' AND t.fid IN ('.$fids.')' : '')
			.($tids ? ' AND t.tid IN ('.dimplode($tids).')' : '')
			.($digest ? ' AND t.digest IN ('.dimplode($digest).')' : '')
			.($special ? ' AND t.special IN ('.dimplode($special).')' : '')
			.((in_array(3, $special) && $rewardstatus) ? ($rewardstatus == 1 ? ' AND t.price < 0' : ' AND t.price > 0') : '')
			. " AND t.closed='0' AND t.isgroup='0'";
		$orderbysql = $historytime = '';
		switch($orderby) {
			case 'dateline':
				$orderbysql = "ORDER BY `attach`.`dateline` DESC";
			break;
			case 'downloads':
				$orderbysql = "ORDER BY `attach`.`downloads` DESC";
			break;
		}
		$htsql = '';
		$dateline = !empty($dateline) ? intval($dateline) : 8640000;
		$historytime = TIMESTAMP - $dateline;
		$htsql = "`attach`.`dateline`>='$historytime'";

		$sqlfield = $sqljoin = '';
		if($style['getsummary']) {
			$sqlfield = ',af.description';
			$sqljoin = "LEFT JOIN `".DB::table('forum_attachmentfield')."` af ON attach.aid=af.aid";
		}
		if($isimage) {
			$sql .= $isimage == 1 ? "AND `attach`.`isimage` IN ('1', '-1')" : "AND `attach`.`isimage`='0'";
		}
		$sqlgroupby = '';
		if($threadmethod) {
			if($isimage==1) {
				$sql .= ' AND t.attachment=2';
			} elseif($isimage==2) {
				$sql .= ' AND t.attachment=1';
			} else {
				$sql .= ' AND t.attachment>0';
			}
			$sqlgroupby = ' GROUP BY t.tid';
		}
		$sqlban = !empty($bannedids) ? ' AND attach.aid NOT IN ('.dimplode($bannedids).')' : '';
		$query = DB::query("SELECT attach.*,t.tid,t.author,t.authorid,t.subject $sqlfield
			FROM `".DB::table('forum_attachment')."` attach
			$sqljoin
			INNER JOIN `".DB::table('forum_thread')."` t
			ON `t`.`tid`=`attach`.`tid` AND `displayorder`>='0'
			WHERE $htsql AND `attach`.`readperm`='0' AND `attach`.`price`='0'
			$sql
			$sqlban
			$sqlgroupby
			$orderbysql
			LIMIT $startrow,$items;"
		);

		require_once libfile('block_thread', 'class/block/forum');
		$bt = new block_thread();
		while($data = DB::fetch($query)) {
			$list[] = array(
				'id' => $data['aid'],
				'idtype' => 'aid',
				'title' => cutstr(str_replace('\\\'', '&#39;', $data['filename']), $titlelength, ''),
				'url' => 'forum.php?mod=attachment&aid='.aidencode($data['aid']),
				'pic' => $data['isimage'] == 1 || $data['isimage'] == -1 ? 'forum/'.$data['attachment'] : '',
				'picflag' => $data['remote'] ? '2' : '1',
				'summary' => !empty($data['description']) ? cutstr(str_replace('\\\'', '&#39;', $data['description']), $summarylength, '') : '',
				'fields' => array(
					'fulltitle' => str_replace('\\\'', '&#39;', addslashes($data['subject'])),
					'author' => $data['author'],
					'authorid' => $data['authorid'],
					'filesize' => sizecount($data['filesize']),
					'dateline' => $data['dateline'],
					'downloads' => $data['downloads'],
					'hourdownloads' => $data['downloads'],
					'todaydownloads' => $data['downloads'],
					'weekdownloads' => $data['downloads'],
					'monthdownloads' => $data['downloads'],
					'threadurl' => 'forum.php?mod=viewthread&tid='.$data['tid'],
					'threadsubject' => cutstr(str_replace('\\\'', '&#39;', $data['subject']), $titlelength, ''),
					'threadsummary' => $bt->getthread($data['tid'], $summarylength),
				)
			);
		}
		return array('html' => '', 'data' => $list);
	}
}

?>