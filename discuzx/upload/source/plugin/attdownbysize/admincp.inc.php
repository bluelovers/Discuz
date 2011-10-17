<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
 
$filename = "source\\plugin\\AttDownBySize\\config.php";

if( isset($_POST['savesubmit']))
{
	$gidlist="<?php \n\$AttachDownBysizeConfig=array(\n";
	$gidlist .=  "\t'att_kg'  => '$_POST[att_kg]'";	
	$gidlist .=  ",\n\t'att_kb'  => '$_POST[att_kb]'";
	$gidlist .=  ",\n\t'att_exts'  => '$_POST[att_exts]'";
	$gidlist .= ",\n\t'att_uptime' => '$_POST[att_uptime]'";
	$gidlist .= ",\n\t'att_upnext' => '$_POST[att_upnext]'";
	$gidlist .= ",\n\t'att_upmax' =>  '$_POST[att_upmax]'";
	$gidlist .= ",\n\t'att_paymax' => '$_POST[att_paymax]'";
	$gidlist .= ",\n\t'att_downTime' => '$_POST[att_downTime]'";	
	$gidlist .= ",\n\t0 => '100'";
	foreach ($_POST as $key => $value)
	{
		$key = intval($key);
		if ( $key != 0 && ($key <= 3 || $key >=9))
		{
			$gidlist .= ",\n\t$key => '$value'";
		}
	}
	$gidlist .= "\n);\n?>";
	$handle = fopen ($filename,"w"); 
	if($gidlist!=''){
		if (!is_writable ($filename))  { die ("文件：".$filename."不可写，请检查其属性后重试！"); }
		if (!fwrite ($handle,$gidlist)){ die ("生成文件".$filename."失败！"); } 
	}
	fclose ($handle);
	echo "<div class=colorbox><span style='margin :15px ;FONT-SIZE: 14px;FONT-WEIGHT: 700;color:red'>设置完成</span></div>";
}

include $filename;

echo "
<STYLE><!--
.glist { list-style:none;}
.glist li { dispaly:block; height:30px; }
.glist li span { padding-top:10px;width:180px;float:left;dispaly:inline }
--></STYLE>";
 
$query = DB::query("SELECT type, groupid, grouptitle FROM ".DB::table('common_usergroup')." ORDER BY (creditshigher<>'0' || creditslower<>'0'), creditslower, groupid");

