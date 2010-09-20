<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_groupattachment.php 11884 2010-06-18 06:40:35Z xupeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class block_groupattachment {

	var $settings = array();

	function block_groupattachment() {
		$this->settings = array(
			'gtids' => array(
				'title' => 'groupattachment_gtids',
				'type' => 'mselect',
				'value' => array(
				),
			),
			'tids'	=> array(
				'title' => 'groupattachment_tids',
				'type' => 'text'
			),
			'special' => array(
				'title' => 'groupattachment_special',
				'type' => 'mcheckbox',
				'value' => array(
					array(1, 'groupattachment_special_1'),
					array(2, 'groupattachment_special_2'),
					array(3, 'groupattachment_special_3'),
					array(4, 'groupattachment_special_4'),
					array(5, 'groupattachment_special_5'),
					array(0, 'groupattachment_special_0'),
				)
			),
			'rewardstatus' => array(
				'title' => 'groupattachment_special_reward',
				'type' => 'mradio',
				'value' => array(
					array(0, 'groupattachment_special_reward_0'),
					array(1, 'groupattachment_special_reward_1'),
					array(2, 'groupattachment_special_reward_2')
				),
				'default' => 0,
			),
			'isimage' => array(
				'title' => 'groupattachment_isimage',
				'type' => 'mradio',
				'value' => array(
					array(0, 'groupattachment_isimage_0'),
					array(1, 'groupattachment_isimage_1'),
					array(2, 'groupattachment_isimage_2')
				),
				'default' => 0
			),
			'threadmethod' => array(
				'title' => 'groupattachment_threadmethod',
				'type' => 'radio',
				'default' => 0
			),
			'digest' => array(
				'title' => 'groupattachment_digest',
				'type' => 'mcheckbox',
				'value' => array(
					array(1, 'groupattachment_digest_1'),
					array(2, 'groupattachment_digest_2'),
					array(3, 'groupattachment_digest_3'),
					array(0, 'groupattachment_digest_0')
				),
			),
			'orderby' => array(
				'title' => 'groupattachment_orderby',
				'type' => 'mradio',
				'value' => array(
					array('dateline', 'groupattachment_orderby_dateline'),
					array('downloads', 'groupattachment_orderby_downloads'),
				),
				'default' => 'dateline'
			),
			'dateline' => array(
				'title' => 'groupattachment_dateline',
				'type' => 'mradio',
				'value' => array(
					array('', 'groupattachment_dateline_nolimit'),
					array('3600', 'groupattachment_dateline_hour'),
					array('86400', 'groupattachment_dateline_day'),
					array('604800', 'groupattachment_dateline_week'),
					array('2592000', 'groupattachment_dateline_month'),
				),
				'default' => ''
			),
			'titlelength' => array(
				'title' => 'groupattachment_titlelength',
				'type' => 'text',
				'default' => 40
			),
			'summarylength' => array(
				'title' => 'groupattachment_summarylength',
				'type' => 'text',
				'default' => 80
			),
			'startrow' => array(
				'title' => 'groupattachment_startrow',
				'type' => 'text',
				'default' => 0
			),
		);
	}

	function name() {
		return lang('blockclass', 'blockclass_groupattachment_script_groupattachment');
	}

	function blockclass() {
		return array('attachment', lang('blockclass', 'blockclass_group_attachment'));
	}

	function fields() {
		return array(
				'url' => array('name' => lang('blockclass', 'blockclass_groupattachment_field_url'), 'formtype' => 'text', 'datatype' => 'string'),
				'title' => array('name' => lang('blockclass', 'blockclass_groupattachment_field_title'), 'formtype' => 'title', 'datatype' => 'title'),
				'pic' => array('name' => lang('blockclass', 'blockclass_groupattachment_field_pic'), 'formtype' => 'pic', 'datatype' => 'pic'),
				'summary' => array('name' => lang('blockclass', 'blockclass_groupattachment_field_summary'), 'formtype' => 'summary', 'datatype' => 'summary'),
				'threadurl' => array('name' => lang('blockclass', 'blockclass_groupattachment_field_threadurl'), 'formtype' => 'text', 'datatype' => 'text'),
				'threadsubject' => array('name' => lang('blockclass', 'blockclass_groupattachment_field_threadsubject'), 'formtype' => 'text', 'datatype' => 'text'),
				'threadsummary' => array('name' => lang('blockclass', 'blockclass_attachment_field_threadsummary'), 'formtype' => 'text', 'datatype' => 'text'),
				'filesize' => array('name' => lang('blockclass', 'blockclass_groupattachment_field_filesize'), 'formtype' => 'text', 'datatype' => 'string'),
				'author' => array('name' => lang('blockclass', 'blockclass_groupattachment_field_author'), 'formtype' => 'text', 'datatype' => 'string'),
				'authorid' => array('name' => lang('blockclass', 'blockclass_groupattachment_field_authorid'), 'formtype' => 'text', 'datatype' => 'int'),
				'dateline' => array('name'=>lang('blockclass', 'blockclass_groupattachment_field_dateline'), 'formtype' => 'date', 'datatype'=>'date'),
				'downloads' => array('name'=>lang('blockclass', 'blockclass_groupattachment_field_downloads'), 'formtype' => 'text', 'datatype'=>'int'),
				'hourdownloads' => array('name'=>lang('blockclass', 'blockclass_groupattachment_field_hourdownloads'), 'formtype' => 'text', 'datatype'=>'int'),
				'todaydownloads' => array('name'=>lang('blockclass', 'blockclass_groupattachment_field_todaydownloads'), 'formtype' => 'text', 'datatype'=>'int'),
				'weekdownloads' => array('name'=>lang('blockclass', 'blockclass_groupattachment_field_weekdownloads'), 'formtype' => 'text', 'datatype'=>'int'),
				'monthdownloads' => array('name'=>lang('blockclass', 'blockclass_groupattachment_field_monthdownloads'), 'formtype' => 'text', 'datatype'=>'int'),
			);
	}

	function fieldsconvert() {
		return array(
				'forum_attachment' => array(
					'name' => lang('blockclass', 'blockclass_forum_attachment'),
					'script' => 'attachment',
					'searchkeys' => array(),
					'replacekeys' => array(),
				),
				'space_pic' => array(
					'name' => lang('blockclass', 'blockclass_space_pic'),
					'script' => 'pic',
					'searchkeys' => array('author', 'authorid', 'downloads'),
					'replacekeys' => array('username', 'uid', 'viewnum'),
				),
			);
	}

	function getsetting() {
		global $_G;
		$settings = $this->settings;

		if($settings['gtids']) {
			loadcache('grouptype');
			$settings['gtids']['value'][] = array(0, lang('portalcp', 'block_all_type'));
			foreach($_G['cache']['grouptype']['first'] as $gid=>$group) {
				$settings['gtids']['value'][] = array($gid, $group['name']);
				if($group['secondlist']) {
					foreach($group['secondlist'] as $subgid) {
						$settings['gtids']['value'][] = array($subgid, '&nbsp;&nbsp;'.$_G['cache']['grouptype']['second'][$subgid]['name']);
					}
				}
			}
		}
		return $settings;
	}

	function cookparameter($parameter) {
		return $parameter;
	}

	function getdata($style, $parameter) {
		global $_G;

		$parameter = $this->cookparameter($parameter);

		loadcache('grouptype');
		$typeids = array();
		if(!empty($parameter['gtids'])) {
			if($parameter['gtids'][0] == '0') {
				unset($parameter['gtids'][0]);
			}
			$typeids = $parameter['gtids'];
		}
		if(empty($typeids)) $typeids = array_keys($_G['cache']['grouptype']['second']);
		$startrow	= isset($parameter['startrow']) ? intval($parameter['startrow']) : 0;
		$items		= isset($parameter['items']) ? intval($parameter['items']) : 10;
		$titlelength	= !empty($parameter['titlelength']) ? intval($parameter['titlelength']) : 40;
		$summarylength	= !empty($parameter['summarylength']) ? intval($parameter['summarylength']) : 80;
		$digest		= isset($parameter['digest']) ? $parameter['digest'] : 0;
		$special	= isset($parameter['special']) ? $parameter['special'] : array();
		$rewardstatus	= isset($parameter['rewardstatus']) ? intval($parameter['rewardstatus']) : 0;
		$orderby = isset($parameter['orderby']) ? (in_array($parameter['orderby'],array('dateline','downloads')) ? $parameter['orderby'] : 'dateline') : 'dateline';
		$dateline = isset($parameter['dateline']) ? intval($parameter['dateline']) : '8640000';
		$threadmethod = !empty($parameter['threadmethod']) ? 1 : 0;
		$isimage = isset($parameter['isimage']) ? intval($parameter['isimage']) : '';

		$bannedids = !empty($parameter['bannedids']) ? explode(',', $parameter['bannedids']) : array();

		if($typeids) {
			$query = DB::query('SELECT f.fid, f.name, ff.description FROM '.DB::table('forum_forum')." f LEFT JOIN ".DB::table('forum_forumfield')." ff ON f.fid = ff.fid WHERE f.fup IN (".dimplode($typeids).")");
			while($value = DB::fetch($query)) {
				$fids[] = intval($value['fid']);
			}
			$fids = array_unique($fids);
		}
		$datalist = $list = array();
		$sql = ($fids ? ' AND t.fid IN ('.dimplode($fids).')' : '')
			.($tids ? ' AND t.tid IN ('.dimplode($tids).')' : '')
			.($digest ? ' AND t.digest IN ('.dimplode($digest).')' : '')
			.($special ? ' AND t.special IN ('.dimplode($special).')' : '')
			.((in_array(3, $special) && $rewardstatus) ? ($rewardstatus == 1 ? ' AND t.price < 0' : ' AND t.price > 0') : '')
			. " AND t.isgroup='1'";
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
		$sqlban = !empty($bannedids) ? ' AND attach.tid NOT IN ('.dimplode($bannedids).')' : '';
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
				'summary' => $data['description'] ? cutstr(str_replace('\\\'', '&#39;', $data['description']), $summarylength, '') : '',
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