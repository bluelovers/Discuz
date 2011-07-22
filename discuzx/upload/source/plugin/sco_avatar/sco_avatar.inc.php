<?php

include_once 'sco_avatar.class.php';

// 建立 class plugin_$identifier
$plugin_self = _sco_dx_plugin::_instance($identifier, $module);

$avatar_base_path = $plugin_self->attr['directory'].'image/avatar/';

$plugin_self->_my_avatar_types_list();

$plugin_self->_my_avatar_pics();

extract($plugin_self->attr['global']);

include $plugin_self->_template('spacecp_avatar');

echo '<pre>';

$a_file = getgpc('a_file');

var_dump(array(
	$a_file,
));

if (empty($a_file) || empty($avatar_pics[$a_file])) {
	unset($a_file);
} else {
	$a_file = $avatar_pics[$a_file];
}

var_dump(array(
	$a_file,
));

if (!empty($a_file)) {
	$member_uc = $plugin_self
		->_uc_init()
		->_uc_call('sc', 'set_user_fields', array(
			'uid' => $_G['uid'],
			'fields'=> array(
				'avatar' => $_G['siteurl'].$a_file,
			),
	));
}

$member_uc2 = $plugin_self
	->_uc_init()
	->_uc_call('sc', 'get_user_fields', array(
		'uid' => $_G['uid'],
		'fields'=> array(
			'avatar',
		),
));

var_dump(array(
	$member_uc,
	$member_uc2,
));

var_dump(array(
	$plugin_self,
));

echo '</pre>';

?>