INSERT INTO pre_common_setting VALUES ('attachdir', '', './data/attachment');
INSERT INTO pre_common_setting VALUES ('attachurl', '', 'data/attachment');
INSERT INTO pre_common_setting VALUES ('autoactivationuser', '', '1');

INSERT INTO pre_common_module (mid, name, displayorder, available, identifier, type) VALUES(1, '全局', 1, 1, 'global', 1);
INSERT INTO pre_common_module (mid, name, displayorder, available, identifier, type) VALUES(2, '界面', 2, 1, 'template', 1);
INSERT INTO pre_common_module (mid, name, displayorder, available, identifier, type) VALUES(3, '投票', 3, 1, 'poll', 0);

INSERT INTO pre_common_nav (title, url, target, available, displayorder, highlight, type) VALUES('廣告服務', '', 0, 1, 0, 0, 2);
INSERT INTO pre_common_nav (title, url, target, available, displayorder, highlight, type) VALUES('聯繫我們', '', 0, 1, 0, 0, 2);
INSERT INTO pre_common_nav (title, url, target, available, displayorder, highlight, type) VALUES('服務條款 ', '', 0, 1, 0, 0, 2);
INSERT INTO pre_common_nav (title, url, target, available, displayorder, highlight, type) VALUES('客服中心', '', 0, 1, 0, 0, 2);
INSERT INTO pre_common_nav (title, url, target, available, displayorder, highlight, type) VALUES('網站導航', '', 0, 1, 0, 0, 2);

INSERT INTO pre_common_template (templateid, name, directory, available, mid, copyright) VALUES (1, '默認模板', 'default', 1, 3, '');

INSERT INTO pre_common_usergroup VALUES(1, 'system', 'private', '管理員', 1);
INSERT INTO pre_common_usergroup VALUES(2, 'member', 'private', '普通會員', 1);

