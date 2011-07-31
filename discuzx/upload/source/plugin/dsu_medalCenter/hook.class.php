<?php
/*
	dsu_medalCenter (C)2010 Discuz Student Union
	This is NOT a freeware, use is subject to license terms

	$Id: hook.class.php 60 2011-07-20 13:04:22Z chuzhaowei@gmail.com $
*/
!defined('IN_DISCUZ') && exit('Access Denied');

class plugin_dsu_medalCenter{
	private $identifier = 'dsu_medalCenter';
	private $cvars=array();

	public function __construct() {
		global $_G;
		$this->cvars = $_G['cache']['plugin']['dsu_medalCenter'];
	}
	
	/*function global_header() {
		global $_G;
		if(defined('CURSCRIPT') && CURSCRIPT == 'admin'){
			if($_G['gp_action'] == 'medals'){
				$url = 'admin.php?frames=yes&action=plugins&operation=config&identifier=dsu_medalCenter&pmod=';
				switch($_G['gp_operation']){
					case 'mod':
						$url .= 'admin_mod';
						break;
					default:
						$url .= 'admin_manage';
				}
				echo '<script>window.location.href ="'.$url.'"</script>';
			}
		}
	}*/
}

class plugin_dsu_medalCenter_forum extends plugin_dsu_medalCenter{
	
}

class plugin_dsu_medalCenter_home extends plugin_dsu_medalCenter{
	public function __construct(){
		parent::__construct();
	}
	
	
	//吐槽：该死的discuz不知道怎么修改了嵌入点的命名规则，导致出现了这么诡异的函数名。。。p.s.仅适用于X2，此函数命名不得修改
	public function space_(){
		global $_G;
		if($_G['gp_mod']=='medal'){
			echo '<script>window.location.href ="plugin.php?id=dsu_medalCenter:memcp"</script>';
			exit();
		}
	}
}
?>