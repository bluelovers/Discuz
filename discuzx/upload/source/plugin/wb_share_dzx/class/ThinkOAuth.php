<?php

/**
  +------------------------------------------------------
  | 公用OAuth类，兼容新浪、腾讯、网易、搜狐等多家微博微博接口。
  | 类名都分别加上Think_前缀，避免和其他接口类名冲突。
  +------------------------------------------------------
  | 代码基于https://github.com/abraham/twitteroauth
  | luofei614<www.3g4k.com> 修改
  +-----------------------------------------------------
 */
if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class BaseOAuth {
    /* Contains the last HTTP status code returned. */

    public $http_code;
    /* Contains the last API call. */
    public $url;
    /* Set up the API root URL. */
    public $host = "";
    /* Set timeout default. */
    public $timeout = 30;
    /* Set connect timeout. */
    public $connecttimeout = 30;
    /* Verify SSL Cert. */
    public $ssl_verifypeer = FALSE;
    /* Respons format. */
    public $format = 'json';
    /* Decode returned json data. */
    public $decode_json = TRUE;
    /* Contains the last HTTP headers returned. */
    public $http_info;
    /* Set the useragnet. */
    public $useragent = '';
    /* Immediately retry the API call if the response was not successful. */
    //public $retry = TRUE;

    /**
     * Set API URLS
     */
    function accessTokenURL() {

    }

    function authenticateURL() {

    }

    function authorizeURL() {

    }

    function requestTokenURL() {

    }

    /**
     * Debug helpers
     */
    function lastStatusCode() {
        return $this->http_status;
    }

    function lastAPICall() {
        return $this->last_api_call;
    }

    /**
     * construct TwitterOAuth object
     */
    function __construct($consumer_key, $consumer_secret, $oauth_token = NULL, $oauth_token_secret = NULL) {
        $this->sha1_method = new Think_OAuthSignatureMethod_HMAC_SHA1();
        $this->consumer = new Think_OAuthConsumer($consumer_key, $consumer_secret);
        if (!empty($oauth_token) && !empty($oauth_token_secret)) {
            $this->token = new Think_OAuthConsumer($oauth_token, $oauth_token_secret);
        } else {
            $this->token = NULL;
        }
    }

    /**
     * Get a request_token from Twitter
     *
     * @returns a key/value array containing oauth_token and oauth_token_secret
     */
    function getRequestToken($oauth_callback = NULL) {
        $parameters = array();
        if (!empty($oauth_callback)) {
            $parameters['oauth_callback'] = $oauth_callback;
        }
        $request = $this->Think_OAuthRequest($this->requestTokenURL(), 'GET', $parameters);
        $token = Think_OAuthUtil::parse_parameters($request);
        $this->token = new Think_OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
        return $token;
    }

    /**
     * Get the authorize URL
     *
     * @returns a string
     */
    function getAuthorizeURL($token, $sign_in_with_twitter = TRUE) {
        if (is_array($token)) {
            $token = $token['oauth_token'];
        }
        if (empty($sign_in_with_twitter)) {
            return $this->authorizeURL() . "?oauth_token={$token}";
        } else {
            return $this->authenticateURL() . "?oauth_token={$token}";
        }
    }

    /**
     * Exchange request token and secret for an access token and
     * secret, to sign API calls.
     *
     * @returns array("oauth_token" => "the-access-token",
     *                "oauth_token_secret" => "the-access-secret",
     *                "user_id" => "9436992",
     *                "screen_name" => "abraham")
     */
    function getAccessToken($oauth_verifier = FALSE) {
        $parameters = array();
        if (!empty($oauth_verifier)) {
            $parameters['oauth_verifier'] = $oauth_verifier;
        }
        $request = $this->Think_OAuthRequest($this->accessTokenURL(), 'GET', $parameters);
        $token = Think_OAuthUtil::parse_parameters($request);
        $this->token = new Think_OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
        return $token;
    }

    /**
     * One time exchange of username and password for access token and secret.
     *
     * @returns array("oauth_token" => "the-access-token",
     *                "oauth_token_secret" => "the-access-secret",
     *                "user_id" => "9436992",
     *                "screen_name" => "abraham",
     *                "x_auth_expires" => "0")
     */
    function getXAuthToken($username, $password) {
        $parameters = array();
        $parameters['x_auth_username'] = $username;
        $parameters['x_auth_password'] = $password;
        $parameters['x_auth_mode'] = 'client_auth';
        $request = $this->Think_OAuthRequest($this->accessTokenURL(), 'POST', $parameters);
        $token = Think_OAuthUtil::parse_parameters($request);
        $this->token = new Think_OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
        return $token;
    }

    /**
     * GET wrapper for Think_OAuthRequest.
     */
    function get($url, $parameters = array()) {
        $response = $this->Think_OAuthRequest($url, 'GET', $parameters);
        if ($this->format === 'json' && $this->decode_json) {
            return json_decode($response,TRUE);
        }
        return $response;
    }

    /**
     * POST wrapper for Think_OAuthRequest.
     */
    function post($url, $parameters = array()) {
        $response = $this->Think_OAuthRequest($url, 'POST', $parameters);
        if ($this->format === 'json' && $this->decode_json) {
            //alter 返回数组
            return json_decode($response, TRUE);
        }
        return $response;
    }

    /**
     * DELETE wrapper for oAuthReqeust.
     */
    function delete($url, $parameters = array()) {
        $response = $this->Think_OAuthRequest($url, 'DELETE', $parameters);
        if ($this->format === 'json' && $this->decode_json) {
            return json_decode($response);
        }
        return $response;
    }

    /**
     * Format and sign an OAuth / API request
     */
    function Think_OAuthRequest($url, $method, $parameters) {
        if (strrpos($url, 'https://') !== 0 && strrpos($url, 'http://') !== 0) {
            $url = "{$this->host}{$url}.{$this->format}";
        }
        $request = Think_OAuthRequest::from_consumer_and_token($this->consumer, $this->token, $method, $url, $parameters);
        $request->sign_request($this->sha1_method, $this->consumer, $this->token);
        switch ($method) {
            case 'GET':
                return $this->http($request->to_url(), 'GET');
            default:
                return $this->http($request->get_normalized_http_url(), $method, $request->to_postdata(), $request->to_header());
        }
    }

    function http_socket($url, $method, $postfields = NULL, $headermulti='') {
        $matches = parse_url($url);
        $host = $matches['host'];
        //判断是否是https请求
        if (strrpos($url, 'https://') === 0) {
            $port = 443;
            $version = '1.1';
            $host = 'ssl://' . $host;
        } else {
            $port = 80;
            $version = '1.0';
        }

        $header = "$method $url HTTP/$version\r\n";
        $header .= "Host: open.t.qq.com\r\n";
        if (!empty(Think_OAuthUtil::$boundary)) {
            $header .= "Content-Type: multipart/form-data; boundary=" . Think_OAuthUtil::$boundary . "\r\n";
            Think_OAuthUtil::$boundary = ''; //清空数据
        } else {
            $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        }
        //兼容网易
        if ($this->host == "http://api.t.163.com/") {
            $header.=$headermulti . "\r\n";
        }
        if (strtolower($method) == 'post') {
            $header .= "Content-Length: " . strlen($postfields) . "\r\n";
            $header .= "Connection: Close\r\n\r\n";
            $header .= $postfields;
        } else {
            $header .= "Connection: Close\r\n\r\n";
        }

        $ret = '';

        $fp = fsockopen($host, $port, $errno, $errstr, 30);

        if (!$fp) {
            $error = 'need openssl';
            Think_log::save($error, 'qq need openssl');
        } else {
            fwrite($fp, $header);
            while (!feof($fp)) {
                $ret .= fgets($fp, 4096);
            }
            fclose($fp);
            if (strrpos($ret, 'Transfer-Encoding: chunked')) {
                $info = explode("\r\n\r\n", $ret);
                $response = explode("\r\n", $info[1]);
                $t = array_slice($response, 1, -1);

                $returnInfo = implode('', $t);
            } else {
                $response = explode("\r\n\r\n", $ret);
                $returnInfo = $response[1];
            }
            //转成utf-8编码
            return iconv("utf-8", "utf-8//ignore", $returnInfo);
        }
    }

    function http($url, $method, $postfields = NULL, $headermulti='') {
        //判断是否支持curl
        if (!function_exists("curl_init")) {
            //如果不支持，使用socket。
            return $this->http_socket($url, $method, $postfields, $headermulti);
        }
        $this->http_info = array();
        $ci = curl_init();
        /* Curl settings */
        curl_setopt($ci, CURLOPT_USERAGENT, $this->useragent);
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout);
        curl_setopt($ci, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
        // curl_setopt($ci, CURLOPT_HTTPHEADER, array('Expect:'));
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer);
        curl_setopt($ci, CURLOPT_HEADERFUNCTION, array($this, 'getHeader'));
        curl_setopt($ci, CURLOPT_HEADER, FALSE);
        //alter 为了监控header
        curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE);

        switch ($method) {
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, TRUE);
                if (!empty($postfields)) {
                    curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
                    //error_log($postfields,3,"./postfiled.txt");
                }
                break;
            case 'DELETE':
                curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
                if (!empty($postfields)) {
                    $url = "{$url}?{$postfields}";
                }
        }
        //alter 增加对二进制数据的处理
        $header_array = array('Expect:');
        if (!empty(Think_OAuthUtil::$boundary)) {
            $header_array[] = "Content-Type: multipart/form-data; boundary=" . Think_OAuthUtil::$boundary;
            Think_OAuthUtil::$boundary = ''; //清空数据
        }
        //alter 兼容网易
        if ($this->host == "http://api.t.163.com/") {
            array_push($header_array, $headermulti);
        }
        curl_setopt($ci, CURLOPT_HTTPHEADER, $header_array);
        curl_setopt($ci, CURLOPT_URL, $url);
        $response = curl_exec($ci);
        $this->http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
        $this->http_info = array_merge($this->http_info, curl_getinfo($ci));
      //  error_log(var_export($this->http_info, true), 3, "./h.txt");
        $this->url = $url;
        curl_close($ci);
        return $response;
    }

    /**
     * Get the header info to store.
     */
    function getHeader($ch, $header) {
        $i = strpos($header, ':');
        if (!empty($i)) {
            $key = str_replace('-', '_', strtolower(substr($header, 0, $i)));
            $value = trim(substr($header, $i + 2));
            $this->http_header[$key] = $value;
        }
        return strlen($header);
    }

}