echo "<FORM method=post action=''><br>
==============================================================================<br>
本插件需要更改discuz源代码才可以使用,更改步骤如下:<br>
1. 在 \source\function\function_post.php 的约244行后用include插入数据<br>
约244行&nbsp;&nbsp;&nbsp;&nbsp; DB::delete('forum_attachment_unused', \"aid='\$aid'\");<br>
&nbsp;&nbsp;&nbsp;245行&nbsp;&nbsp;&nbsp;&nbsp; include DISCUZ_ROOT . \"source/plugin/attdownbysize/function_post_attsizeforauthor.php\";<br>
<br>
2. 在 \source\module\forum\forum_attachment.php 的199行后用include插入数据<br>
约198行&nbsp;&nbsp;&nbsp;&nbsp; if(empty(\$_G['gp_noupdate'])) {<br>
&nbsp;&nbsp;&nbsp;199行&nbsp;&nbsp;&nbsp;&nbsp; include DISCUZ_ROOT . \"source/plugin/attdownbysize/forum_attachment_downbyattachsize.php\";<br>
-------------------------------------------------------------------------------------------------------------------------------------------<br>
扩展应用:调用 paysize.php 可以获取支付流量,调用语句为: <br>
include DISCUZ_ROOT . \"source/plugin/attdownbysize/paysize.php\"; <br>
echo \"文件名:\$filename, 附件大小:\$filesize, 支付流量: \$paysize, 您的剩余流量 \$userhave\" ;<br>
==============================================================================<br><br>
<div class='glist'>
<li style='FONT-SIZE: 14px;FONT-WEIGHT: 700;'>基本设置</li>
<li><span>开关</span><INPUT class=radio value=1 ".($AttachDownBysizeConfig['att_kg'] ? 'CHECKED' : '')." type=radio name=att_kg>是 
<INPUT class=radio value=0 ".($AttachDownBysizeConfig['att_kg'] ? '' : 'CHECKED')." type=radio name=att_kg>否</LI></li>
<li><span>启用字段</span><span>
<SELECT name='att_kb'>
<OPTION value='extcredits1' ".($AttachDownBysizeConfig[att_kb]=='extcredits1' ? 'selected' : '').">extcredits1</OPTION>
<OPTION value='extcredits2' ".($AttachDownBysizeConfig[att_kb]=='extcredits2' ? 'selected' : '').">extcredits2</OPTION>
<OPTION value='extcredits3' ".($AttachDownBysizeConfig[att_kb]=='extcredits3' ? 'selected' : '').">extcredits3</OPTION>
<OPTION value='extcredits4' ".($AttachDownBysizeConfig[att_kb]=='extcredits4' ? 'selected' : '').">extcredits4</OPTION>
<OPTION value='extcredits5' ".($AttachDownBysizeConfig[att_kb]=='extcredits5' ? 'selected' : '').">extcredits5</OPTION>
<OPTION value='extcredits6' ".($AttachDownBysizeConfig[att_kb]=='extcredits6' ? 'selected' : '').">extcredits6</OPTION>
<OPTION value='extcredits7' ".($AttachDownBysizeConfig[att_kb]=='extcredits7' ? 'selected' : '').">extcredits7</OPTION>
<OPTION value='extcredits8' ".($AttachDownBysizeConfig[att_kb]=='extcredits8' ? 'selected' : '').">extcredits8</OPTION>
</SELECT>
</span><span>单位为KB, extcredits3</span></li>
<li style='height:5px'></li>
<li><span>不参与流量的附件扩展名</span><span><input type='text' name='att_exts' value='$AttachDownBysizeConfig[att_exts]' style='width:200px;margin-top:-5px'></input></span><span style='margin-left:65px;width:400px;'>用逗号分隔,如 gif,jpg,bmp,png</span></li>
<li style='FONT-SIZE: 14px;FONT-WEIGHT: 700;margin-top:20px'>附件上传者所得流量百分比</li>
<li><span>附件上传时获得流量</span><span><input type='text' name='att_uptime' value='$AttachDownBysizeConfig[att_uptime]' style='width:90px;margin-top:-5px'></input></span><span>%</span></li>
<li><span>附件上传时获得最大流量</span><span><input type='text' name='att_upmax' value='$AttachDownBysizeConfig[att_upmax]' style='width:90px;margin-top:-5px'></input></span><span>KB,为0则不限制</span></li>
<li><span>附件被下载获得分成</span><span><input type='text' name='att_upnext' value='$AttachDownBysizeConfig[att_upnext]' style='width:90px;margin-top:-5px'></input></span><span>%</span></li>

</div><br><div class='glist'><li style='FONT-SIZE: 14px;FONT-WEIGHT: 700;'>以下为用户下载附件所需支出的百分比</li>
<li><span>附件被下载支出最大流量</span><span><input type='text' name='att_paymax' value='$AttachDownBysizeConfig[att_paymax]' style='width:90px;margin-top:-5px'></input></span><span>KB,为0则不限制</span></li>
<li><span>附件被下载多少次后免流量下载</span><span><input type='text' name='att_downTime' value='$AttachDownBysizeConfig[att_downTime]' style='width:90px;margin-top:-5px'></input></span><span>次, 为0则一直需要支付流量</span></li>

<li></li>
";
 
while($group = DB::fetch($query)) {
	$gid    = $group[groupid];
	$gtitle = $group[grouptitle];
	if ($gid <= 3 || $gid >=9){
		echo "<li><span>$gtitle</span><span ><input type='text' name='$gid' value='$AttachDownBysizeConfig[$gid]' style='width:90px;margin-top:-5px'></input></span><span>%</span></li>";
	}
}

echo '<DIV style="CLEAR: both"></DIV><div  style="margin :30px 0 80px;float:leftdispaly:block"><input type="submit" class="btn" name="savesubmit" value="提交">';
echo "&nbsp;&nbsp;&nbsp;&nbsp;本插件由 <a href='http://www.hgcad.com/' target='_blank'> www.hgcad.com </a>提供</div></div></FORM>";
?>
