<?php

define('APPTYPEID', 4);
define('CURSCRIPT', 'study');
require './source/class/class_core.php';
$discuz = & discuz_core::instance();$discuz->cachelist = $cachelist;$discuz->init();
require DISCUZ_ROOT.'./source/plugin/study_linkkiller/outlink.inc.php';

?>