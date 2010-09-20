<?exit?>
<!--{template 'templates/site/default/attend_header.html.php', 1}-->
<form id='form_attend' name='form_attend' action="attend.php?do=register" method="post">
	<div class="step1">
		<h4 id="opt_status_display">
			<!--{if !empty($_G['uid'])}-->
				{$lang['attend_apply_tips1']}{$_G['username']}{$lang['attend_apply_tips2']}
			<!--{else}-->
				{$lang['attend_apply_tips3']}
			<!--{/if}-->
		</h4>
		<!--{if empty($_G['uid'])}-->
		<script>
			function close_newuser_area(close) {
				if (close) {
					$('#tr_email').hide();
					$('#tr_pwd_repeat').hide();
				} else {
					$('#tr_email').show();
					$('#tr_pwd_repeat').show();
				}					
			}
		</script>
		<div class="account_select">
			<div class="new_user"><span><input onclick="close_newuser_area(false);" type="radio" name="existuser" checked value="0" /></span><label>{$lang['new_user']}</label></div>
			<div class="already_user"><span><input onclick="close_newuser_area(true);" type="radio" name="existuser" value="1" /></span><label>{$lang['already_user']}</label></div>
		</div>
		<table style="clear:both; width:450px; float:left;">
			<tr>
				<th>$lang[username]</th>
				<td>
					<label>
						<input type="text" name="username" id="username">
						<span id="span_username">*</span>
					</label>
				</td>
			</tr>
			<tr>
				<th>$lang[password]</th>
				<td>
					<label>
						<input type="password" name="password" id="password">
						<span id="span_password">*</span>
					</label>
				</td>
			</tr>
			<tr id="tr_pwd_repeat">
				<th>$lang[password_repeat]</th>
				<td>
					<label>
						<input type="password" value="" name="password_1" id="password_1">
						<span id="span_password_1">*</span>
					</label>
				</td>
			</tr>
			<tr id="tr_email">
				<th>$lang[email]</th>
				<td>
					<label>
						<input type="text" value="" name="email" id="email">
						<span id="span_email">*</span>
					</label>
				</td>
			</tr>
			
			<!--{loop $cacheinfo['columns'] $column}-->
			<!--{if ($column['allowpost'] == 1) && ($column['allowshow'] == 1) && ($column['formtype'] != 'img') && strpos($column['fieldname'], 'applicant')===0}-->
			<tr>
				<th>$column['fieldtitle']</th>
				<td>
					<label>
						<input type="text" id="$column['fieldname']" name="$column['fieldname']" />
						<span id="span_$column['fieldname']"><!--{if ($column['isrequired'] == 1)}-->*<!--{/if}--></span>
					</label>
				</td>
			</tr>
			<!--{/if}-->
			<!--{/loop}-->
		</table>
		<!--{/if}-->
	</div>
	<div class="step2">
		<h3>$lang['attend_apply_step2_title']</h3>
		<table>
			<tr>
				<th>$cacheinfo['fielddefault']['subject']</th>
				<td class="zs_nowarp" colspan="3">
					<label>
						<input type="text" id="subject" name="subject" value="" />
						<span id="span_subject">*</span>
					</label>
					<em>&nbsp;$lang['attend_subject_tips']</em>
				</td>
			</tr>
			<tr>
				<th>$lang['attend_cats']</th>
				<td>
					<label>
						<div id="catdiv" style="width:700px;">
							<select id="catid_0" name="catid">
								<option value="-1" selected="selected">$lang['attend_cats_select']</option>
								<!--{loop $_G['categorylist'] $cat}-->
								<!--{if $cat[upid] == 0}-->
								<option value="{$cat['catid']}" >{$cat['pre']}{$cat['name']}</option>
								<!--{/if}-->
								<!--{/loop}-->
							</select>
							<span id="span_catid">*</span>
						</div>
					</label>
					<script type="text/javascript" charset="utf-8">
						$(function() {
								var arr_cats = <!--{echo json_encode_region($arr_cat);}-->;
								createmultiselect("catid_0", "catid", arr_cats, "catdiv", "{$lang['attend_cats_select']}");});
					</script>
				</td>
			</tr>
			<tr>
				<th>$lang['attend_shopregion']</th>
				<td class="zs_nowarp" colspan="3">
					<div id="regiondiv" style="width:700px;">
						<select id="selector_0" name="region">
							<option value="-1">$lang['attend_region']</option>
							<!--{loop $showarr $rid $region}-->
							<!--{if $region['upid'] == 0}-->
							<option value="{$region['catid']}">{$region['name']}</option>
							<!--{/if}-->
							<!--{/loop}-->
						</select>
						<span id="span_region">*</span>
						<script type="text/javascript">
							$(function() {
									var arr_regon = <!--{echo json_encode_region($showarr);}-->;
									createmultiselect("selector_0", "region", arr_regon, "regiondiv", "{$lang['attend_region']}");});
						</script>
					</div>
				</td>
			</tr>
			<tr>
				<th>$lang['shopaddress']</th>
				<td class="zs_nowarp" colspan="3">
					<label>
						<input type="text" id="address" name="address" value="" />
						<span id="span_address">*</span>
					</label>
				</td>
			</tr>

			<!--{loop $cacheinfo['columns'] $column}-->
			<!--{if ($column['allowpost'] == 1) && ($column['allowshow'] == 1) && ($column['formtype'] != 'img') && preg_match('/^ext_/i',$column['fieldname'])}-->
			<tr>
				<th>$column['fieldtitle']</th>
				<td>
					<label>
						<input type="text" id="$column['fieldname']" name="$column['fieldname']" value="{$column['fielddefault']}" />
						<!--{if ($column['isrequired'] == 1)}-->
						<span id="span_$column['fieldname']">*</span>
						<!--{/if}-->
					</label>
				</td>
			</tr>
			<!--{/if}-->
			<!--{/loop}-->
		</table>
	</div>
	<div class="step4">
		<div id="ajax_status_display" style="padding:20px 0 0 75px;"></div>
		<div class="zs_join"> <a href="javascript:;" onclick="$('#form_attend').submit();">$lang['attend_rule']</a>
			<label>
				<div>
					<!--{eval $_G['setting']['registerrule']=str_replace("\r\n", "<br />", $_G['setting']['registerrule']);}-->
					$_G['setting']['registerrule']
				</div>
			</label>
		</div>
		<input type="hidden" name="formhash" value="<!--{eval echo formhash();}-->" />
		<input type="hidden" name="refer" value="$refer" />
		<input type="hidden" id="attendsubmit" name="attendsubmit" value="{$lang['attend_submit']}" />
	</div>
</form>
<script type="text/javascript" charset="{$_G['charset']}">
	// 邦定AJAX提交
	bindform('form_attend');
</script>
<style>

</style>
<!--{template 'templates/site/default/attend_footer.html.php', 1}-->