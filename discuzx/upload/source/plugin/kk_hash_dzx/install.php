<?php
if(!defined('IN_ADMINCP')) exit('Access Denied');

$request_url=str_replace('&kk_step='.$_GET['kk_step'],'',$_SERVER['QUERY_STRING']);
showsubmenusteps($installlang['header'], array(
	array($installlang['step1'], !$_GET['kk_step']),
	array($installlang['step2'], $_GET['kk_step']=='ok'),
));
switch($_GET['kk_step']){
	default:
	case 'validator':
		if(!$_G['config']['plugindeveloper']){
			DB::delete('common_plugin', array('identifier' => $_G['gp_dir']));
			cpmsg($installlang['not_developer'], dreferer, 'error');
		}
		$checkdata['key'][$_G['gp_dir']] = pluginvalidator($_G['gp_dir']);
		$check_result = pluginupgradecheck($checkdata);
		$result = $check_result[$_G['gp_dir']]['result'];
		$newver = $check_result[$_G['gp_dir']]['newver'];
		$param = array('id' => $_G['gp_dir'], 'newver' => $newver ? $newver : '', 'url' => "http://addons.discuz.com/?id={$_G[gp_dir]}");
		if($result == '1') {
			cpmsg($installlang['step1_ok'], "{$request_url}&kk_step=ok", 'loading');
		} elseif($result == '2') {
			cpmsg($installlang['validator_new'], "{$request_url}&kk_step=ok", 'form', $param);
		} else{
			cpmsg($installlang['validator_error'], "{$request_url}&kk_step=ok", 'form', $param);
		}
		break;
	case 'ok':
		DB::query('UPDATE '.DB::table('common_plugin')." SET available='1' WHERE identifier='{$_G[gp_dir]}'");
		$finish = TRUE;
		break;
}
?>