/**
 * 搜狐微博OAuth
 */
class Sohu_OAuth extends BaseOAuth {

    public $host = "http://api.t.sohu.com/";
    public $useragent = 'SohuOAuth v0.0.1';

    function accessTokenURL() {
        return 'http://api.t.sohu.com/oauth/access_token';
    }

    function authenticateURL() {
        return 'http://api.t.sohu.com/oauth/authorize';
    }

    function authorizeURL() {
        return 'http://api.t.sohu.com/oauth/authorize';
    }

    function requestTokenURL() {
        return 'http://api.t.sohu.com/oauth/request_token';
    }

    function getAuthorizeUrl($token, $oauth_callback) {
        if (is_array($token)) {
            $token = $token['oauth_token'];
        }
        return $this->authorizeURL() . "?oauth_token={$token}" . "&oauth_callback=" . urlencode($oauth_callback);
    }

}

/**
 * 新浪微博OAuth
 */
class Sina_OAuth extends BaseOAuth {

    public $host = "http://api.t.sina.com.cn/";
    public $useragent = 'Sae T OAuth v0.2.0-beta2';

    function accessTokenURL() {
        return 'http://api.t.sina.com.cn/oauth/access_token';
    }

    function authenticateURL() {
        return 'http://api.t.sina.com.cn/oauth/authenticate';
    }

