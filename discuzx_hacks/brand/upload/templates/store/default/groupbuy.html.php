<?exit?>

<!--/**
 *      [品牌空間] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: groupbuy.html.php 4397 2010-09-10 10:07:01Z fanshengshuai $
 */-->

<!--{if empty($_GET['xid'])}-->
<div id="groupbuyslist" class="main layout store_list">
	<div class="content">
		<h3>{$lang['groupbuylist']}</h3>
		<div id="searchList">
		{eval $step = 0;}
			<!--{loop $groupbuylist $groupbuy}-->
			<!--{eval
				$border = "";
				$step ++ ;
				if(($step % 2) == 1)
					$border = "border-right:#ccc 1px dashed;";
			}-->
			<div class="list_item" style="{$border}">
				<div class="groupbytitle">
					<h5><a target="_blank" href="store.php?id={$shop['itemid']}&action=groupbuy&xid={$groupbuy['itemid']}"><!--{eval echo cutstr($groupbuy['subject'], 36 , '.');}--></a></h5>
					<span>{$lang['groupbuytime']}{$lang['colon']}</span>{$groupbuy['groupbuytime']}
				</div>

				<div class="groupbuy_item_content" style="height:170px;">
					<dl>
						<dt>
						<!--{if $groupbuy['validity_end'] < $_G['timestamp'] || $groupbuy['close'] == 1}-->
							<span class="expire"></span>
						<!--{else}-->
							<span class="ineffect"></span>
						<!--{/if}-->
						<span><a target="_blank" href="store.php?id={$shop['itemid']}&action=groupbuy&xid={$groupbuy['itemid']}"><img width="100" height="80" src="{$groupbuy['thumb']}" alt=""></a></span>
						</dt>
						<dd class="info">
						<ul>
							<li><label class="red b">$lang['groupbuyprice_now']</label><span class="b">{$lang['rmb']}{$groupbuy['groupbuyprice']}</span></li>
							<li><label>$lang['groupbuyprice']</label><span>{$lang['rmb']}{$groupbuy['groupbuypriceo']}</span></li>
							<li><label>$lang['groupbuydiscount']</label><span class="b">{$groupbuy['groupbuydiscount']}{$lang['zhe']}</span></li>
							<li><label>$lang['groupbuysave']</label><span>{$groupbuy['groupbuysave']}</span> $lang['yuan']</li>
							<li><em class="b" style="font-size:16px;">{$groupbuy['buyingnum']}</em> $lang['groupbuyingnum']</li>
							<li><a id="iwantjoin" href="store.php?id={$shop['itemid']}&action=groupbuy&xid={$groupbuy['itemid']}#groupbyjoin">$lang['iwantjoin']</a></li>
						</ul>
						</dd>
					</dl>
				</div>
			</div>
			<!--{/loop}-->
			<div class="c h10"></div>
		</div>
		$groupbuylist_multipage
	</div>
	<!--{eval include template('templates/store/default/sidebar.html.php', 1);}-->
</div>
<!--{elseif ($_GET['do'] == 'groupbuy_attend_detail')}-->
<div class="main layout">
	<div class="groupbuy_member_detail" style="border:#e0ddcc 1px solid; background:#fefee6; ">
		<ul class="groupbuy_attend_list">
			<li>
			<div style="float:left;">$lang['groupbuy_join'] <span style="color:#896943; font-weight:bold;">{$groupbuy['subject']}</span></div>
			<div style="float:right; padding-right:10px;">{$groupbuy['date']}</div>
			</li>
			<li>
			<div style="float:left;width:120px;">$lang['groupbuy_join_days'] <span style="color:#896943; font-weight:bold;">$groupbuy['days']</span></div>
			<div style="float:left;width:120px;">$lang['groupbuy_join_persons'] <span style="color:#896943; font-weight:bold;">{$groupbuy['join_total_num']}</span></div>
			</li>
			<li><strong>$lang['groupbuy_join_table']</strong></li>
		</ul>
		<table id="mem_list">
			<tr>
				<th>$lang['joinusername']</th>
				<th>$lang['realname']</th>
				<th>$lang['mobile']</th>
				<!--{loop $groupbuyattr $attr}-->
				<th>{$attr['fieldtitle']}</li>
					<!--{/loop}-->
					<th style="width:20px;"></th>
				</tr>
				<!--{loop $groupbuy_join_list $list_item}-->
				<!--{if $list_item['status']}-->
				<tr>
					<td>{$list_item['username']}</td>
					<td>{$list_item['realname']}</td>
					<td>{$list_item['mobile']}</td>
					<!--{loop $groupbuyattr $attr}-->
					<td>{$list_item[$attr['fieldname']]}</td>
					<!--{/loop}-->
					<td><div id="mard_del" title="{$lang['groupbuy_keepthisguy']}" onclick="location='?id={$shop['itemid']}&action=groupbuy&do=markdelstatus&xid={$groupbuy['itemid']}&uid={$list_item['uid']}';"></div></td>
				</tr>
				<!--{else}-->
				<tr class="deleted">
					<td>{$list_item['username']}</td>
					<td>{$list_item['realname']}</td>
					<td>{$list_item['mobile']}</td>
					<!--{loop $groupbuyattr $attr}-->
					<td>{$list_item[$attr['fieldname']]}</td>
					<!--{/loop}-->
					<td><div id="mard_normal" title="{$lang['groupbuy_restorekeepstatus']}" onclick="location='?id={$shop['itemid']}&action=groupbuy&do=marknormalstatus&xid={$groupbuy['itemid']}&uid={$list_item['uid']}';"></div></td>
				</tr>
				<!--{/if}-->
				<!--{/loop}-->
			</table>
			<div style="text-align:right; padding:20px 20px;"><a href="?id={$shop['itemid']}&action=groupbuy&do=groupbuy_attend_detail&exportexcel=1&xid={$groupbuy['itemid']};">$lang['groupbuy_join_excel']</a></div>
			<div class="c h10"></div>
		</div>
	</div>
