<?php

/*
	UCenter [SC] (C)2000-2009 Bluelovers Net.

	$Id: sc.php 6 2009-10-15 18:30:32Z bluelovers $
*/

!defined('IN_UC') && exit('Access Denied');

class sccontrol extends base {

	function __construct() {
		$this->sccontrol();
	}

	function sccontrol() {
		parent::__construct();

		if(!$this->settings) {
			$this->settings = $this->cache('settings');
		}
	}

	function onget_setting() {
		$this->init_input();
		$arg = $this->input('fields');

		if (!$arg) {
			$ret = $this->settings;
		} else {
			if (is_array($arg)) {
				$ret = array();

				foreach ($arg as $k) {
					$ret[$k] = $this->settings[$k];
				}

			} else {
				$ret = $this->settings[$arg];
			}
		}

		return $ret;
	}

	function onset_user_fields() {
		$this->init_input();
		$uid = $this->input('uid');
		$fields = $this->input('fields');

		$sql = array(
			'members' => array('bday', 'gender', 'timeoffset'),
			'memberfields' => array('nickname', 'avatar'),
		);

		$uids = $this->implodeids($uid);

		foreach ($sql as $sqldb => $sqlfields) {
			$chkfields = $this->array_inarray_key($fields, $sqlfields);

			if ($chkfields) {
				$sqladd = $this->implode_mode($chkfields, 'mysql_0');

				$this->db->query("UPDATE ".UC_DBTABLEPRE."$sqldb SET $sqladd[2] WHERE uid IN ($uids)");
			}
		}

		return $this->db->errno() ? -1 : 1;
	}

	function onget_user_fields() {
		$this->init_input();
		$uid = $this->input('uid');
		$fields = $this->input('fields');
		$bykeys = $this->input('bykeys');

		if ($bykeys) $fields = array_keys($fields);

		$sql = array(
			'members' => array('bday', 'gender', 'timeoffset'),
			'memberfields' => array('nickname', 'avatar'),
		);

		$uids = $this->implodeids($uid);

		$sqladd = $c = '';

		if ($chkfields = $this->array_inarray_value($fields, $sql['members'])) {
			$sqladd .= $c.'`m`.`'.$this->implode_by_key(null, $chkfields, '`,', 0, '`m`.`').'`';
			$c = ',';
		}

		if ($chkfields = $this->array_inarray_value($fields, $sql['memberfields'])) {
			$sqladd .= $c.'`mf`.`'.$this->implode_by_key(null, $chkfields, '`,', 0, '`mf`.`').'`';
			$c = ',';
		}

		$members = $this->db->fetch_all("SELECT `m`.`uid`, $sqladd FROM ".UC_DBTABLEPRE."members m LEFT JOIN ".UC_DBTABLEPRE."memberfields mf ON mf.uid = m.uid WHERE m.uid IN ($uids)", 'uid');

		return $members;
	}

}

?>