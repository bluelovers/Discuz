<?php
/**
* Twow 小说1.1.0 Plugin For Discuz X2
* **********************************************************
* Support by 风的翅膀 QQ:404618868
* First publish : 2011-10-25
* Last update : 2011-11-05
*/
defined('IN_DISCUZ') OR exit('Access Denied');
define('TWOW_PLUG_ID', 'twow_novel');
class twow_novel
{
    var $cfg     = array();
    var $fid     = 0;
    var $typeid  = 0;
    var $tid     = 0;
    var $novel     = array();   //小说内容
    var $novelchan = array();   //小说频道/分类/等信息
    
    function twow_novel()
    {
        global $_G;
       
        //系统设置
        $this->cfg = $_G['cache']['plugin'][TWOW_PLUG_ID];
        $this->cfg['novelforums']  = unserialize($this->cfg['novelforums']);
        if (!$this->cfg['novelforums'][0]) {
            showmessage(lang('plugin/twow_novel', 'should_select_one_forum'));
            dexit();
        }
        $this->cfg['defaultforum']  = (int)$this->cfg['defaultforum']>0 ? (int)$this->cfg['defaultforum'] : 0;
        if (count($this->cfg['novelforums'])<2) {
            $this->cfg['is_novelforumlink'] = FALSE;
        }
        $this->cfg['perpage'] = (int)$this->cfg['perpage']>0 ? (int)$this->cfg['perpage'] : 20;
        $this->cfg['excludetypes'] = explode("\n",str_replace("\r",'',$this->cfg['excludetypes']));
        if ($this->cfg['excludetypes'][0]) {
            foreach ($this->cfg['excludetypes'] AS $k=>$v) {
                $v = trim($v);
                $this->cfg['excludetypes'][$k] = strpos(':', $v)===FALSE ? (':'.$v) : $v;    
            }
        } else {
            $this->cfg['excludetypes'] = array();
        }
        $this->cfg['feedbacklimit'] = (int)$this->cfg['feedbacklimit']>0 ? (int)$this->cfg['feedbacklimit'] : 5; 
        $this->cfg['TWOW_PLUG_ID']  = TWOW_PLUG_ID;

        //外部参数
        $this->fid    = (int)$_G['gp_fid']>0 ? (int)$_G['gp_fid'] : (in_array($this->cfg['defaultforum'], $this->cfg['novelforums'])?$this->cfg['defaultforum']:$this->cfg['novelforums'][0]);
        $this->typeid = (int)$_G['gp_typeid']>0 ? (int)$_G['gp_typeid'] : 0;
        $this->tid    = (int)$_G['gp_tid']>0 ? (int)$_G['gp_tid'] : 0;
        if ($this->tid) {
            $this->get_novelbrief($this->tid);
            $this->fid = $this->novel['fid'];
        }
               
        //小说全部版块，及分类
        $sql = "SELECT fd.fid, fd.threadtypes, f.name FROM ".DB::table('forum_forumfield')." fd
                LEFT JOIN ".DB::table('forum_forum')." f ON fd.fid=f.fid 
                WHERE fd.fid={$this->fid} OR fd.fid IN ( " . implode(',', $this->cfg['novelforums']) . " ) ";
        $rs  = DB::query($sql); 
        while ($row = DB::fetch($rs)) {
            $this->novelchan['forumnames'][$row['fid']]   = $row['name'];                     //小说全部版块名称
            $this->novelchan['types_all'][$row['fid']][0] = lang('plugin/twow_novel', 'all'); //小说版块的全部分类
            $row['types'] = unserialize($row['threadtypes']); 
            if (is_array($row['types']['types'])) {
                foreach ($row['types']['types'] AS $typeid=>$typename) {
                    if ($this->is_excludetype($row['name'].':'.$typename)) {
                        continue;
                    }
                    $this->novelchan['types_all'][$row['fid']][$typeid] = $typename;   
                }
            }    
        }
        $this->novelchan['forumnum'] = count($this->novelchan['forumnames']);         //小说的版块的数量 
        $this->novelchan['cuformname'] = $this->novelchan['forumnames'][$this->fid];    //当前版块名称
        $this->novelchan['types']    = $this->novelchan['types_all'][$this->fid];     //当前版块内的分类
        $this->novelchan['typenum']  = count($this->novelchan['types'])-1;            //当前版块分类数 
    }