    function authorizeURL() {
        return 'http://api.t.sina.com.cn/oauth/authorize';
    }

    function requestTokenURL() {
        return 'http://api.t.sina.com.cn/oauth/request_token';
    }

    function getAuthorizeURL($token, $url) {
        if (is_array($token)) {
            $token = $token['oauth_token'];
        }
        return $this->authenticateURL() . "?oauth_token={$token}&oauth_callback=" . urlencode($url);
    }

}

/**
 * 腾讯微博OAuth
 */
class Qq_OAuth extends BaseOAuth {

    public $host = 'http://open.t.qq.com/';
    public $userAgent = 'oauth test';

    function accessTokenURL() {
        return 'https://open.t.qq.com/cgi-bin/access_token';
    }

    function authenticateURL() {
        return 'http://open.t.qq.com/cgi-bin/authenticate';
    }

    function authorizeURL() {
        return 'http://open.t.qq.com/cgi-bin/authorize';
    }

    function requestTokenURL() {
        return 'https://open.t.qq.com/cgi-bin/request_token';
    }

    function getAuthorizeURL($token, $url='') {
        if (is_array($token)) {
            $token = $token['oauth_token'];
        }
        return $this->authorizeURL() . "?oauth_token={$token}";
    }

}

/**
 * 网易微博OAuth
 */
class T163_OAuth extends BaseOAuth {

    public $host = "http://api.t.163.com/";
    public $useragent = 't.163.com OAuth';

    function accessTokenURL() {
        return 'http://api.t.163.com/oauth/access_token';
    }

    function authenticateURL() {
        return 'http://api.t.163.com/oauth/authenticate';
    }

    function authorizeURL() {
        return 'http://api.t.163.com/oauth/authorize';
    }

    function requestTokenURL() {
        return 'http://api.t.163.com/oauth/request_token';
    }

    function getAuthorizeURL($token, $url=null) {
        if (is_array($token)) {
            $token = $token['oauth_token'];
        }
        return $this->authenticateURL() . "?oauth_token={$token}&oauth_callback=" . urlencode($url);
    }

}

//alter 新增log类
class Think_log {

    //是否记录日志
    public static $iflog = false;

    public static function save($msg, $title='') {
        static $time;
        static $N = 1;
        if (empty($time)) {
            $time = date('Y-m-d-H-i-s');
        }
        if (self::$iflog) {
            if (!is_string($msg)) {
                $msg = var_export($msg, TRUE);
            }
            if (!is_dir('./data/WeiboLog')) {
                @(mkdir('./data/WeiboLog'));
            }
            // error_log("#{$n}#-----------------------\r\n" . $msg, 3, "./data/WeiboLog/{$time}.log");
            error_log("#{$N}#----------{$title}-------------\r\n" . $msg . "\r\n", 3, "./data/WeiboLog/{$time}.log");
            $N++;
        }
    }

}

class Think_OAuthException extends Exception {
    //pass
}

/**
 * 存储客户端信息
 * 存储密钥和访问地址
 */
class Think_OAuthConsumer {

    public $key;
    public $secret;

    function __construct($key, $secret, $callback_url=NULL) {
        $this->key = $key;
        $this->secret = $secret;
        $this->callback_url = $callback_url;
    }

    function __toString() {
        return "Think_OAuthConsumer[key=$this->key,secret=$this->secret]";
    }

}

class Think_OAuthToken {

    // access tokens and request tokens
    public $key;
    public $secret;

    /**
     * key = the token
     * secret = the token secret
     */
    function __construct($key, $secret) {
        $this->key = $key;
        $this->secret = $secret;
    }

    /**
     * generates the basic string serialization of a token that a server
     * would respond to request_token and access_token calls with
     */
    function to_string() {
        return "oauth_token=" .
        Think_OAuthUtil::urlencode_rfc3986($this->key) .
        "&oauth_token_secret=" .
        Think_OAuthUtil::urlencode_rfc3986($this->secret);
    }

    function __toString() {
        return $this->to_string();
    }

}

/**
 * A class for implementing a Signature Method
 * See section 9 ("Signing Requests") in the spec
 */
abstract class Think_OAuthSignatureMethod {

    /**
     * Needs to return the name of the Signature Method (ie HMAC-SHA1)
     * @return string
     */
    abstract public function get_name();

    /**
     * Build up the signature
     * NOTE: The output of this function MUST NOT be urlencoded.
     * the encoding is handled in Think_OAuthRequest when the final
     * request is serialized
     * @param Think_OAuthRequest $request
     * @param Think_OAuthConsumer $consumer
     * @param Think_OAuthToken $token
     * @return string
     */
    abstract public function build_signature($request, $consumer, $token);

    /**
     * Verifies that a given signature is correct
     * @param Think_OAuthRequest $request
     * @param Think_OAuthConsumer $consumer
     * @param Think_OAuthToken $token
     * @param string $signature
     * @return bool
     */
    public function check_signature($request, $consumer, $token, $signature) {
        $built = $this->build_signature($request, $consumer, $token);
        return $built == $signature;
    }

}

/**
 * The HMAC-SHA1 signature method uses the HMAC-SHA1 signature algorithm as defined in [RFC2104] 
 * where the Signature Base String is the text and the key is the concatenated values (each first 
 * encoded per Parameter Encoding) of the Consumer Secret and Token Secret, separated by an '&' 
 * character (ASCII code 38) even if empty.
 *   - Chapter 9.2 ("HMAC-SHA1")
 */
class Think_OAuthSignatureMethod_HMAC_SHA1 extends Think_OAuthSignatureMethod {

