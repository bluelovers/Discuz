===============================
SS7 轉換到 Discuz！ X1 注意事項
===============================

問題：轉換後的圖片及附件地址不對？
方案： 步驟如下：
1. 在原 SS7 源碼下找到圖標 images/base/attachment.gif，放在 Disucuz！ X1 的目錄 static/image/filetype/ 下；
2. 找到 source/module/portal/portal_view.php 文件，在代碼「$content['content'] = blog_bbcode($content['content']);」後換行添加以下代碼：

$ss_url = 'http://your_ss_site_url/'; // 請將此鏈接地址改為您的 SS 站點地址！！！
$findarr = array(
	$ss_url.'batch.download.php?aid=', // 附件下載地址
	$ss_url.'attachments/',  // 附件圖片目錄
	$ss_url.'images/base/attachment.gif'  // 附件下載圖標
);
$replacearr = array(
	'porta.php?mod=attachment&id=',
	$_G['setting']['attachurl'].'/portal/',
	STATICURL.'image/filetype/attachment.gif'
);
$content['content'] = str_replace($findarr, $replacearr, $content['content']);