    function run()
    {
        global $_G;
       
        $action = 'on_' . ($_G['gp_do'] ? $_G['gp_do'] : 'defaction');
        if (method_exists($this, $action)) {
            $this->$action();
        } else {
            showmessage(lang('plugin/twow_novel', 'no_such_action'));
        }      
    }
    
    function on_defaction()
    {
        //if ($this->cfg['indexpagestyle']==1) {
        //    $this->on_index();
        //} else {
            $this->on_listnovel();
        //}
    }

    function on_listnovel()
    {
        global $_G;
     
        $cfg        = &$this->cfg;
        $novelchan  = &$this->novelchan;
       
        $fid        = &$this->fid;
        $typeid     = &$this->typeid;
        $page       = (int)$_G['gp_page']>0 ? (int)$_G['gp_page'] : 1;
        
        $navtitle   = $cfg['channelname'];
        
        $sql_where = " AND t.fid='{$fid}' ".$this->sql_build_types('AND t.typeid', $typeid);
        //小说数量
        $sql   = "SELECT COUNT(1) AS total FROM ".DB::table('forum_thread')." t
                  WHERE 1=1 " . $sql_where . " AND displayorder=0";        
        $novel_num = DB::result(DB::query($sql), 0);
        $page  = $page > ceil($novel_num/$cfg['perpage']) ? ceil($novel_num/$cfg['perpage']) : $page;
        $page  = $page < 1 ? 1 : $page;
        $pagesplit = multi($novel_num, $cfg['perpage'], $page, "plugin.php?id=".TWOW_PLUG_ID.":novel&amp;fid={$fid}&amp;typeid={$typeid}");

        //小说列表
        $sql = "SELECT t.*, ti.attachment FROM ".DB::table('forum_thread')." t
                LEFT JOIN ".DB::table('forum_threadimage')." ti ON t.tid=ti.tid
                WHERE 1=1 " . $sql_where . " AND t.displayorder=0 ";
        if ($typeid != 0) {
            $navtitle = $noveltypes[$typeid] . $navtitle;
        }
        $start = ($page-1)*$cfg['perpage'];
        $sql  .= " ORDER BY t.lastpost DESC, t.dateline DESC LIMIT {$start},{$cfg['perpage']}";
        $rs    = DB::query($sql);
        $novel_list = array();
        while ($row = DB::fetch($rs)) {
            $row['novelpic'] = trim($row['attachment']) ? $_G['setting']['attachurl']."forum/".$row['attachment'] : '';
            //最新更新章节
            $sql = "SELECT subject, message 
                    FROM ".DB::table('forum_post')." 
                    WHERE tid={$row['tid']} ".$this->sql_build_author('AND authorid',$row['authorid'])." AND first=0  
                    ORDER BY dateline DESC LIMIT 0,1";
            $p = DB::fetch(DB::query($sql));     
            $row['lastchapter'] = $this->get_chaptersubject($p['subject'], $this->html2text($this->clear_bbcode($p['message'])));
            $row['uptime']      = date('Y-m-d', $row['lastpost']);
            $novel_list[]       = $row;
        }

        include template(TWOW_PLUG_ID.':novel');            
    }
    
