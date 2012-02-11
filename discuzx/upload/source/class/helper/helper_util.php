<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: helper_util.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class helper_util {

	public static function compute($v1, $v2, $glue = '+') {
		switch ($glue) {
			case '+':
				return $v1 + $v2;
				break;
			case '-':
				return $v1 - $v2;
				break;
			case '.':
				return $v1 . $v2;
				break;
			case '=':
			case '==':
				return $v1 == $v2;
				break;
			case 'merge':
				return array_merge((array)$v1, (array)$v2);
				break;
			case '===':
				return $v1 === $v2;
				break;
			case '!==':
				return $v1 === $v2;
				break;
			case '&&':
				return $v1 && $v2;
				break;
			case '||':
				return $v1 && $v2;
				break;
			case 'and':
				return $v1 and $v2;
				break;
			case 'xor':
				return $v1 xor $v2;
				break;
			case '|':
				return $v1 | $v2;
				break;
			case '&':
				return $v1 & $v2;
				break;
			case '^':
				return $v1 ^ $v2;
				break;
			case '>':
				return $v1 > $v2;
				break;
			case '<':
				return $v1 < $v2;
				break;
			case '<>':
				return $v1 <> $v2;
				break;
			case '!=':
				return $v1 != $v2;
				break;
			case '<=':
				return $v1 <= $v2;
				break;
			case '>=':
				return $v1 >= $v2;
				break;
			case '*':
				return $v1 * $v2;
				break;
			case '/':
				return $v1 / $v2;
				break;
			case '%':
				return $v1 % $v2;
				break;
			case 'or':
				return $v1 or $v2;
				break;
			case '<<':
				return $v1 << $v2;
				break;
			case '>>':
				return $v1 >> $v2;
				break;
			default:
				return null;
		}
	}

	public static function single_compute($v, $glue = '+') {
		switch ($glue) {
			case '!':
				return ! $v;
				break;
			case '-':
				return - $v;
				break;
			case '~':
				return ~ $v;
				break;
			default:
				return null;
				break;
		}
	}

}

?>