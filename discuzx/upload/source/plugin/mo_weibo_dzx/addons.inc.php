<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
loadcache('plugin');
echo '<style type="text/css">.momoodfm{margin:15px;} .momoodfm textarea{width:500px; padding:5px; height:60px;} b{font-size:16px; color:#0101ff;} .momoodfm p{line-height:24px; height:24px;}</style><div class=momoodfm>
	<p><b>'.lang('plugin/mo_weibo_dzx','cweibo').'</b></p>
	<p>'.lang('plugin/mo_weibo_dzx','cctitle').':</p><input name="wtitle" id="wtitle" value="'.$_G[cache][plugin][mo_weibo_dzx][mo_weibo_title].'" class="px" type="text">
	<p>'.lang('plugin/mo_weibo_dzx','crow').':</p><input name="wrow" id="wrow" value="4" class="px" type="text">
	<p>'.lang('plugin/mo_weibo_dzx','ccol').':</p><input name="wcol" id="wcol" value="3" class="px" type="text">
	<p>'.lang('plugin/mo_weibo_dzx','cscroll').':</p><select name="wscroll" id="wscroll" class="ps"><option value="1" selected>'.lang('plugin/mo_weibo_dzx','cscroll1').'</option><option value="2">'.lang('plugin/mo_weibo_dzx','cscroll2').'</option></select>
	<p>'.lang('plugin/mo_weibo_dzx','ccode').':</p><textarea name="pcode" id="pcode" class="pt"></textarea>
	<p><button onclick="generate_code(\'pcode\');" class="pn pnc"><em>'.lang('plugin/mo_weibo_dzx','cgenerate').'</em></button>&nbsp;<button name="ccopy" onclick="ccopy(\'pcode\');" title="'.lang('plugin/mo_weibo_dzx','ccopy').'" class="pn"><em>'.lang('plugin/mo_weibo_dzx','ccopy').'</em></button></p>
</div>
<div class=momoodfm>
	<p><b>'.lang('plugin/mo_weibo_dzx','cfeed').'</b></p>
	<p>'.lang('plugin/mo_weibo_dzx','cfeedc').':</p><input name="fcount" id="fcount" value="20" class="px" type="text">
	<p>'.lang('plugin/mo_weibo_dzx','cheight').':</p><input name="fheight" id="fheight" value="280" class="px" type="text">
	<p>'.lang('plugin/mo_weibo_dzx','ccode').':</p><textarea name="fcode" id="fcode" class="pt"></textarea>
	<p><button onclick="generate_code(\'fcode\');" class="pn pnc"><em>'.lang('plugin/mo_weibo_dzx','cgenerate').'</em></button>&nbsp;<button name="ccopy" onclick="ccopy(\'fcode\');" title="'.lang('plugin/mo_weibo_dzx','ccopy').'" class="pn"><em>'.lang('plugin/mo_weibo_dzx','ccopy').'</em></button></p>
</div>';
$lbtn=$_G[config][output][language]=='zh_cn'?'release':'release_tc';
?>
<script type="text/javascript">
function generate_code(cid){
	var wrow = $('wrow').value;
	var wcol = $('wcol').value;
	var wtitle = $('wtitle').value;
	var wscroll = $('wscroll').value;
	var fcount = $('fcount').value;
	var fheight = $('fheight').value;
	if(wrow && wcol && cid=='pcode'){
		$(cid).value = '<div class="frame"><style type="text/css">.momoodfm{margin:15px;} .momoodfm textarea{width:310px; padding:5px; height:50px;} .momoodfm .moodfm_f{padding:5px; line-height:24px; vertical-align:middle;} .facel{width:260px; top:0; padding:0 6px 6px 0;} .facel img{margin:6px 0 0 6px;}.moodfm_btn{padding-left: 5px; background: url(static/image/common/mood_input_btn.png) no-repeat 5px 0;} .moodfm_btn button {width: 58px; height: 58px; cursor: pointer; opacity: 0; filter: alpha(opacity=0);}</style><div class="title frame-title"><span class="titletext">'+wtitle+'</span><a href="plugin.php?id=mo_weibo_dzx&mod=doing" onclick=showWindow(\'mo_weibo_dzx\',this.href) style="margin-left:10px; display:block; height:100%; width:71px; background:url(source/plugin/mo_weibo_dzx/images/<? echo $lbtn;?>.png) no-repeat 0 50%; float:right;"></a></div><iframe id="mo_weibo_code" allowtransparency=\"true\" scrolling="no" border="0" width="100%" height="'+wrow*60+'" frameborder="0" src="plugin.php?id=mo_weibo_dzx&mod=portal&row='+wrow+'&col='+wcol+'&scroll='+wscroll+'"></iframe></div>';
	} else if(cid=='fcode' && fheight){
		$(cid).value = '<iframe id="mo_weibo_code" allowtransparency="true" scrolling="no" border="0" width="100%" height="'+fheight+'" frameborder="0" src="plugin.php?id=mo_weibo_dzx&mod=feed&fc='+fcount+'&fh='+fheight+'"></iframe>';
	} else{
		alert('<? echo lang('plugin/mo_weibo_dzx','code_alert');?>');
	}
}
function ccopy(cvd){
	var vcode = $(cvd).value;
	setCopy(vcode, '<? echo lang('plugin/mo_weibo_dzx','ccopy');?>');
}
</script>