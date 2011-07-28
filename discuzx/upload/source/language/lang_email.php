<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: lang_email.php 20525 2011-02-25 04:25:21Z congyushuai $
 */


$lang = array
(
	'hello' => 'Hello',
	'moderate_member_invalidate' => 'invalidate',
	'moderate_member_delete' => 'Del',
	'moderate_member_validate' => 'Pass',


	'get_passwd_subject' =>		'Retrieve Password instructions',
	'get_passwd_message' =>		'
<p>{username}﹐
This letter is {bbname} Sent。</p>

<p>You received this message because this email address in {bbname} is registered as a users mailbox﹐
Email and request the use of the user due to the password reset feature。</p>
<p>
----------------------------------------------------------------------<br />
<strong>Important！</strong><br />
----------------------------------------------------------------------</p>

<p>If you do not submit requests for password reset or not {bbname} registered users﹐ please ignore
And delete this message。 Only if you need to reset the password confirmation cases﹐ it needs to continue to read the following
Content。</p>
<p>
----------------------------------------------------------------------<br />
<strong>Password reset instructions</strong><br />
----------------------------------------------------------------------</p>
</p>
You only need to submit within three days after a request by clicking the link below to reset your password：<br />

<a href="{siteurl}member.php?mod=getpasswd&amp;uid={uid}&amp;id={idstring}" target="_blank">{siteurl}member.php?mod=getpasswd&amp;uid={uid}&amp;id={idstring}</a>
<br />
(If the above is not a link in the form﹐ please paste the address manually into your browser address bar then visit)</p>

<p>In the above link to the page that opens﹐ enter the new password to submit﹐ you can use the new password to log site。 User control panel you can change your password at any time。</p>

<p>he request was submitted by the IP {clientip}</p>


<p>
Sincerely<br />
</p>
<p>{bbname} Management Team
{siteurl}</p>',


	'email_verify_subject' =>	'Email Address Verification',
	'email_verify_message' =>	'
<p>{username}﹐
This letter is {bbname} Sent。</p>

<p>Your received this email﹐ is due to {bbname} for the new user registration﹐ use﹐ or the user to modify Email This email address。 If you have not visited {bbname}﹐ or not done so﹐ please ignore Slightly this message。 You do not need to unsubscribe or other further action。</p>
<br />
----------------------------------------------------------------------<br />
<strong>Account activation instructions</strong><br />
----------------------------------------------------------------------<br />

<p>If you are a new user {bbname}﹐ or modify your registration Email address to use this﹐ we need to To verify the validity of your address to avoid junk e-mail or address to be abused。</p>

<p>Just click the link below to activate your account：<br />

<a href="{url}" target="_blank">{url}</a>
<br />
(If the above is not a link in the form﹐ please paste the address manually into your browser address bar﹐ then visit)</p>

<p>Thank you for your visit﹐ I wish you a happy！</p>


<p>
Sincerely<br />

{bbname} Management Team<br />
{siteurl}</p>',

	'add_member_subject' =>		'Add to become a member you are',
	'add_member_message' => 	'
{newusername} ﹐
This letter is {bbname} Sent。

I am {adminusername} ﹐{bbname} One of the managers。 You received this message because you are
Has just been added as a member of {bbname}﹐ that is our current Email your registered email address。

----------------------------------------------------------------------
Important！
----------------------------------------------------------------------

If you {bbname} Not interested or do not intend to become a member﹐ please ignore this message。

----------------------------------------------------------------------
Account Information
----------------------------------------------------------------------

Site Name: {bbname}
Web site address: {siteurl}

User Name: {newusername}
Password: {newpassword}

From now on﹐ you can use your account login {bbname}﹐ I wish you happy!



Sincerely

{bbname} Management Team
{siteurl}',


	'birthday_subject' =>		'Happy birthday to you',
	'birthday_message' => 		'
{username}﹐
This letter is {bbname} Sent。

You received this message because this email address in {bbname} is registered as a users mailbox﹐
And in accordance with the information you fill out﹐ today is your birthday。 Is pleased to present you at this time﹐ a
Birthday wishes﹐ on behalf of {bbname} Management Team﹐ would like to wish you a happy birthday。

If you are not a member of {bbname}﹐ or today is not your birthday﹐ may be the misuse of your email
Mail address﹐ or fill out the birthday wrong information。 Send e-mail will not be repeated in this﹐ please ignore this
Message。



Sincerely

{bbname} Management Team
{siteurl}',

	'email_to_friend_subject' =>	'{$_G[member][username]} Recommended for you: $thread[subject]',
	'email_to_friend_message' =>	'
This letter is {$_G[setting][bbname]} 的 {$_G[member][username]} Sent。

You have received this e-mail﹐Is due {$_G[member][username]} pass {$_G[setting][bbname]} The ＂Tell a Friend＂
Functional content recommended the following to you。 If you are not interested﹐ please ignore this message。 You do not
Need to unsubscribe or other further action。

----------------------------------------------------------------------
Original letter began
----------------------------------------------------------------------

$message

----------------------------------------------------------------------
The end of the original letter
----------------------------------------------------------------------

Please note that this letter is only by the user using the ＂Tell a Friend＂Sent﹐ not the site of official mail﹐
Site Management Team will be responsible for such messages。

Welcome {$_G[setting][bbname]}
$_G[siteurl]',

	'email_to_invite_subject' =>	'Your friends {$_G[member][username]} send {$_G[setting][bbname]} Up an invitation code to your site',
	'email_to_invite_message' =>	'
$sendtoname﹐
This letter is {$_G[setting][bbname]} {$_G[member][username]} Sent。

You have received this e-mail﹐Is due {$_G[member][username]} pass {bbname} ＂Send an invitation code to a friend＂
Functional content recommended the following to you。 If you are not interested﹐ please ignore this message。 You do not
Need to unsubscribe or other further action。
----------------------------------------------------------------------
Original letter began
----------------------------------------------------------------------

$message

----------------------------------------------------------------------
The end of the original letter
----------------------------------------------------------------------

Please note that this letter is only used by the user ＂to send an invitation code to a friend＂Sent﹐ not the site of official mail﹐
Site Management Team will be responsible for such messages。

Welcome {$_G[setting][bbname]}
$_G[siteurl]',


	'moderate_member_subject' =>	'Notify the user audit results',
	'moderate_member_message' =>	'
<p>{username} ﹐
This letter is {bbname} Sent。</p>

<p>You received this message because this email address in {bbname} is the new user registration
Use﹐ and the administrator of the need for manual review of new users﹐ the message will inform you to submit
Application of the audit results。</p>

----------------------------------------------------------------------<br />
<strong>Registration information and audit results</strong><br />
----------------------------------------------------------------------<br />

User Name: {username} <br />
Up time: {regdate} <br />
Submitted: {submitdate} <br />
Commits: {submittimes} <br />
Registration in: {message} <br />
<br />
Audit Results: {modresult} <br />
Processing time: {moddate} <br />
Audit Manager: {adminusername} <br />
WEBMASTER: {remark} <br />
<br />
----------------------------------------------------------------------<br />
<strong>Audit results indicate</strong><br />
----------------------------------------------------------------------<br />

<p>Pass: Your registration has been approved﹐ you become an official user {bbname}。</p>

<p>Veto: Your registration information is incomplete﹐ or meet our requirements for some of the new user﹐ you can WEBMASTER﹐<a href="home.php?mod=spacecp&ac=profile" target="_blank">Complete your registration information</a>﹐And then re-submit。</p>

<p>Del：Your registration error due to the requirements of our large﹐ or the site of the new registration number has
Than expected﹐ the application has been rejected。 Your account removed from the database﹐ it will not
Re-use or submit re-examine their login﹐ please understand。</p>

<br />
<br />
Sincerely<br />
<br />
{bbname} Management Team。<br />
{siteurl}',

	'adv_expiration_subject' =>	'Your ad will be site {day} Days expire﹐please deal',
	'adv_expiration_message' =>	'The following ad will be your site {day} Days after the expiration﹐please deal：<br /><br />{advs}',
	'invite_payment_email_message' => '
Welcome{bbname}（{siteurl}）﹐Your order{orderid}Completed have been paid﹐Order has been confirmed effective。<br />
<br />----------------------------------------------------------------------<br />
Heres what you get the invitation code
<br />----------------------------------------------------------------------<br />

{codetext}

<br />----------------------------------------------------------------------<br />
Important！
<br />----------------------------------------------------------------------<br />',
);

?>