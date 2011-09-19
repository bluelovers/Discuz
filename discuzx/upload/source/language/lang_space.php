<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: lang_space.php 20882 2011-03-07 07:22:17Z lifangming $
 *
 *	Translated by discuzindo.net
 
 */

$lang = array(
	'hour' => 'Hour',
	'before' => 'ago',
	'minute' => 'minutes',
	'second' => 'seconds',
	'now' => 'now',
	'dot' => '.',
	'poll' => 'Polls',
	'blog' => 'Blogs',
	'friend_group_default' => 'Others',
	'friend_group_1' => 'From Site',
	'friend_group_2' => 'Know by Common Activity',
	'friend_group_3' => 'Mutual Friend',
	'friend_group_4' => 'Family',
	'friend_group_5' => 'Colleagues',
	'friend_group_6' => 'Classmates',
	'friend_group_7' => 'Not Known',
	'friend_group' => 'Custom',
	'wall' => 'Wall',
	'pic_comment' => 'Image Comment',
	'blog_comment' => 'Blog Comment',
	'clickblog' => 'Blog Positioning',
	'clickpic' => 'Image Positioning',
	'clickthread' => 'Thread Positioning',
	'share_comment' => 'Share Comment',
	'share_notice' => 'Shares',
	'doing_comment' => 'Twit Comment',
	'friend_notice' => 'Friends',
	'poll_comment' => 'Poll Comment',
	'poll_invite' => 'Poll Invite',
	'default_albumname' => 'Default Album',
	'credit' => 'Money',
	'credit_unit' => 'Points',
	'man' => 'M',
	'woman' => 'F',
	'gender_0' => 'Secret',
	'gender_1' => 'Male',
	'gender_2' => 'Female',
	'year' => 'Year',
	'month' => 'Mont',
	'day' => 'Day',
	'unmarried' => 'Unmarried',
	'married' => 'Married',
	'hidden_username' => 'Anonymous',
	'gender' => 'Gender',
	'age' => 'Age',
	'comment' => 'Comment',
	'reply' => 'Reply',
	'from' => 'From',
	'anonymity' => 'Anonymity',
	'viewmore' => 'View More',
	'constellation_1' => 'Aquarius',
	'constellation_2' => 'Picses',
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
	'zodiac_1' => 'Rat',
	'zodiac_2' => 'Cow',
	'zodiac_3' => 'Tiger',
	'zodiac_4' => 'Rabbit',
	'zodiac_5' => 'Dragon',
	'zodiac_6' => 'Snake',
	'zodiac_7' => 'Horse',
	'zodiac_8' => 'Sheep',
	'zodiac_9' => 'Monkey',
	'zodiac_10' => 'Chicken',
	'zodiac_11' => 'Dog',
	'zodiac_12' => 'Pig',

	'credits' => 'Points',
	'usergroup' => 'User Group',
	'friends' => 'Friends',
	'blogs' => 'Blogs',
	'threads' => 'Threads',
	'albums' => 'Albums',
	'sharings' => 'Shares',
	'space_views' => 'Visitors: <strong class="xi1">{views}</strong>',
	'views' => 'Views',
	'block1' => 'Custom Block 1',
	'block2' => 'Custom Block 2',
	'block3' => 'Custom Block 3',
	'block4' => 'Custom Block 4',
	'block5' => 'Custom Block 5',
	'blockdata' => array('personalinfo' => 'Personal Info', 'profile' => 'Profile', 'doing' => 'Doings', 'feed' => 'Feeds',
				'blog' => 'Blogs', 'stickblog' => 'Top Blog', 'album' => 'Albums', 'friend' => 'Friend',
				'visitor' => 'Visitors', 'wall' => 'Wall', 'share' => 'Shares',
				'thread' => 'Threads', 'group'=>$_G[setting][navs][3][navname],'music'=>'Music',
				'statistic' => 'Statistics','myapp' => 'My App',
				'block1'=>' Free Block 1', 'block2'=>'Free Block 2', 'block3'=>'Free Block 3',
				'block4'=>'Free Block 4','block5'=>'Free Block 5'),

	'block_title' => '<div class="blocktitle title"><span>{bname}</span>{more}</div>',
	'blog_li' => '<dl class="bbda cl"><dt><a href="home.php?mod=space&uid={uid}&do=blog&id={blogid}" target="_blank">{subject}</a><span class="xg2 xw0"> {date}</span></dt>',
	'blog_li_img' => '<dd class="atc"><a href="home.php?mod=space&uid={uid}&do=blog&id={blogid}" target="_blank"><img src="{src}" class="summaryimg" /></a></dd>',
	'blog_li_ext' => '<dd class="xg1"><a href="home.php?mod=space&uid={uid}&do=blog&id={blogid}" target="_blank">({viewnum})Views</a><span class="pipe">|</span><a href="home.php?mod=space&uid={uid}&do=blog&id={blogid}#comment" target="_blank">({replynum}) Comments</a></dd>',
	'album_li' => '<li style="width:70px"><div class="c"><a href="home.php?mod=space&uid={uid}&do=album&id={albumid}" target="_blank" title="{albumname}, Update {date}"><img src="{src}" alt="{albumname}" width="70" height="70" /></a></div><p><a href="home.php?mod=space&uid={uid}&do=album&id={albumid}" target="_blank" title="{albumname}, Update {date}">{albumname}</a></p><span>图片数: {picnum}</span></li>',
	'doing_li' => '<li>{message}</li><br />{date} {from} Reply ({replynum})',
	'visitor_anonymity' => '<div class="avatar48"><img src="image/magic/hidden.gif" alt="Anonymous"></div><p>Anonymous</p>',
	'visitor_list' => '<a href="home.php?mod=space&uid={uid}" target="_blank" class="avt"><em class="{class}"></em>{avatar}</a><p><a href="home.php?mod=space&uid={uid}" title="{username}">{username}</a></p>',
	'wall_form' => '<div class="space_wall_post">
						<form action="home.php?mod=spacecp&ac=comment" id="quickcommentform_{uid}" name="quickcommentform_{uid}" method="post" autocomplete="off" onsubmit="ajaxpost(\'quickcommentform_{uid}\', \'return_commentwall_{uid}\');doane(event);">
							'.($_G['uid'] ? '<span id="message_face" onclick="showFace(this.id, \'comment_message\');return false;" class="cur1"><img src="static/image/common/facelist.gif" alt="facelist" class="mbn vm" /></span>
							<br /><textarea name="message" id="comment_message" class="pt" rows="3" cols="60" onkeydown="ctrlEnter(event, \'commentsubmit_btn\');" style="width: 90%;"></textarea>
							<input type="hidden" name="refer" value="home.php?mod=space&uid={uid}" />
							<input type="hidden" name="id" value="{uid}" />
							<input type="hidden" name="idtype" value="uid" />
							<input type="hidden" name="commentsubmit" value="true" />' :
							'<div class="pt hm">You need to log in before you can <a href="member.php?mod=logging&action=login" onclick="showWindow(\'login\', this.href)" class="xi2">登录</a> | <a href="member.php?mod='.$_G['setting']['regname'].'" class="xi2">'.$_G['setting']['reglinkname'].'</a></div>').'
							<p class="ptn"><button '.($_G['uid'] ? 'type="submit"' : 'type="button" onclick="showWindow(\'login\', \'member.php?mod=logging&action=login&guestmessage=yes\')"').' name="commentsubmit_btn" value="true" id="commentsubmit_btn" class="pn"><strong>Message</strong></button></p>
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
	'wall_more' => '<dl><dt><span class="y xw0"><a href="home.php?mod=space&uid={uid}&do=wall">View All</a></span><dt></dl>',
	'wall_edit' => '<a href="home.php?mod=spacecp&ac=comment&op=edit&cid={cid}&handlekey=editcommenthk_{cid}" id="c_{cid}_edit" onclick="showWindow(this.id, this.href, \'get\', 0);">Edit</a> ',
	'wall_del' => '<a href="home.php?mod=spacecp&ac=comment&op=delete&cid={cid}&handlekey=delcommenthk_{cid}" id="c_{cid}_delete" onclick="showWindow(this.id, this.href, \'get\', 0);">Delete</a> ',
	'wall_reply' => '<a href="home.php?mod=spacecp&ac=comment&op=reply&cid={cid}&handlekey=replycommenthk_{cid}" id="c_{cid}_reply" onclick="showWindow(this.id, this.href, \'get\', 0);">Reply</a>',
	'group_li' => '<li><a href="forum.php?mod=group&fid={groupid}" target="_blank"><img src="{icon}" alt="{name}" /></a><p><a href="forum.php?mod=group&fid={groupid}" target="_blank">{name}</a></p></li>',
	'poll_li' => '<div class="c z"><img alt="poll" src="static/image/feed/poll.gif" alt="poll" class="t" /><h4 class="h"><a target="_blank" href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a></h4><div class="mtn xg1">Posted: {dateline}</div></div>',
	'myapp_li_icon' => '<li><img src="{icon}" onerror="this.onerror=null;this.src=\'http://appicon.manyou.com/icons/{appid}\'" alt="{appname}" class="vm" /> <a href="userapp.php?mod=app&id={appid}">{appname}</a></li>',
	'myapp_li_logo' => '<li><a href="userapp.php?mod=app&id={appid}"><img src="http://appicon.manyou.com/logos/{appid}" alt="{appname}" /><p><a href="userapp.php?mod=app&id={appid}">{appname}</a></p></li>',
	'music_no_content' => 'Music Box has No Content set',
	'block_profile_diy' => 'Dress',
	'block_profile_wall' => 'View Wall',
	'block_profile_avatar' => 'Edit Avatar',
	'block_profile_update' => 'Profile',
	'block_profile_wall_to_me' => 'Wall to Me',
	'block_profile_friend_add' => 'Add Friend',
	'block_profile_friend_ignore' => 'Remove',
	'block_profile_poke' => 'Poke',
	'block_profile_sendmessage' => 'P.M',
	'block_doing_reply' => 'Reply',
	'block_doing_no_content' => 'No Doings Found',
	'block_doing_no_content_publish' => ' <a href ="home.php?mod=space&uid={uid}&do=doing&view=me&from=space">Update Status</a>',
	'block_blog_no_content' => 'There are no Blogs',
	'block_blog_no_content_publish' => ' <a href ="home.php?mod=spacecp&ac=blog">Publish Blog</a>',
	'block_album_no_content' => 'There are no albums',
	'block_album_no_content_publish' => ' <a href ="home.php?mod=spacecp&ac=upload">Upload Albums</a>',
	'block_feed_no_content' => 'There are no feed',
	'block_thread_no_content' => 'There are no threads',
	'block_thread_no_content_publish' => ' <a href ="forum.php?mod=misc&action=nav&special=0&from=home" onclick="showWindow(\'nav\', this.href);return false;">Post Threads</a>',
	'block_friend_no_content' => 'There are no friends',
	'block_friend_no_content_publish' => ' <a href ="home.php?mod=spacecp&ac=search">Find Friends</a> Or <a href ="home.php?mod=spacecp&ac=invite">Invite Friends</a>',
	'block_visitor_no_content' => 'There are no visitor',
	'block_visitor_no_content_publish' => ' <a href ="home.php?mod=space&do=friend&view=online&type=member">Check Online Members</a>',
	'block_share_no_content' => 'Not yet shared',
	'block_wall_no_content' => 'No comments yet',
	'block_group_no_content' => 'There are no group',
	'block_group_no_content_publish' => ' <a href ="forum.php?mod=group&action=create">Create Group</a> Or <a href ="group.php?mod=index">Join Group</a>',
	'block_group_no_content_join' => ' <a href ="group.php?mod=index">Join Group</a>',
	'block_myapp_no_content' => 'Have not yet applied',
	'block_myapp_no_content_publish' => ' <a href ="userapp.php?mod=manage&my_suffix=/app/list">I want to play applications</a>',
	'block_view_noperm' => 'Entitled to See',
	'block_view_profileinfo_noperm' => 'No data',
	'click_play' => 'Click to Play',
	'click_view' => 'Click to View',
	'feed_view_only' => 'Only such a dynamic',

	'export_pm' => 'Export P.M',
	'pm_export_header' => 'Discuz! X P.M record (record does not support this message to re-import)',
	'pm_export_touser' => 'Message Object: {touser}',
	'pm_export_subject' => 'Message Topic: {subject}',
	'all' => 'All',
	'manage_post' => 'Posts',
	'manage_album' => 'Albums',
	'manage_blog' => 'Blogs',
	'manage_comment' => 'Comments',
	'manage_doing' => 'Doings',
	'manage_feed' => 'Feeds',
	'manage_group_prune' => 'Group Posts',
	'manage_group_threads' => 'Group Threads',
	'manage_share' => 'Shares',
	'manage_pic' => 'Images',

	'sb_blog' => '{who} Blogs',
	'sb_album' => '{who} Albums',
	'sb_space' => '{who} Space',
	'sb_feed' => '{who} Feeds',
	'sb_doing' => '{who} Doings',
	'sb_sharing' => '{who} Shares',
	'sb_friend' => '{who} Friends',
	'sb_wall' => '{who} Wall',
	'sb_profile' => '{who} Profile',
	'sb_thread' => '{who} Threads',
	'doing_you_can' => 'What\'s on your mind?',
	'block_profile_all' => '<p style="text-align: right;"><a href="home.php?mod=space&uid={uid}&do=profile">View Personal Data</a></p>',
	'block_profile_edit' => '<span class="y xw0"><a href="home.php?mod=spacecp&ac=profile">Edit My Profile</a></span>',

	'viewthread_userinfo_hour' => 'Hour',
	'viewthread_userinfo_uid' => 'UID',
	'viewthread_userinfo_posts' => 'Posts',
	'viewthread_userinfo_threads' => 'Threads',
	'viewthread_userinfo_doings' => 'Doings',
	'viewthread_userinfo_blogs' => 'Blogs',
	'viewthread_userinfo_albums' => 'Albums',
	'viewthread_userinfo_sharings' => 'Shares',
	'viewthread_userinfo_friends' => 'Friends',
	'viewthread_userinfo_digest' => 'Digest',
	'viewthread_userinfo_credits' => 'Points',
	'viewthread_userinfo_readperm' => 'R.P',
	'viewthread_userinfo_regtime' => 'Reg Time',
	'viewthread_userinfo_lastdate' => 'Last Time',
	'viewthread_userinfo_oltime' => 'Online Time',

);

?>