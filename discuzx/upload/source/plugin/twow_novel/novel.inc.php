<?php
/**
* Twow 小说1.0 Plugin For Discuz x2
* **********************************************************
* Support by 风的翅膀 QQ:404618868
* First publish : 2011-10-25
* Last update : 2011-10-26
*/
defined('IN_DISCUZ') OR exit('Access Denied');

class twow_novel
{
    var $cfg     = array();
    var $typeid  = 0;
    var $tid     = 0;
    var $novel   = array();
    var $noveltypes  = array();
    var $sql_intypes = '';
    
    function twow_novel()
    {
        global $_G;
     
        require_once './source/function/function_home.php';
        
        $this->cfg = $_G['cache']['plugin']['twow_novel'];
        $this->cfg['perpage'] = (int)$this->cfg['perpage'];
        $this->cfg['perpage'] = $this->cfg['perpage']>0 ? $this->cfg['perpage'] : 20;
        $this->cfg['excludetypes'] = explode("\n",str_replace("\r",'',$this->cfg['excludetypes']));
        
        $this->typeid  = (int)$_G['gp_typeid'] ? (int)$_G['gp_typeid'] : 0;
        $this->tid     = (int)$_G['gp_tid']    ? (int)$_G['gp_tid']    : 0;
        //小说简介
        if ($this->tid) {
            $this->get_novelbrief($this->tid);
        }
        
        //小说分类专题
        $forumfield = DB::fetch(DB::query("SELECT threadtypes FROM ".DB::table('forum_forumfield')." where fid='{$this->cfg['novelforum']}' LIMIT 0,1"));
        $forumfield['threadtypes'] = unserialize($forumfield['threadtypes']);
        if (is_array($forumfield['threadtypes']['types'])) {
            foreach ($forumfield['threadtypes']['types'] AS $typeid=>$typename) {
                if (in_array($typename, $this->cfg['excludetypes'])) {
                    continue;
                } else {
                    $this->noveltypes[$typeid] = $typename;
                }
            }
        }
        unset($forumfield);
    }

    function run()
    {
        global $_G;
       
        $action = 'on_' . ($_G['gp_do'] ? $_G['gp_do'] : 'defaction');
        if (method_exists($this, $action)) {
            $this->$action();
        } else {
            dexit('no_such_action');
        }      
    }
    
    function on_defaction()
    {
        $this->on_listnovel();
    }
    
    function on_listnovel()
    {
        global $_G;
     
        $cfg        = &$this->cfg;
        $typeid     = &$this->typeid;
        $noveltypes = &$this->noveltypes;
        $page       = (int)$_G['gp_page']>0 ? (int)$_G['gp_page'] : 1;
        $navtitle   = lang('plugin/twow_novel', 'novel_channel');
 
        //小说数量
        $sql   = "SELECT COUNT(1) AS total FROM ".DB::table('forum_thread')." WHERE fid='{$cfg['novelforum']}' AND displayorder=0".($typeid==0?$this->sql_build_intypes('AND typeid'):" AND typeid='{$typeid}'");        
        $novel_num = DB::result(DB::query($sql), 0);
        $page  = $page > ceil($novel_num/$cfg['perpage']) ? ceil($novel_num/$cfg['perpage']) : $page;
        $page  = $page < 1 ? 1 : $page;
        $pagesplit = multi($novel_num, $cfg['perpage'], $page, "plugin.php?id=twow_novel:novel&typeid={$typeid}");

        //小说列表
        $sql = "SELECT t.*, ti.attachment FROM ".DB::table('forum_thread')." t
                LEFT JOIN ".DB::table('forum_threadimage')." ti ON t.tid=ti.tid
                WHERE t.fid='{$cfg['novelforum']}' AND t.displayorder=0 ";
        if ($typeid != 0) {
            $sql     .= " And t.typeid='{$typeid}' ";
            $navtitle = $noveltypes[$typeid] . $navtitle;
        } else {
            $sql     .= $this->sql_build_intypes('AND t.typeid');
        }
        $start = ($page-1)*$cfg['perpage'];
        ckstart($start, $cfg['perpage']);
        $sql  .= " ORDER BY t.lastpost DESC, t.dateline DESC LIMIT {$start},{$cfg['perpage']}";
        $rs    = DB::query($sql);
        $novel_list = array();
        while ($row = DB::fetch($rs)) {
            $row['novelpic'] = trim($row['attachment']) ? $_G['setting']['attachurl']."forum/".$row['attachment'] : '';
            //最新更新章节
            $sql = "SELECT * 
                    FROM ".DB::table('forum_post')." 
                    WHERE tid={$row['tid']} ".$this->sql_build_andauthor('AND authorid',$row['authorid'])." AND first=0  
                    ORDER BY dateline DESC LIMIT 0,1";
            $post = DB::fetch(DB::query($sql));     
            $row['lastchapter'] = $this->get_chaptersubject($p['subject'], $this->discuzcode($post));
            $row['uptime']      = date('Y-m-d', $row['lastpost']);
            $novel_list[]       = $row;
        }

        include template('twow_novel:novel');            
    }
    
