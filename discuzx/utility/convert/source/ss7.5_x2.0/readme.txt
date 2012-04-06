====================================
SupeSite 7.5 升級至 Discuz! X2.0 說明
====================================

特別警示!!!
Discuz! X2.0中並未具備SupeSite 7.5中的全部功能，
此轉換程序，僅轉換SupeSite 7.5中的資訊分類、資訊文章數據到 Discuz! X2.0產品的文章系統中。
其他數據將不進行轉換。
因此，數據轉換後，Discuz! X2.0產品存在原有SupeSite功能丟失和數據丟失問題，請自行權衡決定是否轉換升級。


I 升級前的準備
---------------
1. 建立程序備份目錄，例如 old
2. 將原SupeSite所有程序移動到 old 目錄中
3. 上傳 Discuz! X2.0 產品的upload目錄中的程序到SupeSite目錄
4. 執行安裝程序 /install
   安裝的時候請指定原SupeSite掛接的UCenter Server地址（如果 UCenter版本低於1.6.0，需先升級 UCenter ）

II 升級SupeSite數據
---------------
1. 安裝完畢，測試Discuz! X2.0可以正常運行以後，上傳convert 程序到Discuz! X2.0根目錄
2. 執行 /convert
3. 選擇相應的程序版本，開始轉換
4. 轉換過程中不可擅自中斷，直到程序自動執行完畢。
5. 轉換過程可能需要較長時間，且消耗較多服務器資源，您應當選擇服務器空閒的時候執行

III 升級完畢, 還要做的幾件事
--------------------------
1. 編輯新Discuz! X2.0的 config/config_global.php 文件，設定好創始人
2. 直接訪問新Discuz! X2.0的 admin.php
3. 使用創始人帳號登錄，進入後台更新緩存
4. 新系統增加了很多設置項目，包括用戶權限、組權限、論壇板塊等等，您需要仔細的重新設置一次。
5. 轉移舊附件目錄到新產品根目錄（在轉移之前，您的資訊內容中的圖片無法正常顯示）
   a)將 old/attachments 目錄和目錄下的文件 全部移動到 新Discuz! X2.0產品的/data/attachment/portal/目錄中
   b) 在原 SS7 源碼下找到圖標 images/base/attachment.gif，放在 Disucuz！ X2.0 的目錄 static/image/filetype/ 下；
   c) 找到 source/module/portal/portal_view.php 文件，在代碼「$content['content'] = blog_bbcode($content['content']);」後換行添加以下代碼：

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

6. 轉移舊圖片目錄到新產品根目錄（在轉移之前，您的資訊內容中的表情無法正常顯示）
   a)將 old/images 目錄和目錄下的文件 移動到 新Discuz! X2.0產品的根目錄中
7. 刪除 convert 程序，以免給您的Discuz! X2.0安裝帶來隱患。