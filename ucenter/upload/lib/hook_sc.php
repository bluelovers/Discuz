<?php

/*
	$HeadURL:  $
	$Revision: $
	$Author: $
	$Date: $
	$Id:  $
*/

!defined('IN_UC') && exit('Access Denied');

global $scHooks, $scParser;
unset($scHooks);
unset($scParser);

$scHooks = new SCHooks();
$scParser = new SCParser();

class SCHooks {
	var $hooklist = array();
	var $calevenlist = array();

	function addHooks ($event, $args) {
		$this->hooklist[$event][] = &$args;
	}

	function runHooks($event, $args = array()) {

		if (!isset($this->calevenlist[$event])) {
			$this->calevenlist[$event] = $args;

//			var_dump( $this->hooklist );
		}

		if (!is_array($this->hooklist)) {
	//		throw new SCException("Global hooks array is not an array!\n");
			return false;
		}

		if (!array_key_exists($event, $this->hooklist)) {
			return true;
		}

		if (!is_array($this->hooklist[$event])) {
	//		throw new SCException("Hooks array for event '$event' is not an array!\n");
			return false;
		}

		foreach ($this->hooklist[$event] as $index => $hook) {

			$object = NULL;
			$method = NULL;
			$func = NULL;
			$data = NULL;
			$have_data = false;
			$have_eval = false;

			/* $hook can be: a function, an object, an array of $function and $data,
			 * an array of just a function, an array of object and method, or an
			 * array of object, method, and data.
			 */

			if (is_array($hook)) {
				if (count($hook) < 1) {
	//				throw new SCException("Empty array in hooks for " . $event . "\n");
				} else if (is_object($hook[0])) {
					$object = $this->hooklist[$event][$index][0];
					if (count($hook) < 2) {
						$method = "on" . $event;
					} else {
						$method = $hook[1];
						if (count($hook) > 2) {
							$data = $hook[2];
							$have_data = true;
						}
					}

				// bluelovers
				} else if (is_string($hook[0]) && $hook[0] == 'func' && count($hook) == 3) {
					$func = create_function($hook[1], $hook[2]);
				// bluelovers

				} else if (is_string($hook[0])) {
					$func = $hook[0];
					if (count($hook) > 1) {
						$data = $hook[1];
						$have_data = true;
					}
				} else {
					var_dump( $this->hooklist );
	//				throw new SCException("Unknown datatype in hooks for " . $event . "\n");
				}
			} else if (is_string($hook)) { # functions look like strings, too
				$func = $hook;
			} else if (is_object($hook)) {
				$object = $this->hooklist[$event][$index];
				$method = "on" . $event;
			} else {
	//			throw new SCException("Unknown datatype in hooks for " . $event . "\n");
			}

			/* We put the first data element on, if needed. */

			if ($have_data) {
				$hook_args = array_merge(array($data), $args);
			} else {
				$hook_args = $args;
			}

			if ( isset( $object ) ) {
				$func = get_class( $object ) . '::' . $method;
				$callback = array( $object, $method );
			} elseif ( false !== ( $pos = strpos( $func, '::' ) ) ) {
				$callback = array( substr( $func, 0, $pos ), substr( $func, $pos + 2 ) );
			} else {
				$callback = $func;
			}

			// Run autoloader (workaround for call_user_func_array bug)
			is_callable( $callback );

			/* Call the hook. */
	//		wfProfileIn( $func );
			$retval = call_user_func_array( $callback, $hook_args );
	//		wfProfileOut( $func );

			/* String return is an error; false return means stop processing. */

			if (is_string($retval)) {
	//			global $wgOut;
	//			$wgOut->showFatalError($retval);
				return false;
			} elseif( $retval === null ) {
				if( is_array( $callback ) ) {
					if( is_object( $callback[0] ) ) {
						$prettyClass = get_class( $callback[0] );
					} else {
						$prettyClass = strval( $callback[0] );
					}
					$prettyFunc = $prettyClass . '::' . strval( $callback[1] );
				} else {
					$prettyFunc = strval( $callback );
				}
	//			throw new SCException( "Detected bug in an extension! " .
	//				"Hook $prettyFunc failed to return a value; " .
	//				"should return true to continue hook processing or false to abort." );
			} else if (!$retval) {
				return false;
			}
		}

	}
}

// ÂÂª©¬Û®e

function scAddHooks($event, $args) {
	global $scHooks;
	$scHooks->addHooks($event, &$args);
}

function scRunHooks($event, $args = array()) {
	global $scHooks;
	$scHooks->runHooks($event, &$args);
}

class SCParser {
	var $hooklist = array();
}

?>