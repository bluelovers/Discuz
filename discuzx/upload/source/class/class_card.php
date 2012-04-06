<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: class_card.php 21053 2011-03-11 04:13:37Z congyushuai $
 */
class card{

	var $set = array();
	var $rulekey = array("str"=>"\@", "num"=>"\#", "full"=>"\*");
	var $sysrule = '';
	var $rule = '';
	var $rulemap_str = "ABCDEFGHIJKLMNPQRSTUVWXYZ";
	var $rulemap_num = "123456789";

	var $rulereturn = array();
	var $cardlist = array();

	var $succeed = 0;
	var $fail = 0;
	var $failmin = 1;
	var $failrate = '0.1';


	function card() {
		$this->init();
	}

	function init() {
		global $_G;
		$this->set = &$_G['setting']['card'];
		$this->sysrule = "^[A-Z0-9".implode('|', $this->rulekey)."]+$";
	}

	function make($rule = '', $num = 1, $cardval = array()) {
		global $_G;
		$this->rule = empty($rule) ? $this->set['rule'] : trim($rule) ;
		if(empty($this->rule)) {
			return -1;
		}
		$this->fail($num);
		if(is_array($cardval)) {
			foreach($cardval AS $key => $val) {
				$sqlkey .= ", $key";
				$sqlval .= ", '{$val}'";
			}

		}
		for($i = 0; $i < $num ; $i++) {
			if($this->checkrule($this->rule)) {
				$card = $this->rule;
				foreach($this->rulereturn AS $key => $val) {
					$search = array();
					foreach($val AS $skey => $sval) {
						$search[] = '/'.$this->rulekey[$key].'/';
					}
					$card =  preg_replace($search, $val, $card, 1);
				}
			} else {
				return 0;
			}

			$sql = "INSERT INTO ".DB::table('common_card')." (id, makeruid, dateline $sqlkey)VALUES('$card', '$_G[uid]', '$_G[timestamp]' $sqlval)";
			DB::query($sql, 'SILENT');
			if($sqlerror = DB::error()) {
				if(DB::errno() == 1062) {
					$this->fail++;
					if($this->failmin > $this->fail) {
						$num++;
					} else {
						$num = $i - 1;
					}
				} else {
					DB::halt($sqlerror, $sql);
				}
			} else {
				$this->succeed += intval(DB::affected_rows());
				$this->cardlist[] = $card;
			}
		}
		return true;
	}


	function checkrule($rule, $type = '0') {
		if(!preg_match("/($this->sysrule)/i", $rule)){
			return -2;
		}
		if($type == 0) {
			foreach($this->rulekey AS $key => $val) {
				$match = array();
				preg_match_all("/($val){1}/i", $rule, $match);
				$number[$key] = count($match[0]);
				if($number[$key] > 0) {
					for($i = 0; $i < $number[$key]; $i++) {
						switch($key) {
						case 'str':
							$rand = mt_rand(0, (strlen($this->rulemap_str) - 1));
							$this->rulereturn[$key][$i] = $this->rulemap_str[$rand];
							break;
						case 'num':
							$rand = mt_rand(0, (strlen($this->rulemap_num) - 1));
							$this->rulereturn[$key][$i] = $this->rulemap_num[$rand];
							break;
						case 'full':
							$fullstr = $this->rulemap_str.$this->rulemap_num;
							$rand = mt_rand(0,(strlen($fullstr) - 1));
							$this->rulereturn[$key][$i] = $fullstr[$rand];
							break;
						}
					}
				}
			}
		}
		return true;

	}

	function fail($num = 1) {
		$failrate = $this->failrate ? (float)$this->failrate : '0.1';
		$this->failmin = ceil($num * $failrate);
		$this->failmin = $this->failmin > 100 ? 100 : $this->failmin;
	}
};
?>