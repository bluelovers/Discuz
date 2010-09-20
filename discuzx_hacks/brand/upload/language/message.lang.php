<?

/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: message.lang.php 4456 2010-09-14 13:45:18Z yexinhao $
 */

if(!defined('IN_BRAND')) {
	exit('Access Denied');
}

$mlang = array (
	'title' => '提示消息',
	'back' => '返回上一页',
	'index' => '进入首页',
	'confirm' => '确定',
	'close' => '关闭',
	'admin_login' => '您无权访问管理面板，请重新登录!',
	'do_success' => '进行的操作完成了',
	'comment_submit_error' => '对不起，咨询信息或点评信息不符合填写要求，请返回重新填写',
	'site_close' => '站点临时关闭，请稍后再访问',
	'comment_fobidden' => '您好，站点未开启评论',
	'not_found' => '您好，您访问的页面不存在，请返回',
	'not_found_msg' => '您好，您要查看的页面内容信息没有找到',
	'not_view' => '您好，您要查看的信息没有公开发布',
	'no_permission' => '对不起，您所在用户组没有权限进行本次操作',
	'noperm_forremark' => '对不起，站点设置为不允许点评',
	'noperm_forcomment' => '对不起，该对象被设置为不允许评论',
	'notcomment_allscoreoption' => '出错了，您必须点评所有评分选项，请返回重新填写。',
	'seccode_error' => '您好，您输入的验证码不正确，请确认',
	'no_login' => '出错了，请您先登录系统后再进行本操作',
	'system_error' => '出错了，您的操作不正确，请检查您的操作',
	'message_length_error' => '出错了，您输入的内容长度不符合要求，请返回检查',
	'no_reply' => '出错了，您没有权限对该主题进行评论，请返回',
	'login_error' => '出错了，您输入的账号信息不正确，请尝试重新登录',
	'enter_the_password_is_incorrect' => '输入的密码不正确，请重新尝试',
	'excessive_number_of_attempts_to_sign' => '您30分钟内尝试登录管理平台的次数超过了3次，为了数据安全，请稍候再试',
	'user_delete' => '用户被删除，请联系管理员',
	'login_succeed' => '操作完成，您已经成功登录站点系统了 {ucsynlogin}',
	'logout_succeed' => '操作完成，您已经成功退出站点系统了 {ucsynlogin}',
	'poll_repeat' => '出错了，您已经投过票了，不能重复投票',
	'no_votekey' => '出错了，您没有选择要投票的选项',
	'the_system_does_not_allow_searches'=>'您未登录，系统不允许搜索',
	'inquiries_about_the_short_time_interval'=>'出错了，您两次查询的时间间隔太短，请稍后再继续搜索',
	'not_find_relevant_data'=>'没有找到相关数据，请更换查询关键字试试',
	'search_types_of_incorrect_information'=>'出错了，搜索信息类型不正确',
	'keyword_import_inquiry'=>'出错了，请输入您要查询的关键字',
	'kwyword_import_short' => '出错了,输入的关键字长度需大于2个字符',
	'page_limit' => '出错了，您要查看页数太大了，请选择其他条件查看列表',
	'view_images_do_not_exist' => '出错了，查看的图片不存在',
	'error_view' => '出错了，相册不存在或者您没有权限查看',
	'credit_not_enough' => '您的积分不足以支付此次操作。',

	//admincp.php
	'admincp_login' => '您没有登录站内系统，请先登录',

	//attend.php
	'applicant_info_failed' => '<span style="font-size:14px;font-weight:700;">您填写的信息有误，请根据提示重新填写。</span>',
	'user_info_failed' => '<span style="font-size:14px;font-weight:700;">您填写的信息有误，请根据提示重新填写。</span>',
	'apm_panel_msg' => '<span style="color:#009900;font-size:14px;font-weight:700;">尊敬的商家，您的申请我们已经收到，请耐心等待工作人员审核！在此期间请您注意查收站内短信！</span>',
	'no_submit' => '没有提交数据',
	'attend_register_success' => '<span style="color:#009900;font-size:14px;font-weight:700;">尊敬的商家，恭喜您已经成功入住。</span>',

	//batch.comment.php
	'words_can_not_publish_the_shield' => '出错了，您输入的内容中因为含有被屏蔽的词语而不能发布',
	'comment_too_much' => '您评论太快了，稍等半分钟再试试',

	//register.php
	'start_listcount_error' => '出错了，您要查看页数不存在',
	'not_found_tag' => '暂时没有找到指定的tag信息',

	//do_register.php
	'incorrect_code' => '验证码填写错误，请重新填写',
	'seccode_notwrite' => '没有输入验证码，请重新填写',
	'submit_invalid' => '您的请求无法提交。请尝试刷新本页面。',
	'not_open_registration' => '非常抱歉，本站目前暂时不开放注册',
	'registered' => '注册成功了',
	'system_uc_error' => '系统错误，未找到UCenter Client文件',
	'password_inconsistency' => '两次输入的密码不一致',
	'profile_passwd_illegal' => '密码空或包含非法字符，请重新填写。',
	'user_name_is_not_legitimate' => '用户名不合法',
	'include_not_registered_words' => '用户名包含不允许注册的词语',
	'email_format_is_wrong' => 'Email 格式有误',
	'email_not_registered' => 'Email 不允许注册',
	'email_has_been_registered' => 'Email 已经被注册',
	'register_error' => '注册失败',
	'user_name_already_exists' => '用户名已经存在',

	//batch.epitome.php,batch.thumb.php
	'parameter_chenged' => '禁止篡改参数',
	'GD_lib_no_load' => '没有加载GD库。',
	'image_little' => '图片太小，无法裁切',

	//store.php
	'notfound_commentmodel' => '对不起！没有找到该商铺的点评模型。',

	//batch.modeldownload.php
	'visit_the_channel_does_not_exist' => '您访问的频道不存在,请返回首页.',
	'downloading_short_time_interval' => '出错了，您下载的时间间隔太短,请稍后再继续下载.',

	//viewpro.php
	'uc_client_dir_error' => 'UCenter连接有误，请与管理员联系。',
	'space_does_not_exist' => '指定的用户空间不存在',

	//blogdetail.php
	'blog_no_info' => '日志不存在',

	//source/do_lostpasswd.php
	'user_does_not_exist' => '该用户不存在',
	'getpasswd_illegal' => '您所用的 ID 不存在或已经过期，无法取回密码。',
	'getpasswd_succeed' => '您的密码已重新设置，请使用新密码登录。',
	'getpasswd_account_invalid' => '对不起，创始人、受保护用户或有站点设置权限的用户不能使用取回密码功能，请返回。',
	'mail_send_fail' => '邮件发送失败!请联系管理员',
	'email_username_does_not_match' => '输入的Email地址与用户名不匹配，请重新确认。',
	'email_send_success' => '取回密码的方式已经发送到您的邮箱中，请于3天之内取回您的密码',
    'link_failure' => '链接失效',

	//source/cp_click.php
	'click_error' => '没有进行正常的表态操作',
	'click_item_error' => '要表态的对象不存在',
	'click_no_self' => '自己不能给自己表态',
	'click_have' => '您已经表过态了',
	'click_success' => '参与表态完成了',

	//source/cp_news.php
	'no_item' => '对不起，没找到指定的信息。',

	//source/cp_credit.php
	'integral_convertible_unopened' => '系统目前没有开启积分兑换功能。',
	'extcredits_dataerror' => '兑换失败，请与管理员联系。',
	'credits_balance_insufficient' => '对不起，您的积分余额不足，兑换失败，请返回。',
	'credits_password_invalid' => '您没有输入密码或密码错误，不能进行积分操作，请返回。',
	'credits_transaction_amount_invalid' => '您要转账或兑换的积分数量输入有误，请返回修改。',
	'credits_exchange_invalid' => '兑换的积分方案有错，不能进行兑换，请返回修改。',

	//source/cp_profile.php
	'old_password_invalid' => '您没有输入旧密码或旧密码错误，请返回重新填写。',
	'no_change' => '没有做任何修改',
	'protection_of_users' => '受保护的用户，没有权限修改',
	'password_is_not_passed' => '输入的登录密码不正确,请返回重新确认',

	//source/cp_models.php
	'space_suject_length_error' => '您输入的标题长度不符合要求(2~80个字符)',
	'admin_func_catid_error' => '您没有正确指定分类，请返回确认',
	'document_types_can_only_upload_pictures' => '标题图片只能上传图片类型文件(.jpg .jpeg .gif .png).',
	'writing_success_online_please_wait_for_audit' => '提交成功,请等待审核通过.',
	'online_contributions_success' => '在线投稿成功.',
	'writing_success_online_please_wait_for_audit' => '提交成功,请等待审核通过.',
	'parameter_error' => '出错了，参数错误,请返回',

	//店铺关闭
	'shop_close' => '您好，您访问的店铺目前暂未开启，请稍后再来！',
	'shop_optpass' => '您好，您所访问的店铺正在接受管理员审核，请稍后再来',
	'no_perm' => '抱歉，您没有权限进行此操作',
	'no_tagids' => '当前分类下没有可以浏览的信息，请返回重新选择！',
	'admin_no_perm_to_panel' => '您只能在站长管理中心进行相关操作。',
	'noperm_manageshop' => '对不起，该店铺未找到，或不属于您。',

	'requirefiled_not_complate' => '请详细填写相关信息',
	'join_success' => '报名成功。',
	'groupbuy_end_join' => '抱歉，该团人数已满',
	'already_joined'=> '抱歉，您已经报名过了，请勿重复报名',

);

?>