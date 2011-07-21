<?php

include_once 'sco_avatar.class.php';

// 建立 class plugin_$identifier
$plugin_self = _sco_dx_plugin::_instance($identifier, $module);

$avatar_path = getgpc('avatar_path', null,'default', 1);

$_loop_avatar = $plugin_self->_loop_glob($plugin_self->attr['directory'].'image/avatar/'.$avatar_path, '*');

include template('common/header');

include $plugin_self->_template('avatar');

echo '<pre>';

loaducenter();
$member_uc = uc_api_call('sc', 'set_user_fields', array(
	'uid' => $_G['uid'],
	'fields'=> array(
		'avatar' => $_G['siteurl'].array_shift($_loop_avatar),
	),
));

$member_uc2 = uc_api_call('sc', 'get_user_fields', array(
	'uid' => $_G['uid'],
	'fields'=> array(
		'avatar',
	),
));

var_dump(array(
	$member_uc,
	$member_uc2
));

var_dump(array(
	$plugin_self,
));

echo '</pre>';

include template('common/footer');

?>