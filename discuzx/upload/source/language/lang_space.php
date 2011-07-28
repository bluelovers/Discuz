<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: lang_space.php 20882 2011-03-07 07:22:17Z lifangming $
 */

$lang = array(
	'hour' => 'hour ',
	'before' => 'before',
	'minute' => 'minute',
	'second' => 'second',
	'now' => 'now',
	'dot' => '、',
	'poll' => 'poll',
	'blog' => 'blog',
	'friend_group_default' => 'other',
	'friend_group_1' => 'awareness through this site',
	'friend_group_2' => 'awareness through the activities',
	'friend_group_3' => 'awareness through friends',
	'friend_group_4' => 'family',
	'friend_group_5' => 'colleagues',
	'friend_group_6' => 'students',
	'friend_group_7' => 'guest',
	'friend_group' => 'custom',
	'wall' => 'leave message',
	'pic_comment' => 'picture comment',
	'blog_comment' => 'blog comment',
	'clickblog' => 'blog position',
	'clickpic' => 'picture position',
	'clickthread' => 'thread position',
	'share_comment' => 'share comment',
	'share_notice' => 'share',
	'doing_comment' => 'record comment',
	'friend_notice' => 'friend',
	'poll_comment' => 'poll comment',
	'poll_invite' => 'poll invite',
	'default_albumname' => 'default album',
	'credit' => 'credit',
	'credit_unit' => 'unit',
	'man' => 'male',
	'woman' => 'female',
	'gender_0' => 'secret',
	'gender_1' => 'man',
	'gender_2' => 'women',
	'year' => 'year',
	'month' => 'month',
	'day' => 'day',
	'unmarried' => 'unmarried',
	'married' => 'married',
	'hidden_username' => 'hidden username',
	'gender' => 'gender',
	'age' => 'age',
	'comment' => 'comment',
	'reply' => 'reply',
	'from' => 'from',
	'anonymity' => 'anonymity',
	'viewmore' => 'viewmore',
	'constellation_1' => 'Aquarius',
	'constellation_2' => 'Pisces',
	'constellation_3' => 'Aries',
	'constellation_4' => 'Taurus',
	'constellation_5' => 'Gemini',
	'constellation_6' => 'Cancer',
	'constellation_7' => 'Leo',
	'constellation_8' => 'Virgo',
	'constellation_9' => 'Libra',
	'constellation_10' => 'Scorpio',
	'constellation_11' => 'Sagittarius',
	'constellation_12' => 'Capricorn',
	'zodiac_1' => 'mouse',
	'zodiac_2' => 'cow',
	'zodiac_3' => 'tiger',
	'zodiac_4' => 'rabbit',
	'zodiac_5' => 'dragon',
	'zodiac_6' => 'snake',
	'zodiac_7' => 'horse',
	'zodiac_8' => 'sheep',
	'zodiac_9' => 'monkey',
	'zodiac_10' => 'chicken',
	'zodiac_11' => 'dog',
	'zodiac_12' => 'pig',
'credits' => 'credits',
	'usergroup' => 'user group',
	'friends' => 'friends',
	'blogs' => 'blogs',
	'threads' => 'threads',
	'albums' => 'albums',
	'sharings' => 'sharings',
	'space_views' => 'Have <strong class="xi1"> {views} </strong> of people had visited',
	'views' => 'Space visited Number',
	'block1' => 'custom module 1',
	'block2' => 'custom module 2',
	'block3' => 'custom module 3',
	'block4' => 'custom module 4',
	'block5' => 'custom module 5',
	'blockdata' => array('personalinfo' => 'personal info', 'profile' => 'profile', 'doing' => 'doing', 'feed' => 'feed',
				'blog' => 'blog', 'stickblog' => 'stick blog', 'album' => 'album', 'friend' => 'friend',
				'visitor' => 'visitor', 'wall' => 'Message Board', 'share' => 'share',
				'thread' => 'thread', 'group'=>$_G[setting][navs][3][navname],'music'=>'music',
				'statistic' => 'statistic','myapp' => 'myapp',
				'block1'=>'custom module 1', 'block2'=>'custom module 2', 'block3'=>'custom module 3',
				'block4'=>'custom module 4','block5'=>'custom module 5'),
	'block_title' => '<div class="blocktitle title"><span>{bname}</span>{more}</div>',
	'blog_li' => '<dl class="bbda cl"><dt><a href="home.php?mod=space&uid={uid}&do=blog&id={blogid}" target="_blank">{subject}</a><span class="xg2 xw0"> {date}</span></dt>',
	'blog_li_img' => '<dd class="atc"><a href="home.php?mod=space&uid={uid}&do=blog&id={blogid}" target="_blank"><img src="{src}" class="summaryimg" /></a></dd>',
	'blog_li_ext' => '<dd class="xg1"><a href="home.php?mod=space&uid={uid}&do=blog&id={blogid}" target="_blank">({viewnum})view</a><span class="pipe">|</span><a href="home.php?mod=space&uid={uid}&do=blog&id={blogid}#comment" target="_blank">({replynum})Reviews</a></dd>',
	'album_li' => '<li style="width:70px"><div class="c"><a href="home.php?mod=space&uid={uid}&do=album&id={albumid}" target="_blank" title="{albumname}, Update {date}"><img src="{src}" alt="{albumname}" width="70" height="70" /></a></div><p><a href="home.php?mod=space&uid={uid}&do=album&id={albumid}" target="_blank" title="{albumname}, Update {date}">{albumname}</a></p><span>picture num: {picnum}</span></li>',
	'doing_li' => '<li>{message}</li><br />{date} {from} replynum({replynum})',
	'visitor_anonymity' => '<div class="avatar48"><img src="image/magic/hidden.gif" alt="anonymity"></div><p>anonymity</p>',
	'visitor_list' => '<a href="home.php?mod=space&uid={uid}" target="_blank" class="avt"><em class="{class}"></em>{avatar}</a><p><a href="home.php?mod=space&uid={uid}" title="{username}">{username}</a></p>',
	'wall_form' => '<div class="space_wall_post">
						<form action="home.php?mod=spacecp&ac=comment" id="quickcommentform_{uid}" name="quickcommentform_{uid}" method="post" autocomplete="off" onsubmit="ajaxpost(\'quickcommentform_{uid}\', \'return_commentwall_{uid}\');doane(event);">
							'.($_G['uid'] ? '<span id="message_face" onclick="showFace(this.id, \'comment_message\');return false;" class="cur1"><img src="static/image/common/facelist.gif" alt="facelist" class="mbn vm" /></span>
							<br /><textarea name="message" id="comment_message" class="pt" rows="3" cols="60" onkeydown="ctrlEnter(event, \'commentsubmit_btn\');" style="width: 90%;"></textarea>
							<input type="hidden" name="refer" value="home.php?mod=space&uid={uid}" />
							<input type="hidden" name="id" value="{uid}" />
							<input type="hidden" name="idtype" value="uid" />
							<input type="hidden" name="commentsubmit" value="true" />' :
							'<div class="pt hm">You need to log in before you can shout <a href="member.php?mod=logging&action=login" onclick="showWindow(\'login\', this.href)" class="xi2">login</a> | <a href="member.php?mod='.$_G['setting']['regname'].'" class="xi2">'.$_G['setting']['reglinkname'].'</a></div>').'
							<p class="ptn"><button '.($_G['uid'] ? 'type="submit"' : 'type="button" onclick="showWindow(\'login\', \'member.php?mod=logging&action=login&guestmessage=yes\')"').' name="commentsubmit_btn" value="true" id="commentsubmit_btn" class="pn"><strong>comment</strong></button></p>
							<input type="hidden" name="handlekey" value="commentwall_{uid}" />
							<span id="return_commentwall_{uid}"></span>
							<input type="hidden" name="formhash" value="{FORMHASH}" />
						</form>'.
						($_G['uid'] ? '<script type="text/javascript">
							function succeedhandle_commentwall_{uid}(url, msg, values) {
								wall_add(values[\'cid\']);
							}
						</script>' : '').'
					</div>',
	'wall_li' => '<dl class="bbda cl" id="comment_{cid}_li">
				<dd class="m avt">
				{author_avatar}
				</dd>
				<dt>
				{author}
				<span class="y xw0">{op}</span>
				<span class="xg1 xw0">{date}</span>
				<span class="xgl">{moderated}</span>
				</dt>
				<dd id="comment_{cid}">{message}</dd>
				</dl>',
	'wall_more' => '<dl><dt><span class="y xw0"><a href="home.php?mod=space&uid={uid}&do=wall">view all</a></span><dt></dl>',
	'wall_edit' => '<a href="home.php?mod=spacecp&ac=comment&op=edit&cid={cid}&handlekey=editcommenthk_{cid}" id="c_{cid}_edit" onclick="showWindow(this.id, this.href, \'get\', 0);">edit</a> ',
	'wall_del' => '<a href="home.php?mod=spacecp&ac=comment&op=delete&cid={cid}&handlekey=delcommenthk_{cid}" id="c_{cid}_delete" onclick="showWindow(this.id, this.href, \'get\', 0);">delete </a> ',
	'wall_reply' => '<a href="home.php?mod=spacecp&ac=comment&op=reply&cid={cid}&handlekey=replycommenthk_{cid}" id="c_{cid}_reply" onclick="showWindow(this.id, this.href, \'get\', 0);">reply</a>',
	'group_li' => '<li><a href="forum.php?mod=group&fid={groupid}" target="_blank"><img src="{icon}" alt="{name}" /></a><p><a href="forum.php?mod=group&fid={groupid}" target="_blank">{name}</a></p></li>',

	'poll_li' => '<div class="c z"><img alt="poll" src="static/image/feed/poll.gif" alt="poll" class="t" /><h4 class="h"><a target="_blank" href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a></h4><div class="mtn xg1">Post on ：{dateline}</div></div>',
	'myapp_li_icon' => '<li><img src="{icon}" onerror="this.onerror=null;this.src=\'http://appicon.manyou.com/icons/{appid}\'" alt="{appname}" class="vm" /> <a href="userapp.php?mod=app&id={appid}">{appname}</a></li>',
	'myapp_li_logo' => '<li><a href="userapp.php?mod=app&id={appid}"><img src="http://appicon.manyou.com/logos/{appid}" alt="{appname}" /><p><a href="userapp.php?mod=app&id={appid}">{appname}</a></p></li>',
	'music_no_content' => 'Music box settings',
	'block_profile_diy' => 'Edit space',
	'block_profile_wall' => 'Check message',
	'block_profile_avatar' => 'Edit Avatar',
	'block_profile_update' => 'Edit profile',
	'block_profile_wall_to_me' => 'write a comment...',
	'block_profile_friend_add' => 'Add friend',
	'block_profile_friend_ignore' => 'Dissolution friend',
	'block_profile_poke' => 'Say hello',
	'block_profile_sendmessage' => 'Send sms',
	'block_doing_reply' => 'Reply',
	'block_doing_no_content' => 'No record',
	'block_doing_no_content_publish' => ' ﹐ <a href ="home.php?mod=space&uid={uid}&do=doing&view=me&from=space">Whats on you mind...?</a>',
	'block_blog_no_content' => 'No diary',
	'block_blog_no_content_publish' => ' ﹐ <a href ="home.php?mod=spacecp&ac=blog">Published diary</a>',
	'block_album_no_content' => 'No albums',
	'block_album_no_content_publish' => ' ﹐ <a href ="home.php?mod=spacecp&ac=upload">Upload pictures</a>',
	'block_feed_no_content' => 'No trended',
	'block_thread_no_content' => 'No topic ',
	'block_thread_no_content_publish' => ' ﹐ <a href ="forum.php?mod=misc&action=nav&special=0&from=home" onclick="showWindow(\'nav\', this.href);return false;">Publish an article</a>',
	'block_friend_no_content' => 'No friend',
	'block_friend_no_content_publish' => ' ﹐ <a href ="home.php?mod=spacecp&ac=search">>People You May Know</a> or <a href ="home.php?mod=spacecp&ac=invite">Invite your friend...</a>',
	'block_visitor_no_content' => 'No net visiter',
	'block_visitor_no_content_publish' => ' ﹐ <a href ="home.php?mod=space&do=friend&view=online&type=member">make the rounds</a>',
	'block_share_no_content' => 'No partake',
	'block_wall_no_content' => 'No message ',
	'block_group_no_content' => 'No social circle',
	'block_group_no_content_publish' => ' ﹐ <a href ="forum.php?mod=group&action=create">Establish social circle</a> or <a href ="group.php?mod=index">Add social circle</a>',
	'block_group_no_content_join' => ' ﹐ <a href ="group.php?mod=index">Join social circle</a>',
	'block_myapp_no_content' => 'No app',
	'block_myapp_no_content_publish' => ' ﹐ <a href ="userapp.php?mod=manage&my_suffix=/app/list">To use app</a>',
	'block_view_noperm' => 'To outstrip of authority',
	'block_view_profileinfo_noperm' => 'There is currently no data to display or Entitled to see',
	'click_play' => 'Player',
	'click_view' => 'See',
	'feed_view_only' => 'To search specifically trend',

	'export_pm' => 'Download message',
	'pm_export_header' => 'Discuz! X message record( Does not support leading-in again)',
	'pm_export_touser' => 'Short message to: {touser}',
	'pm_export_subject' => 'We are talking about. . . {subject}',
	'all' => 'All',
	'manage_post' => 'Managerial post ',
	'manage_album' => 'Managerial album',
	'manage_blog' => 'Managerialdiary',
	'manage_comment' => 'Managerial comment',
	'manage_doing' => 'Managerial feelings',
	'manage_feed' => 'Managerial trend',
	'manage_group_prune' => 'Social circle theme',
	'manage_group_threads' => 'Social circle topic',
	'manage_share' => 'Managerial share',
	'manage_pic' => 'Managerial ticture',

	'sb_blog' => 'blog of {who}',
	'sb_album' => 'album of {who}',
	'sb_space' => ' space of {who}',
	'sb_feed' => ' feed of {who}',
	'sb_doing' => ' doing of {who}',
	'sb_sharing' => 'sharing { of who}',
	'sb_friend' => 'friend of {who}',
	'sb_wall' => ' wall of {who}',
	'sb_profile' => 'profile of {who}',
	'sb_thread' => 'threads of {who}',
	'doing_you_can' => 'You can update the record﹐ let friends know what you are doing...',
	'block_profile_all' => '<p style="text-align: right;"><a href="home.php?mod=space&uid={uid}&do=profile">View all personal profile</a></p>',
	'block_profile_edit' => '<span class="y xw0"><a href="home.php?mod=spacecp&ac=profile">edit profile</a></span>',

	'viewthread_userinfo_hour' => ' hour ',
	'viewthread_userinfo_uid' => 'UID',
	'viewthread_userinfo_posts' => 'posts',
	'viewthread_userinfo_threads' => 'threads',
	'viewthread_userinfo_doings' => 'doings',
	'viewthread_userinfo_blogs' => 'blogs',
	'viewthread_userinfo_albums' => 'albums',
	'viewthread_userinfo_sharings' => 'sharings',
	'viewthread_userinfo_friends' => 'friends',
	'viewthread_userinfo_digest' => 'digest',
	'viewthread_userinfo_credits' => 'credits',
	'viewthread_userinfo_readperm' => 'readperm',
	'viewthread_userinfo_regtime' => 'regtime',
	'viewthread_userinfo_lastdate' => 'last login',
	'viewthread_userinfo_oltime' => 'online',

);

?>