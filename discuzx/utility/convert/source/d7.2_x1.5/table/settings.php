<?php

/**
 * DiscuzX Convert
 *
 * $Id: settings.php 16424 2010-09-06 09:07:32Z wangjinbo $
 */

$curprg = basename(__FILE__);

$table_source = $db_source->tablepre.'settings';
$table_target = $db_target->tablepre.'common_setting';

$newsetting = array();
$query = $db_target->query("SELECT * FROM $table_target");
while($row = $db_source->fetch_array($query)) {
	$newsetting[$row['skey']] = $row['skey'];
}

$skips = array('attachdir', 'attachurl', 'cachethreaddir', 'jspath', 'my_status');

$query = $db_source->query("SELECT  * FROM $table_source");
while ($row = $db_source->fetch_array($query)) {
	if(in_array($row['variable'], $skips)) continue;

	if(isset($newsetting[$row['variable']])) {
		$rownew['svalue'] = array();
		if($row['variable'] == 'my_search_status' && $row['value'] != -1) {
			$row['value'] = 0;
		}
		if(in_array($row['variable'], array('watermarkminheight', 'watermarkminwidth', 'watermarkquality', 'watermarkstatus', 'watermarktext', 'watermarktrans', 'watermarktype'))) {
			$rownew['skey'] = $row['variable'];
			$rownew['svalue'] = array();
			if($row['variable'] == 'watermarktype') {
				$watermarktype_map = array(
					0 => 'gif',
					1 => 'png',
					2 => 'text',
				);
				$rownew['svalue']['forum'] = $watermarktype_map[$row['value']];
			} elseif($row['variable'] == 'watermarktext') {
				$dataold = (array)unserialize($row['value']);
				foreach($dataold as $data_k => $data_v) {
					$rownew['svalue'][$data_k]['forum'] = $data_v;
				}
			} else {
				$dataold = $row['value'];
				$rownew['svalue']['forum'] = $dataold;
			}
			$rownew['svalue'] = serialize($rownew['svalue']);
		} else {
			$rownew['skey'] = $row['variable'];

			$rownew['svalue'] = $row['value'];
		}
		$rownew  = daddslashes($rownew, 1);

		$data = implode_field_value($rownew, ',', db_table_fields($db_target, $table_target));

		$db_target->query("REPLACE INTO $table_target SET $data");
	}
}
?>