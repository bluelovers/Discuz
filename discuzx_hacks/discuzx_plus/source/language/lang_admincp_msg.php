<?php

/**
 *      [Discuz! XPlus] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: lang_admincp_msg.php 646 2010-09-13 03:37:40Z yexinhao $
 */

$lang = array
(
	'undefined_action' => ' 未定义操作。',
	'noaccess' => '您没有权限访问管理中心。',
	'noaccess_isfounder' => '您没有权限访问该设置，出于安全考虑此设置只有站点创始人可以使用，请检查 config/config_global.php 文件内创始人的设置。',
	'noaccess_ip' => '对不起，站长设定了只有特定 IP 地址范围才能访问管理中心，您的地址不在被允许的范围内。',
	'action_noaccess' => '对不起，站长限制您无权使用本功能。',
	'action_noaccess_config' => '对不起，出于系统安全考虑，站长关闭了该功能，如需要打开请自行修改 config/config_global.php 文件内对应的相关安全配置信息。',
	'action_access_noexists' => '站点缺少安全设置，请对照标准程序的 config/config_global.php 仔细修改您的配置文件。否则无法使用本功能。',
	'function_config' => '此功能已经关闭，请到config/config_global.php里面打开',

	'basic_succeed' => '基本设置完成',
	'module_succeed' => '模块管理完成',
	'credit_succeed' => '积分设置完成',
	'cache_succeed' => '缓存更新完毕',
	'member_succeed' => '会员更新完毕',
	'template_succeed' => '模板更新完毕',
	'template_nav_succeed' => '导航更新完毕',
	'credit_add_invalid' => '每个模块只能设置一种积分，请返回',
	'attachment_succeed' => '附件管理完成',
	'attachment_setting_succeed' => '附件设置完成',
	'members_add_succeed' => '用户 {username}(UID {uid}) 添加成功。',
	'members_edit_succeed' => '编辑用户完成',
	'memberprofile_succeed' => '用户自定义栏目完成',

	'members_add_toolong' => '对不起，您的用户名超过 15 个字符，请返回输入一个较短的用户名。',
	'members_add_tooshort' => '对不起，您输入的用户名小于3个字符, 请返回输入一个较长的用户名。',
	'members_add_illegal' => '用户名包含敏感字符或被系统屏蔽，请返回重新填写。',
	'members_username_protect' => '用户名包含被系统屏蔽的字符，请返回重新填写。',
	'members_add_invalid' => '您没有填写完整用户资料，请返回修改。',
	'members_add_username_duplicate' => '用户名已经存在，请返回修改。',
	'members_add_username_activation' => '用户名已经存在，但尚未激活，请返回修改。',
	'members_email_duplicate' => '该 Email 地址已经被注册，请返回重新填写。',
	'members_email_illegal' => 'Email 地址无效，请返回重新填写。',
	'members_email_domain_illegal' => 'Email 包含不可使用的邮箱域名，请返回重新填写。',

	'founder_perm_group_update_succeed' => '管理团队职务资料已成功更新。',
	'founder_perm_group_name_duplicate' => '团队职务 {name} 已经存在，请返回更改。',
	'founder_perm_groupperm_update_succeed' => '职务权限成功更新。',
	'founder_perm_member_update_succeed' => '管理团队成员资料已成功更新。',
	'founder_perm_member_noexists' => '指定的用户 {name} 不存在，请返回更改。',
	'founder_perm_member_duplicate' => '用户 {name} 已经存在，请返回更改。',
	'founder_perm_gperm_update_succeed' => '管理团队权限资料已成功更新。',
	'founder_perm_member_noexists' => '用户不存在，请返回。',

	'database_run_query_invalid' => '升级错误，MySQL 提示: {sqlerror} ，请返回。',
	'database_run_query_succeed' => 'Discuz! 数据结构成功升级，影响的记录行数 {affected_rows}，请返回。',

	'profile_option_invalid' => '项目名称或变量名为空，请返回修改。',
	'profile_optionvariable_invalid' => '变量名重复，请返回修改',

);

?>