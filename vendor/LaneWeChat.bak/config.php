<?php

/*
 * 开发者配置
 */
//define("WECHAT_APPID", 'wx9bc9decf65a52165');
//define("WECHAT_APPSECRET", 'c4f6055c5e07292307c2fc5891491b39');

/*
* 需要到微信启动  启动后微信官网的菜单等功能无效 
* 服务器配置，详情请参考@link http://mp.weixin.qq.com/wiki/index.php?title=接入指南
*/
//define("WECHAT_URL", 'https://juplus.cn/');
//define('WECHAT_TOKEN', 'weixin');
//define('ENCODING_AES_KEY', "MqAuKoex6FyT5No0OcpRyCicThGs0P1vz4mJ2gwvvkF");

class weChatConfig {
    /*
    * 开发者配置 
    */
    private static $WECHAT_APPID;
    private static $WECHAT_APPSECRET;

    /*
    * 需要到微信启动  启动后微信官网的菜单等功能无效 
    * 服务器配置，详情请参考@link http://mp.weixin.qq.com/wiki/index.php?title=接入指南
    */
    private static $WECHAT_URL;
    private static $WECHAT_TOKEN;
    private static $ENCODING_AES_KEY;
     //定义一个构造方法初始化赋值
    function __construct( $array ) {
    }

    //获取appId
    public static function getAppId() {
        return self::$WECHAT_APPID;
    }

    //设置appId
    public static function setAppId($appId) {
        self::$WECHAT_APPID = $appId;
    }

    //获取appSecret 
    public static function getAppSecret() {
        return self::$WECHAT_APPSECRET;
    }

    //设置appSecret
    public static function setAppSecret($appSecret) {
        self::$WECHAT_APPSECRET = $appSecret;
    } 


    //获取WECHAT_URL https://juplus.cn/
    public static function getUrl() {
        return self::$WECHAT_URL;
    }

    //设置appSecret
    public static function setUrl($url) {
        self::$WECHAT_URL = $url;
    } 

    //获取WECHAT_TOKEN 微信配置
    public static function getToken() {
        return self::$WECHAT_TOKEN;
    }

    //设置appSecret
    public static function setToken($token) {
        self::$WECHAT_TOKEN = $token;
    } 

   //获取WECHAT_TOKEN 微信配置
    public static function getKey() {
        return self::$ENCODING_AES_KEY;
    }

    //设置appSecret
    public static function setKey($key) {
        self::$ENCODING_AES_KEY = $key;
    } 


}

?>