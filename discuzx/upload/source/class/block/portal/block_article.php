<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_article.php 11853 2010-06-17 09:23:42Z xupeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
class block_article {
	var $setting = array();
	function block_article() {
		$this->setting = array(
			'aids'	=> array(
				'title' => 'articlelist_aids',
				'type' => 'text',
				'value' => ''
			),
			'uids'	=> array(
				'title' => 'articlelist_uids',
				'type' => 'text',
				'value' => ''
			),
			'catid' => array(
				'title' => 'articlelist_catid',
				'type' => 'mselect',
				'value' => array(
				),
			),
			'tag' => array(
				'title' => 'articlelist_tag',
				'type' => 'mcheckbox',
				'value' => array(
				),
			),
			'picrequired' => array(
				'title' => 'articlelist_picrequired',
				'type' => 'radio',
				'default' => '0'
			),
			'starttime' => array(
				'title' => 'articlelist_starttime',
				'type' => 'calendar',
				'default' => ''
			),
			'endtime' => array(
				'title' => 'articlelist_endtime',
				'type' => 'calendar',
				'default' => ''
			),
			'picrequired' => array(
				'title' => 'articlelist_picrequired',
				'type' => 'radio',
				'default' => '0'
			),
			'orderby' => array(
				'title' => 'articlelist_orderby',
				'type' => 'mradio',
				'value' => array(
					array('dateline', 'articlelist_orderby_dateline'),
					array('viewnum', 'articlelist_orderby_viewnum'),
					array('commentnum', 'articlelist_orderby_commentnum'),
				),
				'default' => 'dateline'
			),
			'titlelength' => array(
				'title' => 'articlelist_titlelength',
				'type' => 'text',
				'default' => 40
			),
			'summarylength'	=> array(
				'title' => 'articlelist_summarylength',
				'type' => 'text',
				'default' => 80
			),
			'startrow' => array(
				'title' => 'articlelist_startrow',
				'type' => 'text',
				'default' => 0
			),
		);
	}

	function name() {
		return lang('blockclass', 'blockclass_article_script_article');
	}

	function blockclass() {
		return array('article', lang('blockclass', 'blockclass_portal_article'));
	}

	function fields() {
		return array(
				'uid' => array('name' => lang('blockclass', 'blockclass_article_field_uid'), 'formtype' => 'text', 'datatype' => 'int'),
				'username' => array('name' => lang('blockclass', 'blockclass_article_field_username'), 'formtype' => 'text', 'datatype' => 'string'),
				'avatar' => array('name' => lang('blockclass', 'blockclass_article_field_avatar'), 'formtype' => 'text', 'datatype' => 'string'),
				'avatar_big' => array('name' => lang('blockclass', 'blockclass_article_field_avatar_big'), 'formtype' => 'text', 'datatype' => 'string'),
				'url' => array('name' => lang('blockclass', 'blockclass_article_field_url'), 'formtype' => 'text', 'datatype' => 'string'),
				'title' => array('name' => lang('blockclass', 'blockclass_article_field_title'), 'formtype' => 'title', 'datatype' => 'title'),
				'pic' => array('name' => lang('blockclass', 'blockclass_article_field_pic'), 'formtype' => 'pic', 'datatype' => 'pic'),
				'summary' => array('name' => lang('blockclass', 'blockclass_article_field_summary'), 'formtype' => 'summary', 'datatype' => 'summary'),
				'dateline' => array('name' => lang('blockclass', 'blockclass_article_field_dateline'), 'formtype' => 'date', 'datatype' => 'date'),
				'caturl' => array('name' => lang('blockclass', 'blockclass_article_field_caturl'), 'formtype' => 'text', 'datatype' => 'string'),
				'catname' => array('name' => lang('blockclass', 'blockclass_article_field_catname'), 'formtype' => 'text', 'datatype' => 'string'),
				'articles' => array('name' => lang('blockclass', 'blockclass_article_field_articles'), 'formtype' => 'text', 'datatype' => 'int'),
				'viewnum' => array('name' => lang('blockclass', 'blockclass_article_field_viewnum'), 'formtype' => 'text', 'datatype' => 'int'),
				'commentnum' => array('name' => lang('blockclass', 'blockclass_article_field_commentnum'), 'formtype' => 'text', 'datatype' => 'int'),
			);
	}

	function fieldsconvert() {
		return array(
				'forum_thread' => array(
					'name' => lang('blockclass', 'blockclass_forum_thread'),
					'script' => 'thread',
					'searchkeys' => array('username', 'uid', 'caturl', 'catname', 'articles', 'viewnum', 'commentnum'),
					'replacekeys' => array('author', 'authorid', 'forumurl', 'forumname', 'posts', 'views', 'replies'),
				),
				'group_thread' => array(
					'name' => lang('blockclass', 'blockclass_group_thread'),
					'script' => 'groupthread',
					'searchkeys' => array('username', 'uid', 'caturl', 'catname', 'articles', 'viewnum', 'commentnum'),
					'replacekeys' => array('author', 'authorid', 'groupurl', 'groupname', 'posts', 'views', 'replies'),
				),
				'space_blog' => array(
					'name' => lang('blockclass', 'blockclass_space_blog'),
					'script' => 'blog',
					'searchkeys' => array('commentnum'),
					'replacekeys' => array('replynum'),
				),
			);
	}

