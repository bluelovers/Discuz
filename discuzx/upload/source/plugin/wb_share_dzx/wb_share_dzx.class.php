<?php

/**
  +--------------------------------------------------
  |discuz!x2.0 插件： 微博控
  +--------------------------------------------------
  |author：luofei614<www.3g4k.com>
  +--------------------------------------------------
 * 用户能绑定新浪、腾讯、网易、搜狐的微博。
 * 绑定后，用户以后不需要再登录微博，就可以向多个微博同步信息。
  + -------------------------------------------------
 * 重新封装了OAuth类，四个微博使用共同的接口，减少了冗余代码，同时避免了类名冲突。
 */
if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class plugin_wb_share_dzx {

    //showmessage的参数
    protected $param;

    /**
     * 网站头部“个人设置”嵌入点
     * 显示“微博控”设置连接。
     */
    function global_usernav_extra1() {
        global $_G;
        if (isset($_G['gp_mod']) && $_G['gp_mod'] == 'space') {
            $addStyle = 'vertical-align:-5px;margin-top: 2px;';
        } else {
            $addStyle = 'vertical-align:-5px;';
        }
        if($_G['cache']['plugin']['wb_share_dzx']['showtype']){
        return <<<EOF
			<span class="pipe">|</span>
			<a href='home.php?mod=spacecp&ac=plugin&id=wb_share_dzx:actionscp'><img style="{$addStyle}" src="source/plugin/wb_share_dzx/style/img/wbk_btn.png" /></a>
			&nbsp;
EOF;
		}else{
         return "<a href='home.php?mod=spacecp&ac=plugin&id=wb_share_dzx:actionscp'>&nbsp;".lang("plugin/wb_share_dzx","sharesetting")."</a>";
	    }
    }

    /**
     * 获取当前需要执行的操作（论坛发帖、论坛回帖、更新记录、相册上传、日志发布）
     * 判断方法：（1）先循环当前用户设置的需要同步信息的操作。
     * （2）用户设置的操作， 用submitcheck函数判断是否为当前操作。
     * @return string  当前操作
     */
    function _getaction() {
        global $_G;
        $actions = DB::result_first("select actions from " . DB::table("share_actions") . " where uid='{$_G['uid']}'");
        foreach (explode("|", $actions) as $action) {
            if (submitcheck($action)) {
                return $action;
            }
        }
    }

    /**
     * 执行操作。
     * 先获得当前操作（调用_getaction）
     * 再执行对应方法，方法名： 下划线+操作名称
     */
    function _run() {
        $action = $this->_getaction();
        $method = "_" . $action;
        if (method_exists($this, $method)) {
            $this->$method();
        }
    }

}

/**
 * 论坛嵌入类
 * 监控论坛发帖、回帖
 */
class plugin_wb_share_dzx_forum extends plugin_wb_share_dzx {

    /**
     * 在论坛发帖、回帖处理后执行showmessage时会被调用。
     */
    function post_message($param) {
        $this->param = $param; //存储showmessage参数。
        $this->_run(); //执行
    }

    /**
     * 论坛发帖处理
     * 帖子的标题作为微博信息。
     * 生成帖子的url
     * 如果帖子中有图片，第一张图片作为微博图片。
     */
    function _topicsubmit() {
        global $_G;
        $param = &$this->param['param'];
        if ($param[0] == "post_newthread_succeed") {
            //私密板块不发布微博
            $secretforums=unserialize($_G['cache']['plugin']['wb_share_dzx']['secretforums']);
            if(in_array($param[2]['fid'],$secretforums)){
                return ;
            }
            require_once 'class/Share.class.php';
            //获得文章地址
            $url = ROOT_URL . "/" . $param[1];
            //获得标题
            $msg = $_POST['subject'] . $url;
            $img = '';
            if (!empty($_G['forum_attachexist'])) {
                $firstaid = DB::result_first("SELECT aid FROM " . DB::table(getattachtablebytid($param[2]['tid'])) . " WHERE pid='{$param[2]['pid']}' AND dateline>'0' AND isimage='1' ORDER BY dateline LIMIT 1");
                if ($firstaid) {
                    //获得图片
                    $img = ROOT_URL . "/" . getforumimg($firstaid);
                }
            }
            $share = new Share();
            $share->sharemsg($msg, $img);
        }
    }

    /**
     * 论坛回复处理
     * 将回复内容作为微博内容。
     * 生成回复的url
     */
    function _replysubmit() {
        $param = &$this->param['param'];
        if ($param[0] == "post_reply_succeed") {
            require_once 'class/Share.class.php';
            $url = ROOT_URL . "/" . $param[1];
            $msg = cutstr(strip_tags($_POST['message']), 270) . $url;
            $share = new Share();
            $share->sharemsg($msg);
        }
    }

}

/**
 * 家园嵌入类
 * 监控在家园发表日志、更新相册、更新记录
 */
class plugin_wb_share_dzx_home extends plugin_wb_share_dzx {

    /**
     * 更新记录处理
     * 将记录内容作为微博内容。
     * 生成个人空间的url
     */
    function _addsubmit() {
        global $_G;
        $param = &$this->param['param'];
        if ($param[0] == "do_success") {
            require_once 'class/Share.class.php';
            $url = ROOT_URL . "/home.php?uid=" . $_G['uid'];
            $msg = $_POST['message'] . $url;
            $share = new Share();
            $share->sharemsg($msg);
        }
    }

    /**
     * 更新相册处理
     * 相册中发表的最后张图片作为微博图片
     * 生成访问相册的url
     */
    function _viewAlbumid() {
        $param = &$this->param['param'];
        if ($param[0] == "upload_images_completed") {
            $id = $_POST['opalbumid'];
            $value = DB::fetch_first("SELECT a.username, a.albumname, a.picnum, a.friend, a.target_ids, p.* FROM " . DB::table('home_pic') . " p
					LEFT JOIN " . DB::table('home_album') . " a ON a.albumid=p.albumid
					WHERE p.albumid='$id' ORDER BY dateline DESC LIMIT 0,1");
            $pic = pic_get($value['filepath'], 'album', $value['thumb'], $value['remote']);
            require_once 'class/Share.class.php';
            $url = ROOT_URL . "/" . $param[1];
            $msg = lang("plugin/wb_share_dzx", "uploadmsg") . $url;
            $pic = ROOT_URL . "/" . $pic;
            $share = new Share();
            $share->sharemsg($msg, $pic);
        }
    }

    /**
     * 发表日志时处理
     * 将日志标题作为微博内容。
     * 生成访问日志的url
     */
    function _blogsubmit() {
        $param = &$this->param['param'];
        if ($param[0] == "do_success") {
            require_once 'class/Share.class.php';
            $url = ROOT_URL . "/" . $param[1];
            $msg = $_POST['subject'] . $url;
            $share = new Share();
            $share->sharemsg($msg);
        }
    }

    //更新记录时，执行showmessage时会被调用
    function spacecp_doing_message($param) {
        $this->param = $param;
        $this->_run();
    }

    //更新相册时，执行showmessage时会被调用
    function spacecp_upload_message($param) {
        $this->param = $param;
        $this->_run();
    }

    //发布日志时，执行showmessage时会被调用
    function spacecp_blog_message($param) {
        $this->param = $param;
        $this->_run();
    }

}

?>
