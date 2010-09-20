<?exit?>

<div class="clearall navgation navgation_pk">
	<div class="layout nav_header" id="headernav">
		<!--{if !$_G['uid']}-->
		<div class="login_panel">
			<form action="batch.login.php?action=login" method="post" name="user_login_form" id="user_login_form">
				<table  border="0" cellspacing="0" cellpadding="0" class="loginform" >
					<tr>
						<td width="48">{$lang['username']}{$lang['colon']}</td>
						<td width="95" align="left"><input tabindex="1" style="width:85px;" type="text" {if $_G['setting']['seccode']} onfocus="addseccode();"{/if} tabindex="1" name="username"></td>
						<td width="48">{$lang['password']}{$lang['colon']}</td>
						<td width="95"><input tabindex="2"  style="width:85px;" type="password" {if $_G['setting']['seccode']} onfocus="addseccode();"{/if} tabindex="2" name="password"></td>
						<!--{if $_G['setting']['seccode']}-->
						<td width="48">{$lang['seccode']}{$lang['colon']}</td>
						<td width="95">
							<div id="login_authcode_img">
								<a title="$lang['captcha_tips']" href="javascript:updateseccode();"><img id="img_seccode" alt="{$lang['seccode_alter']}" src="seccode.php"></a>
							</div>
							<input tabindex="3"  type="text" autocomplete="off" onfocus="showseccode();" tabindex="2" name="seccode" id="seccode" />
						</td>
						<!--{/if}-->
						<td width="70"><label class="iblock inp_sm"><input tabindex="4"  type="submit" name="loginsubmit" value="{$lang['login']}" onclick="return submitcheck();"></label></td>
						<td>
						<input type="hidden" name="formhash" value="<!--{eval echo formhash();}-->" />
						<input type="hidden" value="2592000" name="cookietime" />
						<a rel="nofollow" target="_blank" href="{$_G['setting']['regurl']}">{$lang['register']}&nbsp;</a>
						</td>
					</tr>
				</table>
			</form>
		</div>
		<!--{/if}-->
		<div class="clearall nav_common">
			<!--{if $_G['uid']}-->
			<div id="navuinfo" class="nav_uinfo">
				<div class="nav_uinfo" id="navuinfo">
					<dl>
						<dd>
						<ul>
							<li>
							<a rel="nofollow" href="batch.login.php?action=logout">[{$lang['logout']}]</a>
							</li>
							<!--{if exists_discuz()}-->
							<li id="nav_message_handle">
							<div id="show_navmsg" class="iblock nav_message" style="position:absolute; display:none;">
								<div class="nav_msglist">
									<ul class="iblock">
										<!--{if $_G['myshopid'] > 0}-->
										<li><a onclick="pm_view('systempm');" href="javascript:;">{$lang['pm_shop_notice']}({$pm_new['systempm']})</a></li>
										<!--{else}-->
										<li><a href="pm.php?filter=systempm" target="_blank">{$lang['pm_system']}({$pm_new['systempm']})</a></li>
										<!--{/if}-->
										<li><a href="pm.php?filter=privatepm" target="_blank">{$lang['pm_personal']}({$pm_new['newprivatepm']})</a></li>
										<li><a href="pm.php?filter=announcepm" target="_blank">{$lang['pm_announce']}({$pm_new['announcepm']})</a></li>
									</ul>
								</div>
							</div>
							<a href="pm.php?filter=privatepm" target="_blank">{$lang['pm_new']}{if $pm_new['newpm']}({$pm_new['newpm']}){/if}</a>
							</li>
							<!--{/if}-->
							<!--{if !ckfounder($_G['uid']) && $_G['myshopid']}-->
							<li>
							<a target="_blank" href="panel.php">[{$lang['shopcp']}]</a>
							</li>
							<li id="nav_myshops_handle">
							<div id="show_myshops" class="iblock nav_message" style="position:absolute; display:none;">
								<div class="nav_myshopslist">
									<ul class="iblock">
									<!--{loop $_G['myshopsarr'] $myshop}-->
										<li><a href="store.php?id={$myshop['itemid']}" title="{$myshop['subject']}" target="_blank">{$myshop['briefsubject']}</a></li>
									<!--{/loop}-->
									</ul>
								</div>
							</div>
							<a target="_blank" href="store.php?id={$_G['myshopid']}">[{$lang['myshop']}]</a>
							</li>
							<!--{elseif !ckfounder($_G['uid']) && $_G['member']['allowadmincp']}-->
							<li><a target="_blank" href="admin.php">[{$lang['admincp']}]</a></li>
							<!--{/if}-->
							<!--{if ckfounder($_G['uid'])}-->
							<li><a target="_blank" href="admin.php">[{$lang['admincp']}]</a></li>
							<!--{/if}-->
							<li>
								<strong>{$_G['username']} {$lang['hello']}</strong>
							</li>
						</ul>
						</dd>
						<div class="nav_uinfo_dt">
							<img height="22" width="22" alt="avatar" src="{UC_API}/avatar.php?uid=$_G[uid]&rand=$_G[timestamp]&size=small" />
						</div>
						<!--{if ($_G['myshopstatus'] == 'verified') && !pkperm('isadmin') && !$_G['member']['taskstatus']}-->
						<span class="dianpu-guanli">{$lang['shopopenmsg']}</span>
						<!--{/if}-->
						</dt>
						</dl>
					</div>
				</div>
				<!--{/if}-->
				<!--login form end-->
				<h2><a href="index.php" title="">$lang['brand']</a></h2>
				<!--{if false}-->
				<div class="top_ad_area">Top Ad Area</div>
				<!--{/if}-->
			</div>
		</div>
		<div class="clearall nav_item" id="itemnav">
			<div class="layout">
				<ul class="nav_item_list">
					<!--{loop $_G['setting']['site_nav'] $v}-->
					<li {$active[$v['flag']]}><a href="$v['url']" title="$v['name']" $v['ext']>$v['name']</a></li>
					<!--{/loop}-->
					<!--Custom NAV-->
				</ul>
				<div class="search_box">
					<form id="form_search" target="_blank" method="post" name="" action="street.php?range=all">
						<div class="search_key">
							<input id="uid" value="0" type="hidden">
							<label>
								<input maxlength="50" class="input_sb1" name="keyword" id="search_keywords" value="{$lang['inputkeyword']}" onfocus="this.value=''" onmouseover="this.className='input_sb2'" onmouseout="this.className='input_sb1'" size="21" onkeypress="javascript:if(event.keyCode==13){search.searchRedirect();}" type="text">
							</label>
						</div>
						<div id="shownavsearch" class="menu">
							<div id="opt">{$lang['sshop']}</div>
							<ul id="searchoption" style="display: none;">
								<li><a href="javascript:;" search_type="shopsearch">{$lang['sshop']}</a></li>
								<li><a href="javascript:;" search_type="consume">{$lang['scoupon']}</a></li>
								<li><a href="javascript:;" search_type="goodssearch">{$lang['sgood']}</a></li>
							</ul>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
	<!--header 导航结束 -->
</div>
<script charset="utf-8" type="text/javascript" src="static/js/header.js"></script>