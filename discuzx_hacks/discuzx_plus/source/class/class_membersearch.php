<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: class_membersearch.php 11901 2010-06-18 08:20:54Z xupeng $
 */

class membersearch {

	function membersearch(){}

	function getfield($fieldid='') {
		static $fields = array(
			'uid'=>'member', 'username'=>'member', 'groupid'=>'member',
			'email'=>'member', 'credits'=>'member', 'regdate'=>'member',
			'status'=>'member', 'emailstatus'=>'member', 'avatarstatus'=>'member','videophotostatus'=>'member',
			'extcredits1'=>'count', 'extcredits2'=>'count', 'extcredits3'=>'count', 'extcredits4'=>'count',
			'extcredits5'=>'count',	'extcredits6'=>'count', 'extcredits7'=>'count', 'extcredits8'=>'count',
			'posts'=>'count',
			'regip'=>'status', 'lastip'=>'status', 'lastvisit'=>'status', 'lastpost' => 'status', 'realname'=>'profile',
			'birthyear'=>'profile', 'birthmonth'=>'profile', 'birthday'=>'profile', 'gender'=>'profile',
			'constellation'=>'profile', 'zodiac'=>'profile', 'telephone'=>'profile', 'mobile'=>'profile',
			'idcardtype'=>'profile', 'idcard'=>'profile', 'address'=>'profile', 'zipcode'=>'profile', 'nationality'=>'profile',
			'birthprovince'=>'profile', 'birthcity'=>'profile', 'resideprovince'=>'profile',
			'residecity'=>'profile', 'residedist'=>'profile', 'residecommunity'=>'profile',
			'residesuite'=>'profile', 'graduateschool'=>'profile', 'education'=>'profile',
			'occupation'=>'profile', 'company'=>'profile', 'position'=>'profile', 'revenue'=>'profile',
			'affectivestatus'=>'profile', 'lookingfor'=>'profile', 'bloodtype'=>'profile',
			'height'=>'profile', 'weight'=>'profile', 'alipay'=>'profile', 'icq'=>'profile',
			'qq'=>'profile', 'yahoo'=>'profile', 'msn'=>'profile', 'taobao'=>'profile', 'site'=>'profile',
			'bio'=>'profile', 'interest'=>'profile', 'field1'=>'profile', 'field2'=>'profile',
			'field3'=>'profile', 'field4'=>'profile', 'field5'=>'profile', 'field6'=>'profile',
			'field7'=>'profile', 'field8'=>'profile');
		return $fieldid ? $fields[$fieldid] : $fields;
	}

	function gettype($fieldid) {
		static $types = array(
			'uid'=>'int', 'groupid'=>'int', 'credits'=>'int',
			'status'=>'int', 'emailstatus'=>'int', 'avatarstatus'=>'int','videophotostatus'=>'int',
			'extcredits1'=>'int', 'extcredits2'=>'int', 'extcredits3'=>'int', 'extcredits4'=>'int',
			'extcredits5'=>'int', 'extcredits6'=>'int', 'extcredits7'=>'int', 'extcredits8'=>'int',
			'posts'=>'int', 'birthyear'=>'int', 'birthmonth'=>'int', 'birthday'=>'int', 'gender'=>'int',
			);
		return $types[$fieldid] ? $types[$fieldid] : 'string';
	}

	function search($condition, $maxsearch=100, $start=0){
		$list = array();
		$sql = membersearch::makesql($condition)." LIMIT $start, $maxsearch";
		$query = DB::query($sql);
		while($value = DB::fetch($query)) {
			$list[] = intval($value['uid']);
		}
		return $list;
	}

	function getcount($condition) {
		$count = DB::result_first(membersearch::makesql($condition, true));
		return intval($count);
	}

	function cachesearch($condition, $start=0, $limit=20, $renew=false) {
		global $_G;
		$hash = membersearch::makehash($condition);
		$option = membersearch::getoption($hash);
		if(empty($option)) {
			$option = array(
				'condition' => serialize($condition),
				'hash' => $hash,
				'users' => 0,
				'updatetime' => 0
			);
			$optionid = DB::insert('common_member_stat_search', daddslashes($option), true);
			$option['optionid'] = intval($optionid);
		}

		$list = array();
		if($renew) {
			DB::query('DELETE FROM '.DB::table('common_member_stat_searchcache')." WHERE optionid='$option[optionid]'");
			$sql = membersearch::makesql($condition);
			$query = DB::query($sql.' LIMIT 5000');
			$inserts = array();
			$total = 0;
			$n = 1000;
			while($value = DB::fetch($query)) {
				if($limit > 0) {
					$list[] = intval($value['uid']);
					$limit--;
				}
				$inserts[] = "('$option[optionid]', '$value[uid]')";
				$total++;
				if($n > 0) {
					$n--;
				} else {
					DB::query('REPLACE INTO '.DB::table('common_member_stat_searchcache').'(optionid, uid) VALUES '.implode(', ', $inserts));
					$inserts = array();
					$n = 100;
				}
			}
			if($inserts) {
				DB::query('REPLACE INTO '.DB::table('common_member_stat_searchcache').'(optionid, uid) VALUES '.implode(', ', $inserts));
			}
			DB::update('common_member_stat_search', array('users'=>$total, 'updatetime'=>$_G['timestamp']), array('optionid'=>$option['optionid']));
			$option['users'] = $total;
			$option['updatetime'] = $_G['timestamp'];
			$_G['statsearch'][$hash] = $option;
		} else {
			$sql = 'SELECT uid FROM '.DB::table('common_member_stat_searchcache')." WHERE optionid='$option[optionid]' LIMIT $start, $limit";
			$query = DB::query($sql);
			while($value = DB::fetch($query)) {
				$list[] = intval($value['uid']);
			}
		}
		return $list;
	}