    function on_chapter()
    {
        global $_G;

        $cfg        = &$this->cfg;
        $novelchan  = &$this->novelchan;
        
        $fid        = &$this->fid;
        $typeid     = &$this->typeid;
        $tid        = &$this->tid;
        $novel      = &$this->novel;
        
        $navtitle   = $novel['subject'].' - '.$cfg['channelname'];
        
        $i   = 1;         
        $chapters['list'] = array();
        $sql = "SELECT * 
                FROM ".DB::table('forum_post')." 
                WHERE tid={$tid} AND first=0 "
                    . $this->sql_build_author('AND authorid', $novel['authorid'])
                    . $this->sql_build_subject('subject') . " 
                ORDER BY pid ASC";
        $rs  = DB::query($sql);
        $chapters['chapternum'] = DB::affected_rows();
        $chapters['textlen']    = 0;
        while ($row = DB::fetch($rs)) {
            $row['page']    = $i;
            $row['message'] = $this->html2text($this->clear_bbcode($row['message'])); 
            $row['subject'] = $this->get_chaptersubject($row['subject'], $row['message']);
            $row['volume']  = $this->get_volume($row['message']);
            $row['message'] = $this->clear_twowtag($row['message']); 
            $row['textlen'] = dstrlen($row['message']);
            $chapters['textlen'] = $chapters['textlen']+$row['textlen'];
            $chapters['list'][$i] = $row;
            $i++;
        }
        $chapters['textlen'] = round($chapters['textlen']/10000, 2);   
        
        //章节列表html
        $cfg['chaptitleperline'] = (int)$cfg['chaptitleperline']>1 ? (int)$cfg['chaptitleperline'] : 5;
        $html_chaptertable = "<table class='chpterlist'  border='0' cellspacing='0' cellpadding='0' width='100%'><tr>\r\n";
        $i    = 0;
        foreach ($chapters['list'] AS $v) {
            if ($i%$cfg['chaptitleperline'] == 0 AND $i != 0) {
                $html_chaptertable .= "</tr></tr>\r\n";    
            }
            if ($v['volume']) {
                $tb_rest = $cfg['chaptitleperline'] - $i%$cfg['chaptitleperline'];
                $tb_rest = $tb_rest==$cfg['chaptitleperline'] ? 0 : $tb_rest;
                for ($j=0; $j<$tb_rest; $j++) {
                    $html_chaptertable .= "<td width='".floor(100/$cfg['chaptitleperline'])."%'><span class='chapteritem'>&nbsp;</span></td>\r\n";    
                }
                $html_chaptertable .= "</tr><tr>\r\n";
                $html_chaptertable .= "<td col='{$cfg['chaptitleperline']}'><span class='volumeitem'>{$v['volume']}</span></td>\r\n";
                $html_chaptertable .= "</tr><tr>\r\n";
                $i = 0;
            }
            $html_chaptertable .= "<td width='".floor(100/$cfg['chaptitleperline'])."%'><span class='chapteritem'><a href='plugin.php?id=".TWOW_PLUG_ID.":novel&amp;do=view&amp;tid={$tid}&page={$v['page']}'>{$v['subject']}</a></a></td>\r\n";
            $i++;
        }
        $tb_rest = $cfg['chaptitleperline'] - $i%$cfg['chaptitleperline'];
        $tb_rest = $tb_rest==$cfg['chaptitleperline'] ? 0 : $tb_rest;
        for ($j=0; $j<$tb_rest; $j++) {
            $html_chaptertable .= "<td width='".floor(100/$cfg['chaptitleperline'])."%'><span class='chapteritem'>&nbsp;</span></td>\r\n";    
        }
        $html_chaptertable .= "</tr></table>\r\n";
                
        DB::query("UPDATE LOW_PRIORITY ".DB::table('forum_thread')." SET views=views+1 WHERE tid='{$tid}'", 'UNBUFFERED');
        
        include template(TWOW_PLUG_ID.':chapter');
    }
    
