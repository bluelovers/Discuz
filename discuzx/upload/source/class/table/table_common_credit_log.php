<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_credit_log.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_credit_log extends discuz_table
{
	public function __construct() {

		$this->_table = 'common_credit_log';
		$this->_pk    = '';

		parent::__construct();
	}
	public function fetch_by_operation_relatedid($operation, $relatedid) {
		$parameter = array($this->_table, $operation, $relatedid);
		$wherearr = array();
		$wherearr[] = is_array($operation) ? 'operation IN(%n)' : 'operation=%s';
		$wherearr[] = is_array($relatedid) ? 'relatedid IN(%n)' : 'relatedid=%d';
		return DB::fetch_all('SELECT * FROM %t WHERE '.implode(' AND ', $wherearr), $parameter);
	}
	public function fetch_all_by_operation($operation, $start = 0, $limit = 0) {
		return DB::fetch_all('SELECT * FROM %t WHERE operation=%s ORDER BY dateline DESC '.DB::limit($start, $limit), array($this->_table, $operation));
	}
	public function fetch_all_by_uid_operation_relatedid($uid, $operation, $relatedid) {
		$parameter = array($this->_table);
		$wherearr = array();
		if($uid) {
			$wherearr[] = is_array($uid) ? 'uid IN(%n)' : 'uid=%d';
			$parameter[] = $uid;
		}
		$wherearr[] = is_array($operation) ? 'operation IN(%n)' : 'operation=%s';
		$wherearr[] = is_array($relatedid) ? 'relatedid IN(%n)' : 'relatedid=%d';
		$parameter[] = $operation;
		$parameter[] = $relatedid;
		return DB::fetch_all('SELECT * FROM %t WHERE '.implode(' AND ', $wherearr).' ORDER BY dateline', $parameter);
	}
	public function fetch_all_by_uid($uid, $start = 0, $limit = 0) {
		return DB::fetch_all('SELECT * FROM %t WHERE uid=%d ORDER BY dateline DESC '.DB::limit($start, $limit), array($this->_table, $uid));
	}
	public function fetch_all_by_search($uid, $optype, $begintime = 0, $endtime = 0, $exttype = 0, $income = 0, $extcredits = array(), $start = 0, $limit = 0, $relatedid = 0) {
		$condition = $this->make_query_condition($uid, $optype, $begintime, $endtime, $exttype, $income, $extcredits, $relatedid);
		return DB::fetch_all('SELECT * FROM %t '.$condition[0].' ORDER BY dateline DESC '.DB::limit($start, $limit), $condition[1]);
	}
	public function delete_by_operation_relatedid($operation, $relatedid) {
		return DB::delete($this->_table, DB::field('operation', $operation).' AND '.DB::field('relatedid', $relatedid));
	}

	public function delete_by_uid_operation_relatedid($uid, $operation, $relatedid) {
		return DB::delete($this->_table, DB::field('uid', $uid).' AND '.DB::field('operation', $operation).' AND '.DB::field('relatedid', $relatedid));
	}
	public function update_by_uid_operation_relatedid($uid, $operation, $relatedid, $data) {
		return DB::update($this->_table, $data, DB::field('uid', $uid).' AND '.DB::field('operation', $operation).' AND '.DB::field('relatedid', $relatedid));
	}
	public function count_by_uid_operation_relatedid($uid, $operation, $relatedid) {
		$wherearr = array();
		$wherearr[] = is_array($uid) ? 'uid IN(%n)' : 'uid=%d';
		$wherearr[] = is_array($operation) ? 'operation IN(%n)' : 'operation=%s';
		$wherearr[] = is_array($relatedid) ? 'relatedid IN(%n)' : 'relatedid=%d';
		return DB::result_first('SELECT COUNT(*) FROM %t WHERE '.implode(' AND ', $wherearr), array($this->_table, $uid, $operation, $relatedid));
	}
	public function count_by_uid($uid) {
		return DB::result_first('SELECT COUNT(*) FROM %t WHERE uid=%d', array($this->_table, $uid));
	}
	public function count_by_operation($operation) {
		return DB::result_first('SELECT COUNT(*) FROM %t WHERE operation=%s', array($this->_table, $operation));
	}
	public function count_stc_by_relatedid($relatedid, $creditid, $operation = 'STC') {
		$creditid = intval($creditid);
		return DB::fetch_first("SELECT COUNT(*) AS payers, SUM(extcredits%d) AS income FROM %t WHERE relatedid=%d AND operation=%s", array($creditid, $this->_table, $relatedid, $operation));
	}
	public function count_credit_by_uid_operation_relatedid($uid, $operation, $relatedid, $creditid) {
		$wherearr = array();
		$wherearr[] = is_array($uid) ? 'uid IN(%n)' : 'uid=%d';
		$wherearr[] = is_array($operation) ? 'operation IN(%n)' : 'operation=%s';
		$wherearr[] = is_array($relatedid) ? 'relatedid IN(%n)' : 'relatedid=%d';
		$creditid = intval($creditid);
		return DB::result_first('SELECT SUM(extcredits%d) AS credit FROM %t WHERE '.implode(' AND ', $wherearr), array($creditid, $this->_table, $uid, $operation, $relatedid));
	}
	public function count_by_search($uid, $optype, $begintime = 0, $endtime = 0, $exttype = 0, $income = 0, $extcredits = array(), $relatedid = 0) {
		$condition = $this->make_query_condition($uid, $optype, $begintime, $endtime, $exttype, $income, $extcredits, $relatedid);
		return DB::result_first('SELECT COUNT(*) FROM %t '.$condition[0], $condition[1]);
	}
	private function make_query_condition($uid, $optype, $begintime = 0, $endtime = 0, $exttype = 0, $income = 0, $extcredits = array(), $relatedid = 0) {
		$wherearr = array();
		$parameter = array($this->_table);
		if($uid) {
			$wherearr[] = is_array($uid) ? 'uid IN(%n)' : 'uid=%d';
			$parameter[] = $uid;
		}
		if($optype) {
			$wherearr[] = is_array($optype) ? 'operation IN(%n)' : 'operation=%s';
			$parameter[] = $optype;
		}
		if($relatedid) {
			$wherearr[] = is_array($relatedid) ? 'relatedid IN(%n)' : 'relatedid=%d';
			$parameter[] = $relatedid;
		}
		if($begintime) {
			$wherearr[] = 'dateline>%d';
			$parameter[] = $begintime;
		}
		if($endtime) {
			$wherearr[] = 'dateline<%d';
			$parameter[] = $endtime;
		}
		if($exttype && $extcredits[$exttype]) {
			$wherearr[] = "extcredits{$exttype}!=0";
		}
		if($income && $extcredits) {
			$incomestr = $income < 0 ? '<' : '>';
			$incomearr = array();
			foreach(array_keys($extcredits) as $id) {
				$incomearr[] = 'extcredits'.$id.$incomestr.'0';
			}
			$wherearr[] = '('.implode(' OR ', $incomearr).')';
		}
		$wheresql = !empty($wherearr) && is_array($wherearr) ? ' WHERE '.implode(' AND ', $wherearr) : '';
		return array($wheresql, $parameter);
	}
}

?>