    function get_name() {
        return "HMAC-SHA1";
    }

    public function build_signature($request, $consumer, $token) {
        $base_string = $request->get_signature_base_string();
        $request->base_string = $base_string;

        $key_parts = array(
            $consumer->secret,
            ($token) ? $token->secret : ""
        );

        $key_parts = Think_OAuthUtil::urlencode_rfc3986($key_parts);
        $key = implode('&', $key_parts);

        return base64_encode(hash_hmac('sha1', $base_string, $key, true));
    }

}

/**
 * The PLAINTEXT method does not provide any security protection and SHOULD only be used 
 * over a secure channel such as HTTPS. It does not use the Signature Base String.
 *   - Chapter 9.4 ("PLAINTEXT")
 */
class Think_OAuthSignatureMethod_PLAINTEXT extends Think_OAuthSignatureMethod {

    public function get_name() {
        return "PLAINTEXT";
    }

    /**
     * oauth_signature is set to the concatenated encoded values of the Consumer Secret and 
     * Token Secret, separated by a '&' character (ASCII code 38), even if either secret is 
     * empty. The result MUST be encoded again.
     *   - Chapter 9.4.1 ("Generating Signatures")
     *
     * Please note that the second encoding MUST NOT happen in the SignatureMethod, as
     * Think_OAuthRequest handles this!
     */
    public function build_signature($request, $consumer, $token) {
        $key_parts = array(
            $consumer->secret,
            ($token) ? $token->secret : ""
        );

        $key_parts = Think_OAuthUtil::urlencode_rfc3986($key_parts);
        $key = implode('&', $key_parts);
        $request->base_string = $key;

        return $key;
    }

}

/**
 * The RSA-SHA1 signature method uses the RSASSA-PKCS1-v1_5 signature algorithm as defined in 
 * [RFC3447] section 8.2 (more simply known as PKCS#1), using SHA-1 as the hash function for 
 * EMSA-PKCS1-v1_5. It is assumed that the Consumer has provided its RSA public key in a 
 * verified way to the Service Provider, in a manner which is beyond the scope of this 
 * specification.
 *   - Chapter 9.3 ("RSA-SHA1")
 */
abstract class Think_OAuthSignatureMethod_RSA_SHA1 extends Think_OAuthSignatureMethod {

    public function get_name() {
        return "RSA-SHA1";
    }

    // Up to the SP to implement this lookup of keys. Possible ideas are:
    // (1) do a lookup in a table of trusted certs keyed off of consumer
    // (2) fetch via http using a url provided by the requester
    // (3) some sort of specific discovery code based on request
    //
    // Either way should return a string representation of the certificate
    protected abstract function fetch_public_cert(&$request);

    // Up to the SP to implement this lookup of keys. Possible ideas are:
    // (1) do a lookup in a table of trusted certs keyed off of consumer
    //
    // Either way should return a string representation of the certificate
    protected abstract function fetch_private_cert(&$request);

    public function build_signature($request, $consumer, $token) {
        $base_string = $request->get_signature_base_string();
        $request->base_string = $base_string;

        // Fetch the private key cert based on the request
        $cert = $this->fetch_private_cert($request);

        // Pull the private key ID from the certificate
        $privatekeyid = openssl_get_privatekey($cert);

        // Sign using the key
        $ok = openssl_sign($base_string, $signature, $privatekeyid);

        // Release the key resource
        openssl_free_key($privatekeyid);

        return base64_encode($signature);
    }

    public function check_signature($request, $consumer, $token, $signature) {
        $decoded_sig = base64_decode($signature);

        $base_string = $request->get_signature_base_string();

        // Fetch the public key cert based on the request
        $cert = $this->fetch_public_cert($request);

        // Pull the public key ID from the certificate
        $publickeyid = openssl_get_publickey($cert);

        // Check the computed signature against the one passed in the query
        $ok = openssl_verify($base_string, $decoded_sig, $publickeyid);

        // Release the key resource
        openssl_free_key($publickeyid);

        return $ok == 1;
    }

}

class Think_OAuthRequest {

    private $parameters;
    private $http_method;
    private $http_url;
    // for debug purposes
    public $base_string;
    //alter 这里 qq必须要为1.0， 不能为 1.0a， 新浪为 1.0也可以
    public static $version = '1.0';
    public static $POST_INPUT = 'php://input';

    function __construct($http_method, $http_url, $parameters=NULL) {
        @$parameters or $parameters = array();
        $parameters = array_merge(Think_OAuthUtil::parse_parameters(parse_url($http_url, PHP_URL_QUERY)), $parameters);
        $this->parameters = $parameters;
        $this->http_method = $http_method;
        $this->http_url = $http_url;
    }

    /**
     * attempt to build up a request from what was passed to the server
     */
    public static function from_request($http_method=NULL, $http_url=NULL, $parameters=NULL) {
        $scheme = (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != "on") ? 'http' : 'https';
        @$http_url or $http_url = $scheme .
                '://' . $_SERVER['HTTP_HOST'] .
                ':' .
                $_SERVER['SERVER_PORT'] .
                $_SERVER['REQUEST_URI'];
        @$http_method or $http_method = $_SERVER['REQUEST_METHOD'];

        // We weren't handed any parameters, so let's find the ones relevant to
        // this request.
        // If you run XML-RPC or similar you should use this to provide your own
        // parsed parameter-list
        if (!$parameters) {
            // Find request headers
            $request_headers = Think_OAuthUtil::get_headers();

            // Parse the query-string to find GET parameters
            $parameters = Think_OAuthUtil::parse_parameters($_SERVER['QUERY_STRING']);

            // It's a POST request of the proper content-type, so parse POST
            // parameters and add those overriding any duplicates from GET
            if ($http_method == "POST"
                    && @strstr($request_headers["Content-Type"],
                            "application/x-www-form-urlencoded")
            ) {
                $post_data = Think_OAuthUtil::parse_parameters(
                                file_get_contents(self::$POST_INPUT)
                );
                $parameters = array_merge($parameters, $post_data);
            }

            // We have a Authorization-header with OAuth data. Parse the header
            // and add those overriding any duplicates from GET or POST
            if (@substr($request_headers['Authorization'], 0, 6) == "OAuth ") {
                $header_parameters = Think_OAuthUtil::split_header(
                                $request_headers['Authorization']
                );
                $parameters = array_merge($parameters, $header_parameters);
            }
        }

        return new Think_OAuthRequest($http_method, $http_url, $parameters);
    }

