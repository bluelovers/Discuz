<?php
date_default_timezone_set('Asia/Chongqing');
/**
 * 环境测试
 * 此页在iframe中显示，所以没有IN_DISCUZ的判断。
 */
//判断是否支持curl
if (function_exists("curl_exec")) {
    $c = true;
    $c_res = "支持";
} else {
    $c = false;
    $c_res = "<font color='#f8505c'>不支持</font>";
}
//判断是否支持socket
if (function_exists("fsockopen")) {
    $s = true;
    $s_res = "支持";
} else {
    $s = false;
    $s_res = "<font color='#f8505c'>不支持</font";
}
//判断是否支持openssl
if (function_exists("openssl_open")) {
    $o = true;
    $o_res = "支持";
} else {
    $o = false;
    $o_res = "<font color='#f8505c'>不支持</font";
}
//定论
if($c){
    $result="能正常使用,程序将使用curl与微博通讯";
}elseif(!$s){
    $result="<font color='#f8505c'>不能正常使用，建议开启curl或者socket </font><a href=\"http://www.3g4k.com/weibokong.html\" target=\"_blank\">【查看配置教程】</a>";
}elseif($s and $o){
    $result="能正常使用，程序将使用socket与微博通讯";
}else{
    $result="<font color='#f8505c'>腾讯微博不能正常使用，建议开启curl或者openssl</font>   <a href=\"http://www.3g4k.com/weibokong.html\" target=\"_blank\">【查看配置教程】</a>";
}
if(isset($_GET['installtype'])){
$installtype=$_GET['installtype'];
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="zh-cn">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
<meta name="author" content="微博控 - <www.3g4k.cn>" />
<meta name="Copyright" content="" />
<meta name="description" content="" />
<meta name="keywords" content="" />
<title>环境检测</title>
<style>
html, body, div, span, applet, object, iframe, h1, h2, h3, h4, h5, h6, p, blockquote, pre, a, abbr, acronym, address, big, cite, code, del, dfn, em, font, img, ins, kbd, q, s, samp, small, strike, strong, sub, sup, tt, var, b, u, i, center, dl, dt, dd, ol, ul, li, fieldset, form, label, legend, table, caption, tbody, tfoot, thead, tr, th, td {margin: 0;padding: 0;border: 0;outline: 0;font-size: 100%;background: transparent;}
ol, ul {list-style: none;}
body{ background:#FFF; margin:0 20px;}
body,table,td{ font-size:12px; color:#666;}
table{ border-collapse:collapse; border-spacing: 0;}
td{ border-top:1px dotted #DEEFFB; padding:5px;}
.title{color:#0099CC; font-weight:700; height:25px; text-align:left; padding:5px; background:#e5f1fb}
a{ color:#f8505c; text-decoration:none;}
.btn{ padding:5px; display:block; width:50px; background:#e5f1fb; text-align:center; height:20px; line-height:20px; text-decoration:none; color:#666; border: 1px solid #c7e1f6; margin-left:80px; font-size:14px; cursor:pointer;}
</style>
</head>
<body>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <th colspan="2" class="title">环境测试</th>
  </tr>
   <tr>
    <td width="13%" align="right"><strong>运行方式：</strong></td>
    <td width="87%"><?php echo strtoupper(php_sapi_name()) ?></td>
  </tr>
   <tr>
    <td width="13%" align="right"><strong>PHP版本：</strong></td>
    <td width="87%"><?php echo phpversion() ?></td>
  </tr>
  <tr>
    <td width="13%" align="right"><strong>curl：</strong></td>
    <td width="87%"><?php echo $c_res ?></td>
  </tr>
  <tr>
    <td align="right"><strong>socket：</strong></td>
    <td><?php echo $s_res?></td>
  </tr>
    <tr>
    <td align="right"><strong>openssl：</strong></td>
    <td><?php echo $o_res ?></td>
  </tr>
   <tr>
    <td align="right"><strong>hash_hmac函数：</strong></td>
    <td><?php if(function_exists('hash_hmac')){
		echo "存在";
		}else{
		echo "不存在";	
			}?></td>
  </tr>
   <tr>
    <td align="right"><strong>json函数：</strong></td>
    <td><?php if(function_exists('json_decode')){
		echo "存在";
		}else{
		echo "不存在";	
			}?></td>
  </tr>
    <tr>
    <td align="right"><strong>是否可写网站日志：</strong></td>
    <td><?php if(function_exists('error_log')){
		echo "可写";
		}else{
		echo "不可写";	
			}?></td>
  </tr>
  <tr>
    <td align="right"><strong>服务器时间：</strong></td>
    <td><?php echo date("Y-m-d H:i:s");?>   (如果服务器时间不准确会导致一些微博不能绑定)</td>
  </tr>
    <tr>
    <td align="right"><strong>检测结果：</strong></td>
    <td><span class="result"><?php echo $result?></span></td>
  </tr>
</table>
<?php
if(!empty($installtype)){
?>
<a href="../../../admin.php?action=plugins&operation=plugininstall&dir=wb_share_dzx&finish=1&installtype=<?php echo $installtype; ?>" target="_parent" class="btn">确认</a>
<?php } ?>
</div>
</body>
</html>
