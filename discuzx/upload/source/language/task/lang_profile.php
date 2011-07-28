<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: lang_profile.php 22302 2011-04-29 02:20:12Z maruitao $
 */

$lang = array
(
	'profile_name' => 'Improve the task of user information',
	'profile_desc' => 'Improve access to the specified user data corresponding rewards',

	'profile_fields' => array(
		'mp.realname' => 'Name',
		'mp.gender' => 'Gender',
		'mp.birthyear' => 'birthy(year)',
		'mp.birthmonth' => 'birthy(month)',
		'mp.birthday' => 'birthy(day)',
		'mp.bloodtype' => 'Blood type',
		'mp.affectivestatus' => 'Emotional states',
		'mp.birthprovince' => 'Home (Province))',
		'mp.birthcity' => 'Hometown (City)',
		'mp.resideprovince' => 'Place of residence (province)',
		'mp.residecity' => 'Place of residence (city)'
	),

	'profile_view' => '<strong>You have the following items need to be supplemented complete profile：</strong><br>
		<span style="color:red;">{profiles}</span><br><br>
		<strong>Follow the instructions to participate in the task：</strong>
		<ul>
		<li><a href="home.php?mod=spacecp&ac=profile" target="_blank" class="xi2">Click here to open the Settings page of personal data</a></li>
		<li>Set in the newly opened page，add the above personal information complete</li>
		</ul>',
);

?>