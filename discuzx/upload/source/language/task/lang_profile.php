<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: lang_profile.php 22302 2011-04-29 02:20:12Z maruitao $
 */

$lang = array
(
	'profile_name' => '完善用户资料任务',
	'profile_desc' => '完善指定的用户资料获得相应的奖励',

	'profile_fields' => array(
		'mp.realname' => '姓名',
		'mp.gender' => '性别',
		'mp.birthyear' => '生日(年)',
		'mp.birthmonth' => '生日(月)',
		'mp.birthday' => '生日(日)',
		'mp.bloodtype' => '血型',
		'mp.affectivestatus' => '情感状态',
		'mp.birthprovince' => '家乡(省)',
		'mp.birthcity' => '家乡(市)',
		'mp.resideprovince' => '居住地(省)',
		'mp.residecity' => '居住地(市)'
	),

	'profile_view' => '<strong>您还有以下个人资料项需要补充完整：</strong><br>
		<span style="color:red;">{profiles}</span><br><br>
		<strong>请按照以下的说明来完成本任务：</strong>
		<ul>
		<li><a href="home.php?mod=spacecp&ac=profile" target="_blank" class="xi2">点击这里打开个人资料设置页面</a></li>
		<li>在新打开的设置页面中，将上述个人资料补充完整</li>
		</ul>',
);

?>