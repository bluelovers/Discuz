<?php
//----基本设置start
$pdnovel['subnovelid'] = 'floor(<{novelid}>/1000)'; //小说子序号运算方法
$pdnovel['subchapterid'] = ''; //小说章节子序号运算方法
//----基本设置end

//----小说信息页面采集规则start
$pdnovel['url'] = 'http://www.uutxt.com/<{novelid}>.html'; //小说信息页地址
$pdnovel['name'] = '/tbooktitle = \'(.+?)\'/i'; //小说名称采集规则
$pdnovel['author'] = '/>作　　者：(.+?)<\/li>/i'; //小说作者采集规则
$pdnovel['cat'] = '/<b><a href="\/Book\/LN\/.+?">(.+?)<\/a><\/b>/i'; //小说分类采集规则
$pdnovel['cover'] = '/<img src="(.+?)" class="imgborder"/i'; //小说封面采集规则
$pdnovel['coverfilter'] = 'http://pic.uutxt.com/Images/NoImg.gif'; //过滤的封面图片
$pdnovel['keyword'] = ''; //小说关键字采集规则
$pdnovel['intro'] = '/>作品简介].*?<li class="h1">(.*?)<\/li>/is'; //小说简介采集规则
$pdnovel['notice'] = ''; //作者公告采集规则
$pdnovel['full'] = '/>写作进程：(.+?)&/i'; //连载状态采集规则
$pdnovel['fullnovel'] = '完结'; //完本标志
$pdnovel['permission'] = '0'; //小说授权
$pdnovel['first'] = '0'; //小说首发

