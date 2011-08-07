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
//生成网站根目录地址
define("ROOT_URL", " http://" . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), "\\"));
require_once 'WeiboApi.class.php';

/**
 * 微博同步分享类
 * 负责调用不同的微博api，进行发布微博等操作
 */
class Share {

    //存储实例化后的微博api类，避免重复实例化微博api
    protected static $obj = array();
    //存储微博配置(AKEY,SKEY)
    protected static $config = array();
    //当前微博api对象
    protected $currentobj;

    /**
     * 初始化时
     * 读取各个微博的配置，存储到config属性。
     */
    function Share() {
        global $_G;
        if (!$_G['uid']) {
            showmessage("wb_share_dzx:notlogin", '', array(), array('login' => true));
        }
        //读取配置
        if (empty(self::$config)) {
            loadcache('plugin');
            //定义是否记录日志
            Think_log::$iflog = $_G['cache']['plugin']['wb_share_dzx']['iflog'];
            self::$config = array(
                'Sina' => array(
                    'AKEY' => $_G['cache']['plugin']['wb_share_dzx']['sina_akey'],
                    'SKEY' => $_G['cache']['plugin']['wb_share_dzx']['sina_skey']
                ),
                'Qq' => array(
                    'AKEY' => $_G['cache']['plugin']['wb_share_dzx']['qq_akey'],
                    'SKEY' => $_G['cache']['plugin']['wb_share_dzx']['qq_skey'],
                ),
                'T163' => array(
                    'AKEY' => $_G['cache']['plugin']['wb_share_dzx']['t163_key'],
                    'SKEY' => $_G['cache']['plugin']['wb_share_dzx']['t163_secret']
                ),
                'Sohu' => array(
                    'AKEY' => $_G['cache']['plugin']['wb_share_dzx']['sohu_key'],
                    'SKEY' => $_G['cache']['plugin']['wb_share_dzx']['sohu_secret']
                )
            );
        }
    }

    /**
     * 选择微博api
     * 实例化选择的微博api，并存储到obj熟悉和currentobj属性。
     */
    function switchapi($name) {
        $name = ucwords(strtolower($name));
        $obj = &self::$obj;
        if (!is_object($obj[$name])) {
            $config = &self::$config;
            if (!isset($config[$name])) {
                showmessage('wb_share_dzx:apinotexists');
            }
            $class = $name . "Api";
            //实例化微博Api，并传递配置
            $obj[$name] = new $class($config[$name]);
        }
        $this->currentobj = $obj[$name];
    }

    /**
     * 获得微博授权页面地址
     */
    function getloginurl($callback) {
        return $this->currentobj->getloginurl($callback);
    }

    /**
     * 执行授权返回处理
     */
    function callback() {
        return $this->currentobj->callback();
    }

    /**
     * 发送信息
     * 读取当前用户已绑定的微博。
     * 循环给已绑定微博发送信息。
     * @param string $msg 微博内容
     * @param string $img 微博图片
     */
    function sharemsg($msg, $img='') {
        global $_G;
        //读取用户绑定的微博
        $query = DB::query("select apiname,keyarr from " . DB::table("share_keys") . " where uid='{$_G['uid']}'");
        $apis = array();
        $keys = array();
        while ($arr = DB::fetch($query)) {
            $apis[] = $arr['apiname'];
            $keys[$arr['apiname']] = unserialize($arr['keyarr']);
        }
        //循环发送信息
        foreach ($apis as $api) {
            $this->switchapi($api);
            //传递用户的AccessToken
            $this->currentobj->keys = $keys[$api];
            $this->currentobj->sharemsg($msg, $img);
        }
    }

}

?>
