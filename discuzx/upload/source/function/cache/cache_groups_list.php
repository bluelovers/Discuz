<?php

/**
 * @author bluelovers
 * @copyright 2011
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

/**
 *
 * $data['groups_list'] = array (
  211 =>
  array (
    'fid' => '211',
    'fup' => '209',
    'recommend' => '',
    'name' => '小說接龍',
    'description' => '如同名字所說，就是小說接龍(?',
    'password' => '',
    'icon' => 'static/image/common/groupicon.gif',
    'jointype' => '0',
    'gviewperm' => '1',
    'dateline' => '2011-8-2',
    'lastupdate' => '',
    'founderuid' => '77868',
    'foundername' => '天晴雪鍊',
    'banner' => '',
    'iconstatus' => 0,
  ),
 */
function build_cache_groups_list() {
	$data = array();

	$orderby = 'displayorder';
	$orderbyarray = array('displayorder' => 'f.fup, f.displayorder DESC, f.name ASC', 'dateline' => 'ff.dateline DESC', 'lastupdate' => 'ff.lastupdate DESC', 'membernum' => 'ff.membernum DESC', 'thread' => 'f.threads DESC', 'activity' => 'f.commoncredits DESC');
	$useindex = $orderby == 'displayorder' ? 'USE INDEX(fup_type)' : '';
	$orderby = !empty($orderby) && $orderbyarray[$orderby] ? "ORDER BY ".$orderbyarray[$orderby] : '';

	$fieldadd = ' ,ff.*';
	$fieldsql = 'f.fid, f.fup, f.recommend, f.displayorder, f.name '.$fieldadd;

	$orderid = 0;

	$group_cache_field = array_flip(array(
		'fid',
		'fup',
		'name',
		'description',

		'iconstatus',
		'icon',
		'banner',

		'dateline',
		'lastupdate',

		'jointype',
		'gviewperm',

		'founderuid',
		'foundername',

		'password',
		'recommend',
	));

	$grouplist = array();

	$query = DB::query("SELECT $fieldsql FROM ".DB::table('forum_forum')." f $useindex LEFT JOIN ".DB::table("forum_forumfield")." ff ON ff.fid=f.fid WHERE f.type='sub' AND f.status=3 $orderby");
	while($group = DB::fetch($query)) {
		$group['iconstatus'] = $group['icon'] ? 1 : 0;
		isset($group['icon']) && $group['icon'] = get_groupimg($group['icon'], 'icon');
		isset($group['banner']) && $group['banner'] = get_groupimg($group['banner']);

		isset($group['dateline']) && $group['dateline'] = $group['dateline'] ? dgmdate($group['dateline'], 'd') : '';
		isset($group['lastupdate']) && $group['lastupdate'] = $group['lastupdate'] ? dgmdate($group['lastupdate'], 'd') : '';

		$grouplist[$group['fid']] = array_intersect_key($group, $group_cache_field);
	}

	$data = $grouplist;

	save_syscache('groups_list', $data);
}

?>