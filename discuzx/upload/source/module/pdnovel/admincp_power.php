<?php

shownav('pdnovel', 'power');

if(!submitcheck('powersubmit')){

	showsubmenu('power');
	showformheader("pdnovel&operation=power");
	showtableheader();

	$query = DB::query("SELECT groupid, grouptitle FROM ".DB::table('common_usergroup')." ORDER BY groupid");
	while($ugroup = DB::fetch($query)) {
		$ugrouparr[] = array($ugroup['groupid'], $ugroup['grouptitle'], '1');
	}
	$query = DB::query("SELECT * FROM ".DB::table('pdmodule_power')." WHERE moduleid = 11");
	while($power = DB::fetch($query)){
		$power['power'] = $power['power'] ? unserialize($power['power']) : array();
		$powerarr = array('power['.$power['action'].']', $ugrouparr, 'isfloat');
		showtitle($power['name']);
		showsetting('', $powerarr, $power['power'], 'omcheckbox');
	}
	
	showtablefooter();
	showsubmit('powersubmit');
	showformfooter();

}else {

	foreach ($_G['gp_power'] as $key => $value){
		DB::update('pdmodule_power', array('power' => serialize($value)), "action='$key'");
	}
	cpmsg('threadtype_infotypes_option_succeed', 'action=pdnovel&operation=power', 'succeed');
	
}

?>