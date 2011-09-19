<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *      
 *	Translated by discuzindo.net
 */


$lang = array
(
	'hello' => 'Hello',
	'moderate_member_invalidate' => 'Decline',
	'moderate_member_delete' => 'Delete',
	'moderate_member_validate' => 'Accept',


	'get_passwd_subject' =>		'Reset Password',
	'get_passwd_message' =>		'
<p>{username},
This mail is sent by {bbname}.</p>

<p>You have received this message, because this email address is registered as a user in our forums,
and the user requests to reset the password by Email.</p>
<p>
----------------------------------------------------------------------<br />
<strong>Important!</strong><br />
----------------------------------------------------------------------</p>

<p>If you did not requested password reset or if you have not registered at our forum,
please ignore and delete this message.
In the case you confirm the password reset,
you need to read the following content.</p>
<p>
----------------------------------------------------------------------<br />
<strong>Password Reset Instructions</strong><br />
----------------------------------------------------------------------</p>
</p>
You only need to submit within 3 days after a request by clicking the link below to reset your password:<br />

<a href="{siteurl}member.php?mod=getpasswd&amp;uid={uid}&amp;id={idstring}" target="_blank">{siteurl}member.php?mod=getpasswd&amp;uid={uid}&amp;id={idstring}</a>
<br />
(If clicking the URL in this message does not work, just copy and
paste it into the address bar of your browser.)</p>

<p>After the above page open, enter a new password and submit a form, after then you can use your new password.
You can change your password at any time in a user control panel.</p>

<p>Request IP: {clientip}</p>


<p>
Yours,<br />
</p>
<p>{bbname} Management Team.
{siteurl}</p>',


	'email_verify_subject' =>	'Email Address Verification',
	'email_verify_message' =>	'
<p>{username},
This mail is sent by {bbname}.</p>

<p>You have received this message, because of new user registration at our Forum,
or some user have used Your address when modified his/her Email.
If you did not visited our forum, or not carry out about such operations,
please ignore this message.
You do not need to unsubscribe or other further action.</p>
<br />
----------------------------------------------------------------------<br />
<strong>Account Activation Instructions</strong><br />
----------------------------------------------------------------------<br />

<p>You are new to our forum, or modify your registration Email address to use this,
We need to verify the validity of your email address to avoid spam or other abuse actions.</p>

<p>Please visit the link below to active your account: <br />

<a href="{url}" target="_blank">{url}</a>
<br />
(If clicking the URL in this message does not work, just copy and
paste it into the address bar of your browser.)</p>

<p>Thank you for your visit and wish you be happy! </p>


<p>
Sincerely yours,<br />

{bbname} Management Team.<br />
{siteurl}</p>',

	'add_member_subject' =>		'You are Added as a Member',
	'add_member_message' => 	'
{newusername},
This mail is sent by {bbname}.

I am {adminusername}, one of the managers at {bbname}. You have received this message because you are just has been added as a member
at our forum, which is our current Email address you have registered.

----------------------------------------------------------------------
Important!
----------------------------------------------------------------------

If you are not interested in our Forum or do not intend to become a member, please ignore this message.
----------------------------------------------------------------------
Account Information
----------------------------------------------------------------------

Forum Name: {bbname}
Fourm URL: {siteurl}

Username: {newusername}
Password: {newpassword}

From now, you can use your account to log in to our forum, I wish you a pleasant to use!



Sincerely Yours,

{bbname} Team.
{siteurl}',


	'birthday_subject' =>		'Happy Birthday to You!',
	'birthday_message' => 		'
{username},
This letter was sent from the {bbname}.

You have received this message, because this email address is registered in our forum.
In accordance with the information in your profile, today is your Birthday.

Forum management team have pleased to congratulate you with your Birthday,
and sincerely wish you a happy birthday!

P.S.
If you are not a member of our forum, or have no birthday today,
may be a mistake occure.
Check for your email address and birthday in your profile.
This message will not be sent to this e-mail address, please ignore this 
Message.


Sincerely yours,

{bbname} Management Team.
{siteurl}',

	'email_to_friend_subject' =>	'{$_G[member][username]} recommend $thread[subject] to you',
	'email_to_friend_message' =>	'
This mail is sent by {$_G[member][username]} of {$_G[setting][bbname]}.

You are receiving this mail because {$_G[member][username]} used "Recommend to friends" of {$_G[setting][bbname]}
and recommend the content below. If you are not intersted it, you can disregard this message.
You do not need to do any other operations.

----------------------------------------------------------------------
Content Start
----------------------------------------------------------------------

$message

----------------------------------------------------------------------
Content End
----------------------------------------------------------------------

This mail is sent by using "Recommend to friends" function,
it is not an official mail, we will not be responsible for it.

Welcome to visit {$_G[setting][bbname]}
$_G[siteurl]',

	'email_to_invite_subject' =>	'your friend {$_G[member][username]} sent invitation code of {$_G[setting][bbname]} to you',
	'email_to_invite_message' =>	'
$sendtoname,
This mail is sent by {$_G[member][username]} of {$_G[setting][bbname]}.

You are receiving this mail because {$_G[member][username]} used "Send invitation code to friends" of our forum
to recommend some contents to you. If you are not intersted in them, you can ignore this email.
You do not need to do any other operations.

----------------------------------------------------------------------
Content Start
----------------------------------------------------------------------

$message

----------------------------------------------------------------------
Content End
----------------------------------------------------------------------

This mail is sent by using "Send invitation code to friends" function,
it is not an official mail, we will not be responsible for it.

Welcome to visit {$_G[setting][bbname]}
$_G[siteurl]',


	'moderate_member_subject' =>	'User Moderation Result',
	'moderate_member_message' =>	'
<p>{username} ,
This mail is sent by {bbname}.</p>

<p>You are receiving this mail because you registered on our site or someone used your email.
This mail is used to send the result of moderation.</p>

----------------------------------------------------------------------<br />
<strong>Reg Information and Moderation Result</strong><br />
----------------------------------------------------------------------<br />

Username: {username}<br />
Reg Time: {regdate}<br />
Submit Time: {submitdate}<br />
Submit Times: {submittimes}<br />
Reg Reason: {message}<br />
<br />
Moderation Result: {modresult}<br />
Moderation Time: {moddate}<br />
Operator: {adminusername}<br />
Message: {remark}<br />
<br />
----------------------------------------------------------------------<br />
<strong>Explanation of Moderation</strong><br />
----------------------------------------------------------------------<br />

<p>Approval: Your register has been approval, you are the member of our site now.</p>

<p>Invalidate: Your information of registration is not completed or do not meet our requirements,
	       You can <a href="home.php?mod=spacecp&ac=profile" target="_blank">complete your information</a> and submit again.</p>

<p>Deleted: Your information do not meet our requirement or number of members is full
	    Your request has been invalidated. You account has been deleted from our
	    database. You cannot use it to login or submit request of moderation agian.</p>

<br />
<br />
Sincerely,<br />
<br />
{bbname} Management Team<br />
{siteurl}',

	'adv_expiration_subject' =>	'Advertising your site will be expired in {day} days, please deal',
	'adv_expiration_message' =>	'The following ads on your site will be expired {day} days, please deal:<br /><br />{advs}',
	'invite_payment_email_message' => '
Welcome to {bbname} ({siteurl}), your order has been paid {orderid} completed order has been validated.<br />
<br />----------------------------------------------------------------------<br />
Here is what you get the invitation code
<br />----------------------------------------------------------------------<br />

{codetext}

<br />----------------------------------------------------------------------<br />
Important!
<br />----------------------------------------------------------------------<br />',
);

?>