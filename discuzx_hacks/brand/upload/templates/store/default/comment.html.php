<?exit?>

<!--/**
*      [品牌空間] (C)2001-2010 Comsenz Inc.
*      This is NOT a freeware, use is subject to license terms
*
*      $Id: comment.html.php 4379 2010-09-09 03:00:50Z fanshengshuai $
*/-->

<!-- comment -->
<script type="text/javascript" charset="$_G['charset']">
	function deletecomm(cid) {
		if (confirm("{$lang['comment_confirm']}")){
			$("#delitemid")[0].value = cid;
			$("#delMegForm").submit();
		}
		return false;
	}
</script>
<div class="message">
	<!--{if $shop['uid']==$_G['uid'] || ckfounder($_G['uid'])}-->
	<form id="delMegForm" action="#action/viewcomment/itemid/$shop[itemid]/php/1/do/del#" method="post">
		<script charset="utf-8" language="javascript" type="text/javascript" src="batch.formhash.php?rand={eval echo rand(1, 999999)}"/></script>
		<input type="hidden" value="<!--{if !empty($_GET['xid'])}-->{$_GET['xid']}<!--{else}-->{$_GET['id']}<!--{/if}-->" name="typeid" />
		<input type="hidden" value="submit" name="submitdelcomm" />
		<input type="hidden" id="delitemid" name="delitemid" value="" />
		<input type="hidden" id="delupcid" name="upcid" value="" size="5" />
		<input type="hidden" id="deltype" name="type" value="{$type}" size="5" />
		<input type="hidden" value="1" name="ismodle" id="ismodle">
		<input type="hidden" value="{$stuffurl}" name="stuffurl">
	</form>
	<!--{/if}-->
	<h3>{$lang['consult']}
		<!--{if $_G['uid']}-->
		<span onclick="pm_send('{$shop[uid]}');" style="color:#999;font-size:12px;cursor:pointer;">&nbsp;&nbsp;$lang['sendpmtoowner']</span>
		<!--{/if}-->
	</h3>
	<!--{if $commentlist}-->
	<!--{loop $commentlist $key $comment}-->
	<!--{eval include template('templates/store/default/comment_node.html.php', 1);}-->
	<!--{/loop}-->
	<!--{/if}-->
	<div class="c h10"></div>

	<div id="postlistreply">
	</div>
	$multipage
	<!--{if $allowreply}-->
	<!--comment form-->
	<script type="text/javascript" charset="$_G['charset']">
		var hdrewardid = "commentscorestr";
		function setreward(rewardid,value) {
			var hdreward = document.getElementById(hdrewardid);
			if(hdreward.value.indexOf("@" + rewardid +"@") > -1 ) {
				var reg = new RegExp("@" + rewardid +"@\\d");
				hdreward.value = hdreward.value.replace(reg,"@" + rewardid +"@" + value);
			} else {
				hdreward.value += "@" + rewardid +"@" + value;
			}
			return false;
		}
