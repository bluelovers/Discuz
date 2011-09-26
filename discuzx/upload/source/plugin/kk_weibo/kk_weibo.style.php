<?php
	return Array(
		'data_position'	=> 1, // 1=等级下面 2=等级上面
		'btn_position'	=> 1, // 1=用户互动后 2=数据输出后		
		'css_output'	=> <<<EOT
.kk_weibo_top {margin-left:20px;}
.kk_weibo_top li {float:left;padding:2px 0px;width:45px;border:1px solid #ccc; background:#FFF;}
.kk_weibo_top li a{color:#336699;}
.kk_weibo_top li:hover{ border:1px solid #00CCFF; background:#FFFFFF;}
.kk_weibo_top li.alt {padding:2px 4px 2px 4px;margin-right:2px;text-align:center;color:#333;}
.kk_weibo_top li.lr  {width:1px;height:32px;margin:5px;border:1px solid #ccc;}
.kk_weibo_top li div.num {font-size:14px;font-family:Arial;font-weight:bold;text-align:center;}
.kk_weibo_top li div.num a{color:#336699;display:inherit;border:1px solid #ccc;border-top-width:2px;}
.kk_weibo_top li div.num a:hover{border:1px solid #00CCFF;text-decoration: none;border-top-width:2px;}
.kk_weibo_bottom {margin-left:20px;margin-top:-4px;}
.kk_weibo_bottom span {padding-top:2px;padding-left:16px;text-indent:16px;}
.kk_weibo_bottom span a{color:#336699;}
.kk_weibo_bottom span.icon1 {background:url(/static/image/common/addbuddy.gif) no-repeat -2px 0px;}
.kk_weibo_bottom span.icon2 {background:url(/static/image/common/data_valid.gif) no-repeat 0px 2px;}
.kk_weibo_top li span.icon1 {background:url(/static/image/common/addbuddy.gif) no-repeat -2px 1px; padding-left:15px;}
.kk_weibo_top li span.icon2 {background:url(/static/image/common/group.ico) no-repeat -2px 1px; padding-left:15px;}

EOT
,		//关注:{count_attention} 粉丝:{count_fans} 帖子:{posts} 主题:{threads} 记录:{doings} 关注地址:{url_attention} 粉丝地址:{url_fans} {uid}
		'data_tpl'		=> <<<EOT
<div class="kk_weibo_top"><ul>
<li class="alt"><div class="num"><a href="{url_attention}" target="_blank">{count_attention}</a></div><span class="icon1">关注</span></li>
<li class="lr"></li>
<li class="alt"><div class="num"><a href="{url_fans}" target="_blank">{count_fans}</a></div><span class="icon2">粉丝</span></li>
</ul></div><br clear="both">
EOT
,		//加关注地址:{url_add} 加关注点击脚本:{click_add} {uid}
		'btn_add_tpl'		=> <<<EOT
<div class="kk_weibo_bottom">
<span class="icon1"><a href="{url_add}" onclick="{click_add}">加关注</a></span>
</div>
EOT
,		//取消关注地址:{url_del} 取消关注点击脚本:{click_del} {uid}
		'btn_del_tpl'		=> <<<EOT
<div class="kk_weibo_bottom">
<span class="icon2"><a href="javascript:void(0)">已关注</a></span>
</div>	
EOT
,
	);
?>