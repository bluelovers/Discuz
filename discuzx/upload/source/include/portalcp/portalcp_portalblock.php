<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: portalcp_article.php 7701 2010-04-12 06:01:33Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('function/block');
$op = in_array($_GET['op'], array('recommend', 'getblocklist', 'updateblock')) ? $_GET['op'] : 'getblocklist';
$_GET['idtype'] = htmlspecialchars($_GET['idtype']);
$_GET['id'] = intval($_GET['id']);

if(!checkperm('allowdiy') && !$admincp4 && !$admincp5 && !$admincp6) {
	showmessage('portal_nopermission', dreferer());
}
$bids = array();

if($op == 'updateblock') {
	if(submitcheck('portalcpblocksubmit')) {
		$bids = $_G['gp_bids'];
		$bids = array_map('intval', $bids);
		$bids = array_filter($bids);
		if($bids) {
			DB::query('UPDATE '.DB::table('common_block').' SET `dateline`='.TIMESTAMP.'-cachetime WHERE bid IN ('.dimplode($bids).')');
		}
		showmessage('portalcp_block_push_the_update_line', dreferer());
	} else {
		showmessage('portalcp_block_has_no_block','portal.php?mod=portalcp&ac=portalblock');
	}
} else {
	$perpage = $op == 'recommend' ? 16 : 30;
	$page = max(1,intval($_GET['page']));
	$start = ($page-1)*$perpage;
	if($start<0) $start = 0;
	$theurl = 'portal.php?mod=portalcp&ac=portalblock&op='.$op.'&idtype='.$_GET['idtype'].'&id='.$_GET['id'];
	$showfavorite = $page == 1 ? true : false;

	loadcache(array('diytemplatename'));
	loadcache('diytemplatename');

	$pagebids = $tpls = $blocks = $tplpermissions = $wherearr = $blockfavorite = $topblocks = array();
	$multi = $fields = $leftjoin = '';
	$blockfields = 'b.bid,b.blockclass,b.name,b.script,b.dateline,b.cachetime';
	$blockfavorite = block_get_favorite($_G['uid']);
	if(checkperm('allowdiy')) {
		$tpls = $_G['cache']['diytemplatename'];
	} else {
		$tplpermissions = getallowdiytemplate($_G['uid']);
		foreach($tplpermissions as $value) {
			if($value['allowmanage'] || ($value['allowrecommend'] && empty($value['needverify'])) || ($op=='recommend' && $value['allowrecommend'])) {
				$tpls[$value['targettplname']] = isset($_G['cache']['diytemplatename'][$value['targettplname']]) ? $_G['cache']['diytemplatename'][$value['targettplname']] : $value['targettplname'];
			}
		}
		$fields = ',bp.allowmanage,bp.allowrecommend,bp.needverify';
		$leftjoin = ' LEFT JOIN '.DB::table('common_block_permission').' bp ON b.bid=bp.bid';
		$wherearr[] = "bp.uid='$_G[uid]'";
		$wherearr[] = "(bp.allowmanage='1' OR (bp.allowrecommend='1'".($op == 'recommend' ? '' : "AND bp.needverify='0'")."))";
	}
	if($_GET['searchkey']) {
		$_GET['searchkey'] = trim($_GET['searchkey']);
		$showfavorite = false;
		if (preg_match('/^[#]?(\d+)$/', $_GET['searchkey'],$match)) {
			$bid = intval($match[1]);
			$wherearr[] = " (b.bid='$bid' OR b.name='$bid')";
		} else {
			$_GET['searchkey'] = stripsearchkey($_GET['searchkey']);
			$wherearr[] = " b.name LIKE '%$_GET[searchkey]%'";
			$perpage = 10000;
		}
		$_GET['searchkey'] = dhtmlspecialchars($_GET['searchkey']);
	}
	if($_GET['targettplname']) {
		$showfavorite = false;
		$targettplname = addslashes(trim($_GET['targettplname']));
		$query = DB::query("SELECT * FROM ".DB::table('common_template_block')." WHERE targettplname='$targettplname'");
		while(($value = DB::fetch($query))){
			$pagebids[] = $value['bid'];
		}
		if(!empty($pagebids)) {
			$wherearr[] = "b.bid IN (".dimplode($pagebids).")";
			$perpage = 10000;
		} else {
			$wherearr[] = "b.bid='0'";
		}
	}

	if($op == 'recommend') {

		$rewhere = array();
		switch ($_GET['idtype']) {
			case 'tid' :
				$rewhere[] = "(blockclass='forum_thread' OR blockclass='forum_activity' OR blockclass='forum_trade')";
				break;
			case 'gtid' :
				$rewhere[] = "(blockclass='group_thread' OR blockclass='group_activity' OR blockclass='group_trade')";
				break;
			case 'blogid' :
				$rewhere[] = "blockclass ='space_blog'";
				break;
			case 'picid' :
				$rewhere[] = "blockclass ='space_pic'";
				break;
			case 'aid' :
				$rewhere[] = "blockclass ='portal_article'";
				break;
		}
		$wherearr = array_merge($rewhere, $wherearr);
		$where = $wherearr ? ' WHERE '.implode(' AND ', $wherearr) : '';

		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_block').' b'."$leftjoin$where");
		if($count) {
			$query = DB::query("SELECT b.bid,b.blockclass,b.name,b.script$fields FROM ".DB::table('common_block').' b'."$leftjoin$where ORDER BY b.bid DESC LIMIT $start, $perpage");
			while(($value = DB::fetch($query))) {
				$value = formatblockvalue($value);
				if(!$value['favorite'] || !$showfavorite) {
					$blocks[$value['bid']] = $value;
				}
			}
			if(!empty($blockfavorite) && $showfavorite) {
				$blocks = $blockfavorite + $blocks;
			}
			$theurl = $_G['inajax'] ? $theurl.'&getdata=yes' : $theurl;
			if($_G['inajax']) $_G['gp_ajaxtarget'] = 'itemeditarea';
			$multi = multi($count, $perpage, $page, $theurl);
		}
	} else {
		$where = empty($wherearr) ? '' : ' WHERE '.implode(' AND ', $wherearr);
		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_block').' b'."$leftjoin$where ORDER BY b.bid DESC");
		if($count) {
			$query = DB::query("SELECT b.bid,b.blockclass,b.name,b.script,b.dateline,b.cachetime$fields FROM ".DB::table('common_block').' b'."$leftjoin$where ORDER BY b.bid DESC LIMIT $start, $perpage");
			while(($value = DB::fetch($query))) {
				$value = formatblockvalue($value);
				if(!$value['favorite'] || !$showfavorite) {
					$blocks[$value['bid']] = $value;
				}
			}
			if(!empty($blockfavorite) && $showfavorite) {
				$blocks = $blockfavorite + $blocks;
			}
			$multi = multi($count, $perpage, $page, $theurl);
		}
	}
	if($blocks) {
		$bids = array_keys($blocks);
		if($bids) {
			$query = DB::query("SELECT targettplname, bid FROM ".DB::table('common_template_block')." WHERE bid IN (".dimplode($bids).")");
			while(($value = DB::fetch($query))) {
				$diyurl = block_getdiyurl($value['targettplname']);
				$diyurl = $diyurl['url'];
				$tplname = isset($_G['cache']['diytemplatename'][$value['targettplname']]) ? $_G['cache']['diytemplatename'][$value['targettplname']] : $value['targettplname'];
				if(!isset($tpls[$value['targettplname']])) {
					$tpls[$value['targettplname']] = $tplname;
				}
				$blocks[$value['bid']]['page'][$value['targettplname']] = $diyurl ? '<a href="'.$diyurl.'" target="_blank">'.$tplname.'</a>' : $tplname;
			}
		}
	}
}

