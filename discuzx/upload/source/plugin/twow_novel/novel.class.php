<?php
//全局嵌入点类（必须存在）
class plugin_twow_novel
{
    function plugin_twow_novel()
    {
        global $_G;
        
        if (!$this->cfg) {
            $this->cfg = $_G['cache']['plugin']['twow_novel'];
            $this->cfg['novelforums']  = unserialize($this->cfg['novelforums']);    
        }
    }
}
//脚本嵌入点类
class plugin_twow_novel_forum extends plugin_twow_novel
{
    function forumdisplay_forumaction_output ()
    {
        global $_G;
        
        if (!$this->cfg['novelforums'][0]) {
            return '';
        }
        if (in_array($_G['gp_fid'], $this->cfg['novelforums'])) {
            return "&nbsp;<span style='color:#CCCCCC;'>|</span>&nbsp;&nbsp;<a href='./plugin.php?id=twow_novel:novel&amp;fid=".$_G['gp_fid']."&amp;typeid=".$_G['gp_typeid']."'><img src='./source/plugin/twow_novel/images/book.jpg' style='width:18px;height:18px;'>&nbsp;小说模式</a>";
        } else {
            return '';
        }
    }
    function viewthread_title_extra_output()
    {
        global $_G;
        
        $fid = $_G['thread']['fid'];
        $tid = (int)$_G['gp_tid'];
        
        if (!$this->cfg['novelforums'][0]) {
            return '';
        }
        if (in_array($fid, $this->cfg['novelforums'])) {
            return "&nbsp;<span style='color:#CCCCCC;'>|</span>&nbsp;&nbsp;<a href='./plugin.php?id=twow_novel:novel&amp;do=chapter&amp;tid=".$tid."'><img src='./source/plugin/twow_novel/images/book.jpg' style='width:18px;height:18px;'>&nbsp;小说模式</a>";
        } else {
            return '';
        }        
    }
}
?>
