<?php

/**
 * DiscuzX Convert
 *
 * $Id: spaceitems.php 15777 2010-08-26 04:00:58Z zhengqingpeng $
 */

$curprg = basename(__FILE__);

$table_source = $db_source->tablepre.'spaceitems';
$table_target = $db_target->tablepre.'portal_article_title';
$table_target_rel = $db_target->tablepre.'portal_article_count';

$table_source_content = $db_source->tablepre.'spacenews';

$limit = 300;
$nextid = 0;

$start = getgpc('start');

if($start == 0) {
	$db_target->query("TRUNCATE $table_target");
	$db_target->query("TRUNCATE $table_target_rel");
}

$query = $db_source->query("SELECT * FROM $table_source WHERE itemid>'$start' ORDER BY itemid LIMIT $limit");
while ($rs = $db_source->fetch_array($query)) {

	$nextid = $rs['itemid'];
	$count = $db_source->result($db_source->query("SELECT COUNT(*) FROM $table_source_content WHERE itemid='$rs[itemid]'"),0);
	$settitle = array();
	$rs['none'] = '';
	$settitle['aid'] = $rs['itemid'];
	$settitle['catid'] = $rs['catid'];
	$settitle['bid'] = $rs['none'];
	$settitle['uid'] = $rs['uid'];
	$settitle['username'] = $rs['username'];
	$settitle['title'] = $rs['subject'];
	$settitle['shorttitle'] = $rs['none'];
	$settitle['author'] = $rs['newauthor'];
	$settitle['from'] = $rs['newfrom'];
	$settitle['fromurl'] = $rs['newfromurl'];
	$settitle['url'] = $rs['newsurl'];
	$settitle['summary'] = $rs['none'];
	$settitle['pic'] = $rs['none'];
	$settitle['thumb'] = $rs['none'];
	$settitle['remote'] = $rs['none'];
	$settitle['prename'] = $rs['none'];
	$settitle['preurl'] = $rs['none'];
	$settitle['id'] = $rs['none'];
	$settitle['idtype'] = $rs['none'];
	$settitle['contents'] = $count;
	$settitle['allowcomment'] = $rs['allowreply'];
	$settitle['dateline'] = $rs['dateline'];

	$settitle  = daddslashes($settitle, 1);

	$data = implode_field_value($settitle, ',', db_table_fields($db_target, $table_target));

	$db_target->query("INSERT INTO $table_target SET $data");


	$setcount = array();
	$setcount['aid'] = $rs['itemid'];
	$setcount['viewnum'] = $rs['viewnum'];
	$setcount['commentnum'] = $rs['replynum'];
	$setcount['catid'] = $rs['catid'];
	$setcount['dateline'] = $rs['dateline'];

	$setcount  = daddslashes($setcount, 1);

	$data = implode_field_value($setcount, ',', db_table_fields($db_target, $table_target_rel));

	$db_target->query("INSERT INTO $table_target_rel SET $data");

}

if($nextid) {
	showmessage("繼續轉換數據表 ".$table_source." itemid> $nextid", "index.php?a=$action&source=$source&prg=$curprg&start=$nextid");
}

?>