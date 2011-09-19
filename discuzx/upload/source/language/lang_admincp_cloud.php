<?php

/**
 *	  [Discuz!] (C)2001-2099 Comsenz Inc.
 *	  This is NOT a freeware, use is subject to license terms
 *
 *	  $Id: lang_admincp_cloud.php 22900 2011-05-30 11:04:15Z yexinhao $
 */

$extend_lang = array
(
	'header_cloud' => 'Cloud',
	'header_navcloud' => 'Cloud Platform',
	'nav_cloud' => 'Cloud Platform',

	'menu_cloud_open' => 'Cloud Platform',
	'menu_cloud_upgrade' => 'Open',
	'menu_cloud_applist' => 'Platform Home',
	'menu_cloud_siteinfo' => 'Site Information',
	'menu_cloud_doctor' => 'Diagnostic Tools',

	'menu_setting_manyou' => 'Manyou Settings',
	'menu_setting_qqconnect' => 'QQ Settings',

	'menu_cloud_manyou' => 'Manyou',
	'menu_cloud_connect' => 'QQ Connect',
	'menu_cloud_search' => 'Search',
	'menu_cloud_stats' => 'Stats',
	'menu_cloud_security' => 'Security',
	'menu_cloud_smilies' => 'Smilies',
	'menu_cloud_qqgroup' => 'QQ Group',
	'menu_cloud_union' => 'Affiliate',

	'close' => 'Close',
	'continue' => 'Continue',
	'message_title' => 'Message',
	'jump_to_cloud' => 'Go to Discuz! Cloud Platform (http://cp.discuz.qq.com) to Complete Process',

	'cloud_status_error' => 'There is some small mistakes, as the site ID / KEY communication of critical information is lost cause Discuz! Unusual cloud platform services, use <a href="admin.php?action=cloud&operation=doctor">Diagnostic Tools</a> Test site ID and KEY, if in doubt please visit <a href="http://www.discuz.net/forum-3926-1.html" target="_blank">Official Forum</a> for help',

	'cloud_introduction' => 'Start',
	'cloud_confirm_open' => 'Open',
	'cloud_confirm_upgrade' => 'Upgrade',
	'cloud_page_loading' => 'Loading...',
	'cloud_time_out' => 'You can not access Discuz! Cloud platform, try <a href="javascript:;" onClick="location.reload()">Refresh</a>。',
	'cloud_unknown_dns' => 'Your site can not currently connect Discuz! Cloud platform, please check your server network settings, use <a href="admin.php?action=cloud&operation=doctor">Diagnostic Tools</a> Test DNS resolution and interface connectivity.<a href="http://cp.discuz.qq.com/faq?fId=1305690058&ADTAG=CP.CLOUD.FAQ.FID" target="_blank">View Help</a> If you have questions please visit <a href="http://www.discuz.net/forum-3926-1.html" target="_blank">Forum</a> Help',

	'cloud_category' => 'Category',
	'cloud_site_name' => 'Site Name',
	'cloud_site_url' => 'URL',
	'cloud_site_category' => 'Category',
	'cloud_select' => 'Please Select',
	'cloud_agree_protocal' => 'I\'ve Read and Agree to ',
	'read_protocal' => 'Discuz! Cloud Platform Services Agreement.',
	'cloud_will_open' => 'Submit',
	'cloud_will_upgrade' => 'Upgrade',
	'cloud_protocal' => 'Discuz! Cloud Platform Services Agreement',
	'cloud_select_category' => 'Select Site Category',
	'cloud_select_sub_category' => 'Select Sub Category',
	'cloud_select_return' => 'Please Select Site Category First , Okay ?',
	'cloud_open_success' => 'Discuz! Cloud Platform Launched Successfully ',
	'cloud_upgrade_success' => 'Discuz! Cloud Platforum Upgrade Successfully ',
	'cloud_network_busy' => 'Newtork Busy Now , Please Come Back Later <br />{errMessage} (ERRCODE:{errCode})',
	'cloud_turnto_applist' => 'Your site has been opened Discuz! Cloud platform, jump to the platform is now home',
	'cloud_site_id' => 'Site ID',
	'cloud_api_ip_btn' => 'Set Cloud Platform Interface IP',
	'cloud_api_ip' => 'Cloud Platform Interface IP',
	'cloud_api_ip_comment' => 'If the site server as a DNS resolution problem can not connect the interface to the cloud platform, please fill out the api.discuz.qq.com IP address, use <a href="admin.php?action=cloud&operation=doctor">Diagnostic Tools</a> Detection, <a href="http://cp.discuz.qq.com/faq?fId=1304068911&ADTAG=CP.CLOUD.FAQ.FID" target="_blank">View Help</a>',
	'cloud_manyou_ip' => 'Manyou IP',
	'cloud_manyou_ip_comment' => 'If the site server as a DNS resolution problem can not connect to the roaming interface, please fill out the api.manyou.com IP address, use <a href="admin.php?action=cloud&operation=doctor">Diagnostic Tools</a> Detection, <a href="http://faq.comsenz.com/viewnews-400" target="_blank">View Help</a>',
	'cloud_ipsetting_success' => 'Cloud Platform Interface IP set Successfully ',
	'cloud_open_first' => 'Please Open Discuz! Cloud platform',
	'cloud_sync' => 'Synchronization Site Information',
	'cloud_sync_success' => 'Site Information Synchronization Success ',
	'cloud_sync_failure' => 'Site information synchronization failed, because: <br />{errMessage} (ERRCODE:{errCode})<br /><br /> For inquiries, please visit <a href="http://www.discuz.net/forum-3926-1.html" target="_blank">Forum</a>Help',
	'cloud_resetkey' => 'Replacement site KEY',
	'cloud_reset_success' => 'KEY Successful Replacement Site ',

	'cloud_siteinfo_tips' => '<li>If the site name or site URL to change, please click the "Sync site information" button. </li><li>KEY is the site site to communicate with the cloud platform authentication key, if the recent leakage of dangerous operation KEY sites and other information, please click on the “replacement site KEY” button.<span style="color:red;"> Be careful using this feature.</span></li>',

	'cloud_doctor_tips' => '<li>Discuz! Cloud platform diagnostic tools to help you analyze the situation on site, whether the cloud platform to communicate properly with other functions.</li>
		<li>Site ID is your site in the cloud platform uniquely identifies, and other sites do not share a site ID and site traffic KEY</li>',

	'cloud_doctor_setidkey' => 'Modify Discuz! On the site ID and KEY',
	'cloud_doctor_setidkey_tips' => '<li style="color:red">Modify Discuz! On the site ID and KEY, may lead to communication errors, mistakes and other failures signature, do not in the absence of official guidance to modify personnel.</li>
		<li style="color:red">Modify the ID, KEY, and the state before the backup forum common_setting Table.</li>',
	'cloud_site_key' => 'Site Communications KEY',
	'cloud_site_key_safetips' => '(For security reasons, some hidden)',
	'cloud_site_key_comment' => 'Do not disclose site traffic KEY',
	'cloud_site_status' => 'Status',
	'cloud_idkeysetting_success' => 'Site ID / KEY Status is Set Successfully ',
	'cloud_idkeysetting_siteid_failure' => 'Site ID must be pure numbers, do not be modified. If necessary, changes in customer service assistance.',
	'cloud_idkeysetting_sitekey_failure' => 'Site communication KEY must be 32, do not be modified. If necessary, changes in customer service assistance.',

	'cloud_doctor_result_success' => '<img align="absmiddle" src="static/image/admincp/cloud/right.gif" />',
	'cloud_doctor_result_failure' => '<img align="absmiddle" src="static/image/admincp/cloud/wrong.gif" /> ',

	'cloud_doctor_api_test_other' => 'Test Other Interfaces Cloud Platform IP',
	'cloud_doctor_manyou_test_other' => 'Testing Roaming other Interfaces IP',
	'cloud_doctor_api_test_success' => '%s Request Interface %s Successful, time-consuming %01.3f 秒 %s',
	'cloud_doctor_api_test_failure' => '%s Request Interface %s Failure, please consult space business %s',
	'cloud_doctor_status_0' => 'Not yet Opened Cloud Platform',
	'cloud_doctor_status_1' => 'Has Opened Cloud Platform',
	'cloud_doctor_status_2' => 'Registration cloud platform, waiting for the completion',

	'cloud_doctor_title_status' => 'System Status',
	'cloud_doctor_modify_siteidkey' => 'Manually modify the site ID/KEY',
	'cloud_doctor_close_yes' => 'Be (Front Connect will not be displayed)',

	'cloud_site_version' => 'Product Version',
	'cloud_site_release' => 'Product Release Date',

	'cloud_doctor_title_result' => 'Test Results (<a href="#" onClick="self.location.reload();">Re-test</a>)',

	'cloud_doctor_php_ini_separator' => 'URL Delimiter',
	'cloud_doctor_php_ini_separator_true' => 'Empty or &',
	'cloud_doctor_php_ini_separator_false' => 'php.ini Of arg_separator.output Set to the value or non &  ini_get Function is disabled, please contact the space business',

	'cloud_doctor_fsockopen_function' => 'fsockopen Function',
	'cloud_doctor_gethostbyname_function' => 'DNS Analytic Functions',
	'cloud_doctor_function_disable' => 'Function is disabled, please contact the space business',

	'cloud_doctor_dns_api' => 'DNS Cloud Platform',
	'cloud_doctor_dns_api_test' => 'Master Interface Test Cloud Platform',
	'cloud_doctor_other_api_test' => 'Other Cloud Platform Interface Testing',
	'cloud_doctor_dns_manyou' => 'Roaming Domain Name',
	'cloud_doctor_dns_manyou_test' => 'Roaming Main Interface Testing',
	'cloud_doctor_other_manyou_test' => 'Roaming other Interface Testing',

	'cloud_doctor_setting_ip' => 'Manually Set IP:',

	'cloud_doctor_dns_success' => '%s DNS Resolution IP %s %s <a href="javascript:;" onClick="showWindow(\'cloudApiIpWin\', \'%s?action=cloud&operation=siteinfo&anchor=cloud_ip&callback=doctor\'); return false;">Set interface IP</a>',
	'cloud_doctor_dns_failure' => '<img align="absmiddle" src="static/image/admincp/cloud/wrong.gif" /> %s DNS resolution failure %s <a href="javascript:;" onClick="showWindow(\'cloudApiIpWin\', \'%s?action=cloud&operation=siteinfo&anchor=cloud_ip&callback=doctor\'); return false;">Set interface IP</a>',

	'cloud_doctor_title_plugin' => 'System plug-in Detection',
	'cloud_doctor_system_plugin_status' => 'System Plug-in Status',
	'cloud_doctor_system_plugin_list' => '<a href="admin.php?action=plugins">View a list of plug-ins and versions</a>',
	'cloud_doctor_system_plugin_status_false' => ' System plugin is not initialized (left menu is not displayed) <a href="misc.php?mod=initsys" target="_doctor_initframe" onClick="$(\'_doctor_initframe\').onload = function () {self.location.reload();};">Click Repair</a><iframe id="_doctor_initframe" name="_doctor_initframe" src="" width="0" height="0" style="display:none;"></iframe>',
	'cloud_doctor_plugin_module_error' => 'common_plugin Table modules Incorrect field values',

	'cloud_doctor_title_connect' => 'QQ Internet Resting',
	'cloud_doctor_connect_app_id' => 'QQ Interconnected Appid',
	'cloud_doctor_connect_app_key' => 'QQ Interconnected Appkey',
	'cloud_doctor_connect_reopen' => 'Current Site Appid/Appkey Lost, <a href="admin.php?action=cloud&operation=applist">Re-opened</a>QQ Internet',

	'cloud_application_close' => 'This site does not open your cloud services, please Discuz! Background cloud platform under the open label',
	'cloud_application_disable' => 'Your site has been banned this cloud service, if you have questions please visit <a href="http://www.discuz.net/forum.php?gid=3923" target="_blank">Forum</a> Help',

	'cloud_search_tips' => '<li>Search on roaming, the user can use the search function on roaming.</li>',

	'cloud_stats' => 'Tencent Analysis',
	'cloud_stats_tips' => '<li>Tencent analysis - analysis of the most professional community, to provide your data to support community development. </li><li>Opening Tencent analysis, the next day you can view the previous day\'s data.</li><li><a href="http://stats.discuz.qq.com/" target="_blank"><span color="blue">See details</span></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="http://cp.discuz.qq.com/faq?fcId=106&ADTAG=CP.CLOUD.FAQ.FCID" target="_blank"><span color="blue">Tencent analysis to understand</span></a></li>',
	'cloud_stats_status' => 'Enable Tencent analysis',
	'cloud_stats_icon_set' => 'Select the style (icon or text displayed in the lower right corner of Forum)',
	'cloud_stats_icon_none' => 'Do not show icons and text',
	'cloud_stats_icon_word9' => 'Tencent Analysis',
	'cloud_stats_icon_word10' => 'Website Statistics',
	'cloud_stats_setting' => 'Set up',
	'cloud_stats_summary' => 'About Site',

	'cloud_smilies' => 'SOSO Smilies',
	'cloud_smilies_tips' => '<li>Search to make the world face, the site brings a lot of fun and experience.</li>
		<li>Omit the tedious process of uploading expression, seamless “Illegal immigration” QQ Expression to your website, forum expression is no longer monotonous.</li>',

	'cloud_smilies_status' => 'Enabled SOSO Expression',

	'setting_manyou' => 'Roaming settings',
	'setting_manyou_tips' => '<li>Open the roaming function, users can freely choose from a variety of applications (Such as Texas Hold\'em, Bouncing Church, ten sword ......)For use in the station.</li>
		<li>Roaming feature by the <a target="_blank" href="http://www.manyou.com/www/">MYOP Open platform</a> Provided, Manyou Open Platform (Manyou Open platform/MYOP) Service by Comsenz Company to provide an open application development platform, enabling roaming services before <a href="http://wiki.developer.manyou.com/wiki/index.php?title=MYOP%E7%BD%91%E7%AB%99%E6%9C%8D%E5%8A%A1%E5%8D%8F%E8%AE%AE&printable=yes" target="_blank">Please read the website service agreement MYOP</a></li>',
	'setting_manyou_base' => 'Basic Settings',
	'setting_manyou_base_status' => 'Enable Roaming',
	'setting_manyou_base_status_comment' => 'Choose whether to open roaming. If you turn off roaming, your site users will not use any application based on roaming',
	'setting_manyou_search_status' => 'Enable Roaming Search',
	'setting_manyou_search_status_comment' => 'Search is designed for roaming Discuz! Products tailored to the efficient, full-text search service, no website MySQL Resources',
	'setting_manyou_search_invite' => 'Roaming invitation code search',
	'setting_manyou_search_invite_comment' => 'Currently in beta, you need to enter an invitation code search only open roaming, <a href="http://www.discuz.net/thread-1669366-1-1.html" target="_blank">Click here to apply for an invitation code</a>',
	'setting_manyou_base_status_no' => 'Roaming is not open, this can not be managed.',
	'setting_manyou_base_ip' => 'Roaming Applications IP',
	'setting_manyou_base_ip_comment' => 'The default is empty. If your server due to DNS Resolve the problem can not use roaming services, please fill out the application roaming IP Address. <a href="http://faq.comsenz.com/viewnews-400" target="_blank">See roaming applications IP</a>',
	'setting_manyou_base_close_prompt' => 'Roaming off Update Prompt',
	'setting_manyou_base_close_prompt_comment' => 'Your site on a roaming multi-application service, when the platform with the new information when roaming the application will automatically prompt to the administrator. Disable this function, you will not get updated tips.',
	'setting_manyou_base_open_app_prompt' => 'Announcement on Roaming',
	'setting_manyou_base_open_app_prompt_comment' => 'When a new application platform announcement, the user opens a browser window will pop up when prompted, apply the update prompt similar to roaming',
	'setting_manyou_base_refresh' => 'Synchronization Roaming Information',
	'setting_manyou_base_refresh_comment' => 'If you change the name of the navigation, search settings, synchronize the roaming information.',
	'setting_manyou_base_showgift_comment' => 'If you open a gift roaming application platform applications, you can display on the home page “Suggested Gift” ',
	'setting_manyou_manage' => 'Roaming Management',
	'setting_manyou_search_manage' => 'Roaming Search Management',

	'connect_menu_setting' => 'Basic Settings',
	'connect_menu_service' => 'Other',
	'connect_menu_stat' => 'Statistics',
	'connect_setting_allow' => 'Open QQ registration / binding / Login Service',
	'connect_setting_allow_comment' => 'Turned on, the user can log on QQ account the site, as well as the associated operations and QQ more',
	'connect_setting_siteid' => 'QQ Binding Site ID',
	'connect_setting_sitekey' => 'QQ Binding Site Key',
	'connect_setting_feed_allow' => 'Synchronous push open post QQ Spatial dynamic',
	'connect_setting_feed_allow_comment' => 'Turned on, users can synchronize push QQ post spatial dynamics, presented to the user\'s QQ space friends',
	'connect_setting_feed_fids' => 'Allow Push Forum',
	'connect_setting_feed_group' => 'Groups are Allowed to Push',
	'connect_setting_feed_group_comment' => 'Set whether the subject published in the group can be pushed to the QQ space dynamic',
	'connect_setting_t_allow' => 'Post synchronized push Tencent open microblogging',
	'connect_setting_t_allow_comment' => 'Turned on, users can synchronize push post Tencent microblogging, microblogging presented to the user audience',
	'connect_setting_t_fids' => 'Allow Push Forum Forum',
	'connect_setting_t_group' => 'Groups are Allowed to Push',
	'connect_setting_t_group_comment' => 'Set whether the subject published in the group can push Tencent microblogging',
	'connect_setting_like_allow' => 'QQ certified display space like site link',
	'connect_setting_like_allow_comment' => 'Certification user clicks QQ space like site link, will immediately become a fan of site space QQ certification, certification of space charge dynamics at any time',
	'connect_setting_like_url' => 'QQ number of certified space',
	'connect_setting_like_url_comment' => 'Set the authentication space QQ number, please submit applications for accreditation <a href="http://opensns.qq.com/" target="_blank">Click here</a>',
	'connect_setting_turl_allow' => 'Show Site to listen to the official micro-Bo quick button',
	'connect_setting_turl_allow_comment' => 'User clicks on the official site microblogging quick listen button will immediately become your set Tencent microblogging account the audience, ready to receive the dynamic micro-Bo',
	'connect_setting_turl_qq' => 'Official QQ number microblogging',
	'connect_setting_turl_qq_comment' => 'Set the number of official microblogging QQ',
	'connect_setting_turl_qq_failed' => 'Official microblogging QQ number setting fails, make sure that the QQ number for validity',
	'connect_member_info' => 'User Information',
	'connect_member_bindlog' => 'QQ Bound Log',
	'connect_member_bindlog_type' => 'Operating',
	'connect_member_bindlog_username' => 'User Name',
	'connect_member_bindlog_date' => 'Date',
	'connect_member_bindlog_type_1' => 'Binding',
	'connect_member_bindlog_type_2' => 'Unbind',
	'connect_member_bindlog_uin' => 'QQ Account Bound Log',
	'connect_member_bindlog_uid' => 'User Account Log Bound',

	'qqgroup_menu_list' => 'Binding Management',
	'qqgroup_menu_manager' => 'Set Name',
	'qqgroup_menu_block' => 'Push Information',
	'qqgroup_menu_history' => 'Push History',

	'qqgroup_msg_deficiency' => 'Push a headline at least a list of themes and topics',
	'qqgroup_msg_save_succeed' => 'Congratulations, information successfully pushed to the QQ group',
	'qqgroup_msg_upload_succeed' => 'Photo Upload Successful ',
	'qqgroup_msg_upload_failure' => 'Images failed to upload, select the length and width of 75 * 75 pictures, support JPG, GIF, PNG format, the file is less than 5M, and check whether the server is on the GD library',
	'qqgroup_msg_remote_exception' => 'Sorry, there is some small mistakes. Cause of the error: {errmsg} Error code: {errno}',
	'qqgroup_msg_unknown_dns' => 'Sorry, an unknown error, please check your server Discuz! Cloud platform to connect',
	'qqgroup_msg_remote_error' => 'Sorry, server error. Please try again later.',

	'qqgroup_search_order_views' => 'Views Descending',
	'qqgroup_search_order_replies' => 'Replies DESC',
	'qqgroup_search_order_heats' => 'Reverse',
	'qqgroup_search_order_dateline' => 'Date DESC',
	'qqgroup_search_order_lastpost' => 'Last Post Reverse',
	'qqgroup_search_order_recommends' => 'Thematic Evaluation of Reverse',

	'qqgroup_search_dateline_1' => '1 Theme released within hours',
	'qqgroup_search_dateline_2' => '24 Theme released within hours',
	'qqgroup_search_dateline_3' => '7 Days theme released',
	'qqgroup_search_dateline_4' => '1 Months theme released',
	'qqgroup_search_dateline_0' => 'No Restrictions',

	'qqgroup_search_tid' => 'Theme ID:',
	'qqgroup_search_button' => 'Search',
	'qqgroup_search_threadslist' => 'Topic List',
	'qqgroup_search_inforum' => 'In Forum',
	'qqgroup_search_operation' => 'Operating',

	'qqgroup_search_loading' => 'Loading ...',
	'qqgroup_search_nothreads' => 'The subject of conditions specified was not found, try replacing filter re-search',

	'qqgroup_ctrl_add_miniportal_topic' => 'Pushed to Headlines',
	'qqgroup_ctrl_add_miniportal_normal' => 'Pushed to List',
	'qqgroup_ctrl_up' => 'Move',
	'qqgroup_ctrl_down' => 'Down',
	'qqgroup_ctrl_edit' => 'Edit',
	'qqgroup_ctrl_remove' => 'Out',
	'qqgroup_ctrl_upload_image' => 'Upload Pictures',
	'qqgroup_ctrl_choose_image' => 'Select a Picture:',
	'qqgroup_ctrl_choose_image_tips' => 'Please select the length and width of 75 * 75 pictures, support JPG, GIF, PNG format, the file is less than 5M.',
	'qqgroup_ctrl_close' => 'Close',

	'qqgroup_preview_tips_topic' => 'Click on the left<img src="static/image/admincp/cloud/qun_op_top.png" align="absmiddle" /> Will push the information to headlines',
	'qqgroup_preview_tips_normal' => 'Click on the left <img src="static/image/admincp/cloud/qun_op_list.png" align="absmiddle" /> Will push the information to the list',
	'qqgroup_preview_more' => 'More',
	'qqgroup_preview_shortname' => 'Page Title Card',
	'qqgroup_preview_button' => 'Push Information',
	'attach_img' => 'Photo Accessories',

);

$GLOBALS['admincp_actions_normal'][] = 'cloud';

?>