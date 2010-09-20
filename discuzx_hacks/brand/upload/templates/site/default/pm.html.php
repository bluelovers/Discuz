<?exit?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=$_G['charset']" />
		<title>$lang['pm']</title>
		<script type="text/javascript" src="static/js/jquery.js"></script>
		<script charset="utf-8" type="text/javascript" src="static/js/common.js"></script>
		<link type="text/css" rel="stylesheet" href="static/css/common.css" />
		<link rel="stylesheet" type="text/css" href="static/css/store.css" />
		<style>
		.pm h1 {
			background:transparent url(static/image/pm_headerbg.gif) repeat-x;
			color:#cc9966;font-size:1.13em;height:32px;line-height:32px;padding-left:10px;
		}
		.pm #pmcontent {
			width:480px; margin:auto;
		}
		#pmcontent{
			margin:10px;
			width:500px;
		}
		.savetitle em {
			color:#BBBBBB;
			font-size:11px;
			margin-left:10px;
		}
		.ucinput,.listarea {
			width:400px;
			border:1px solid #A4B2BD;
			padding:4px 6px;
		}
		.pmlist td, .pmlist th, .newpm td, .newpm th {
			color:#cc9966;
			padding:5px 0;
			text-align:left;
		}
		.pmsubmit {
			background:#cc9966 none repeat scroll 0 0;
			border:none;
			color:#FFFFFF;
		}
		.pmlist tfoot td, .newpm tfoot td, .newpm tfoot th {
			border-bottom:medium none;
		}
		.ucinfo table {
			table-layout:fixed;
			width:100%;
		}
		table {
			border-collapse:collapse;
			empty-cells:show;
		}
		.xlda dl {
			padding-left:10px;
			padding-right:10px;
		}
		.bbda {
			border-bottom:1px dashed #E6E7E1;
		}
		.xlda .m {
			display:inline;
			margin:8px 0 0 -65px;
		}
		.xld .m {
			float:left;
			margin:8px 8px 0 0;
		}
		.xld dd {
			margin-bottom:8px;
		}
		.xld dt {
			font-weight:700;
			padding:8px 0 5px;
		}
		.xi2, .xi2 a, .xi3 a {
			color:#336699;
		}
		.xw0  {
			font-weight:400;
		}
		.xw1 {
			font-weight:700;
		}
		.xlda dd a {
			color:#336699;
		}
		.xg1, .xg1 a {
			color:#999999 !important;
		}
		.xld a.d, .xl a.d, .attc a.d, .c a.d, .imgf a.d, .sinf a.d {
			background:url("static/image/op.png") no-repeat scroll 0 -2px transparent;
			float:right;
			height:20px;
			margin-right:10px;
			line-height:100px;
			overflow:hidden;
			width:20px;
		}
		.xld a.d:hover, .xl a.d:hover, .attc a.d:hover, .c a.d:hover, .imgf a.d:hover, .sinf a.d:hover {
			background-position:0 -22px;
		}
		.content .pages {width:510px;}
	</style>
	</head>

	<body>
		<div id="append_parent"></div>
		<div id="container" class="pm">
			<!--{if $send_result=='notlogin'}-->
			<h1>$lang['sendpm']</h1><br/><br/>
			<div id="pmcontent">$lang['please_login']</div>
			<div style="text-align:center; margin-top:20px;"><a onclick="parent.pm_close();" href="javascript;" style="border:#ccc 1px solid; padding:5px;" >$lang['closewindow']</a></div>
			<!--{elseif $send_result=='notallowtomyself'}-->
			<h1>$lang['sendpm']</h1><br/><br/>
			<div id="pmcontent">$lang['pm_yourself']</div>
			<div style="text-align:center;margin-top:20px;"><a onclick="parent.pm_close();" href="javascript;" style="border:#ccc 1px solid; padding:5px;" >$lang['closewindow']</a></div>
			<!--{elseif $send_result > 0}-->
			<h1>$lang['sendpm']</h1><br/><br/>
			<div id="pmcontent">$lang['pm_sendok']</div>
			<div style="text-align:center;margin-top:20px;"><a onclick="parent.pm_close();" href="javascript;" style="border:#ccc 1px solid; padding:5px;" >$lang['closewindow']</a></div>
			<script>
				setTimeout("parent.pm_close();",3000);
			</script>
			<!--{elseif $act=='sendbox'}-->
			<h1>$lang['pm_sendto'] $username </h1>

			<form id="postpmform" action="pm.php?act=send" name="postpmform" method="post">
				<div id="pmcontent" class="pmcontent noside">
					<table cellspacing="0" cellpadding="0" border="0" width="100%" class="newpm">
						<tbody>
							<tr>
								<th>{$lang['pm_title']}{$lang['colon']}</th>
								<td><input type="text" value="" maxlength="75" size="65" name="subject" class="ucinput" /></td>
							</tr>
							<tr>
								<th>{$lang['pm_message']}{$lang['colon']}</th>
								<td>
									<textarea name="message" style="height: 100px;" cols="10" rows="15" id="pm_textarea" class="listarea"></textarea>
								</td>
							</tr>
						</tbody>
						<tfoot>
							<tr>
								<th></th>
								<td style="text-align:right; padding-right:10px;"><input type="hidden" value="{$uid}" size="65" name="msgto" class="ucinput"/>
									<input type="hidden" name="formhash" value="<!--{eval echo formhash();}-->" />
									<input class="pmsubmit" type="submit" name="pmsubmit" value="$lang['send']" />
								</td>
							</tr>
						</tfoot>
					</table>
				</div>
			</form>

			<!--{elseif $act == 'list'}-->
			<h1>$lang['pm_view_systempm']</h1>
			<div class="xld xlda">
				<!--{loop $pm_notices['data'] $pm}-->
				<!--{eval
				$pm['date'] = date('Y-m-d H:i:s', $pm['dateline']);
				}-->
				<dl class="bbda cl" id="pmlist_{$pm['dateline']}">
					<dd class="m avt">
					<a href="javascript:;"><img src="static/image/systempm.gif" /></a>
					</dd>
					<dt>
					<a title="{$lang['delete']}" id="a_delete_{$pm['dateline']}" href="?act=del&msgtype=systempm&pmid=$pm['pmid']&inajax=1" class="d">{$lang['delete']}</a>
					<span class="xg1 xw0"><!--{$pm['date']}--></span>
					</dt>
					<dd {if $pm['new']} class="xw1"{/if}>$pm['subject']</dd>
					<dd class="pns">
					<a class="xi2" href="?act=view&msgtype=systempm&pmid=$pm['pmid']&inajax=1">{$lang['see_details']}</a>
					</dd>
				</dl>
				<!--{/loop}-->
			</div>
			<div class="content" style="width:510px; overflow:hidden;height:45px;">$multi</div>
			<!--{elseif $act=='view'}-->
			<h1>$lang['pm_view_systempm']</h1>
			<div style="padding:10px;"><a href="$return_url">$lang['return']</a></div>
			<div class="xld xlda" id="pm_ul">
				<dl id="pmlist_2151852078" class="cl bbda">
					<dd class="m avt">
					<a href="javascript:;"><img src="static/image/systempm.gif" /></a>
					</dd>
					<dt>
					<span class="xg1 xw0"><!--{$pm['date']}--></span>
					</dt>
					<dd>$pm['subject']<br />
					<br />
					$pm['message']</dd>
				</dl>
			</div>
			<!--{/if}-->
		</div>
	</body>
</html>