	function cachecheck($condition, $expire=0) {
		global $_G;
		$hash = membersearch::makehash($condition);
		$option = membersearch::getoption($hash);
		if(!empty($option) && (empty($expire) || $option['updatetime'] + $expire > $_G['timestamp'])) {
			return $option;
		} else {
			return false;
		}
	}

	function getoption($hash) {
		global $_G;
		$_G['statsearch'] = isset($_G['statsearch']) ? $_G['statsearch'] : array();
		if(!isset($_G['statsearch'][$hash])) {
			$_G['statsearch'][$hash] = DB::fetch_first('SELECT * FROM '.DB::table('common_member_stat_search')." WHERE hash = '$hash'");
		}
		return $_G['statsearch'][$hash];
	}

	function makehash($condition) {
		return md5(serialize($condition));
	}

	function makesql($condition, $onlyCount=false) {

		$tables = $wheres = array();
		$fields = membersearch::getfield();
		foreach ($fields as $key=>$value) {
			$return = array();
			if($condition[$key]) {
				$return = membersearch::makeset($key, $condition[$key], membersearch::gettype($key));
			} elseif($condition[$key.'_low'] || $condition[$key.'_high']) {
				$return = membersearch::makerange($key, $condition[$key.'_low'], $condition[$key.'_high'], membersearch::gettype($key));
			} elseif($condition[$key.'_after'] || $condition[$key.'_before']) {
				$condition[$key.'_after'] = dmktime($condition[$key.'_after']);
				$condition[$key.'_before'] = dmktime($condition[$key.'_before']);
				$return = membersearch::makerange($key, $condition[$key.'_after'], $condition[$key.'_before'], membersearch::gettype($key));
			}
			if($return) {
				$tables[$return['table']] = true;
				$wheres[] = $return['where'];
			}
		}
		if($tables && $wheres) {
			$parts = array();
			$table1 = '';
			foreach ($tables as $key=>$value) {
				$value = membersearch::gettable($key);
				$parts[] = "$value as $key";
				if(! $table1) {
					$table1 = $key;
				} else {
					$wheres[] = $table1.'.uid = '.$key.'.uid';
				}
			}

			$selectsql = $onlyCount ? 'SELECT COUNT('.$table1.'.uid) as cnt ' : 'SELECT '.$table1.'.uid';
			return $selectsql.' FROM '.implode(', ', $parts).' WHERE '.implode(' AND ', $wheres);
		} else {
			$selectsql = $onlyCount ? 'SELECT COUNT(uid) as cnt ' : 'SELECT uid';
			return $selectsql.' FROM '.DB::table('common_member')." WHERE 1";
		}
	}

	function makeset($field, $condition, $type='string') {
		$return = $values = array();

		$return['table'] = membersearch::getfield($field);
		if(! $return['table']){
			return array();
		}
		$field = $return['table'].'.'.$field;

		$islikesearch = false;
		if(!is_array($condition)) {
			$condition = explode(',', $condition);
		}
		foreach ($condition as $value) {
			$value = trim($value);
			if($type == 'int') {
				$value = intval($value);
			} elseif(!$islikesearch && strexists($value, '*')) {
				$islikesearch = true;
			}
			if($value) {
				$values[] = $value;
			}
		}

		if(!$values) {
			return array();
		}

		if($islikesearch) {
			$likes = array();
			foreach ($values as $value) {
				if(strexists($value, '*')) {
					$value = str_replace('*', '%', $value);
					$likes[] = "$field LIKE '$value'";
				} else {
					$likes[] = "$field = '$value'";
				}
			}
			$return['where'] = '('.implode(' OR ', $likes).')';
		} elseif(count($values) > 1) {
			$return['where'] = "$field IN ('".implode("','", $values)."')";
		} else {
			$return['where'] = "$field = '$values[0]'";
		}
		return $return;
	}

	function makerange($field, $range_low='', $range_high='', $type='string') {
		$return = array();

		$return['table'] = membersearch::getfield($field);
		if(!$return['table']){
			return array();
		}
		$field = $return['table'].'.'.$field;

		if($type == 'int') {
			$range_low = intval($range_low);
			$range_high = intval($range_high);
		}  else {
			$range_low = trim($range_low);
			$range_high = trim($range_high);
		}

		$wheres = array();
		if($range_low) {
			$wheres[] = "$field >= '$range_low'";
		}
		if($range_high && $range_high > $range_low) {
			$wheres[] = "$field <= '$range_high'";
		}
		if($wheres) {
			$return['where'] = implode(' AND ', $wheres);
			return $return;
		} else {
			return array();
		}
	}

	function gettable($alias) {
		static $mapping = array('member'=>'member', 'status'=>'member_status', 'profile'=>'member_profile', 'count'=>'member_count');
		return DB::table('common_'.$mapping[$alias]);
	}

}

?>