<?php

include_once 'sco_avatar.class.php';

// 建立 class plugin_$identifier
$plugin_self = _sco_dx_plugin::_instance($identifier, $module);

$avatar_base_path = $plugin_self->attr['directory'].'image/avatar/';

// 取得所有 avatar 目錄
$avatar_types = array();

$avatar_types['default'] = 'default';

$d = dir($avatar_base_path);
while (false !== ($entry = $d->read())) {
	if ($entry == '.' || $entry == '..' || $entry == 'default') continue;

	$avatar_types[$entry] = $entry;
}

// 取得目前瀏覽的 avatar 目錄
$avatar_view_path = getgpc('avatar_view_path', null,'default', 1);
$avatar_view_path = in_array($avatar_view_path, $avatar_types) ? $avatar_view_path : 'default';

$imgexts = array('jpg', 'jpeg', 'gif', 'png', 'bmp');

$avatar_pics = array();
$d = dir($avatar_base_path.$avatar_view_path);
while (false !== ($entry = $d->read())) {
	if ($entry == '.' || $entry == '..'
		|| !in_array(fileext($entry), $imgexts)
	) continue;

	$avatar_pics[$entry] = $avatar_base_path.$avatar_view_path.'/'.$entry;
}

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