	function getsetting() {
		global $_G;
		$settings = $this->setting;

		if($settings['catid']) {
			$settings['catid']['value'][] = array(0, lang('portalcp', 'block_all_category'));
			loadcache('portalcategory');
			foreach($_G['cache']['portalcategory'] as $value) {
				if($value['level'] == 0) {
					$settings['catid']['value'][] = array($value['catid'], $value['catname']);
					if($value['children']) {
						foreach($value['children'] as $catid2) {
							$value2 = $_G['cache']['portalcategory'][$catid2];
							$settings['catid']['value'][] = array($value2['catid'], '-- '.$value2['catname']);
							if($value2['children']) {
								foreach($value2['children'] as $catid3) {
									$value3 = $_G['cache']['portalcategory'][$catid3];
									$settings['catid']['value'][] = array($value3['catid'], '---- '.$value3['catname']);
								}
							}
						}
					}
				}
			}
		}
		if($settings['tag']) {
			$tagnames = article_tagnames();
			foreach($tagnames as $k=>$v) {
				$settings['tag']['value'][] = array($k, $v);
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
		$aids		= !empty($parameter['aids']) ? explode(',', $parameter['aids']) : array();
		$uids		= !empty($parameter['uids']) ? explode(',', $parameter['uids']) : array();
		$tag		= !empty($parameter['tag']) ? $parameter['tag'] : array();
		$starttime	= !empty($parameter['starttime']) ? strtotime($parameter['starttime']) : 0;
		$endtime	= !empty($parameter['endtime']) ? strtotime($parameter['endtime']) : 0;
		$startrow	= isset($parameter['startrow']) ? intval($parameter['startrow']) : 0;
		$items		= isset($parameter['items']) ? intval($parameter['items']) : 10;
		$titlelength = isset($parameter['titlelength']) ? intval($parameter['titlelength']) : 40;
		$summarylength = isset($parameter['summarylength']) ? intval($parameter['summarylength']) : 80;
		$orderby	= in_array($parameter['orderby'], array('dateline', 'viewnum', 'commentnum')) ? $parameter['orderby'] : 'dateline';
		$catid = array();
		if(!empty($parameter['catid'])) {
			if($parameter['catid'][0] == '0') {
				unset($parameter['catid'][0]);
			}
			$catid = $parameter['catid'];
		}

		$picrequired = !empty($parameter['picrequired']) ? 1 : 0;

		$bannedids = !empty($parameter['bannedids']) ? explode(',', $parameter['bannedids']) : array();

		loadcache('portalcategory');

		$list = array();
		$wheres = array();
		if($aids) {
			$wheres[] = 'at.aid IN ('.dimplode($aids).')';
		}
		if($uids) {
			$wheres[] = 'at.uid IN ('.dimplode($uids).')';
		}
		if($catid) {
			include_once libfile('function/portalcp');
			$childids = array();
			foreach($catid as $id) {
				if($_G['cache']['portalcategory'][$id]['disallowpublish']) {
					$childids = array_merge($childids, category_get_childids('portal', $id));
				}
			}
			$catid = array_merge($catid, $childids);
			$catid = array_unique($catid);
			$wheres[] = 'at.catid IN ('.dimplode($catid).')';
		}
		if($style['getpic'] && $picrequired) {
			$wheres[] = "at.pic != ''";
		}
		if($starttime) {
			$wheres[] = "at.dateline >= '$starttime'";
		}
		if($endtime) {
			$wheres[] = "at.dateline <= '$endtime'";
		}
		if($bannedids) {
			$wheres[] = 'at.aid NOT IN ('.dimplode($bannedids).')';
		}
		$wheres[] = "at.status='0'";
		if(is_array($tag)) {
			$article_tags = array();
			foreach($tag as $k) {
				$article_tags[$k] = 1;
			}
			include_once libfile('function/portalcp');
			$v=article_make_tag($article_tags);
			if($v > 0) {
				$wheres[] = "(at.tag & $v) = $v";
			}
		}
		$wheresql = $wheres ? implode(' AND ', $wheres) : '1';
		$orderby = ($orderby == 'dateline') ? 'at.dateline DESC ' : "ac.$orderby DESC";
		$query = DB::query("SELECT at.*, ac.viewnum, ac.commentnum FROM ".DB::table('portal_article_title')." at LEFT JOIN ".DB::table('portal_article_count')." ac ON at.aid=ac.aid WHERE $wheresql ORDER BY $orderby LIMIT $startrow, $items");
		while($data = DB::fetch($query)) {
			if(empty($data['pic'])) {
				$data['pic'] = STATICURL.'image/common/nophoto.gif';
				$data['picflag'] = '0';
			} else {
				$data['pic'] = 'portal/'.$data['pic'];
				$data['picflag'] = $data['remote'] == '1' ? '2' : '1';
			}
			$list[] = array(
				'id' => $data['aid'],
				'idtype' => 'aid',
				'title' => cutstr($data['title'], $titlelength, ''),
				'url' => 'portal.php?mod=view&aid='.$data['aid'],
				'pic' => $data['pic'],
				'picflag' => $data['picflag'],
				'summary' => cutstr(strip_tags($data['summary']), $summarylength, ''),
				'fields' => array(
					'uid'=>$data['uid'],
					'username'=>$data['username'],
					'avatar' => avatar($data['uid'], 'small', true),
					'avatar_big' => avatar($data['uid'], 'middle', true),
					'fulltitle' => $data['title'],
					'dateline'=>$data['dateline'],
					'caturl'=> $_G['cache']['portalcategory'][$data['catid']]['caturl'],
					'catname' => $_G['cache']['portalcategory'][$data['catid']]['catname'],
					'articles' => $_G['cache']['portalcategory'][$data['catid']]['articles'],
					'viewnum' => intval($data['viewnum']),
					'commentnum' => intval($data['commentnum'])
				)
			);
		}
		return array('html' => '', 'data' => $list);
	}
}

?>