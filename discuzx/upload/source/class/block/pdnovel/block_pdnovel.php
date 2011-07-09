<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class block_pdnovel {
	var $setting = array();
	function block_pdnovel() {
		$this->setting = array(
			'novelids'	=> array(
				'title' => 'pdnovellist_novelids',
				'type' => 'text',
				'value' => ''
			),
			'authorids'	=> array(
				'title' => 'pdnovellist_authorids',
				'type' => 'text',
				'value' => ''
			),
			'catid' => array(
				'title' => 'pdnovellist_catid',
				'type' => 'mselect',
				'value' => array(
				),
			),
			'picrequired' => array(
				'title' => 'pdnovellist_picrequired',
				'type' => 'radio',
				'default' => '0'
			),
			'starttime' => array(
				'title' => 'pdnovellist_starttime',
				'type' => 'calendar',
				'default' => ''
			),
			'endtime' => array(
				'title' => 'pdnovellist_endtime',
				'type' => 'calendar',
				'default' => ''
			),
			'orderby' => array(
				'title' => 'pdnovellist_orderby',
				'type' => 'mradio',
				'value' => array(
					array('lastupdate', 'pdnovellist_orderby_lastupdate'),
					array('dayvisit', 'pdnovellist_orderby_dayvisit'),
					array('weekvisit', 'pdnovellist_orderby_weekvisit'),
					array('monthvisit', 'pdnovellist_orderby_monthvisit'),
					array('allvisit', 'pdnovellist_orderby_allvisit'),
					array('dayvote', 'pdnovellist_orderby_dayvote'),
					array('weekvote', 'pdnovellist_orderby_weekvote'),
					array('monthvote', 'pdnovellist_orderby_monthvote'),
					array('allvote', 'pdnovellist_orderby_allvote'),	
					array('words', 'pdnovellist_orderby_words'),
					array('allmark', 'pdnovellist_orderby_allmark'),
					array('comments', 'pdnovellist_orderby_comments'),
				),
				'default' => 'lastupdate'
			),
			'titlelength' => array(
				'title' => 'pdnovellist_titlelength',
				'type' => 'text',
				'default' => 20
			),
			'summarylength'	=> array(
				'title' => 'pdnovellist_summarylength',
				'type' => 'text',
				'default' => 80
			),
			'startrow' => array(
				'title' => 'pdnovellist_startrow',
				'type' => 'text',
				'default' => 0
			),
		);
	}

	function name() {
		return lang('block_pdnovel', 'blockclass_pdnovel_script_novel');
	}

	function blockclass() {
		return array('pdnovel', lang('block_pdnovel', 'blockclass_pdnovel_pdnovel'));
	}

	function fields() {
		return array(
				'authorid' => array('name' => lang('block_pdnovel', 'blockclass_pdnovel_field_authorid'), 'formtype' => 'text', 'datatype' => 'int'),
				'author' => array('name' => lang('block_pdnovel', 'blockclass_pdnovel_field_author'), 'formtype' => 'text', 'datatype' => 'string'),
				'authorurl' => array('name' => lang('block_pdnovel', 'blockclass_pdnovel_field_authorurl'), 'formtype' => 'text', 'datatype' => 'string'),
				'avatar' => array('name' => lang('block_pdnovel', 'blockclass_pdnovel_field_avatar'), 'formtype' => 'text', 'datatype' => 'string'),
				'avatar_middle' => array('name' => lang('block_pdnovel', 'blockclass_pdnovel_field_avatar_middle'), 'formtype' => 'text', 'datatype' => 'string'),
				'avatar_big' => array('name' => lang('block_pdnovel', 'blockclass_pdnovel_field_avatar_big'), 'formtype' => 'text', 'datatype' => 'string'),
				'url' => array('name' => lang('block_pdnovel', 'blockclass_pdnovel_field_url'), 'formtype' => 'text', 'datatype' => 'string'),
				'title' => array('name' => lang('block_pdnovel', 'blockclass_pdnovel_field_title'), 'formtype' => 'title', 'datatype' => 'title'),
				'pic' => array('name' => lang('block_pdnovel', 'blockclass_pdnovel_field_pic'), 'formtype' => 'pic', 'datatype' => 'pic'),
				'summary' => array('name' => lang('block_pdnovel', 'blockclass_pdnovel_field_summary'), 'formtype' => 'summary', 'datatype' => 'summary'),
				'lastupdate' => array('name' => lang('block_pdnovel', 'blockclass_pdnovel_field_dateline'), 'formtype' => 'date', 'datatype' => 'date'),
				'upname' => array('name' => lang('block_pdnovel', 'blockclass_pdnovel_field_upname'), 'formtype' => 'text', 'datatype' => 'string'),
				'upurl' => array('name' => lang('block_pdnovel', 'blockclass_pdnovel_field_upurl'), 'formtype' => 'text', 'datatype' => 'string'),
				'catname' => array('name' => lang('block_pdnovel', 'blockclass_pdnovel_field_catname'), 'formtype' => 'text', 'datatype' => 'string'),
				'caturl' => array('name' => lang('block_pdnovel', 'blockclass_pdnovel_field_caturl'), 'formtype' => 'text', 'datatype' => 'string'),
				'lastchapter' => array('name' => lang('block_pdnovel', 'blockclass_pdnovel_field_lastchapter'), 'formtype' => 'text', 'datatype' => 'string'),
				'lastchapterurl' => array('name' => lang('block_pdnovel', 'blockclass_pdnovel_field_lastchapterurl'), 'formtype' => 'text', 'datatype' => 'string'),
				'dayvisit' => array('name' => lang('block_pdnovel', 'blockclass_pdnovel_field_dayvisit'), 'formtype' => 'text', 'datatype' => 'int'),
				'weekvisit' => array('name' => lang('block_pdnovel', 'blockclass_pdnovel_field_weekvisit'), 'formtype' => 'text', 'datatype' => 'int'),
				'monthvisit' => array('name' => lang('block_pdnovel', 'blockclass_pdnovel_field_monthvisit'), 'formtype' => 'text', 'datatype' => 'int'),
				'allvisit' => array('name' => lang('block_pdnovel', 'blockclass_pdnovel_field_allvisit'), 'formtype' => 'text', 'datatype' => 'int'),
				'dayvote' => array('name' => lang('block_pdnovel', 'blockclass_pdnovel_field_dayvote'), 'formtype' => 'text', 'datatype' => 'int'),
				'weekvote' => array('name' => lang('block_pdnovel', 'blockclass_pdnovel_field_weekvote'), 'formtype' => 'text', 'datatype' => 'int'),
				'monthvote' => array('name' => lang('block_pdnovel', 'blockclass_pdnovel_field_monthvote'), 'formtype' => 'text', 'datatype' => 'int'),
				'allvote' => array('name' => lang('block_pdnovel', 'blockclass_pdnovel_field_allvote'), 'formtype' => 'text', 'datatype' => 'int'),
				'allmark' => array('name' => lang('block_pdnovel', 'blockclass_pdnovel_field_allmark'), 'formtype' => 'text', 'datatype' => 'int'),
				'words' => array('name' => lang('block_pdnovel', 'blockclass_pdnovel_field_words'), 'formtype' => 'text', 'datatype' => 'int'),
				'comments' => array('name' => lang('block_pdnovel', 'blockclass_pdnovel_field_comments'), 'formtype' => 'text', 'datatype' => 'int'),
			);
	}

	function getsetting() {
		global $_G;
		$settings = $this->setting;

		if($settings['catid']) {
			$settings['catid']['value'][] = array(0, lang('portalcp', 'block_all_category'));
			loadcache('pdnovelcategory');
			foreach($_G['cache']['pdnovelcategory'] as $value) {
				if($value['level'] == 0) {
					$settings['catid']['value'][] = array($value['catid'], $value['catname']);
					if($value['children']) {
						foreach($value['children'] as $catid2) {
							$value2 = $_G['cache']['pdnovelcategory'][$catid2];
							$settings['catid']['value'][] = array($value2['catid'], '-- '.$value2['catname']);
						}
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
		$novelids = !empty($parameter['novelids']) ? explode(',', $parameter['novelids']) : array();
		$authorids = !empty($parameter['authorids']) ? explode(',', $parameter['authorids']) : array();
		$starttime	= !empty($parameter['starttime']) ? strtotime($parameter['starttime']) : 0;
		$endtime = !empty($parameter['endtime']) ? strtotime($parameter['endtime']) : 0;
		$startrow = isset($parameter['startrow']) ? intval($parameter['startrow']) : 0;
		$items = isset($parameter['items']) ? intval($parameter['items']) : 10;
		$titlelength = isset($parameter['titlelength']) ? intval($parameter['titlelength']) : 20;
		$summarylength = isset($parameter['summarylength']) ? intval($parameter['summarylength']) : 80;
		$orderby = in_array($parameter['orderby'], array('lastupdate', 'dayvisit', 'weekvisit', 'monthvisit', 'allvisit', 'dayvote', 'weekvote', 'monthvote', 'allvote', 'words', 'allmark', 'comments')) ? $parameter['orderby'] : 'lastupdate';
		$catid = array();
		if(!empty($parameter['catid'])) {
			if($parameter['catid'][0] == '0') {
				unset($parameter['catid'][0]);
			}
			$catid = $parameter['catid'];
		}

		$picrequired = !empty($parameter['picrequired']) ? 1 : 0;

		$bannedids = !empty($parameter['bannedids']) ? explode(',', $parameter['bannedids']) : array();

		loadcache('pdnovelcategory');

		$list = array();
		$wheres = array();
		if($novelids) {
			$wheres[] = 'novelid IN ('.dimplode($novelids).')';
		}
		if($authorids) {
			$wheres[] = 'authorid IN ('.dimplode($authorids).')';
		}
		if($catid) {
			$wheres[] = 'catid IN ('.dimplode($catid).')';
		}
		if($style['getpic'] && $picrequired) {
			$wheres[] = "cover != ''";
		}
		if($starttime) {
			$wheres[] = "lastupdate >= '$starttime'";
		}
		if($endtime) {
			$wheres[] = "lastupdate <= '$endtime'";
		}
		if($bannedids) {
			$wheres[] = 'novelid NOT IN ('.dimplode($bannedids).')';
		}
		$wheres[] = "display='0'";
		$wheresql = $wheres ? implode(' AND ', $wheres) : '1';
		$orderby = ($orderby == 'lastupdate') ? 'lastupdate DESC ' : "$orderby DESC";
		$query = DB::query("SELECT * FROM ".DB::table('pdnovel_view')." WHERE $wheresql ORDER BY $orderby LIMIT $startrow, $items");
		while($data = DB::fetch($query)) {
			if(empty($data['cover'])) {
				$data['cover'] = STATICURL.'image/common/nophoto.gif';
				$data['picflag'] = '0';
			} else {
				$data['picflag'] = 1;
			}
			$upid = $_G['cache']['pdnovelcategory'][$data['catid']]['upid'];
			$list[] = array(
				'id' => $data['novelid'],
				'idtype' => 'novelid',
				'title' => cutstr($data['name'], $titlelength, ''),
				'url' => 'pdnovel.php?mod=view&novelid='.$data['novelid'],
				'pic' => 'pdnovel/cover/'.$data['cover'],
				'picflag' => $data['picflag'],
				'summary' => cutstr(strip_tags($data['intro']), $summarylength, ''),
				'fields' => array(
					'authorid'=>$data['authorid'],
					'author'=>$data['author'],
					'authorurl'=> 'pdnovel.php?mod=search&name='.$data['author'],
					'avatar' => avatar($data['authorid'], 'small', true, false, false, $_G['setting']['ucenterurl']),
					'avatar_middle' => avatar($data['authorid'], 'middle', true, false, false, $_G['setting']['ucenterurl']),
					'avatar_big' => avatar($data['authorid'], 'big', true, false, false, $_G['setting']['ucenterurl']),
					'fulltitle' => $data['name'],
					'lastupdate'=>$data['lastupdate'],
					'upurl'=> 'pdnovel.php?mod=list&catid='.$upid,
					'upname' => $_G['cache']['pdnovelcategory'][$upid]['catname'],
					'caturl'=> 'pdnovel.php?mod=list&catid='.$data['catid'],
					'catname' => $_G['cache']['pdnovelcategory'][$data['catid']]['catname'],
					'lastchapter' => $data['lastchapter'],
					'lastchapterurl' => 'pdnovel.php?mod=read&novelid='.$data['novelid'].'&chapterid='.$data['lastchapterid'],
					'dayvisit' => intval($data['dayvisit']),
					'weekvisit' => intval($data['weekvisit']),
					'monthvisit' => intval($data['monthvisit']),
					'allvisit' => intval($data['allvisit']),
					'dayvote' => intval($data['dayvote']),
					'weekvote' => intval($data['weekvote']),
					'monthvote' => intval($data['monthvote']),
					'allvote' => intval($data['allvote']),
					'allmark' => intval($data['allmark']),
					'words' => intval($data['words']),
					'comments' => intval($data['comments'])
				)
			);
		}
		return array('html' => '', 'data' => $list);
	}

}

?>