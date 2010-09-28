<?php

define(ROOT_DIR,dirname(__FILE__)."/");

if(!file_exists('./data/config.inc.php') || !is_writeable('./data')){
		$isucdir= 0;
		echo 'UCenter創始人密碼重置工具必須放在UCenter根目錄下才能正常使用.';
		exit;

	}else{
		$isucdir = 1;
}

$info="";

setucadministrator();


function setucadministrator(){
	global $isucdir;
	global $info;
	if($_POST['setucsubmit']){

		if($isucdir){
			$configfile = ROOT_DIR."./data/config.inc.php";
			$uc_password = $_POST["uc_password"];
			$salt = substr(uniqid(rand()), 0, 6);

			if(!$uc_password){
				$info = "密碼不能為空";

			}else{

				$md5_uc_password = md5(md5($uc_password).$salt);
				$config = file_get_contents($configfile);
				$config = preg_replace("/define\('UC_FOUNDERSALT',\s*'.*?'\);/i", "define('UC_FOUNDERSALT', '$salt');", $config);
				$config = preg_replace("/define\('UC_FOUNDERPW',\s*'.*?'\);/i", "define('UC_FOUNDERPW', '$md5_uc_password');", $config);
				$fp = @fopen($configfile, 'w');
				@fwrite($fp, $config);
				@fclose($fp);
				$info = "UCenter創始人密碼更改成功為：$uc_password";
			}

		}else{
			$info = "本程序文件放置在UCenter跟目錄,才能通過程序修改UCenter創始人管理員的密碼<br />";
		}
	}

	templates("setucadministrator");
}

function errorpage($message,$title = '',$isheader = 1,$isfooter = 1){



		$message = "<h4>$title</h4><br><br><table><tr><th>提示信息</th></tr><tr><td>$message</td></tr></table>";
		echo $message;
		exit;
}


function templates($tpl){

	switch ($tpl){
		case "header":
			echo '<html>
			<head>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
			<title>UCenter 創始人密碼更改工具</title>
			<style type="text/css">
			<!--
			body {font-family: Arial, Helvetica, sans-serif, "細明體";font-size: 12px;color:#000;line-height: 120%;padding:0;margin:0;background:#DDE0FF;overflow-x:hidden;word-break:break-all;white-space:normal;scrollbar-3d-light-color:#606BFF;scrollbar-highlight-color:#E3	EFF9;scrollbar-face-color:#CEE3F4;scrollbar-arrow-color:#509AD8;scrollbar-shadow-color:#F0F1FF;scrollbar-base-color:#CEE3F4;}
			a:hover {color:#60F;}
			ul {padding:2px 0 10px 0;margin:0;}
			textarea,table,td,th,select{border:1px solid #868CFF;border-collapse:collapse;}
			input{margin:10px 0 0px 30px;border-width:1px;border-style:solid;border-color:#FFF #64A7DD #64A7DD #FFF;padding:2px 8px;background:#E3EFF9;}
			input.radio,input.checkbox,input.textinput,input.specialsubmit {margin:0;padding:0;border:0;padding:0;background:none;}
			input.textinput,input.specialsubmit {border:1px solid #AFD2ED;background:#FFF;height:24px;}
			input.textinput {padding:4px 0;} 			input.specialsubmit {border-color:#FFF #64A7DD #64A7DD #FFF;background:#E3EFF9;padding:0 5px;}
			option {background:#FFF;}
			select {background:#F0F1FF;}
			#header {height:60px;width:100%;padding:0;margin:0;}
		    h2 {font-size:24px;font-weight:bold;position:absolute;top:24px;left:20px;padding:10px;margin:0;}
		    h3 {font-size:14px;position:absolute;top:28px;right:20px;padding:10px;margin:0;}
			#content {height:510px;background:#F0F1FF;overflow-x:hidden;z-index:1000;}
		    #nav {top:60px;left:0;height:510px;width:180px;border-right:1px solid #DDE0FF;position:absolute;z-index:2000;}
		        #nav ul {padding:0 10px;padding-top:30px;}
		        #nav li {list-style:none;}
		        #nav li a {font-size:14px;line-height:180%;font-weight:400;color:#000;}
		        #nav li a:hover {color:#60F;}
		    #textcontent {padding-left:200px;height:510px;width:100%;line-height:160%;overflow-y:auto;overflow-x:hidden;}
			    h4,h5,h6 {padding:4px;font-size:16px;font-weight:bold;margin-top:20px;margin-bottom:5px;color:#006;}
				h5,h6 {font-size:14px;color:#000;}
				h6 {color:#F00;padding-top:5px;margin-top:0;}
				.specialdiv {width:70%;border:1px dashed #C8CCFF;padding:0 5px;margin-top:20px;background:#F9F9FF;}
				#textcontent ul {margin-left:30px;}
				textarea {width:78%;height:320px;text-align:left;border-color:#AFD2ED;}
				select {border-color:#AFD2ED;}
				table {width:74%;font-size:12px;margin-left:18px;margin-top:10px;}
				    table.specialtable,table.specialtable td {border:0;}
					td,th {padding:5px;text-align:left;}
				    caption {font-weight:bold;padding:8px 0;color:#3544FF;text-align:left;}
				    th {background:#D9DCFF;font-weight:600;}
					td.specialtd {text-align:left;}
				.specialtext {background:#FCFBFF;margin-top:20px;padding:5px 40px;width:64.5%;margin-bottom:10px;color:#006;}
			#footer p {padding:0 5px;text-align:center;}
			-->
			</style>
			</head>

			<body>


			<div id="content">
			<div id="textcontent">';
			break;

		case "footer":
			echo  '
					</div></div>

					<div id="footer"><p>UCenter 創始人密碼更改工具 &nbsp;
					版權所有 &copy;2001-2007 <a href="http://www.comsenz.com" style="color: #888888; text-decoration: none">
					康盛創想(北京)科技有限公司 Comsenz Inc.</a></font></td></tr><tr style="font-size: 0px; line-height: 0px; spacing: 0px; padding: 0px; background-color: #698CC3">
					</p></div>
					</body>
					</html>';
			exit;
			break;

		case "setucadministrator":
			templates("header");
			if(!empty($_POST['setucsubmit'])){
				echo "<h5>UCenter 創始人密碼更改工具</h5><h5> <font color=red>使用完畢後請及時刪除本文件，以免給您造成不必要的損失</font></h5>";
				echo '<form action="?action=setadmin" method="post"><input type="hidden" name="action" value="login" />';
				global $info;
				errorpage($info,'',0,0);
				echo '</form>';
			}else{
				echo '<form action="?action=setucadministrator" method="post">
				<h5>UCenter 創始人密碼更改工具</h5>
				<h5> <font color=red>使用完畢後請及時刪除本文件，以免給您造成不必要的損失</font></h5>
				<table>
				<tr><th width="30%">用戶名</th><td width="70%"><input class="textinput" readonly="readonly" disabled type="text" name="username" size="25" maxlength="40" value="UCenter Administrator"></td></tr>
				<tr><th width="30%">請輸入密碼</th><td width="70%"><input class="textinput" type="text" name="uc_password" size="25"></td></tr>

				</table>
				<input type="submit" name="setucsubmit" value="提 &nbsp; 交">
				</form>';
			}
			templates("footer");
			break;

	}
}

?>