    function on_chapter()
    {
        global $_G;

        $cfg        = &$this->cfg;
        $typeid     = &$this->typeid;
        $noveltypes = &$this->noveltypes;
        $novel      = &$this->novel;
        $tid        = &$this->tid; 
        $navtitle   = $novel['subject'].' - '.lang('plugin/twow_novel', 'novel_channel');
        
        $i   = 1;         
        $chapterlist = array();
        $sql = "SELECT * 
                FROM ".DB::table('forum_post')." 
                WHERE fid='{$cfg['novelforum']}' AND tid={$tid} "
                    . $this->sql_build_andauthor('AND authorid', $novel['authorid']) . " AND first=0 "
                    . $this->sql_build_andsubject('AND subject') . " 
                ORDER BY pid ASC";
        $rs  = DB::query($sql);
        while ($row = DB::fetch($rs)) {
            $row['page']    = $i;
            $row['subject'] = $this->get_chaptersubject($row['subject'], $this->discuzcode($row));
            $chapterlist[] = $row;
            $i++;
        }
        
        DB::query("UPDATE LOW_PRIORITY ".DB::table('forum_thread')." SET views=views+1 WHERE fid='{$cfg['novelforum']}' and tid='{$tid}'", 'UNBUFFERED');
        
        include template('twow_novel:chapter');
    }
    
    function on_view()
    {
        global $_G; 

        $cfg        = &$this->cfg;
        $typeid     = &$this->typeid;
        $noveltypes = &$this->noveltypes;
        $novel      = &$this->novel; 
        $tid        = &$this->tid;
        
        $page    = (int)$_G['gp_page']>1 ? (int)$_G['gp_page'] : 1;  
        $sql_where = " WHERE fid={$cfg['novelforum']} AND tid={$tid} "
                    .$this->sql_build_andauthor('AND authorid', $novel['authorid'])." AND first=0 "
                    .$this->sql_build_andsubject('AND subject');
        $sql     = "SELECT count(1) AS total FROM ".DB::table('forum_post')." {$sql_where} ";
        $chapter_num = DB::result(DB::query($sql),0);
        $page    = $page > $chapter_num ? $chapter_num : $page;
        $pagesplit = multi($chapter_num, 1, $page, "plugin.php?id=twow_novel:novel&amp;do=view&amp;tid={$tid}");
        
        $start   = $page-1; 
        $sql     = "SELECT * FROM ".DB::table('forum_post')." {$sql_where} ORDER BY pid ASC LIMIT {$start},1 ";
        $chapter = DB::fetch(DB::query($sql));
        $chapter['message'] = $this->discuzcode($chapter);
        $chapter['subject'] = $this->get_chaptersubject($chapter['subject'], $chapter['message']);

        $navtitle = $novel['subject'].':'.$chapter['subject'].' - '.lang('plugin/twow_novel', 'novel_channel');

        $prepage   = "plugin.php?id=twow_novel:novel&amp;do=view&amp;tid={$tid}&amp;page=".($page-1);
        $nextpage  = "plugin.php?id=twow_novel:novel&amp;do=view&amp;tid={$tid}&amp;page=".($page+1);
        $novelpage = "plugin.php?id=twow_novel:novel&amp;do=chapter&amp;tid={$tid}";

        include template('twow_novel:view');        
    }
    