    function on_view()
    {
        global $_G; 

        $cfg        = &$this->cfg;
        $novelchan  = &$this->novelchan;
        
        $fid        = &$this->fid;
        $typeid     = &$this->typeid;
        $tid        = &$this->tid;
        $novel      = &$this->novel;
        
        $navtitle   = $novel['subject'].' - '.$cfg['channelname'];
        
        $page    = (int)$_G['gp_page']>1 ? (int)$_G['gp_page'] : 1;  
        $sql_where = " WHERE tid={$tid} AND first=0 "
                    .$this->sql_build_author('AND authorid', $novel['authorid'])
                    .$this->sql_build_subject('subject');
        $sql     = "SELECT count(1) AS total FROM ".DB::table('forum_post')." {$sql_where} ";
        $chapter_num = DB::result(DB::query($sql),0);
        $page    = $page > $chapter_num ? $chapter_num : $page;
        $pagesplit = multi($chapter_num, 1, $page, "plugin.php?id=".TWOW_PLUG_ID.":novel&amp;do=view&amp;tid={$tid}");
        
        $start   = $page-1; 
        $sql     = "SELECT * FROM ".DB::table('forum_post')." {$sql_where} ORDER BY pid ASC LIMIT {$start},1 ";
        $chapter = DB::fetch(DB::query($sql));
        $msg     = $this->html2text($this->clear_bbcode($chapter['message']));
        $chapter['subject'] = $this->get_chaptersubject($chapter['subject'], $msg);
        $chapter['volume']  = $this->get_volume($msg);
        $chapter['message'] = $this->clear_twowtag($chapter['message']);
        $chapter['message'] = $this->discuzcode($chapter); 
        $fid     = $chapter['fid'];

        $navtitle = $chapter['subject'].':'.$navtitle;

        $prepage   = "plugin.php?id=".TWOW_PLUG_ID.":novel&amp;do=view&amp;tid={$tid}&amp;page=".($page-1);
        $nextpage  = "plugin.php?id=".TWOW_PLUG_ID.":novel&amp;do=view&amp;tid={$tid}&amp;page=".($page+1);
        $channelpage = "plugin.php?id=".TWOW_PLUG_ID.":novel&amp;fid={$fid}";
        $novelpage   = "plugin.php?id=".TWOW_PLUG_ID.":novel&amp;do=chapter&amp;tid={$tid}";

        include template(TWOW_PLUG_ID.':view');        
    }
    function on_ajax_feedbacklist()
    {
        global $_G;

        $cfg        = &$this->cfg;
        $novelchan  = &$this->novelchan;
        
        $fid        = &$this->fid;
        $typeid     = &$this->typeid;
        $tid        = &$this->tid;
        $novel      = &$this->novel;
        
        //回帖
        $chapters['feedback_list'] = array();
        if ($cfg['is_thisauthor'] OR $cfg['is_mustsubject']) {
            $sql = "SELECT * 
                    FROM ".DB::table('forum_post')." 
                    WHERE tid={$tid} AND first=0 AND authorid<>{$novel['authorid']} "
                     . ($cfg['is_mustsubject'] ? " AND subject='' " : '') . " 
                    ORDER BY pid DESC 
                    LIMIT 0, {$cfg['feedbacklimit']} ";
            $rs  = DB::query($sql);
            while ($row = DB::fetch($rs)) {
                $row['message'] = $this->html2text($this->clear_twowtag($this->clear_bbcode($row['message'])));
                $chapters['feedback_list'][] = $row;    
            }
        }
        include template(TWOW_PLUG_ID.':feedbacklist');
    }
    /**
    * @desc 筛选掉排除分类      
    */
    function is_excludetype($extype)
    {
        if ($this->cfg['excludetypes']) {
            foreach ($this->cfg['excludetypes'] AS $v) {
                if (strpos($extype, $v)!==FALSE) {
                    return TRUE;                
                }
            }
        }
        return FALSE;        
    }
      
