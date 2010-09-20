<!--{if $_G['login_type'] == 'login'}-->
<div>
	<form action="batch.login.php?action=login" method="post" name="user_login_form" id="user_login_form">
			<input type="hidden" name="formhash" value="<!--{eval echo formhash();}-->" />
			<input type="hidden" value="2592000" name="cookietime" />
			<div class="lgfm nlf">
				<div class="ftid sipt lpsw">
					<label for="username_input">{$lang['username']} {$lang['colon']}</label>
					<input type="text" value="" tabindex="1" class="txt" size="36" autocomplete="off" id="username_input" name="username" />
				</div>
				<p class="sipt lpsw">
				<label for="password_input">{$lang['password']} {$lang['colon']}</label>
				<input type="password" tabindex="1" class="txt" size="36" name="password" id="password_input" />
				</p>
				<!--{if $_G['setting']['seccode']}-->
				<p class="sipt lpsw" style="width:150px;float:left; margin-right:10px;">
					<label for="seccode">{$lang['seccode']} {$lang['colon']}</label>
					<input type="password" tabindex="1" class="txt" size="36" name="seccode" id="seccode" onfocus="showseccode();" />
					
				</p>
				<a title="$lang['captcha_tips']" href="javascript:updateseccode();"> {$lang['anotherone']}</a>
				<div style="margin-left:60px; clear:both;">
					<a title="$lang['captcha_tips']" href="javascript:updateseccode();"><img id="img_seccode" alt="{$lang['seccode_alter']}" src="seccode.php" /></a>
				</div>
				<!--{/if}-->
			</div>
			<div class="lgf minf">
			<!--{if $_G['setting']['regurl']}--><h4>{$lang['no_account']}? <a href="{$_G['setting']['regurl']}">{$lang['register']}</a></h4><!--{/if}-->
			</div>
		<p class="fsb pns cl">
		<button tabindex="1" value="true" name="loginsubmit" type="submit" class="pn pnc"><span>{$lang['login']}</span></button>
		</p>
	</form>
</div>
<!--{elseif $_G['login_type'] == 'register'}-->

<!--{elseif $_G['login_type'] == 'manage'}-->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="content-type" content="text/html; charset={$_G['charset']}" />
		<title>Brand Control Panel</title>
		<style type="text/css">
			* { word-break: break-all; word-wrap: break-word; }
			body { background: #FFF; color: #000; text-align: center; line-height: 1.5em; }
			body, h1, h2, h3, h4, h5, p, ul, dl, ol, form, fieldset { margin: 0; padding: 0; }
			body, td, input, textarea, select, button { font-size: 12px; font-family: Verdana,Arial,Helvetica,sans-serif; }
			ul { list-style: none; }
			cite { font-style: normal; }
			a { color: #46498E; text-decoration: none; }
			a:hover { text-decoration: underline; }
			a img { border: none; }

			/*布局*/
			#wrap { margin: 0 auto; padding: 0 2px; width: 1000px; text-align: left; }
			#header { position: relative; height: 80px; border-bottom: 5px solid #B7C6F5; }
			#header h2, #topmenu, #menu { position: absolute; }
			#header h2 { left: 0; bottom: 10px; }
			#topmenu { right: 1em; bottom: 3.5em; }
			#menu { right: 1em; bottom: -5px; line-height: 28px; }
			#menu li { float: left; padding: 2px 1em; }
			#menu li.active { padding-top: 0; border: solid #B7C6F5; border-width: 2px 1px 0; background: #FFF; }
			.mainarea { float: right; width: 100%; margin-left: -150px; }
			.maininner { margin-left: 170px; }
			.side { float: left; width: 150px; }

			#content { margin: 1em 0;}
			.title { margin-bottom: 10px; padding-bottom: 0.5em; border-bottom: 1px solid #B7C6F5;}
			.title h1, .title h3 { padding: 0.6em 0 0.2em 0; font-size: 1.17em; }
			.footactions { margin: 0 0 1em; padding: 0.5em; border: 2px solid #B7C6F5; border-top: 0px; }
			/*\*/ * html .footactions { height: 1%; } /**/ * > .footactions { overflow: hidden; }
			.footactions .pages { float: right; }
			.footactions a { margin-right:12px;}

			/*细线边框区域*/
			.bdrcontent { padding: 1em; border: 2px solid #B7C6F5; zoom: 1; }

			#footer { clear: both; padding: 1em 0; color: #939393; text-align: center; }
			#footer p { font-size: 0.83em; }
			#footer .menu a { padding: 0 1em; }
		</style>
		<script charset="utf-8" type="text/javascript" src="static/js/jquery.js"></script>
		<script charset="utf-8" type="text/javascript" src="static/js/common.js"></script>
	</head>
	<body style="background: #FFF; color: #000; font: 75% Arial, Helvetica, sans-serif;">
		<div style="position: absolute; left: 50%; top: 50%; width: 500px; height: 230px; margin-left: -250px; margin-top: -115px;">
			<div style="border: 1px solid #CCC; background: #EEE; padding: 5px; text-align:left;">
				<form method="post" name="login" action="{$BASESCRIPT}" style="background: #FFF url(static/image/pklogo.gif) no-repeat -10px 50%; margin: 0; padding: 20px 0 20px 180px;">
					<input type="hidden" name="formhash" value="<!--{eval echo formhash();}-->" />
					<fieldset style="border: none; border-left: 1px solid #EEE; padding-left: 3em;">
						<p style="margin: 0.5em 0;">{$lang['whologin']}{$lang['colon']}<strong>$_G['username']</strong>  <a href="{B_URL}/batch.login.php?action=logout" target="_blank">{$lang['logout']}</a></p>
						<p style="margin: 0.5em 0;">{$lang['password']}{$lang['colon']}<input type="password" id="admin_password" name="admin_password" tabindex="1" style="width: 10em; border: 1px solid #CCC; padding: 4px 2px;" /></p>
						<!--{if $_G['setting']['seccode'] == 1 }-->
						<p style="margin: 0.5em 0;">{$lang['seccode']}{$lang['colon']}<input type="text" name="seccode" autocomplete="off" tabindex="2" style="width: 5em; border: 1px solid #CCC; padding: 4px 2px;" /></p>
						<a title="$lang['captcha_tips']" href="javascript:updateseccode();" style="margin-left: 52px;"><img id="img_seccode" alt="{$lang['seccode_alter']}" src="seccode.php" /></a>
						<!--{/if}-->
						<p style="margin: 0.5em 0;margin-left:52px;"><input type="submit" class="button" name="dologinbtn" value="{$lang['loginadminchatform']}" style="background: #DDD; border-top: 1px solid #EEE; border-right: 1px solid #BBB; border-bottom: 1px solid #BBB; border-left: 1px solid #EEE; padding: 3px; cursor: pointer;" tabindex="3" /></p>
					</fieldset>
					<input type="hidden" name="dologin" value="yes" />
				</form>
			</div>
			<p style="margin: 0.5em 0; text-align: center; font-size: 10px;">
			Powered by <a href="http://www.comsenz.com" target="_blank" style="color: #006"><b>Comsenz Inc.</b></a>
			</p>
		</div>
	</body>
</html>
<!--{/if}-->