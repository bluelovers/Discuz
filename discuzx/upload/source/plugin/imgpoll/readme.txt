插件名称: 图片投票插件  
插件来源: 原创插件  
适用版本: Discuz! X2  
语言编码: GBK简体   
最后更新时间: 2011-07-10  
插件作者: star6710  
版权所属: Discuz特殊主题站  
支持网站: http://www.thread8.com  
插件简介: 在Discuz原有投票主题基础之上增加了图片选项。  

本插件经测试可以在Discuz! X2 论坛之上正常安装。

为了维护数据库的冗余性，保证在删除图片投票主题帖子后无数据库冗余信息，请站长朋友们将source\function\function_delete.php
文件进行替换或更改。
具体更改项目：
function deletethread($tids, $membercount = false, $credit = false, $ponly = false) {
……
foreach(array('forum_forumrecommend', 'forum_polloption', 'forum_poll', 'forum_activity', 'forum_activityapply', 'forum_debate',
		'forum_debatepost', 'forum_threadmod', 'forum_relatedthread', 'forum_typeoptionvar',
		'forum_postposition', 'forum_poststick', 'forum_pollvoter', 'forum_threadimage') as $table) {
		DB::delete($table, "tid IN ($tids)");
	}
……
}

欢迎各位站长到Discuz特殊主题站(www.thread8.com)交流插件使用、开发等建站信息。