    /**
     * pretty much a helper function to set up the request
     */
    public static function from_consumer_and_token($consumer, $token, $http_method, $http_url, $parameters=NULL) {
        @$parameters or $parameters = array();
        $defaults = array("oauth_version" => Think_OAuthRequest::$version,
            "oauth_nonce" => Think_OAuthRequest::generate_nonce(),
            "oauth_timestamp" => Think_OAuthRequest::generate_timestamp(),
            "oauth_consumer_key" => $consumer->key);
        if ($token)
            $defaults['oauth_token'] = $token->key;

        $parameters = array_merge($defaults, $parameters);

        return new Think_OAuthRequest($http_method, $http_url, $parameters);
    }

    public function set_parameter($name, $value, $allow_duplicates = true) {
        if ($allow_duplicates && isset($this->parameters[$name])) {
            // We have already added parameter(s) with this name, so add to the list
            if (is_scalar($this->parameters[$name])) {
                // This is the first duplicate, so transform scalar (string)
                // into an array so we can add the duplicates
                $this->parameters[$name] = array($this->parameters[$name]);
            }

            $this->parameters[$name][] = $value;
        } else {
            $this->parameters[$name] = $value;
        }
    }

    public function get_parameter($name) {
        return isset($this->parameters[$name]) ? $this->parameters[$name] : null;
    }

    public function get_parameters() {
        return $this->parameters;
    }

    public function unset_parameter($name) {
        unset($this->parameters[$name]);
    }

    /**
     * The request parameters, sorted and concatenated into a normalized string.
     * @return string
     */
    public function get_signable_parameters() {
        // Grab all parameters
        //alter 去掉图片
        $params = $this->parameters;
        if (isset($params['pic'])) {
            unset($params['pic']);
        }

        if (isset($params['image'])) {
            unset($params['image']);
        }
        // Remove oauth_signature if present
        // Ref: Spec: 9.1.1 ("The oauth_signature parameter MUST be excluded.")
        if (isset($params['oauth_signature'])) {
            unset($params['oauth_signature']);
        }

        return Think_OAuthUtil::build_http_query($params);
    }

    /**
     * Returns the base string of this request
     *
     * The base string defined as the method, the url
     * and the parameters (normalized), each urlencoded
     * and the concated with &.
     */
    public function get_signature_base_string() {
        $parts = array(
            $this->get_normalized_http_method(),
            $this->get_normalized_http_url(),
            $this->get_signable_parameters()
        );

        $parts = Think_OAuthUtil::urlencode_rfc3986($parts);

        return implode('&', $parts);
    }

    /**
     * just uppercases the http method
     */
    public function get_normalized_http_method() {
        return strtoupper($this->http_method);
    }

    /**
     * parses the url and rebuilds it to be
     * scheme://host/path
     */
    public function get_normalized_http_url() {
        $parts = parse_url($this->http_url);

        $port = @$parts['port'];
        $scheme = $parts['scheme'];
        $host = $parts['host'];
        $path = @$parts['path'];

        $port or $port = ($scheme == 'https') ? '443' : '80';

        if (($scheme == 'https' && $port != '443')
                || ($scheme == 'http' && $port != '80')) {
            $host = "$host:$port";
        }
        return "$scheme://$host$path";
    }

    /**
     * builds a url usable for a GET request
     */
    public function to_url() {
        $post_data = $this->to_postdata();
        $out = $this->get_normalized_http_url();
        if ($post_data) {
            $out .= '?' . $post_data;
        }
        return $out;
    }

    /**
     * builds the data one would send in a POST request
     */
    public function to_postdata() {
        return Think_OAuthUtil::build_http_query($this->parameters);
    }

    /**
     * builds the Authorization: header
     */
    public function to_header($realm=null) {
        $first = true;
        if ($realm) {
            $out = 'Authorization: OAuth realm="' . Think_OAuthUtil::urlencode_rfc3986($realm) . '"';
            $first = false;
        } else
            $out = 'Authorization: OAuth';

        $total = array();
        foreach ($this->parameters as $k => $v) {
            if (substr($k, 0, 5) != "oauth")
                continue;
            if (is_array($v)) {
                //throw new Think_OAuthException('Arrays not supported in headers');
                Think_log::save('Arrays not supported in headers');
            }
            $out .= ( $first) ? ' ' : ',';
            $out .= Think_OAuthUtil::urlencode_rfc3986($k) .
                    '="' .
                    Think_OAuthUtil::urlencode_rfc3986($v) .
                    '"';
            $first = false;
        }
        return $out;
    }

    public function __toString() {
        return $this->to_url();
    }

    public function sign_request($signature_method, $consumer, $token) {
        $this->set_parameter(
                "oauth_signature_method",
                $signature_method->get_name(),
                false
        );
        $signature = $this->build_signature($signature_method, $consumer, $token);
        $this->set_parameter("oauth_signature", $signature, false);
    }

    public function build_signature($signature_method, $consumer, $token) {
        $signature = $signature_method->build_signature($this, $consumer, $token);
        return $signature;
    }

    /**
     * util function: current timestamp
     */
    private static function generate_timestamp() {
        return time();
    }

