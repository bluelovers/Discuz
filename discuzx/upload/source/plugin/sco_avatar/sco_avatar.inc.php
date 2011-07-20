<?php

include_once 'sco_avatar.class.php';

// 建立 class plugin_$identifier
$plugin_self = _sco_dx_plugin::_instance($identifier, $module);

$avatar_path = getgpc('avatar_path', null,'default', 1);

$_loop_avatar = $plugin_self->_loop_glob($plugin_self->attr['directory'].'image/avatar/'.$avatar_path, '*');

include template('common/header');

include $plugin_self->_template('avatar');

echo '<pre>';

var_dump(array(
	$identifier, $module,
	$plugin_self,
));

echo '</pre>';

include template('common/footer');

?>