    function get_novelbrief($tid)
    {
        global $_G;
        
        $this->novel = '';      
        $sql = "SELECT t.*, p.*, ti.attachment FROM ".DB::table('forum_thread')." t
                LEFT JOIN ".DB::table('forum_threadimage')." ti ON t.tid=ti.tid
                LEFT JOIN ".DB::table('forum_post')." p ON t.tid=p.tid 
                WHERE t.fid='{$this->cfg['novelforum']}' AND t.tid='{$tid}' AND p.first=1
                LIMIT 0,1";
        $this->novel = DB::fetch(DB::query($sql));
        $this->novel['message']  = $this->discuzcode($this->novel);
        $this->novel['message']  = preg_replace("#\[attach\](\d+)\[\/attach\]#i", '', $this->novel['message']); 
        $this->novel['message']  = preg_replace("#<!-- chapter start -->(.*)<!-- chapter end -->#i", '', $this->novel['message']);
        $this->novel['novelpic'] = trim($this->novel['attachment']) ? $_G['setting']['attachurl']."forum/".trim($this->novel['attachment']) : '';
    }
    function sql_build_intypes($fieldname)
    {
        if ($this->sql_intypes) {
            return $this->sql_intypes;
        }
        if (is_array($this->noveltypes)) {
            $this->sql_intypes = " {$fieldname} IN ( ".implode(',', array_keys($this->noveltypes + array(0=>0)))." ) ";  
        }
        return $this->sql_intypes;
    }
    function sql_build_andauthor($fieldname, $autherid)
    {
        $cfg = &$this->cfg;
        
        if ($cfg['is_thisauthor']) {
            return " {$fieldname}={$autherid} ";
        } else {
            return '';
        }      
    }
    function sql_build_andsubject($fieldname)
    {
        $cfg = &$this->cfg;
        
        if ($cfg['is_mustsubject']) {
            return " {$fieldname}<>'' ";
        } else {
            return '';
        }
    }
    function get_chaptersubject($subject, $message)
    {
        $cfg = &$this->cfg;
        
        if ($subject) {
            return strip_tags($subject);
        } else if (!$cfg['is_mustsubject']) {          
            require_once libfile('function/post');           
            $message = preg_replace("#\[attach\](\d+)\[\/attach\]#i", '', $message);
            $message = str_replace('&nbsp;', '', $message);
            $message = html_entity_decode(strip_tags(trim($message)));
            $message = messagecutstr($message, ((int)$cfg['msg2subject_leng'] ? (int)$cfg['msg2subject_leng'] : 10));
            return $message;
        } else {
            return '';
        }   
    }
    function discuzcode($post)
    {
        global $_G;

        require_once libfile('function/discuzcode');
        
        $post['message'] = discuzcode($post['message'], $post['smileyoff'], 0, $post['htmlon'] & 1, $_G['forum']['allowsmilies'], 1, ($_G['forum']['allowimgcode'] && $_G['setting']['showimages'] ? 1 : 0), $_G['forum']['allowhtml'], ($_G['forum']['jammer'] && $post['authorid'] != $_G['uid'] ? 1 : 0), 0, $post['authorid'], $_G['cache']['usergroups'][$post['groupid']]['allowmediacode'] && $_G['forum']['allowmediacode'], $post['pid'], $_G['setting']['lazyload']);
        $post['message'] = preg_replace("#\[attach\](\d+)\[\/attach\]#i", '', $post['message']); 
        
        return $post['message'];        
    }
}
$twow_novel_o = new twow_novel();
$twow_novel_o->run();
?>