$pdnovel['catid']['default'] = '96'; //默认分类替换
$pdnovel['catid']['东方玄幻'] = '17';
$pdnovel['catid']['玄幻魔法'] = '25';
$pdnovel['catid']['远古神话'] = '20';
$pdnovel['catid']['异世大陆'] = '19';
$pdnovel['catid']['异界大陆'] = '19';
$pdnovel['catid']['玄幻异界'] = '18';
$pdnovel['catid']['奇幻玄幻'] = '21';
$pdnovel['catid']['西方奇幻'] = '21';
$pdnovel['catid']['吸血家族'] = '22';
$pdnovel['catid']['魔法校园'] = '25';
$pdnovel['catid']['奇幻魔法'] = '25';
$pdnovel['catid']['奇幻架空'] = '21';
$pdnovel['catid']['魔幻笔调'] = '25';
$pdnovel['catid']['新新武侠'] = '28';
$pdnovel['catid']['武侠修真'] = '26';
$pdnovel['catid']['浪子异侠'] = '27';
$pdnovel['catid']['传统武侠'] = '26';
$pdnovel['catid']['历史武侠'] = '26';
$pdnovel['catid']['古典武侠'] = '26';
$pdnovel['catid']['谐趣武侠'] = '27';
$pdnovel['catid']['武侠仙侠'] = '29';
$pdnovel['catid']['仙侠修真'] = '29';
$pdnovel['catid']['仙侠异能'] = '29';
$pdnovel['catid']['仙侠奇侠'] = '29';
$pdnovel['catid']['奇幻修真'] = '30';
$pdnovel['catid']['现代修真'] = '31';
$pdnovel['catid']['古典仙侠'] = '29';
$pdnovel['catid']['白领生涯'] = '35';
$pdnovel['catid']['爱在职场'] = '35';
$pdnovel['catid']['都市生活'] = '40';
$pdnovel['catid']['纵横四海'] = '33';
$pdnovel['catid']['光怪陆离'] = '33';
$pdnovel['catid']['青春成长'] = '42';
$pdnovel['catid']['青春幻想'] = '42';
$pdnovel['catid']['菁菁校园'] = '42';
$pdnovel['catid']['青春校园'] = '42';
$pdnovel['catid']['都市恋曲'] = '44';
$pdnovel['catid']['都市情感'] = '44';
$pdnovel['catid']['豪门世家'] = '34';
$pdnovel['catid']['商海沉浮'] = '34';
$pdnovel['catid']['都市小说'] = '33';
$pdnovel['catid']['官场沉浮'] = '36';
$pdnovel['catid']['宦海风云'] = '36';
$pdnovel['catid']['激情生活'] = '37';
$pdnovel['catid']['都市言情'] = '37';
$pdnovel['catid']['激情'] = '37';
$pdnovel['catid']['都市激战'] = '34';
$pdnovel['catid']['品味人生'] = '40';
$pdnovel['catid']['现代都市'] = '33';
$pdnovel['catid']['都市重生'] = '45';
$pdnovel['catid']['都市异能'] = '45';
$pdnovel['catid']['异术超能'] = '45';
$pdnovel['catid']['虐恋残心'] = '108';
$pdnovel['catid']['异国恋歌'] = '108';
$pdnovel['catid']['血缘羁绊'] = '108';
$pdnovel['catid']['穿越架空'] = '110';
$pdnovel['catid']['穿越时空'] = '110';
$pdnovel['catid']['前世今生'] = '110';
$pdnovel['catid']['穿越女尊'] = '110';
$pdnovel['catid']['转世重生'] = '110';
$pdnovel['catid']['日韩风尚'] = '120';
$pdnovel['catid']['耽美其他'] = '111';
$pdnovel['catid']['女频耽美'] = '111';
$pdnovel['catid']['纯爱耽美'] = '111';
$pdnovel['catid']['变身情缘'] = '108';
$pdnovel['catid']['校园文学'] = '42';
$pdnovel['catid']['校园言情'] = '42';
$pdnovel['catid']['浪漫校园'] = '42';
$pdnovel['catid']['现代言情'] = '108';
$pdnovel['catid']['快意江湖'] = '113';
$pdnovel['catid']['恩怨情仇'] = '113';
$pdnovel['catid']['宫闱情仇'] = '109';
$pdnovel['catid']['王朝争霸'] = '109';
$pdnovel['catid']['千千心结'] = '111';
$pdnovel['catid']['网络情缘'] = '108';
$pdnovel['catid']['网络故事'] = '108';
$pdnovel['catid']['军事历史'] = '54';
$pdnovel['catid']['历史军事'] = '54';
$pdnovel['catid']['历史穿越'] = '46';
$pdnovel['catid']['架空历史'] = '46';
$pdnovel['catid']['西方传奇'] = '53';
$pdnovel['catid']['三国梦想'] = '48';
$pdnovel['catid']['历史传记'] = '54';
$pdnovel['catid']['历史小说'] = '54';
$pdnovel['catid']['机器时代'] = '69';
$pdnovel['catid']['军事战争'] = '55';
$pdnovel['catid']['战争风云'] = '55';
$pdnovel['catid']['星际战争'] = '68';
$pdnovel['catid']['战争幻想'] = '67';
$pdnovel['catid']['特种军旅'] = '58';
$pdnovel['catid']['现代战争'] = '71';
$pdnovel['catid']['网游动漫'] = '60';
$pdnovel['catid']['游戏竞技'] = '61';
$pdnovel['catid']['网游竟技'] = '61';
$pdnovel['catid']['网络游戏'] = '61';
$pdnovel['catid']['网游竞技'] = '61';
$pdnovel['catid']['电子竞技'] = '61';
$pdnovel['catid']['幻想网游'] = '60';
$pdnovel['catid']['虚拟网游'] = '60';
$pdnovel['catid']['游戏生涯'] = '59';
$pdnovel['catid']['体育竞技'] = '63';
$pdnovel['catid']['足球运动'] = '65';
$pdnovel['catid']['篮球运动'] = '64';
$pdnovel['catid']['科幻小说'] = '72';
$pdnovel['catid']['科幻动漫'] = '73';
$pdnovel['catid']['军事科幻'] = '71';
$pdnovel['catid']['科幻冒险'] = '74';
$pdnovel['catid']['科幻灵异'] = '74';
$pdnovel['catid']['灵异科幻'] = '74';
$pdnovel['catid']['科幻世界'] = '74';
$pdnovel['catid']['恐怖灵异'] = '24';
$pdnovel['catid']['灵异恐怖'] = '24';
$pdnovel['catid']['恐怖惊悚'] = '24';
$pdnovel['catid']['少女悬疑'] = '115';
$pdnovel['catid']['推理灵异'] = '115';
$pdnovel['catid']['推理侦探'] = '115';
$pdnovel['catid']['侦探推理'] = '77';
$pdnovel['catid']['侦探冒险'] = '78';
$pdnovel['catid']['冒险推理'] = '78';
$pdnovel['catid']['灵异鬼怪'] = '75';
$pdnovel['catid']['灵异神怪'] = '75';
$pdnovel['catid']['骇客时空'] = '70';
$pdnovel['catid']['古风古韵'] = '82';
$pdnovel['catid']['古风雅韵'] = '82';
$pdnovel['catid']['名著读物'] = '85';
$pdnovel['catid']['美文名著'] = '85';
$pdnovel['catid']['中国名著'] = '85';
$pdnovel['catid']['名著'] = '85';
$pdnovel['catid']['出版书籍'] = '87';
$pdnovel['catid']['出版小说'] = '87';
$pdnovel['catid']['美文散文'] = '79';
$pdnovel['catid']['散文'] = '79';
$pdnovel['catid']['随笔'] = '79';
$pdnovel['catid']['散文诗词'] = '82';
$pdnovel['catid']['诗词散曲'] = '82';
$pdnovel['catid']['现代诗歌'] = '82';
$pdnovel['catid']['休闲美文'] = '79';
$pdnovel['catid']['同人小说'] = '99';
$pdnovel['catid']['小说同人'] = '99';
$pdnovel['catid']['游戏同人'] = '103';
$pdnovel['catid']['动漫同人'] = '97';
$pdnovel['catid']['武侠同人'] = '98';
$pdnovel['catid']['美文同人'] = '102';
$pdnovel['catid']['札记'] = '88';
$pdnovel['catid']['学习'] = '89';
$pdnovel['catid']['其他'] = '96';
$pdnovel['catid']['法律'] = '90';
$pdnovel['catid']['时尚'] = '91';
$pdnovel['catid']['英语'] = '92';
$pdnovel['catid']['电脑'] = '93';
$pdnovel['catid']['经管'] = '94';
$pdnovel['catid']['少儿'] = '95';
$pdnovel['catid']['短篇小说'] = '84';
$pdnovel['catid']['作家访谈'] = '83';
$pdnovel['catid']['笑话'] = '86';
$pdnovel['catid']['杂文笔札'] = '80';
$pdnovel['catid']['网络杂文'] = '80';
$pdnovel['catid']['童话寓言'] = '81';
$pdnovel['catid']['浪漫言情'] = '108'; //分类替换
//----小说信息页面采集规则end