<!--{else}-->
	<div class="main layout">
		<div id="groupbuy" class="content">
			<!--{if $_G['uid'] && !$_G['myshopid'] && !ckfounder($_G['uid'])}-->
			<span style="color: font-size: 12px; margin-left: 700px; margin-top: 5px; cursor: pointer; position: absolute;" onclick="report('groupbuy', '{$groupbuy['itemid']}');">$lang['report']</span>
			<!--{/if}-->
			<h3 class="title">{$groupbuy['subject']}</h3>
			<div class="info">
				<span>
					<img width="300" height="220" class="pic" src="{$groupbuy['thumb']}" srcimg="{$groupbuy['subjectimage']}" title="" onclick="zoom(this, $('#groupbuy').find('.pic').attr('srcimg'), $('#groupbuy').find('h3').html())" style="cursor:pointer;" />
				</span>
				<div style="float:right; width:300px;">
					<ul class="prices">
						<li class="nowprice"><div class="nowpriceDiv">$lang['groupbuypriceo'] {$lang['rmb']}{$groupbuy['groupbuypriceo']}</div></li>
						<li class="oldprice"><span>$lang['groupbuyprice']</span><em>{$lang['rmb']}{$groupbuy['groupbuyprice']}</em></li>
						<li class="zhekou"><span>$lang['groupbuydiscount']</span><em>{$groupbuy['groupbuydiscount']}</em></li>
						<li class="jiesheng"><span>$lang['groupbuysave']</span><em>{$lang['rmb']}{$groupbuy['groupbuysave']}</em></li>
					</ul>
					<ul class="groupbuysinfo">
						<li><label>$lang['groupbuyendtime']</label><span>{$groupbuy['groupbuytime']}</span></li>
						<li><label>$lang['attendnum']</label><span class="red" style="line-height:16px;">{$groupbuy['buyingnum']}</span>
						<!--{if ckfounder($_G['uid']) || $shop['uid'] == $_G['uid']}-->
						<a style="padding-left:10px; line-height:16px;" href="store.php?id={$shop['itemid']}&action=groupbuy&do=groupbuy_attend_detail&xid={$groupbuy['itemid']}" target="_blank">$lang['groupbuy_join_list']</a>
						<!--{/if}-->
						</li>
						<li><label>$lang['groupbuysurplusnum']</label><span>{$groupbuy['surplusnum']}</span></li>
					</ul>
					<!--{if $groupbuy['validity_end'] < $_G['timestamp'] || $groupbuy['grade'] < 3 || $groupbuy['close'] == 1}-->
					<div class="groupbuy_status_end">{$lang['groupbuy_end_join']}</div>
					<!--{else}-->
					<div class="groupbuy_status">{$lang['groupbuy_join_ison']}</div>
					<!--{/if}-->
				</div>
				<div class="c h10"></div>
				<div class="shopinfo">
					<ul>
						<li><label>{$lang['shoptel']}{$lang['colon']}</label><span>{$shop['tel']}</span></li>
						<li><label>{$lang['shopaddr']}{$lang['colon']}</label><span>{$shop['address']}</span></li>
						<li style="color:#aaa;"><label>{$lang['disclaimer']}{$lang['colon']}</label><span>$lang['groupbuy_disclaimer_content']</span></li>
					</ul>
					<div class="c"></div>
				</div>
			</div>
			<div class="c mt10 intro">
				<h4>$lang['details']</h4>
				<div id="groupbuysDescription">
					<div style="padding:5px;color:#000; border:none; border-bottom:#ccc 1px dashed;font-weight:normal; font-size:14px;">{$groupbuy['message']}</div>
					<!--{if !empty($relatedarr)}-->
					<ul style="margin-top:10px;">
						<label>$lang['relatedinfo']</label>
						<!--{loop $relatedarr $related}-->
						<!--{eval $typename = $lang['header_'.$related['type']];}-->
						<li><p>[{$typename}]<a href="store.php?id={$shop['itemid']}&action={$related['type']}&xid={$related['itemid']}" target="_blank" title="{$related['subject']}">{$related['simplesubject']}</a></p></li>
						<!--{/loop}-->
					</ul>
					<!--{/if}-->
				</div>
			</div>
			<div class="c h10"></div>
			
			<div>
			<!--{if !empty($_G['uid']) }-->
				<!--{if $already_joined}-->
					<!--{if $my_join_info['status'] == 1}-->
					<div id="groupbyjoin">
						<h3 class="title">{$lang['groupbuy_my_join_info']}</h3>
						<ul class="form_groupby_attent_info">
							<li><label>{$lang['realname']}</label><span>$my_join_info['realname']</span></li>
							<li><label>{$lang['mobile']}</label><span>$my_join_info['mobile']</span></li>
							<!--{loop $groupbuyattr $attr}-->
							<li><label>{$attr['fieldtitle']}</label><span>$my_join_info[$attr['fieldname']]</span></li>
							<!--{/loop}-->
						</ul>
						</div>
					<!--{else}-->
						<!--{if $groupbuy_is_on}-->
						<div id="groupbyjoin">
							<form id="groupbuyform" method="post" action="store.php?id={$shop['itemid']}&action=groupbuy&xid={$groupbuy['itemid']}">
								<h3 class="title">{$lang['groupbuy_my_join_info']}</h3>
								<ul class="form_groupby_attent">
									<li><label>{$lang['realname']}</label><span><input name="join[realname]" value="$my_join_info['realname']" /></span></li>
									<li><label>{$lang['mobile']}</label><span><input name="join[mobile]" value="$my_join_info['mobile']" /></span></li>
									<!--{loop $groupbuyattr $attr}-->
									<li><label>{$attr['fieldtitle']}</label><span><input name="join[{$attr['fieldname']}]" value="$my_join_info[$attr['fieldname']]" /></span></li>
									<!--{/loop}-->
								</ul>
								<div class="c" style="margin:10px; margin-left:70px;">
									<input type="hidden" value="{eval echo formhash();}" name="formhash">
									<input type="hidden" value="{$groupbuy['itemid']}" name="join[groupbuyid]">
									<input type="submit" class="submitgroupbuyjoin"  name="submitgroupbuyjoin" value="{$lang['groupbuy_join_btn_text']}" />
								</div>
							</form>
							</div>
						<!--{/if}-->
					<!--{/if}-->
				<!--{else}-->
					<!--{if $groupbuy_is_on}-->
					<div id="groupbyjoin">
						<form id="groupbuyform" method="post" action="store.php?id={$shop['itemid']}&action=groupbuy&xid={$groupbuy['itemid']}">
							<h3 class="title">{$lang['iwantjoin']}<span class="my_account">&nbsp;{$lang['account']}{$lang['colon']}{$_G['username']}</span></h3>
							<ul class="form_groupby_attent">
								<li><label>{$lang['realname']}</label><span><input name="join[realname]" value="" /></span></li>
								<li><label>{$lang['mobile']}</label><span><input name="join[mobile]" value="" /></span></li>
								<!--{loop $groupbuyattr $attr}-->
								<li><label>{$attr['fieldtitle']}</label><span><input name="join[{$attr['fieldname']}]" value="" /></span></li>
								<!--{/loop}-->
							</ul>
							<div class="c" style="margin:10px; margin-left:70px;">
								<input type="hidden" value="{eval echo formhash();}" name="formhash">
								<input type="hidden" value="{$groupbuy['itemid']}" name="join[groupbuyid]">
								<input type="submit"  class="submitgroupbuyjoin" name="submitgroupbuyjoin" value="{$lang['groupbuy_join_btn_text']}"  />
							</div>
						</form>
						</div>
					<!--{/if}-->
				<!--{/if}-->
			<!--{else}-->
			<div id="groupbyjoin">
				<h3 class="title">{$lang['iwantjoin']}</h3>
				<div class="form_groupby_not_login">
					$lang['not_login_to_join']
				</div>
				</div>
			<!--{/if}-->
		</div>
		<div class="c h10"></div>
		
		<div class="news_msg">
		<!--{eval include template('templates/store/default/comment.html.php', 1);}-->
		</div>
	</div>
	<!--{eval include template('templates/store/default/sidebar.html.php', 1);}-->
</div>
<div class="main">
	<div class="sidebar">
		<div class="box" id="maptop" style="width:600px;height:430px;position:absolute;display:none;top:400px;left:400px;background-color:#FFFFFF;">
			<h3 href="#" style="position:">{$lang['shopaddr']}{$lang['colon']}</h3>
			<a href="#" onclick="closemap();return false;" style="position:absolute;right:5px;top:5px;">$lang['close']</a>
		</div>
</div></div>
<div id="append_parent"></div>
<!--{/if}-->
<script>
	<!--
	$(function(){
		$("#consumerlist dl").hover(function(){$(this).addClass("thison")},function(){$(this).removeClass("thison")})
	});
	$(document).ready(function() {

		$("#searchList .everylist").hover(function() {
			$(this).css({'background' : 'url(static/image/bg_groupbuyssearch_hover.png) repeat-x scroll 0 bottom'});

			} , function() {
			$(this).css({'background' : 'url(static/image/bg_groupbuyssearch.png) repeat-x scroll 0 bottom'});

		});
	});
	//-->
</script>