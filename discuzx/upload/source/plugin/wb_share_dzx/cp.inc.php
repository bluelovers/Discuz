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
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
/**
 * 绑定微博
 * 跳转到微博的授权页面
 */
function share_create() {
    require_once 'class/Share.class.php';
    $share = new Share();
    $share->switchapi($_GET['apiname']);
    $callback=ROOT_URL."/plugin.php?id=wb_share_dzx:cp&action=callback&apiname=".$_GET['apiname'];
    $loginurl=$share->getloginurl($callback);
    if(!$loginurl){
        //如果失败，提示错误
        showmessage("wb_share_dzx:createerror");
    }
   header("location:" . $loginurl);
}
/**
 *解除绑定
 */
function share_del(){
    global $_G;
    DB::delete("share_keys","apiname='{$_GET['apiname']}' and uid='{$_G['uid']}'");
    showmessage("wb_share_dzx:deletesuccess",$_SERVER['HTTP_REFERER']);
}
/**
 * 绑定返回处理，
 */
function share_callback(){
    require_once 'class/Share.class.php';
    $share=new Share();
    $share->switchapi($_GET['apiname']);
    if(!$share->callback()){
        showmessage("wb_share_dzx:bindingerror");
    }else{
        showmessage("wb_share_dzx:bindingsuccess","home.php?mod=spacecp&ac=plugin&id=wb_share_dzx:actionscp");
    }
}
/**
 *直接向微博发布信息。
 *上传图片时，不会在服务器遗留图片垃圾。
 */
function share_sendmsg(){
    global $_G;
    //判断是否绑定微博
     checkbind();
    require_once 'class/Share.class.php';
    if(!empty($_FILES['pic']['tmp_name'])){
        //上传图片
        $img=$_FILES['pic']['tmp_name']."::".$_FILES['pic']['name'];
    }
    $share = new Share();
    $msg=$_POST['msg'];
    $share->sharemsg($msg,$img);
    if(!empty($_POST['doing'])){
        //同时更新到记录
        if(censormod($msg) || $_G['group']['allowdoingmod']) {
		$doing_status = 1;
	} else {
		$doing_status = 0;
	}
        $setarr = array(
		'uid' => $_G['uid'],
		'username' => $_G['username'],
		'dateline' => $_G['timestamp'],
		'message' => $msg,
		'ip' => $_G['clientip'],
		'status' => $doing_status,
	);
	$newdoid = DB::insert('home_doing', $setarr, 1);
        $feed = array(
			'icon' => 'doing',
			'uid' => $_G['uid'],
			'username' => $_G['username'],
			'dateline' => $_G['timestamp'],
			'title_template' =>'feed_doing_title',
			'title_data' => array('message'=>$msg),
			'id' => $newdoid,
			'idtype' => 'doid'
		);
       require_once libfile('function/feed');
       feed_add($feed['icon'], $feed['title_template'], $feed['title_data'], '', '', '',array(), array(), '', '', '', 0, $feed['id'], $feed['idtype']);
    }
    showmessage("wb_share_dzx:sendsuccess","home.php?mod=spacecp&ac=plugin&id=wb_share_dzx:actionscp");
}
/**
 *设置需要同步信息的操作
 */
function share_actionsave(){
    global $_G;
    //判断是否绑定微博
    checkbind();
    //加入数据库
    $setarr=array(
        'uid'=>$_G['uid'],
        'actions'=>  implode("|", $_POST['actions'])
    );
    DB::insert("share_actions",$setarr,false,true);
    showmessage("wb_share_dzx:savesuccess",$_SERVER['HTTP_REFERER']);

}
/**
 *判断是否已绑定微博
 */
function checkbind(){
    global $_G;
    $result=DB::fetch_first("select apiname from " . DB::table("share_keys") . " where uid='{$_G['uid']}'");
    if($result===false){
        showmessage("wb_share_dzx:notbinding");
    }
}

$fun = "share_" . $_GET['action'];
if (!function_exists($fun)) {
    showmessage("wb_share_dzx:paramserror");
} else {
    $fun();
}

?>