include_once template("portal/portalcp_portalblock");

function formatblockvalue($value) {
	global $blockfavorite;
	$value['name'] = empty($value['name']) ? '<strong>#'.$value['bid'].'</strong>' : $value['name'];
	$theclass = block_getclass($value['blockclass']);
	$value['blockclassname'] = $theclass['name'];
	$value['datasrc'] = $theclass['script'][$value['script']];
	$value['isrecommendable'] = block_isrecommendable($value);
	$value['perm'] = formatblockpermissoin($value);
	$value['favorite'] = isset($blockfavorite[$value['bid']]) ? true : false;
	return $value;
}
function formatblockpermissoin($block) {
	$perm = array('allowproperty' => 0, 'allowdata'=> 0);
	$bid = !empty($block) ? $block['bid'] : 0;
	if(!empty($bid)) {
		if(checkperm('allowdiy')) {
			$perm = array('allowproperty' => 1, 'allowdata'=> 1);
		} else {
			if($block['allowmanage']) {
				$perm = array('allowproperty' => 1, 'allowdata'=> 1);
			}
			if ($block['allowrecommend'] && !$block['needverify']) {
				$perm['allowdata'] = 1;
			}
		}
	}
	return $perm;
}

function block_get_favorite($uid){
	$blockfavorite = array();
	$uid = intval($uid);
	if($uid) {
		$query = DB::query('SELECT bid FROM '.DB::table('common_block_favorite')." WHERE uid='$uid' ORDER BY dateline DESC");
		while($value = DB::fetch($query)) {
			$blockfavorite[$value['bid']] = $value['bid'];
		}
	}
	$blockfields = 'b.bid,b.blockclass,b.name,b.script,b.dateline,b.cachetime';
	if(!empty($blockfavorite)) {
		if(checkperm('allowdiy')) {
			$query = DB::query("SELECT $blockfields FROM ".DB::table('common_block').' b'." WHERE b.bid IN (".dimplode($blockfavorite).")");
		} else {
			$query = DB::query("SELECT $blockfields,bp.allowmanage,bp.allowrecommend,bp.needverify FROM ".DB::table('common_block').' b'.' LEFT JOIN '.DB::table('common_block_permission').' bp ON b.bid=bp.bid'." WHERE bp.uid='$uid' AND b.bid IN (".dimplode($blockfavorite).")");
		}
		while(($value = DB::fetch($query))) {
			$value = formatblockvalue($value);
			$value['favorite'] = true;
			$blockfavorite[$value['bid']] = $value;
		}
		$blockfavorite = array_filter($blockfavorite, 'is_array');
	}
	return $blockfavorite;
}

?>