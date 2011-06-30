<?php

/**
 *
 * $HeadURL$
 * $Revision$
 * $Author$
 * $Date$
 * $Id$
 *
 * @author bluelovers
 * @copyright 2010
 */

$curprg = basename(__FILE__);

$table_target = $db_target->tablepre.'common_syscache';

$db_target->query("TRUNCATE $table_target");

if ($tpl = dir(DISCUZ_ROOT.'../data/template')) {
	while($entry = $tpl->read()) {
		if(preg_match("/\.tpl\.php$/", $entry)) {
			@unlink(DISCUZ_ROOT.'../data/template/'.$entry);
		}
	}
	$tpl->close();
}

if ($tpl = dir(DISCUZ_ROOT.'../data/cache')) {
	while($entry = $tpl->read()) {
		if(preg_match("/\.php$/", $entry)) {
			@unlink(DISCUZ_ROOT.'../data/cache/'.$entry);
		}
	}
	$tpl->close();
}

?>