
# 方便當 手機版訪問設置 > 開啟電腦訪問手機版預覽功能時 允許在電腦上直接瀏覽手機頁面
UPDATE pre_common_nav SET `url` = 'misc.php?mod=mobile' WHERE `identifier` = 'mobile' AND `navtype` =1;

