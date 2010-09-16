<?php

/**
 *      [Discuz! XPlus] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: lang_admincp_global.php 617 2010-09-09 01:37:59Z yexinhao $
 */

$extend_lang = array
(
	'header_global' => '全局设置',
	'nav_global' => '全局',
	'nav_module' => '模块管理',
	'nav_member' => '用户管理',
	'nav_member_profile' => '用户栏目',
	'nav_member_add' => '添加用户',
	'nav_attachment' => '附件管理',
	'nav_log' => '系统日志',
	'nav_other' => '其他设置',
	'nav_credit' => '积分管理',
	'nav_template' => '模板管理',
	'nav_basic' => '基本设置',

	'menu_global_home' => '管理首页',
	'menu_global_member' => '用户管理',
	'menu_global_module' => '模块管理',
	'menu_global_attachment' => '附件管理',
	'menu_global_log' => '操作日志',
	'menu_global_other' => '其他设置',
	'menu_global_perm' => '管理权限',
	'menu_global_member_profile' => '用户栏目',
	'menu_global_credit' => '积分管理',
	'menu_global_basic' => '基本设置',
	'menu_global_permgrouplist' => '编辑权限 - {perm}',

	'module_list' => '模块列表',

	'log_list' => '日志列表',

	'basic_setting'	=> '基本设置',
	'basic_bbname'	=> '网站名称',
	'basic_siteurl'	=> '网站 URL',
	'basic_regurl'	=> '论坛注册 URL',
	'basic_autoactivationuser' => '同步登录时自动激活会员',
	'basic_autoactivationuser_comment' => '开启此功能可将UC同步登录过来的用户自动激活，但会轻微增加服务器负载',

	'member_list' => '用户列表',
	'member_add' => '添加用户',
	'member_profile_list' => '用户栏目列表',
	'member_profile' => '用户栏目详情',
	'member_edit' => '编辑用户',

	'credit_list' => '积分列表',
	'add_credit' => '添加积分',

	'attachment_filename' => '附件文件名',
	'attachment_type' => '附件类型',
	'attachment_size' => '附件尺寸',
	'attachment_url' => '附件地址',
	'attachment_module' => '附件所在模块',
	'attachment_dateline' => '上传时间',
	'attachment_basic_dir' => '附件保存位置',
	'attachment_basic_url' => '附件 URL 地址',
	'attachment_list' => '附件列表',
	'attachment_setting' => '附件设置',

	'template_nav' => '导航管理',

	'log_module' => '所在模块',
	'log_dateline' => '日志日期',
	'log_action' => '日志动作',
	'log_ip' => '日志ip',
	'log_content' => '日志内容',

	'other_cache' => '更新缓存',
	'other_database' => '数据库升级',

	'db_runquery_sql' => '数据库升级 - 请将数据库升级语句粘贴在下面',

	'operation' => '操作',
	'apikey' => '通信密匙',
	'variable' => '变量名',
	'icon' => '图标',
	'inital' => '初始值',
	'version' => '版本',

	'attach_type_1' => '图片',

);

$GLOBALS['admincp_actions_normal'][] = 'global';

?>