//----小说目录页面采集规则start
$pdnovel['chapterurl'] = 'http://www.uutxt.com/BookHtml/<{subnovelid}>/<{novelid}>/Index.shtml'; //小说目录页地址
$pdnovel['lastchapter'] = '/<{lastid}>\.shtml".+?<\/li>(.+)<\/html>/is'; //最后章节
$pdnovel['chapter'] = '/<li><a href="(\d+).shtml".*?>(.+)<\/a>/is'; //章节名称和章节ID采集规则
$pdnovel['chapter'] = '/更新字数:\d+">(.+?)<\/a><\/li>/i';
$pdnovel['chapterid'] = '/<li><a href="(\d+).shtml" title="更新时间:/i';
$pdnovel['volume'] = '/<div id="ClassTitle">最新章节:(.+?)\/文字版\/手打版</div>/i';
//----小说目录页面采集规则end

//----章节内容页面采集规则start
$pdnovel['readurl'] = 'http://files.uutxt.com/<{subnovelid}>/<{novelid}>/<{chapterid}>.dec'; //章节阅读地址
$pdnovel['content'] = '/var nbtxt="(.*)";/is'; //章节内容采集规则
$pdnovel['contentfilter'] = '';
//----章节内容页面采集规则end

//----页面列表格式start
$pdnovel['pageurl'] = 'http://www.uutxt.com/Book/ShowBookList.aspx?page=<{pageid}>'; //列表页采集地址
$pdnovel['page'] = '/\[目录\]<\/font>.*?<a href="\/(\d+).html"/i'; //列表页采集规则
//----页面列表格式end
?>