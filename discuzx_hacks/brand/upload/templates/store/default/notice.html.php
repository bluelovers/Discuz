<?exit?>

<!--/**
*      [品牌空間] (C)2001-2010 Comsenz Inc.
*      This is NOT a freeware, use is subject to license terms
*
*      $Id: notice.html.php 4337 2010-09-06 04:48:05Z fanshengshuai $
*/-->

<!--{if empty($_GET['xid'])}-->
<div id="newslist" class="main layout store_list">
	<div class="content">
		<h3>{$lang['noticelistpage']}</h3>
		<ul class="listcontent">
			<!--{loop $noticelist $notice}-->
			<!--{eval $notice['styletitle'] = pktitlestyle($notice['styletitle']);}-->
			<li>
			<a {if $notice['styletitle']} style="{$notice['styletitle']}"{/if} target="_blank" href="store.php?id={$shop['itemid']}&action=notice&xid={$notice['itemid']}">{$notice['subject']}</a>
			<span>{$notice['time']}</span>
			<!--{loop $notice['attr'] $attr}-->
			<span>$attr['attr_name']:$attr['attr_value']</span>
			<!--{/loop}-->
			</li>
			<!--{/loop}-->
		</ul>
		$noticelist_multipage
	</div>
	<!--{eval include template('templates/store/default/sidebar.html.php', 1);}-->
</div>
<!--{else}-->
<div id="news" class="main layout">
	<div style="width:740; float:left;"><div class="content">
			<!--{if $_G['uid'] && !$_G['myshopid'] && !ckfounder($_G['uid'])}-->
			<span style="color: font-size: 12px; margin-left: 680px; margin-top: 5px; cursor: pointer; position: absolute;" onclick="report('notice', '{$notice['itemid']}');">$lang['report']</span>
			<!--{/if}-->
			<h1>{$notice['subject']}</h1>
			<div class="tips">
				{$lang['dateline']}{$lang['colon']} {$notice['time']} <span>{$lang['promulgator']}{$lang['colon']} {$shop['subject']}</span>
				<span>{$lang['viewnum']}{$lang['colon']}{$notice['viewnum']}</span>
			</div>
			<!--{if strpos($notice['subjectimage'], 'nophoto.gif')===false}-->
			<div style="text-align:center"><img onload="if(this.width > 600) this.width=600;" style="border:1px solid #AEAEAE;padding:2px;" src="{$notice['subjectimage']}" /></div>
			<!--{/if}-->
			<!--{if !empty($noticeattr)}-->
			<div>
				<ul>
					<!--{loop $noticeattr $attr}-->
					<li>$attr['attr_name']: $attr['attr_value']</li>
					<!--{/loop}-->
				</ul>
			</div>
			<!--{/if}-->
			<div>{$notice['message']}</div>
		</div>
		<style type="text/css">
			.news_msg .pages {margin:5px auto;width:717px;margin-bottom:20px;height:23px;text-align:right;font-family:Arail}/*perpage*/
			.news_msg .pages a, .news_msg .pages strong, .news_msg .pages span  {display:inline-block;*display:inline;zoom:1;padding:0 6px;height:21px;line-height:21px;border:1px solid #C98E3E;margin-left:3px;}
			.news_msg .pages a:link, .news_msg .pages a:visited {color:#CD9040}
			.news_msg .pages strong, .pages a:hover {text-decoration:none;color:#FFFFFF;background:#C98E3E;}
			.news_msg .pages span {border:1px solid #DDDDDD;color:#999999}
			.news_msg .pages .prevpage, .news_msg .pages .lastpage {width:auto;}
		</style>
		<div class="news_msg">
			<!--{eval include template('templates/store/default/comment.html.php', 1);}-->
	</div></div>
	<!--{eval include template('templates/store/default/sidebar.html.php', 1);}-->

</div>
<script type="text/javascript">
	function showCaptcha(){
		$("#hiddenCaptcha").attr("style","display:block");
		if(!$("img#captcha").attr("src"))
			$("img#captcha").attr("src", "do.php?action=seccode&rand="+new Date().getTime());
	}

function getRemoteCaptcha(){
	$("img#captcha").attr("src", "do.php?action=seccode&rand="+new Date().getTime());
	$('input#inputCaptcha').val('');
}


String.prototype.trim = function(){
	return this.replace(/(^\s*)|(\s*$)/g, "");
}

function showReplyForm(obj){
	$(obj).parents('div').children('form').eq(0).show();
}

function showEditReplyForm(obj){
	var textarea = $(obj).parents('div').children('form').children('textarea');
	var dl = $(obj).parents('div').children(dl).children('dd').next().children('div').html();

	$(textarea).val(dl);
	$(obj).parents('div').children('form').eq(0).show();
	textarea = null;
	dl = null;
}

function hideReplyForm(obj){
	$(obj).parents('form').hide();
}
function submitReplyForm(obj) {
	replayString = $(obj).children('textarea').val().trim();
	if(replayString.length < 2 || replayString.length > 250) {
		$(obj).children('textarea').focus();
		$(obj).children('label.error').show();
		return false;
	} else {
		$(obj).children('label.error').hide();
		$(obj).submit();
	}
}


function deleteMsg(url){
	setTimeout("deleteMsgBackend('"+ url +"')",200);
}
function deleteMsgBackend(url) {
	if(confirm('$lang[comment_confirm]')){
		self.location.href = url;
	}
}
</script>
<!--{/if}-->