// onmouseover change the style
function star_hover(rewardid,cur){
	for(var i = 1;i<6;i++) {
		var oldclick = document.getElementById("reward"+i+"_"+rewardid);
		if(i < (cur+1)) {
			oldclick.src = "static/image/star_yellow.gif";
		}else{
			oldclick.src = "static/image/star_grey.gif";
		}
	}
}
// onmouseout restore the style
function star_restore(rewardid){
	var hdreward = document.getElementById(hdrewardid).value;
	var reg = new RegExp("@" + rewardid +"@(\\d)");
	oldscore = reg.exec(hdreward);
	if(oldscore == null || oldscore[1]==null){
		star_hover(rewardid,0);
	}else{
		star_hover(rewardid,parseInt(oldscore[1]));
	}

}
</script>
<form id="msgForm" action="#action/viewcomment/itemid/$shop[itemid]/php/1#" method="post">
	<script charset="utf-8" language="javascript" type="text/javascript" src="batch.formhash.php?rand={eval echo rand(1, 999999)}"/></script>
	<div class="writemessage">
		<h5 id="reply" style="display:none;">{$lang['reply']}{$lang['colon']}<span id="replyname"></span></h5>
		<h5>{$lang['ownercomment']}{$lang['colon']}<span id="span_commentmessage">$lang['wordlimited']</span></h5>
		<label>
			<textarea cols="10" rows="5" id="commentmessage" name="commentmessage" {if $_G['setting']['seccode']} onfocus="addcomseccode();"{/if}></textarea>
		</label>

		<!--{if $_G['setting']['commentmodel'] && $action == 'index' && $_SGLOBAL['commentmodel']['scorename'] && $shop['uid'] != $_G['uid']}-->
		<div class="c h10"></div>
			<h5>{$lang['ownerscore']}{$lang['colon']}<span id="span_score">$lang['scorelimited']</span> <img id="ico_opt" src="static/image/ico_add.png" onclick="show_comment_score_area();" title="{$lang['folder_open']}" /></h5>
		
			<input name="commentscorestr" id="commentscorestr" type="hidden" />
			<div id="comment_score_area">
				<ul>
					<!--{eval $j = 0}-->
					<!--{loop $_SGLOBAL['commentmodel']['scorename'] $key $scorename}-->
					<!--{eval $j++}-->
					<!--{if ($j > 1 && $j%4 == 1)}-->
					<!--{eval echo '</ul><ul>';}-->
					<!--{/if}-->
					<li>
					<label>$scorename: </label>
					<p><img id="reward1_1$key" onmouseover="star_hover(1$key,1);" onmouseout="star_restore(1$key);" onclick="return setreward(1$key,1);" src="static/image/star_grey.gif"><img id="reward2_1$key" onmouseover="star_hover(1$key,2);" onmouseout="star_restore(1$key);" onclick="return setreward(1$key,2);" src="static/image/star_grey.gif"><img id="reward3_1$key" onmouseover="star_hover(1$key,3);" onmouseout="star_restore(1$key);" onclick="return setreward(1$key,3);" src="static/image/star_grey.gif"><img id="reward4_1$key" onmouseover="star_hover(1$key,4);" onmouseout="star_restore(1$key);" onclick="return setreward(1$key,4);" src="static/image/star_grey.gif"><img id="reward5_1$key" onmouseover="star_hover(1$key,5);" onmouseout="star_restore(1$key);" onclick="return setreward(1$key,5);" src="static/image/star_grey.gif"> </p>
					</li>
					<!--{/loop}-->
				</ul>
				<div class="c"></div>
			</div>
		
		<!--{/if}-->
		
		<!--{if $_G['setting']['seccode']}-->
		<div id="seccode_area">
			<div id="com_authcode_img">
				<div class="fl" style="width:100px;"><a title="$lang['captcha_tips']" href="javascript:updatecomseccode(25);"><img id="img_comseccode" border="0" src="seccode.php?h=25" /></a></div>
				<div class="chang_seccode"><a title="$lang['captcha_tips']" href="javascript:updatecomseccode(25);">$lang['chagepic']</a></div>
			</div>
			$lang['captcha']&nbsp;
			<input id="comseccode" name="seccode" autocomplete="off" onfocus="showcomseccode(25);" />
		</div>
		<!--{/if}-->
		<div id="ajax_status_display"></div>
		<div class="action">
			<span id="span_score"></span>
			<input id="submitMsgForm" name="searchbtn" value="{$lang['consult']}" type="submit" onclick="return comsubmitcheck();">
			<!--{if $shop['uid'] != $_G['uid']}-->
			<input id="isprivate" name="isprivate" class="checkbox" value="1" type="checkbox" />$lang['privatecomment']
			<!--{/if}-->
			<input type="hidden" value="submit" name="submitcomm" />
			<input type="hidden" id="itemid" name="itemid" value="{$comment_itemid}" />
			<input type="hidden" id="upcid" name="upcid" value="" size="5" />
			<input type="hidden" id="type" name="type" value="{$type}" size="5" />
			<input type="hidden" value="1" name="ismodle" id="ismodle">
			<input type="hidden" value="{$stuffurl}" name="stuffurl">
			<input type="hidden" value="{$page}" name="page">
		</div>
	</div>
</form>
<!--{/if}-->
</div>
<script type="text/javascript" charset="{$_G['charset']}">
	bindform('msgForm');
</script>