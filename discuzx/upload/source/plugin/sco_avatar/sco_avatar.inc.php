<?php

include_once 'sco_avatar.class.php';

// 建立 class plugin_$identifier
$plugin_self = _sco_dx_plugin::_instance($identifier, $module);

$plugin_self->_my_avatar_types_list();

$plugin_self->_my_avatar_pics(
	$plugin_self->_my_avatar_view_path(getgpc('avatar_view_path'))
);

// 取出值給模板使用
extract($plugin_self->attr['global']);

include $plugin_self->_template('spacecp_avatar');

echo '<pre>';

$a_file = getgpc('a_file');

if (empty($a_file) || empty($avatar_pics[$a_file])) {
	unset($a_file);
} else {
	$a_file = $avatar_pics[$a_file];
}

if (!empty($a_file)) {
	$member_uc = $plugin_self->_my_avatar_user_save($_G['uid'], $_G['siteurl'].$a_file);
}

$member_uc2 = $plugin_self->_my_avatar_user_get($_G['uid']);

var_dump(array(
	$a_file,

	$member_uc,
	$member_uc2,
));

var_dump(array(
	$plugin_self,
));

echo '</pre>';

?>