<?php

/**
 *      [品牌空間] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: db.class.php 4067 2010-07-30 08:38:14Z fanshengshuai $
 */

class DB {

	function table($table) {
		return tname($table);
	}

	function delete($table, $condition, $limit = 0, $unbuffered = true) {
		if(empty($condition)) {
			$where = '1';
		} elseif(is_array($condition)) {
			$where = DB::implode_field_value($condition, ' AND ');
		} else {
			$where = $condition;
		}
		$sql = "DELETE FROM ".DB::table($table)." WHERE $where ".($limit ? "LIMIT $limit" : '');
		return DB::query($sql, ($unbuffered ? 'UNBUFFERED' : ''));
	}

	function insert($table, $data, $return_insert_id = false, $replace = false, $silent = false) {

		$sql = DB::implode_field_value($data);

		$cmd = $replace ? 'REPLACE INTO' : 'INSERT INTO';

		$table = DB::table($table);
		$silent = $silent ? 'SILENT' : '';

		$return = DB::query("$cmd $table SET $sql", $silent);

		return $return_insert_id ? DB::insert_id() : $return;

	}

	function update($table, $data, $condition, $unbuffered = false, $low_priority = false) {
		$sql = DB::implode_field_value($data);
		$cmd = "UPDATE ".($low_priority ? 'LOW_PRIORITY' : '');
		$table = DB::table($table);
		$where = '';
		if(empty($condition)) {
			$where = '1';
		} elseif(is_array($condition)) {
			$where = DB::implode_field_value($condition, ' AND ');
		} else {
			$where = $condition;
		}
		$res = DB::query("$cmd $table SET $sql WHERE $where", $unbuffered ? 'UNBUFFERED' : '');
		return $res;
	}

	function implode_field_value($array, $glue = ',') {
		$sql = $comma = '';
		foreach ($array as $k => $v) {
			$sql .= $comma."`$k`='$v'";
			$comma = $glue;
		}
		return $sql;
	}

	function insert_id() {
		$db = & DB::object();
		return $db->insert_id();
	}

	function fetch($resourceid) {
		$db = & DB::object();
		return $db->fetch_array($resourceid);
	}

	function fetch_first($sql) {
		$db = & DB::object();
		return $db->fetch_first($sql);
	}

	function fetch_row($query) {
		$db = & DB::object();
		return $db->fetch_row($query);
	}

	function result($resourceid, $row = 0) {
		$db = & DB::object();
		return $db->result($resourceid, $row);
	}

	function result_first($sql) {
		$db = & DB::object();
		return $db->result_first($sql);
	}

	function query($sql, $type = '') {
		$db = & DB::object();
		return $db->query($sql, $type);
	}

	function num_rows($resourceid) {
		$db = & DB::object();
		return $db->num_rows($resourceid);
	}

	function num_fields($query) {
		$db = & DB::object();
		return $db->num_fields($query);
	}

	function affected_rows() {
		$db = & DB::object();
		return $db->affected_rows();
	}

	function free_result($query) {
		$db = & DB::object();
		return $db->free_result($query);
	}

	function error() {
		$db = & DB::object();
		return $db->error();
	}

	function errno() {
		$db = & DB::object();
		return $db->errno();
	}

	function &object() {
		static $db;
		if(empty($db)) {
			include_once(B_ROOT.'./source/class/db_mysql.class.php');
			$db = new db_mysql();
		}
		return $db;
	}

	function version() {
		$db = & DB::object();
		return $db->version();
	}
}
?>