    /**
     * util function: current nonce
     */
    private static function generate_nonce() {
        $mt = microtime();
        $rand = mt_rand();
        return md5($mt . $rand); // md5s look nicer than numbers
    }

}

class Think_OAuthServer {

    protected $timestamp_threshold = 300; // in seconds, five minutes
    protected $version = '1.0';             // hi blaine
    protected $signature_methods = array();
    protected $data_store;

    function __construct($data_store) {
        $this->data_store = $data_store;
    }

    public function add_signature_method($signature_method) {
        $this->signature_methods[$signature_method->get_name()] =
                $signature_method;
    }

    // high level functions

    /**
     * process a request_token request
     * returns the request token on success
     */
    public function fetch_request_token(&$request) {
        $this->get_version($request);

        $consumer = $this->get_consumer($request);

        // no token required for the initial token request
        $token = NULL;

        $this->check_signature($request, $consumer, $token);

        // Rev A change
        $callback = $request->get_parameter('oauth_callback');
        $new_token = $this->data_store->new_request_token($consumer, $callback);

        return $new_token;
    }

    /**
     * process an access_token request
     * returns the access token on success
     */
    public function fetch_access_token(&$request) {
        $this->get_version($request);

        $consumer = $this->get_consumer($request);

        // requires authorized request token
        $token = $this->get_token($request, $consumer, "request");

        $this->check_signature($request, $consumer, $token);

        // Rev A change
        $verifier = $request->get_parameter('oauth_verifier');
        $new_token = $this->data_store->new_access_token($token, $consumer, $verifier);

        return $new_token;
    }

    /**
     * verify an api call, checks all the parameters
     */
    public function verify_request(&$request) {
        $this->get_version($request);
        $consumer = $this->get_consumer($request);
        $token = $this->get_token($request, $consumer, "access");
        $this->check_signature($request, $consumer, $token);
        return array($consumer, $token);
    }

    // Internals from here
    /**
     * version 1
     */
    private function get_version(&$request) {
        $version = $request->get_parameter("oauth_version");
        if (!$version) {
            // Service Providers MUST assume the protocol version to be 1.0 if this parameter is not present.
            // Chapter 7.0 ("Accessing Protected Ressources")
            $version = '1.0';
        }
        if ($version !== $this->version) {
            //throw new Think_OAuthException("OAuth version '$version' not supported");
            Think_log::save("OAuth version '$version' not supported");
        }
        return $version;
    }

    /**
     * figure out the signature with some defaults
     */
    private function get_signature_method(&$request) {
        $signature_method =
                @$request->get_parameter("oauth_signature_method");

        if (!$signature_method) {
            // According to chapter 7 ("Accessing Protected Ressources") the signature-method
            // parameter is required, and we can't just fallback to PLAINTEXT
            // throw new Think_OAuthException('No signature method parameter. This parameter is required');
            Think_log::save('No signature method parameter. This parameter is required');
        }

        if (!in_array($signature_method,
                        array_keys($this->signature_methods))) {
//            throw new Think_OAuthException(
//                    "Signature method '$signature_method' not supported " .
//                    "try one of the following: " .
//                    implode(", ", array_keys($this->signature_methods))
//            );
            Think_log::save(
                            "Signature method '$signature_method' not supported " .
                            "try one of the following: " .
                            implode(", ", array_keys($this->signature_methods))
            );
        }
        return $this->signature_methods[$signature_method];
    }

    /**
     * try to find the consumer for the provided request's consumer key
     */
    private function get_consumer(&$request) {
        $consumer_key = @$request->get_parameter("oauth_consumer_key");
        if (!$consumer_key) {
            Think_log::save("Invalid consumer key");
        }

        $consumer = $this->data_store->lookup_consumer($consumer_key);
        if (!$consumer) {
            Think_log::save("Invalid consumer");
        }

        return $consumer;
    }

    /**
     * try to find the token for the provided request's token key
     */
    private function get_token(&$request, $consumer, $token_type="access") {
        $token_field = @$request->get_parameter('oauth_token');
        $token = $this->data_store->lookup_token(
                        $consumer, $token_type, $token_field
        );
        if (!$token) {
            Think_log::save("Invalid $token_type token: $token_field");
        }
        return $token;
    }

    /**
     * all-in-one function to check the signature on a request
     * should guess the signature method appropriately
     */
    private function check_signature(&$request, $consumer, $token) {
        // this should probably be in a different method
        $timestamp = @$request->get_parameter('oauth_timestamp');
        $nonce = @$request->get_parameter('oauth_nonce');

        $this->check_timestamp($timestamp);
        $this->check_nonce($consumer, $token, $nonce, $timestamp);

        $signature_method = $this->get_signature_method($request);

        $signature = $request->get_parameter('oauth_signature');
        $valid_sig = $signature_method->check_signature(
                        $request,
                        $consumer,
                        $token,
                        $signature
        );

        if (!$valid_sig) {
            Think_log::save("Invalid signature");
        }
    }

    /**
     * check that the timestamp is new enough
     */
    private function check_timestamp($timestamp) {
        if (!$timestamp)
            Think_log::save(
                            'Missing timestamp parameter. The parameter is required'
            );

        // verify that timestamp is recentish
        $now = time();
        if (abs($now - $timestamp) > $this->timestamp_threshold) {
            Think_log::save(
                            "Expired timestamp, yours $timestamp, ours $now"
            );
        }
    }

    /**
     * check that the nonce is not repeated
     */
    private function check_nonce($consumer, $token, $nonce, $timestamp) {
        if (!$nonce)
            Think_log::save(
                            'Missing nonce parameter. The parameter is required'
            );

        // verify that the nonce is uniqueish
        $found = $this->data_store->lookup_nonce(
                        $consumer,
                        $token,
                        $nonce,
                        $timestamp
        );
        if ($found) {
            Think_log::save("Nonce already used: $nonce");
        }
    }

}

