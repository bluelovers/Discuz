<?php

/**
 *      [品牌空間] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: db_mysql.class.php 4490 2010-09-15 09:31:46Z bihuizi $
 */

if(!defined('IN_BRAND')) {
	exit('Access Denied');
}

class db_mysql {
	var $tablepre;
	var $version = '';
	var $curlink;
	var $link = array();
	var $config = array();
	var $sqldebug = array();
	var $map = array();

	function db_mysql($config = array()) {
		if(!empty($config)) {
			$this->set_config($config);
		}
	}

	function set_config($config) {
		$this->config = &$config;
		$this->tablepre = $config['1']['tablepre'];
		if(!empty($this->config['map'])) {
			$this->map = $this->config['map'];
		}
	}

	function connect($serverid = 1) {

		if(empty($this->config) || empty($this->config[$serverid])) {
			$this->halt('config_db_not_found');
		}

		$this->link[$serverid] = $this->_dbconnect(
			$this->config[$serverid]['dbhost'],
			$this->config[$serverid]['dbuser'],
			$this->config[$serverid]['dbpw'],
			$this->config[$serverid]['dbcharset'],
			$this->config[$serverid]['dbname'],
			$this->config[$serverid]['pconnect']
			);
		$this->curlink = $this->link[$serverid];
	}

	function _dbconnect($dbhost, $dbuser, $dbpw, $dbcharset, $dbname, $pconnect) {
		$link = null;
		$func = empty($pconnect) ? 'mysql_connect' : 'mysql_pconnect';
		if(!$link = @$func($dbhost, $dbuser, $dbpw, 1)) {
			$this->halt('notconnect');
		} else {
			$this->curlink = $link;
			if($this->version() > '4.1') {
				$dbcharset = $dbcharset ? $dbcharset : $this->config[1]['dbcharset'];
				$serverset = $dbcharset ? 'character_set_connection='.$dbcharset.', character_set_results='.$dbcharset.', character_set_client=binary' : '';
				$serverset .= $this->version() > '5.0.1' ? ((empty($serverset) ? '' : ',').'sql_mode=\'\'') : '';
				$serverset && mysql_query("SET $serverset", $link);
			}
			$dbname && @mysql_select_db($dbname, $link);
		}
		return $link;
	}

	function table_name($tablename) {
		if(!empty($this->map) && !empty($this->map[$tablename])) {
			$id = $this->map[$tablename];
			if(!$this->link[$id]) {
				$this->connect($id);
			}
			$this->curlink = $this->link[$id];
		} else {
			$this->curlink = $this->link[1];
		}
		return $this->tablepre.$tablename;
	}

	function select_db($dbname) {
		return mysql_select_db($dbname, $this->curlink);
	}

	function fetch_array($query, $result_type = MYSQL_ASSOC) {
		return mysql_fetch_array($query, $result_type);
	}

	function fetch_first($sql) {
		return $this->fetch_array($this->query($sql));
	}

	function result_first($sql) {
		return $this->result($this->query($sql), 0);
	}

	function query($sql, $type = '') {
		if(D_BUG) {
			$mtime = explode(' ', microtime());
			$sqlstarttime = $mtime[1] + $mtime[0];
		}
		$func = $type == 'UNBUFFERED' && @function_exists('mysql_unbuffered_query') ? 'mysql_unbuffered_query' : 'mysql_query';
		if(!($query = $func($sql, $this->curlink)) && $type != 'SILENT') {
			if(in_array($this->errno(), array(2006, 2013)) && substr($type, 0, 5) != 'RETRY') {
				$this->connect();
				return $this->query($sql, 'RETRY'.$type);
			}
			if($type != 'SILENT' && substr($type, 5) != 'SILENT') {
				$this->halt('MySQL Query Error', $sql);
			}
		}
		if(D_BUG) {
			$this->sqldebug[] = array($sql, number_format(($mtim - $starttime), 6), debug_backtrace());
			global $_G, $_SGLOBAL;
			if(empty($_SGLOBAL['debug_query'])) $_SGLOBAL['debug_query'] = array();
			$mtime = explode(' ', microtime());
			$sqltime = number_format(($mtime[1] + $mtime[0] - $sqlstarttime), 6)*1000;
			$explain = array();
			$info = mysql_info();
			if(strtolower(substr($sql,0,7)) == 'select ') {
				$explain = mysql_fetch_assoc(mysql_query('EXPLAIN '.$sql, $this->curlink));
			}
			$_SGLOBAL['debug_query'][] = array('sql'=>$sql, 'time'=>$sqltime, 'info'=>$info, 'explain'=>$explain);
		}
		$_G['debug']['querynum']++;
		return $query;
	}

	function affected_rows() {
		return mysql_affected_rows($this->curlink);
	}

	function error() {
		return (($this->curlink) ? mysql_error($this->curlink) : mysql_error());
	}

	function errno() {
		return intval(($this->curlink) ? mysql_errno($this->curlink) : mysql_errno());
	}

	function result($query, $row = 0) {
		$query = @mysql_result($query, $row);
		return $query;
	}


	function num_rows($query) {
		$query = mysql_num_rows($query);
		return $query;
	}

	function num_fields($query) {
		return mysql_num_fields($query);
	}

	function free_result($query) {
		return mysql_free_result($query);
	}

	function insert_id() {
		return ($id = mysql_insert_id($this->curlink)) >= 0 ? $id : $this->result($this->query("SELECT last_insert_id()"), 0);
	}

	function fetch_row($query) {
		$query = mysql_fetch_row($query);
		return $query;
	}

	function fetch_fields($query) {
		return mysql_fetch_field($query);
	}

	function version() {
		if(empty($this->version)) {
			$this->version = mysql_get_server_info($this->curlink);
		}
		return $this->version;
	}

	function close() {
		return mysql_close($this->curlink);
	}

	function halt($message = '', $sql = '') {
		global $_G;
		if(!$this->config[1]['silent']) {
			$dberror = $this->error();
			$dberrno = $this->errno();
			$phperror = '<table style="font-size:11px" cellpadding="0"><tr><td width="270">File</td><td width="80">Line</td><td>Function</td></tr>';
			foreach (debug_backtrace() as $error) {
				$error['file'] = str_replace(B_ROOT, '', $error['file']);
				$error['class'] = isset($error['class']) ? $error['class'] : '';
				$error['type'] = isset($error['type']) ? $error['type'] : '';
				$error['function'] = isset($error['function']) ? $error['function'] : '';
				$phperror .= "<tr><td>$error[file]</td><td>$error[line]</td><td>$error[class]$error[type]$error[function]()</td></tr>";
			}
			$phperror .= '</table>';
			$helplink = "http://faq.comsenz.com/?type=mysql&dberrno=".rawurlencode($dberrno)."&dberror=".rawurlencode($dberror);
			@header('Content-Type: text/html; charset=gb2312');
	
			echo '<div style="position:absolute;font-size:11px;font-family:verdana,arial;background:#EBEBEB;padding:0.5em;line-height:1.5em">'.
			error('db_error', array(
				'$message' => error($message, array(), true),
				'$info' => $dberror ? error('db_error_message', array('$dberror' => $dberror), true) : '',
				'$sql' => $sql ? error('db_error_sql', array('$sql' => $sql), true) : '',
				'$errorno' => $dberrno ? error('db_error_no', array('$dberrno' => $dberrno), true) : '',
				'$helplink' => $helplink,
				), true);
			echo "<b>PHP Backtrace</b><br />$phperror<br /></div>";
			exit();
		}
	}

}


?>