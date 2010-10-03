<?php

/**
 * DiscuzX Convert
 *
 * $Id: usergroup.php 15815 2010-08-27 02:56:14Z monkey $
 */

$curprg = basename(__FILE__);

$table_source = $db_source->tablepre.'usergroups';
$table_target = $db_target->tablepre.'common_usergroup';
$table_target_field = $table_target.'_field';

$limit = 100;
$nextid = 0;

$start = getgpc('start');
if($start == 0) {
	$db_target->query("TRUNCATE $table_target");
	$db_target->query("TRUNCATE $table_target_field");
}

$usergroup = array('groupid', 'radminid', 'type', 'system', 'grouptitle', 'creditshigher', 'creditslower', 'stars', 'color', 'allowvisit', 'allowsendpm', 'allowinvite', 'allowmailinvite', 'maxinvitenum', 'inviteprice', 'maxinviteday');
$usergroup_field = array('groupid', 'readaccess', 'allowpost', 'allowreply', 'allowpostpoll', 'allowpostreward', 'allowposttrade', 'allowpostactivity', 'allowdirectpost', 'allowgetattach', 'allowpostattach', 'allowvote', 'allowmultigroups', 'allowsearch', 'allowcstatus', 'allowinvisible', 'allowtransfer', 'allowsetreadperm', 'allowsetattachperm', 'allowhidecode', 'allowhtml', 'allowhidecode', 'allowhtml', 'allowanonymous', 'allowsigbbcode', 'allowsigimgcode', 'disableperiodctrl', 'reasonpm', 'maxprice', 'maxsigsize', 'maxattachsize', 'maxsizeperday', 'maxpostsperhour', 'attachextensions', 'raterange', 'mintradeprice', 'maxtradeprice', 'allowhidecode', 'allowhtml', 'allowanonymous', 'allowsigbbcode', 'allowsigimgcode', 'disableperiodctrl', 'reasonpm', 'maxprice', 'maxsigsize', 'maxattachsize', 'maxsizeperday', 'maxpostsperhour', 'attachextensions', 'raterange', 'mintradeprice', 'maxtradeprice', 'minrewardprice', 'maxrewardprice', 'magicsdiscount', 'maxmagicsweight', 'allowpostdebate', 'tradestick', 'exempt', 'maxattachnum', 'allowposturl', 'allowrecommend', 'edittimelimit', 'allowpostrushreply');

// bluelovers
$fixgroupid = array();
// bluelovers

$userdata = $userfielddata = array();
$query = $db_source->query("SELECT * FROM $table_source WHERE groupid>'$start' ORDER BY groupid LIMIT $limit");
while ($data = $db_source->fetch_array($query)) {

	$nextid = $data['groupid'];

	// bluelovers
	$fixgroupid[] = $nextid;
	// bluelovers

	$data  = daddslashes($data, 1);

	foreach($usergroup as $field) {
		$userdata[$field]= $data[$field];
	}

	foreach($usergroup_field as $field) {
		$userfielddata[$field]= $data[$field];
	}

	$userdatalist = implode_field_value($userdata, ',', db_table_fields($db_target, $table_target));
	$userfielddatalist = implode_field_value($userfielddata, ',', db_table_fields($db_target, $table_target_field));

//	$db_target->query("INSERT INTO $table_target SET $userdatalist");
//	$db_target->query("INSERT INTO $table_target_field SET $userfielddatalist");
	$db_target->query("REPLACE INTO $table_target SET $userdatalist");
	$db_target->query("REPLACE INTO $table_target_field SET $userfielddatalist");
}

// bluelovers
if ($fixgroupid = implode(',', (array)$fixgroupid)) {

	$sqlfix = "groupid>'$start' AND groupid NOT IN($fixgroupid)";
	if ($nextid) {
		$sqlfix .= " AND groupid<'$nextid'";
	}

	$db_target->query("DELETE FROM $table_target WHERE $sqlfix ORDER BY groupid");
	$db_target->query("DELETE FROM $table_target_field WHERE $sqlfix ORDER BY groupid");
}
// bluelovers

if($nextid) {
	showmessage("繼續轉換數據表 ".$table_source." groupid > $nextid", "index.php?a=$action&source=$source&prg=$curprg&start=$nextid");

// bluelovers
} else {
	$count = $db_target->result_first("SELECT count(*) FROM $table_source");
	$count += 1;

	$db_target->query("ALTER TABLE $table_target AUTO_INCREMENT =$count");
	$db_target->query("ALTER TABLE $table_target_field AUTO_INCREMENT =$count");
// bluelovers

}

?>