class Think_OAuthDataStore {

    function lookup_consumer($consumer_key) {
        // implement me
    }

    function lookup_token($consumer, $token_type, $token) {
        // implement me
    }

    function lookup_nonce($consumer, $token, $nonce, $timestamp) {
        // implement me
    }

    function new_request_token($consumer, $callback = null) {
        // return a new token attached to this consumer
    }

    function new_access_token($token, $consumer, $verifier = null) {
        // return a new access token attached to this consumer
        // for the user associated with this token if the request token
        // is authorized
        // should also invalidate the request token
    }

}

class Think_OAuthUtil {

    //alter 增加二进制属性
    public static $boundary = '';

    public static function urlencode_rfc3986($input) {
        if (is_array($input)) {
            return array_map(array('Think_OAuthUtil', 'urlencode_rfc3986'), $input);
        } else if (is_scalar($input)) {
            return str_replace(
                    '+',
                    ' ',
                    str_replace('%7E', '~', rawurlencode($input))
            );
        } else {
            return '';
        }
    }

    // This decode function isn't taking into consideration the above
    // modifications to the encoding process. However, this method doesn't
    // seem to be used anywhere so leaving it as is.
    public static function urldecode_rfc3986($string) {
        return urldecode($string);
    }

    // Utility function for turning the Authorization: header into
    // parameters, has to do some unescaping
    // Can filter out any non-oauth parameters if needed (default behaviour)
    public static function split_header($header, $only_allow_oauth_parameters = true) {
        $pattern = '/(([-_a-z]*)=("([^"]*)"|([^,]*)),?)/';
        $offset = 0;
        $params = array();
        while (preg_match($pattern, $header, $matches, PREG_OFFSET_CAPTURE, $offset) > 0) {
            $match = $matches[0];
            $header_name = $matches[2][0];
            $header_content = (isset($matches[5])) ? $matches[5][0] : $matches[4][0];
            if (preg_match('/^oauth_/', $header_name) || !$only_allow_oauth_parameters) {
                $params[$header_name] = Think_OAuthUtil::urldecode_rfc3986($header_content);
            }
            $offset = $match[1] + strlen($match[0]);
        }

        if (isset($params['realm'])) {
            unset($params['realm']);
        }

        return $params;
    }

    // helper to try to sort out headers for people who aren't running apache
    public static function get_headers() {
        if (function_exists('apache_request_headers')) {
            // we need this to get the actual Authorization: header
            // because apache tends to tell us it doesn't exist
            $headers = apache_request_headers();

            // sanitize the output of apache_request_headers because
            // we always want the keys to be Cased-Like-This and arh()
            // returns the headers in the same case as they are in the
            // request
            $out = array();
            foreach ($headers AS $key => $value) {
                $key = str_replace(
                                " ",
                                "-",
                                ucwords(strtolower(str_replace("-", " ", $key)))
                );
                $out[$key] = $value;
            }
        } else {
            // otherwise we don't have apache and are just going to have to hope
            // that $_SERVER actually contains what we need
            $out = array();
            if (isset($_SERVER['CONTENT_TYPE']))
                $out['Content-Type'] = $_SERVER['CONTENT_TYPE'];
            if (isset($_ENV['CONTENT_TYPE']))
                $out['Content-Type'] = $_ENV['CONTENT_TYPE'];

            foreach ($_SERVER as $key => $value) {
                if (substr($key, 0, 5) == "HTTP_") {
                    // this is chaos, basically it is just there to capitalize the first
                    // letter of every word that is not an initial HTTP and strip HTTP
                    // code from przemek
                    $key = str_replace(
                                    " ",
                                    "-",
                                    ucwords(strtolower(str_replace("_", " ", substr($key, 5))))
                    );
                    $out[$key] = $value;
                }
            }
        }
        return $out;
    }

    // This function takes a input like a=b&a=c&d=e and returns the parsed
    // parameters like this
    // array('a' => array('b','c'), 'd' => 'e')
    public static function parse_parameters($input) {
        if (!isset($input) || !$input)
            return array();

        $pairs = explode('&', $input);

        $parsed_parameters = array();
        foreach ($pairs as $pair) {
            $split = explode('=', $pair, 2);
            $parameter = Think_OAuthUtil::urldecode_rfc3986($split[0]);
            $value = isset($split[1]) ? Think_OAuthUtil::urldecode_rfc3986($split[1]) : '';

            if (isset($parsed_parameters[$parameter])) {
                // We have already recieved parameter(s) with this name, so add to the list
                // of parameters with this name

                if (is_scalar($parsed_parameters[$parameter])) {
                    // This is the first duplicate, so transform scalar (string) into an array
                    // so we can add the duplicates
                    $parsed_parameters[$parameter] = array($parsed_parameters[$parameter]);
                }

                $parsed_parameters[$parameter][] = $value;
            } else {
                $parsed_parameters[$parameter] = $value;
            }
        }
        return $parsed_parameters;
    }