    function get_novelbrief($tid)
    {
        global $_G;
        
        $this->novel = '';      
        $sql = "SELECT t.*, p.*, ti.attachment FROM ".DB::table('forum_thread')." t
                LEFT JOIN ".DB::table('forum_threadimage')." ti ON t.tid=ti.tid
                LEFT JOIN ".DB::table('forum_post')." p ON t.tid=p.tid 
                WHERE t.tid='{$tid}' AND p.first=1
                LIMIT 0,1";
        $this->novel = DB::fetch(DB::query($sql));
        $this->novel['message']  = $this->discuzcode($this->novel);
        $this->novel['message']  = preg_replace("#\[attach\](\d+)\[\/attach\]#i", '', $this->novel['message']); 
        $this->novel['message']  = preg_replace("#<!-- chapter start -->(.*)<!-- chapter end -->#i", '', $this->novel['message']);
        $this->novel['novelpic'] = trim($this->novel['attachment']) ? $_G['setting']['attachurl']."forum/".trim($this->novel['attachment']) : '';
        $this->novel['threadpagenum'] = ceil($this->novel['replies']/$_G['setting']['postperpage']);
        
        $this->novel = $this->novel + $this->get_sharenumber($tid);
    }
    function sql_build_novelforums($fieldname, $forumid=0)
    {  
        if ($forumid) {
            return " {$fieldname}={$forumid} ";
        } else {
            return " {$fieldname} IN ( ".implode(',', $this->cfg['novelforums'])." ) ";
        } 
    }
    function sql_build_types($fieldname, $typeid=0)
    {       
        if ($typeid) {
            return " {$fieldname}='{$typeid}' ";
        } else if (is_array($this->novelchan['types'])) {
            return " {$fieldname} IN ( ".implode(',', array_keys($this->novelchan['types']))." ) ";  
        } else {
            return '';
        }
    }
    function sql_build_author($fieldname, $autherid)
    {
        $cfg = &$this->cfg;
        
        if ($cfg['is_thisauthor']) {
            return " {$fieldname}={$autherid} ";
        } else {
            return '';
        }      
    }
    function sql_build_subject($fieldname, $type='AND')
    {
        $cfg = &$this->cfg;
        
        if ($cfg['is_mustsubject']) {
            return " {$type} ( {$fieldname}<>'' OR message LIKE '%[twow_chaptitle:%' ) ";
        } else {
            return '';
        }
    }
    function sql_build_feedback(&$chapters)
    { 
        foreach ($chapters AS $chap) {
            
        }
    }
    function get_volume($message)
    {
        preg_match("#\[twow_volume:(.*)\]#i", $message, $maches);
        if ($maches[1]) {
            return strip_tags($maches[1]);
        } else {
            return '';
        }    
    }
    function get_chaptersubject($subject, $message)
    {
        $cfg = &$this->cfg;
        
        if ($subject) {
            return strip_tags($subject);
        } else {
            preg_match("#\[twow_chaptitle:(.*)\]#i", $message, $maches);
            if ($maches[1]) {
                return strip_tags($maches[1]);
            } else if (!$cfg['is_mustsubject']) {
                $message = cutstr($this->clear_twowtag($message), ((int)$cfg['msg2subject_leng'] ? (int)$cfg['msg2subject_leng'] : 10));
                return $message;
            } else {
                return '';
            }
        }  
    }
    function clear_twowtag($message)
    {
        return trim(preg_replace("#\[twow_(.*)\]#i", '', $message));
    }
    function discuzcode($post)
    {
        global $_G;

        require_once libfile('function/discuzcode');
        
        $post['message'] = discuzcode($post['message'], $post['smileyoff'] & 0, 0, $post['htmlon'] & 1, $_G['forum']['allowsmilies'] | 1, 1, ($_G['forum']['allowimgcode'] && $_G['setting']['showimages'] ? 1 : 0), $_G['forum']['allowhtml'], ($_G['forum']['jammer'] && $post['authorid'] != $_G['uid'] ? 1 : 0), 0, $post['authorid'], $_G['cache']['usergroups'][$post['groupid']]['allowmediacode'] && $_G['forum']['allowmediacode'], $post['pid'], $_G['setting']['lazyload']);
        $post['message'] = preg_replace("#\[attach\](\d+)\[\/attach\]#i", '', $post['message']);
        
        return $post['message'];        
    }
    function clear_bbcode($message)
    {
        $message = preg_replace(array(
            "/\s?\[code\](.+?)\[\/code\]\s?/is",
            "/attach:\/\/(\d+)\.?(\w*)/i",
            "/ed2k:\/\/(.+?)\//",
            "/\[url=[^\r\n\[\"']+?\](.+?)\[\/url\]/is",
            "/\[email(=([a-z0-9\-_.+]+)@([a-z0-9\-_]+[.][a-z0-9\-_.]+))?\](.+?)\[\/email\]/is",
            "/\[table(?:=(\d{1,4}%?)(?:,([\(\)%,#\w ]+))?)?\]\s*(.+?)\s*\[\/table\]/is",
            "/\s?\[quote\][\n\r]*(.+?)[\n\r]*\[\/quote\]\s?/is",
            "/\s*\[free\][\n\r]*(.+?)[\n\r]*\[\/free\]\s*/is",
            "/\[media=([\w,]+)\]\s*([^\[\<\r\n]+?)\s*\[\/media\]/is",
            "/\[audio(=1)*\]\s*([^\[\<\r\n]+?)\s*\[\/audio\]/is",
            "/\[flash(=(\d+),(\d+))?\]\s*([^\[\<\r\n]+?)\s*\[\/flash\]/is",
            "#\[attach\](\d+)\[\/attach\]#i",
            "#\[img(=(\d+),(\d+))?\]\s*([^\[\<\r\n]+?)\s*\[\/img\]#is",  
            ), '', $message);       
        $message = str_replace(
            array(
            '[/color]', '[/backcolor]', '[/size]', '[/font]', '[/align]', '[b]', '[/b]', '[s]', '[/s]', '[hr]', '[/p]',
            '[i=s]', '[i]', '[/i]', '[u]', '[/u]', '[list]', '[list=1]', '[list=a]',
            '[list=A]', "\r\n[*]", '[*]', '[/list]', '[indent]', '[/indent]', '[/float]',
            '[/hide]',
            ), '', 
            preg_replace(array(
            "/\[color=([#\w]+?)\]/i",
            "/\[color=(rgb\([\d\s,]+?\))\]/i",
            "/\[backcolor=([#\w]+?)\]/i",
            "/\[backcolor=(rgb\([\d\s,]+?\))\]/i",
            "/\[size=(\d{1,2}?)\]/i",
            "/\[size=(\d{1,2}(\.\d{1,2}+)?(px|pt)+?)\]/i",
            "/\[font=([^\[\<]+?)\]/i",
            "/\[align=(left|center|right)\]/i",
            "/\[p=(\d{1,2}|null), (\d{1,2}|null), (left|center|right)\]/i",
            "/\[float=left\]/i",
            "/\[float=right\]/i",
            "/\[hide\]/i"
            ), '', $message));
            
        return $message;
    }
    function html2text($str)
    {
        return trim(html_entity_decode(strip_tags(str_replace('&nbsp;', '', $str))));
    }
    /**
    * @desc 分享/收藏统计
    */
    function get_sharenumber($id)
    {
        $count['sharetimes'] = DB::result_first("SELECT COUNT(*) FROM ".DB::table('home_share')." WHERE itemid='{$id}' AND type='thread'");
        $count['favtimes'] = DB::result_first("SELECT COUNT(*) FROM ".DB::table('home_favorite')." WHERE id='{$id}' AND idtype='thread'");
        
        return $count;    
    }
}
$twow_novel_o = new twow_novel();
$twow_novel_o->run();
?>