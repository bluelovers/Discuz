
# 修改預設的 feedday 為 180 天
UPDATE `pre_common_setting` SET `svalue` = '180' WHERE `pre_common_setting`.`skey` = 'feedday';