    //alter  增加二进制处理
    public static function build_http_query_multi($params) {
        if (!$params)
            return '';

        //print_r( $params );
        //return null;
        // Urlencode both keys and values
        $keys = array_keys($params);
        $values = array_values($params);
        //$keys = Think_OAuthUtil::urlencode_rfc3986(array_keys($params));
        //$values = Think_OAuthUtil::urlencode_rfc3986(array_values($params));
        $params = array_combine($keys, $values);

        // Parameters are sorted by name, using lexicographical byte value ordering.
        // Ref: Spec: 9.1.1 (1)
        uksort($params, 'strcmp');

        $pairs = array();

        self::$boundary = $boundary = uniqid('------------------');
        $MPboundary = '--' . $boundary;
        $endMPboundary = $MPboundary . '--';
        $multipartbody = '';

        foreach ($params as $parameter => $value) {

            //if( $parameter == 'pic' && $value{0} == '@' )
            if (in_array($parameter, array("pic", "image"))) {
                $url = ltrim($value, '@');
                //alter  允许自定义文件名，格式为：  真实地址::文件名
                $arr = explode("::", $url);
                if (isset($arr[1])) {
                    $url = $arr[0];
                    $filename = $arr[1];
                } else {
                    $filename = reset(explode('?', basename($url)));
                }
                $content = file_get_contents($url);
                $mime = self::get_image_mime($filename);
                $multipartbody .= $MPboundary . "\r\n";
                $multipartbody .= 'Content-Disposition: form-data; name="' . $parameter . '"; filename="' . $filename . '"' . "\r\n";
                $multipartbody .= 'Content-Type: ' . $mime . "\r\n\r\n";
                $multipartbody .= $content . "\r\n";
            } else {
                $multipartbody .= $MPboundary . "\r\n";
                $multipartbody .= 'content-disposition: form-data; name="' . $parameter . "\"\r\n\r\n";
                $multipartbody .= $value . "\r\n";
            }
        }

        // $multipartbody .= $endMPboundary;
        //alter  qq 要加上\r\n,sina加上\r\n也能通过
        $multipartbody .= $endMPboundary . "\r\n";
        // For each parameter, the name is separated from the corresponding value by an '=' character (ASCII code 61)
        // Each name-value pair is separated by an '&' character (ASCII code 38)
        // echo $multipartbody;
        return $multipartbody;
    }

    //alter 增加判断文件类型
    public static function get_image_mime($file) {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        switch ($ext) {
            case 'gif';
                $mime = 'image/gif';
                break;

            case 'png';
                $mime = 'image/png';
                break;
            case 'jpg':
            case 'jpeg':
            default:
                $mime = 'image/jpg';
                break;
        }
        return $mime;
    }

    public static function build_http_query($params) {
        if (!$params)
            return '';
        //alter 判断是否进行二进制处理
        if (isset($params['pic']) || isset($params['image'])) {
            return self::build_http_query_multi($params);
        }
        // Urlencode both keys and values
        $keys = Think_OAuthUtil::urlencode_rfc3986(array_keys($params));
        $values = Think_OAuthUtil::urlencode_rfc3986(array_values($params));
        $params = array_combine($keys, $values);

        // Parameters are sorted by name, using lexicographical byte value ordering.
        // Ref: Spec: 9.1.1 (1)
        uksort($params, 'strcmp');

        $pairs = array();
        foreach ($params as $parameter => $value) {
            if (is_array($value)) {
                // If two or more parameters share the same name, they are sorted by their value
                // Ref: Spec: 9.1.1 (1)
                natsort($value);
                foreach ($value as $duplicate_value) {
                    $pairs[] = $parameter . '=' . $duplicate_value;
                }
            } else {
                $pairs[] = $parameter . '=' . $value;
            }
        }
        // For each parameter, the name is separated from the corresponding value by an '=' character (ASCII code 61)
        // Each name-value pair is separated by an '&' character (ASCII code 38)
        return implode('&', $pairs);
    }

}

//增加hash_hmac函数判断
if (!function_exists('hash_hmac')) {

    function hash_hmac($algo, $data, $key, $raw_output = false) {
        $algo = strtolower($algo);
        $pack = 'H' . strlen($algo('test'));
        $size = 64;
        $opad = str_repeat(chr(0x5C), $size);
        $ipad = str_repeat(chr(0x36), $size);

        if (strlen($key) > $size) {
            $key = str_pad(pack($pack, $algo($key)), $size, chr(0x00));
        } else {
            $key = str_pad($key, $size, chr(0x00));
        }

        for ($i = 0; $i < strlen($key) - 1; $i++) {
            $opad[$i] = $opad[$i] ^ $key[$i];
            $ipad[$i] = $ipad[$i] ^ $key[$i];
        }

        $output = $algo($opad . pack($pack, $algo($ipad . $data)));

        return ($raw_output) ? pack($pack, $output) : $output;
    }

}

if(!function_exists("json_decode")){
   function json_utf_slash_strip($data) {
    if (is_array($data)) {
        return $data[1].iconv("UCS-2","UTF-8",chr(hexdec($data[2])).chr(hexdec($data[3])));
    } else {
        return preg_replace_callback('/(?<!\\\\)((?:\\\\\\\\)*)\\\\u([a-f0-9]{2})([a-f0-9]{2})/i',__FUNCTION__,$data);
    }
}
function json_decode($data,$flag=true) {
    static $strings,$count=0;
    if (is_string($data)) {
        $data=trim($data);
        if ($data{0}!='{' && $data{0}!='[') return json_utf_slash_strip($data);
        $strings=array();
        $data=preg_replace_callback('/"([\s\S]*?(?<!\\\\)(?:\\\\\\\\)*)"/',__FUNCTION__,$data);
        //简单的危险性检测
        //echo $data;
        $cleanData=str_ireplace(array('true','false','undefined','null','{','}','[',']',',',':','#'),'',$data);
        if (!is_numeric($cleanData)) {
           // throw new Exception('Dangerous!The JSONString is dangerous!');
            return NULL;
        }
        $data=str_replace(
            array('{','[',']','}',':','null'),
            array('array(','array(',')',')','=>','NULL')
            ,$data);
        $data=preg_replace_callback('/#\d+/',__FUNCTION__,$data);
        //抑制错误,诸如{123###}这样错误的JSON是不能转换成PHP数组的
        @$data=eval("return $data;");
        $strings=$count=0;
        return $data;
    } elseif (count($data)>1) {//存储字符串
        $strings[]=json_utf_slash_strip(str_replace(array('$','\\/'),array('\\$','/'),$data[0]));
        return '#'.($count++);
    } else {//读取存储的值
        $index=substr($data[0],1);
        return $strings[$index];
    }
}
}
?>
