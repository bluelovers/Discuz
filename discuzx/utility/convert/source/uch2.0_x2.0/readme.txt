====================================
UCenter Home 2.0 升級至 Discuz! X2.0 說明
====================================

特別警示!!!
由於UCHome與Discuz!部分功能進行了整合性融合，因此UCHome的部分功能，在整合到Discuz! X後將會部分丟失，
其中包括：
由於新增專題功能，原UCH熱鬧功能將不再支持；
UCH投票、UCH活動將與論壇投票貼、活動貼的形式融合為一體，活動相冊、活動群組功能將不再支持；
UCH群組將以新的群組功能存在，原群組相冊、群組活動功能將不再支持；
個人資料進行了新的調整，UCH原個人資料中的學校、工作信息將需要重新填寫；
UCH的全站實名功能不再支持；

請根據自己建站需求，權衡決定是否將UCHome轉換升級到Discuz! X。

I 升級前的準備
---------------
1. 建立程序備份目錄，例如 old
2. 將原UCHome所有程序移動到 old 目錄中
3. 上傳 Discuz! X 產品的upload目錄中的程序到UCHome目錄
4. 執行安裝程序 /install
   安裝的時候請指定原UCHome掛接的UCenter Server地址（如果 UCenter版本低於1.6.0，需先升級 UCenter ）

II 升級UCHome數據
---------------
1. 安裝完畢，測試Discuz! X可以正常運行以後，上傳convert 程序到Discuz! X根目錄
2. 執行 /convert
3. 選擇相應的程序版本，開始轉換
4. 轉換過程中不可擅自中斷，直到程序自動執行完畢。
5. 轉換過程可能需要較長時間，且消耗較多服務器資源，您應當選擇服務器空閒的時候執行

III 升級完畢, 還要做的幾件事
--------------------------
1. 編輯新Discuz! X的 config/config_global.php 文件，設定好創始人
2. 直接訪問新Discuz! X的 admin.php
3. 使用創始人帳號登錄，進入後台更新緩存
4. 新系統增加了很多設置項目，包括用戶權限、組權限、論壇板塊等等，您需要仔細的重新設置一次。
5. 轉移舊附件目錄到新產品根目錄（在轉移之前，您的動態、日誌、評論、留言等內容中的圖片無法正常顯示）
   a)進入 old/attachment 目錄
   b)將所有文件移動到 新Discuz! X產品 /data/attachment/album/ 目錄中
   c)同時，修改一下 Discuz! X的代碼
	 讓日誌內容中的已經插入的圖片地址，通過字符串替換，改為最新的圖片地址，解決日誌內容圖片無法顯示的問題。
	 方法如下：
	 打開Discuz! X的 ./source/include/space/space_blog.php 程序
	 找到：
	 $blog['message'] = blog_bbcode($blog['message']);
	 在下面增加如下代碼：
	 $home_url = 'http://your_home_site_url/'; // 請將此鏈接地址改為您的 UCHome 站點地址！！！
	 $bbs_url = 'http://your_bbs_site_url/'; // 請將此鏈接地址改為您的 BBS 站點地址！！！
	 $findarr = array(
		'<img src="attachment/',  //原uchmoe附件圖片目錄
		'<IMG src="'.$home_url.'attachment/',  // 原UCHome附件圖片目錄
		$bbs_url.'attachments/month',  // 原論壇附件圖片目錄
	 );
	 $replacearr = array(
		'<img src="'.$_G['setting']['attachurl'].'album/',
		'<IMG src="'.$_G['setting']['attachurl'].'album/',
		$bbs_url.$_G['setting']['attachurl'].'forum/month',
	 );
	 $blog['message'] = str_replace($findarr, $replacearr, $blog['message']);

	 如果你的UCHome的附件不是存放在默認的 ./attachment 目錄，那麼
	 修正上面代碼的 <img src="attachment/ 中的 attachment 為你自己的附件目錄名字
6. 轉移舊圖片目錄到新產品根目錄（在轉移之前，您的動態、日誌、評論、留言等內容中的表情無法正常顯示）
   a)將 old/image 目錄和目錄下的文件 移動到 新Discuz! X產品的根目錄中
7. 恢復 space.php URL地址的訪問（在恢復之前，您的動態中的站內信息鏈接將指向無法訪問的地址）
   1)將 utility/oldprg/uchome/space.php 文件移動到 新Discuz! X產品的根目錄中
8. 刪除 convert 程序，以免給您的Discuz! X安裝帶來隱患
9. 待測試新Discuz! X產品的所有功能均正常後，可以刪除 舊的程序備份和數據備份