
# 修正預設樣式
UPDATE `pre_common_style` SET `extstyle` = 't1	t2	t3	t4	t5	t5a|t5a' WHERE `pre_common_style`.`styleid` =1;

UPDATE `pre_common_stylevar` SET substitute = '12px' WHERE variable = 'msgfontsize' OR variable = 'threadtitlefontsize';
