<?php
require_once 'ThinkOAuth.php';
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
//date_default_timezone_set('Asia/Chongqing');
/**
 * 微博Api接口基类。
 * 负责连接微博前对参数的处理
 */
class BaseApi {

    protected $config;
    public $keys;
    public $name;
/**
 *初始化，存储微博配置到config属性，存储当前微博名称到name属性
 * @param array $config 当前微博配置
 */
    public function __construct($config) {
        $this->config = $config;
        $this->name = substr(get_class($this), 0, -3);
    }

    /**
     * 获得授权地址
     */
    public function getloginurl($callback) {
        $config = &$this->config;
        $class = $this->name . "_OAuth";
        $oauth = new $class($config['AKEY'], $config['SKEY']);
        $keys = $oauth->getRequestToken($callback);
        if(!isset($keys['oauth_token'])){
            Think_log::save($keys,$this->name.'get requestToken error');
            return false;
        }
        $aurl = $oauth->getAuthorizeURL($keys['oauth_token'], $callback);
        setcookie("oauth_token", $keys['oauth_token']);
        setcookie("oauth_token_secret", $keys['oauth_token_secret']);
        return $aurl;
    }

    /**
     * 授权返回处理
     * 存储用户的AccessToken到数据库
     */
    public function callback() {
        global $_G;
        $config = &$this->config;
        $class = $this->name . "_OAuth";
        $o = new $class($config['AKEY'], $config['SKEY'], $_COOKIE['oauth_token'], $_COOKIE['oauth_token_secret']);
        $last_key = $o->getAccessToken($_REQUEST['oauth_verifier']); //获取ACCESSTOKEN
        if (!isset($last_key['oauth_token'])) {
            return false;
        }
        $setarr = array(
            'uid' => $_G['uid'],
            'apiname' => $this->name,
            'keyarr' => serialize($last_key)
        );
        DB::insert("share_keys", $setarr, false, true);
        return true;
    }

    /**
     * 发布信息，不同微博的处理过程有所不同
     */
    public  function sharemsg($msg, $img=''){}
/**
 *字符串编码转换， 能自动判断编码。
 */
    protected function utf8_encode($string){
        if(CHARSET!='utf-8'){
            //GBK编码需要转换
           return iconv('gbk','utf-8',$string);
        }else{
           return $string;
        }
    }
    /**
     *获得当前微博的OAuth对象
     * @return class
     */
    protected function _getoauth(){
        $config = &$this->config;
        $lastkey = &$this->keys;
        $class = $this->name . "_OAuth";
        return new $class($config['AKEY'], $config['SKEY'], $lastkey['oauth_token'], $lastkey['oauth_token_secret']);
    }

}

/**
 * 网易微博接口
 */
class T163Api extends BaseApi {

    public function sharemsg($msg, $img = '') {
        $msg=$this->utf8_encode($msg);
        $oauth = $this->_getoauth();
        if (!empty($img)) {
            $img.="::filename.jpg";//自定义文件名
            $pic = $oauth->post('http://api.t.163.com/statuses/upload.json', array('pic' => '@' . $img));
            $msg.=" ".$pic['upload_image_url'];
        }
        $result=$oauth->post('http://api.t.163.com/statuses/update.json', array('status' => $msg));
        if(!isset($result['id'])){
        Think_log::save($result,'T163 send msg error');
        }
    }

}

/**
 * 新浪微博接口
 */
class SinaApi extends BaseApi {

    public function sharemsg($msg, $img = '') {
        $msg=$this->utf8_encode($msg);
        $oauth = $this->_getoauth();
        if (!empty($img)) {
            //带图片发布微博
            $result=$oauth->post('http://api.t.sina.com.cn/statuses/upload.json', array(
                'status' => $msg,
                'pic' => '@' . $img
                    ));
        } else {
            //不带图片发布微博
            $result=$oauth->post('http://api.t.sina.com.cn/statuses/update.json', array('status' => $msg));
        }
        if(!isset($result['id'])){
        Think_log::save($result,'Sina send msg error');
        }
    }

}

/**
 * 腾讯微博接口
 */
class QqApi extends BaseApi {

    public function sharemsg($msg, $img = '') {
        $msg=$this->utf8_encode($msg);
        $oauth = $this->_getoauth();
        if (!empty($img)) {
            $result=$oauth->post('http://open.t.qq.com/api/t/add_pic?f=1', array('format' => 'json',
                'content' => $msg,
                'clientip' => $_SERVER['REMOTE_ADDR'],
                'pic' => '@' . $img
                    ));
        } else {
            $result=$oauth->post('http://open.t.qq.com/api/t/add?f=1', array('format' => 'json',
                'content' => $msg,
                'clientip' => $_SERVER['REMOTE_ADDR'],));
        }
        if(!isset($result['data']['id'])){
        Think_log::save($result,'qq send msg error');
        }
    }

}

/**
 * 搜狐微博接口
 */
class SohuApi extends BaseApi {
    //todu 搜狐微博 发布图片经常容易失败，尚未解决。
    function  sharemsg($msg, $img = '') {
        $msg=$this->utf8_encode($msg);
        $oauth = $this->_getoauth();
        $result=$oauth->post("http://api.t.sohu.com/statuses/update.json", array('status' =>$msg));
        //$oauth->post("http://api.t.sohu.com/statuses/upload.json", array('status' =>$msg,'pic'=>'@'.$img));
        if(!isset($result['id'])){
        Think_log::save($result,'sohu send msg error');
        }
    }
}

?>
