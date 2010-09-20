<?exit?>

<!--/**
 *      [品牌空間] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: comment_node.html.php 4324 2010-09-04 07:08:16Z fanshengshuai $
 */-->

<div id="comment$comment[cid]">
<dl id="commentdl$comment[cid]" {if $key%2 == 1} class="double"{/if}>
	<dt><!--{if $comment['score']}--> <!--{eval $comment['score'] = round($comment['score'])}-->
	<span class="comment_score"><label>{$lang['comment_total_fee']}{$lang['colon']}</label><img
		src="static/image/{$comment['score']}.gif"></span> <!--{/if}--> <!--{if $comment['isprivate']}-->
	<span class="private"><img src="static/image/lock_red.gif">$lang['private']</span>
	<!--{/if}--> <span class="option"><!--{if $shop[uid]==$_G['uid'] || ckfounder($_G['uid'])}--><a
		href="#" onclick="deletecomm('{$comment['cid']}');return false;">$lang['delete']</a><!--{/if}--><!--{if $allowreply}--><!--{if ($comment['recomment'] == "") && ($shop[uid]==$_G['uid'])}--><a
		onclick="addupcid({$comment['cid']});" href="javascript:;">$lang['ownerreply']</a><!--{/if}--><!--{/if}--></span>
	<span class="author"> <!--{if $_G['uid']}--> <a
		id="comauth_{$comment['cid']}"
		onclick="pm_send('{$comment['authorid']}');" href="javascript:;">{$comment['author']}</a>
	<!--{else}--> {$comment['author']} <!--{/if}--> </span> <span
		class="time">[<!--{eval echo date("Y-m-d H:i", $comment['dateline']);}-->]</span>
	</dt>
	<dt></dt>
	<dd><!--{if $_SGLOBAL['commentmodel']['scorename'] && $comment['score']}-->
	<!--{loop $_SGLOBAL['commentmodel']['scorename'] $key $scorename}--> <!--{if $comment['score'.$key]}-->
	<span class="">$scorename:<font color="red">{$comment['score'.$key]}</font>
	{$lang['fee']}</span> <!--{/if}--> <!--{/loop}--> <!--{/if}--></dd>
	<dd><span class="bord">{$lang['consultmessage']}:</span>
	{$comment['message']}</dd>
	<!--{if $comment['recomment']}-->
	<!--{eval include template('templates/store/default/comment_recomment.html.php', 1);}-->
	<!--{/if}-->
</dl>
</div>
