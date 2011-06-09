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

// 轉換升級時複製原有用戶欄目擴充訊息的資料
$curprg = basename(__FILE__);

$table_source = $db_source->tablepre.'profilefields';
$table_target = $db_target->tablepre.'common_member_profile_setting';

$query = $db_source->query("SELECT * FROM {$table_source} WHERE 1 ORDER BY fieldid ASC");

while ($row = $db_source->fetch_array($query)) {
	$row['formtype'] = $row['selective'] ? 'select' : 'text';
	$row['fieldid'] = 'field'.$row['fieldid'];

	$data = implode_field_value($row, ',', db_table_fields($db_target, $table_target));
	$db_target->query("UPDATE {$table_target} SET $data WHERE fieldid='$row[fieldid]'");
}

?>