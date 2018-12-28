<?php
namespace app\index\controller;
use think\Controller;
use think\Db;


class Sso extends Controller
{

	private static $appId; // 当前公众号appId
    private static $appSecret; // 当前公众号appSecret
    private static $bu_id;
    private static $type;

    function __construct() {

        parent::__construct();

        //初始化
        $param = input('param.');

        self::$appId = "wx846178961ef0ca8b";
        self::$appSecret = "cf0eff7e06f50680d3a4d4304f16d26d";
        //  dump(self::$is_wx);exit();
        //配置微信公众号
        vendor("LaneWeChat.config");

        \weChatConfig::setAppId(self::$appId); //juplus wxb42ad5150aacbee4
        \weChatConfig::setAppSecret(self::$appSecret); //juplus 8b023a4ec3bfb58ecaf1b96b95b2e1ba

        //自动载入函数载入相关操作类
        vendor("LaneWeChat.wechat");

		$this->checkAccess();
    }

	function checkAccess(){
		$userInfo = session("userInfo");
		if(empty($userInfo)){
			$this->wechatLogin();
		}
	}
	/**
	* =========获取微信用户详细信息=====================
	**/
	public function wechatLogin(){
		//-------配置
		$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $domain = $protocol.'www.'.$_SERVER['HTTP_HOST'];

		
		$callback  = $domain.url( 'sso/getWechatUserInfo');  //回调地址
		// 微信登录
		//-------生成唯一随机串防CSRF攻击
		$state  = md5(uniqid(rand(), TRUE));
		$_SESSION["wx_state"]    =   $state; //存到SESSION snsapi_userinfo snsapi_base
		
		$callback = urlencode($callback);
		
		$wxurl = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".self::$appId."&redirect_uri=".$callback."&response_type=code&scope=snsapi_base&state={$state}#wechat_redirect";
		header("Location: $wxurl");
    }
 
    //获取用户信息
    function getWechatUserInfo(){
       
		//openid与网页授权access_token
		$ret = \LaneWeChat\Core\WeChatOAuth::getAccessTokenAndOpenId($_GET['code']);
		
		$userInfo = \LaneWeChat\Core\WeChatOAuth::getUserInfo($ret['access_token'], $ret['openid']);
		//处理用户信息
	    session("userInfo",$userInfo);
		$this->redirect(url('sso/index'));	
    }
    public function index()
    {
		$ret = Db('member')->where(['unionid'=>session("userInfo.unionid")])->find();
		if(!empty($ret) && $ret['is_in_idp']==1)
			$this->redirect('https://sp.juplus.cn/module.php/core/authenticate.php?as=default-sp-test&phone='.$ret['tel']);	
		else
			return $this->fetch();
    }

}