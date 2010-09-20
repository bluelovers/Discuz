<?exit?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8'charset']" />
<title>$lang['pm']</title>
<script type="text/javascript" src="static/js/jquery.js"></script>
<script type="text/javascript" src="static/js/jquery.cycle.lite.min.js"></script>
<script charset="utf-8" type="text/javascript" src="static/js/common.js"></script>
<!--{if $_G['setting']['sitetheme'] == 'default'}-->
	<link type="text/css" rel="stylesheet" href="static/css/common.css" />
<!--{else}-->
	<link type="text/css" rel="stylesheet" href="templates/site/{$_G['setting']['sitetheme']}/css/common.css" />
<!--{/if}-->
<link rel="stylesheet" type="text/css" href="static/css/store.css" />
</head>

<!--/**
 *      [品牌空間] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: report.html.php 4379 2010-09-09 03:00:50Z fanshengshuai $
 */-->

<body>
<div id="append_parent"></div>
<div id="container">
<!--{if !empty($result)}-->
<!--{eval $reportlang = $lang["$result"];}-->
<h1 style="background:transparent url(static/image/pm_headerbg.gif) repeat-x;border-bottom:1px solid #CAD9EA;color:#005C89;font-size:1.13em;height:32px;line-height:32px;padding-left:10px;">$lang['report']</h1><br/><br/>
<div id="reportcontent" style="width:300px; margin:auto;text-align:center;">$reportlang</div>
<div style="text-align:center; margin-top:20px;"><a onclick="parent.closereportdiv();" href="javascript;" style="border:#ccc 1px solid; padding:5px;" >$lang['closewindow']</a></div>
<script>
setTimeout("parent.closereportdiv();",3000);
</script>
<!--{else}-->
<h1 style="background:transparent url(static/image/pm_headerbg.gif) repeat-x;border-bottom:1px solid #CAD9EA;color:#005C89;font-size:1.13em;height:32px;line-height:32px;padding-left:10px;">$lang['report']</h1>
<form id="reportform" action="report.php" name="reportform" method="post">
<div id="reportcontent" class="reportcontent noside">
	<table cellspacing="0" cellpadding="0" border="0" width="100%" class="report">
		<tbody>
			<tr>
				<td class="sel"></td>
				<th>$lang['resultmessage']</th>
				<td>

				<textarea name="reason" style="height: 100px;" cols="10" rows="15" id="reason" class="listarea"></textarea>
				</td>
			</tr>
		</tbody>
		<tfoot>
			<tr>
				<td class="sel"/>
				<th/>
				<td>
					<select name="reasonid">
						<option value="" selected="selected">$lang['please_select']</option>
						<!--{loop $reasonarr $reason}-->
							<option value="$reason['rrid']">$reason['content']</option>
						<!--{/loop}-->
					</select>
					<input type="hidden" value="$id" size="65" name="id" class="ucinput"/>
					<input type="hidden" value="$type" size="65" name="type" class="ucinput"/>
					<input type="hidden" name="formhash" value="<!--{eval echo formhash();}-->" />
					<input class="reportsubmit" type="submit" name="reportsubmit" value="$lang['report']" />
				</td>
			</tr>
		</tfoot>
	</table>
	</div>
</form>
</div>
<!--{/if}-->

<style>
#reportcontent{
	margin:5px;
width:300px;
}
.savetitle em {
color:#BBBBBB;
font-size:11px;
margin-left:10px;
}
.ucinput,.listarea {
width:200px;
border:1px solid #A4B2BD;
padding:4px 6px;
}
.reportlist td, .reportlist th, .report td, .report th {
border-bottom:1px solid #CAD9EA;
border-top:medium none;
color:#666666;
padding:5px 0;
text-align:left;
}
.reportsubmit {
background:#005C89 none repeat scroll 0 0;
color:#FFFFFF;
}
.reportlist tfoot td, .report tfoot td, .report tfoot th {
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
</style>
</body>
</html>