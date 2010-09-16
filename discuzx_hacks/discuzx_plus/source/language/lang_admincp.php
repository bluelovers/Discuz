<?php

/**
 *      [Discuz! XPlus] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: lang_admincp.php 619 2010-09-09 02:15:10Z tiger $
 */

//note 后台语言包规范：为了方便后台搜索，除通用名词外，设置类语言项目统一使用 “action_operation_*******”的方式命名

$lang = array(

	'display_order' => '显示顺序',
	'available' => '可用',
	'name' => '名称',
	'del' => '删',
	'submit' => '提交',
	'username' => '用户名',
	'usergroup' => '用户组',
	'email' => '电子邮件',
	'password' => '密码',
	'detail' => '详情',
	'type' => '类型',
	'unit' => '单位',
	'add_new' => '新增',
	'edit' => '编辑',
	'discuz_message' => 'Discuz! 提示',
	'avatar' => '头像',
	'regip' => '注册IP',
	'lastip' => '最后访问IP',
	'regdate' => '注册日期',
	'avatar_clear' => '删除头像',
	'description' => '描述',
	'tips_textarea' => '双击输入框可扩大/缩小',
	'required' => '必填',
	'all_module' => '所有模块',
	'yes' => '是',
	'no' => '否',
	'message_redirect' => '如果您的浏览器没有自动跳转，请点击这里',
	'copyright' => '版权信息',
	'all' => '全部',
	'home' => '首页',
	'preview' => '预览',

	'db_runquery_comment' => '注意: 为确保升级成功，请不要修改 SQL 语句的任何部分。',
	'db_runquery_createcompatible' => '转换建表语句格式和字符集',

	'founder_cpgroupname' => '职务名称',
	'founder_username' => '成员用户',
	'founder_usergname' => '职务',
	'founder_admin' => '创始人',
	'founder_master' => '副站长',
	'founder_usergname_comment' => '设置当前管理团队成员的职务',
	'founder_perm_setting' => '基本权限',
	'founder_perm_all' => '<span title="设置成员拥有全部权限(创始人特定权限除外)">拥有全部权限</span>',
	'founder_perm_allowpost' => '<span title="设置成员能修改设置的内容">允许修改设置</span>',
	'founder_group_switch' => '切换团队职务',

	'home' => '首页',
	'home_welcome' => '{bbname} 管理中心',
	'home_sys_info' => '系统信息',
	'home_discuz_version' => 'Discuz XPlus! 程序版本',
	'home_ucclient_version' => 'UCenter 客户端版本',
	'home_check_newversion' => '查看最新版本',
	'home_environment' => '服务器系统及 PHP',
	'home_serversoftware' => '服务器软件',
	'home_database' => '服务器 MySQL 版本',
	'home_upload_perm' => '上传许可',
	'home_database_size' => '当前数据库尺寸',
	'home_attach_size' => '当前附件尺寸',
	'home_dev' => 'Discuz! 开发团队',
	'home_dev_copyright' => '版权所有',
	'home_dev_manager' => '总策划兼项目经理',
	'home_dev_team' => '开发与支持团队',
	'home_dev_addons' => '插件与模板设计',
	'home_dev_skins' => '界面与用户体验团队',
	'home_dev_thanks' => '感谢贡献者',
	'home_dev_supportwebs' => '第三方支持网站',
	'home_dev_links' => '相关链接',
	'home_security_founder' => '<li>您可以制定多种团队职务分配给您网站管理团队的各个成员，让他们管理网站的不同事务<li>“<strong>副站长</strong>”拥有除“创始人（站长）”专有权限以外的所有后台权限，仅次于“创始人（站长）”</li>',
	'home_security_nofounder' => '<li>您尚未设置 <u>站点创始人</u>，所有在管理员用户组的用户均可以登录管理中心。请修改 config/config_global.php 添加创始人，以有效控制管理团队成员的权限范围',

	'vars_type_number' => '数字(number)',
	'vars_type_text' => '字串(text)',
	'vars_type_textarea' => '文本(textarea)',
	'vars_type_radio' => '单选(radio)',
	'vars_type_checkbox' => '多选(checkbox)',
	'vars_type_select' => '选择(select)',
	'vars_type_calendar' => '日历(calendar)',
	'vars_type_url' => '超级链接(url)',
	'vars_type_image' => '上传图片(image)',
	'vars_type_email' => '电子邮件(email)',
	'vars_type_upload' => '上传(upload)',
	'vars_type_range' => '范围(range)',
	'vars_type_area' => '地区(area)',

	'profile_edit_maxnum' => '数值最大值（可选）',
	'profile_edit_minnum' => '数值最小值（可选）',
	'profile_edit_textmax' => '内容最大长度（可选）',
	'profile_edit_inputsize' => '表单显示长度（可选）',
	'profile_edit_searchtxt' => '搜索范围预置值(可选)',
	'profile_edit_searchtxt_comment' => '设置项目的快速搜索预置数值，逗号分隔，例如：<i>0,50,10</i>，将显示 0 - 50、50 - 100 的搜索快捷链接',
	'profile_edit_rowsize' => '输入框行数（可选）：',
	'profile_edit_colsize' => '输入框宽度（可选）：',
	'profile_edit_choices' => '选项内容',
	'profile_edit_choices_comment' => '只在项目为可选时有效，每行一个选项，等号前面为选项索引(建议用数字)，后面为内容，例如: <br /><i>1 = 光电鼠标<br />2 = 机械鼠标<br />3 = 没有鼠标</i><br />注意: 选项确定后请勿修改索引和内容的对应关系，但仍可以新增选项。如需调换显示顺序，可以通过移动整行的上下位置来实现',
	'profile_add' => '添加用户栏目',

	'template_add' => '添加新模板',

);

$adminextend = array();
if(file_exists($adminextendfile = DISCUZ_ROOT.'./data/cache/cache_adminextend.php')) {
	@include_once $adminextendfile;
	foreach($adminextend as $extend) {
		$extend_lang = array();
		@include_once DISCUZ_ROOT.'./source/language/lang_admincp_'.$extend;
		$lang = array_merge($lang, $extend_lang);
	}
}

?>
