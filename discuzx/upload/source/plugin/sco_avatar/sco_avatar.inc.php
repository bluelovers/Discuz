<?php

include_once 'sco_avatar.class.php';

// 建立 class plugin_$identifier
$plugin_self = _sco_dx_plugin::_instance($identifier, $module);

echo '<pre>';

var_dump(array(
	$identifier, $module,
	$plugin_self
));

dexit();

?>