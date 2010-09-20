<?exit?>
<!--{template 'templates/site/default/attend_header.html.php', 1}-->

<!--/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: attend_step1.html.php 4359 2010-09-07 07:58:57Z fanshengshuai $
 */-->

			<div class="step1">
				<!--<h3>$lang['attend_step1_title']</h3>-->
				<p>$lang['attend_step1']</p>
			</div>
			<div class="step2">
				<h3>$lang['attend_step2_title']</h3>
				<dl>
					<dt><img src="static/image/zs_step2_cont1.jpg" alt="" width="167" height="183"></dt>
					<dd>
						<p>
						<!--{eval $_G['setting']['attenddescription']=str_replace("\r\n", "<br />", $_G['setting']['attenddescription']);}-->
						$_G['setting']['attenddescription']
						</p>
					</dd>
				</dl>
			</div>
			<div class="step3">
				<h3>$lang['attend_step3_title']</h3>
				<ul>$lang['attend_step3']</ul>
			</div>
			<div class="step4"> <a href="attend.php?do=attend" title="">$lang['attend_send']</a> </div>
<!--{template 'templates/site/default/attend_footer.html.php', 1}-->