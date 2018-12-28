<?php
namespace app\index\controller;

use think\Controller;
use think\Loader;
use think\Db;

class Wechat extends Controller
{
    private static $appId; // 当前公众号appId
    private static $appSecret; // 当前公众号appSecret
    private static $bu_id;
    private static $type;
    private static $url;
    private static $is_wx;
    private static $is_open;
    private static $activity_id;
    private static $on_activity_id;
    private static $question_id;

    function __construct() {

        parent::__construct();

        //初始化
        $param = input('param.');

        self::$appId = $param['appId'];
        self::$appSecret = $param['appSecret'];
        self::$bu_id = $param['bu_id'];
        self::$type = $param['type'];
        self::$url = $param['url'];
        // dump();
        if(!empty($param['is_wx'])){
            self::$is_wx = $param['is_wx'];
        }
        if(input('param.activity_id')){
            self::$activity_id = input('param.activity_id');
        }
        if(input('param.is_open')){
            self::$is_open = input('param.is_open');
        }
        if(input('param.on_activity_id')){
            self::$on_activity_id = input('param.on_activity_id');
        }
        if (input('param.question_id')){
            self::$question_id = input('param.question_id');
        }

        //  dump(self::$is_wx);exit();
        //配置微信公众号
        vendor("LaneWeChat.config");

        \weChatConfig::setAppId(self::$appId); //juplus wxb42ad5150aacbee4
        \weChatConfig::setAppSecret(self::$appSecret); //juplus 8b023a4ec3bfb58ecaf1b96b95b2e1ba

        //自动载入函数载入相关操作类
        vendor("LaneWeChat.wechat");

    }

    //用户授权登陆入口
    function login(){
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        //todo is_wx指分享打开的
        $redir_info = [
            'appId' => self::$appId,
            'appSecret' => self::$appSecret,
            'bu_id' => self::$bu_id,
            'type' => self::$type,
            'url' => self::$url,
        ];
        if(self::$activity_id){
            $redir_info['activity_id'] = self::$activity_id;
            $redir_info['is_open'] =  self::$is_open;
        }
        if (self::$on_activity_id){
            $redir_info['on_activity_id'] =  self::$on_activity_id;
        }
        if (self::$question_id){
            $redir_info['question_id'] = self::$question_id;
        }
        $scope = 'snsapi_userinfo';//动态需要授权打开
        // if(self::$is_wx){
        //     $redir_info['is_wx'] = self::$is_wx;
        //     $scope = 'snsapi_base';//静态无需授权打开
        // }

        $redirect_uri = "$protocol$_SERVER[HTTP_HOST]".url("Wechat/getWechatUserInfo",$redir_info);//获取CODE时，发送请求和参数给微信服务器，微信服务器会处理后将跳转到本参数指定的URL页面 snsapi_userinfo snsapi_base
        \LaneWeChat\Core\WeChatOAuth::getCode($redirect_uri, $state=1, $scope);
    }

    //获取用户信息
    function getWechatUserInfo(){
        if($_GET['code']){
            //openid与网页授权access_token
            $ret = \LaneWeChat\Core\WeChatOAuth::getAccessTokenAndOpenId($_GET['code']);
            $userInfo = \LaneWeChat\Core\WeChatOAuth::getUserInfo($ret['access_token'], $ret['openid']);
            //处理用户信息
            $this->dealOauthUser($userInfo);

        }else{

            //处理微信关注与消息回复

        }
    }

    //入口处理用户信息及会话信息
    function dealOauthUser( $user_info ){
        // dump($user_info);exit();
        //需要处理的用户信息
        $user_info = array(
            'openid' => $user_info['openid'],
            'nickname' => base64_encode($user_info['nickname']), //名称
            'sex' => $user_info['sex'],//性别
            'city' => $user_info['city'],//城市
            'province' => $user_info['province'],//省份
            'country' => $user_info['country'], //国家
            'headimgurl' => $user_info['headimgurl'],//头像
            'unionid' => $user_info['unionid'],//unionid unionid
            'create_time' => date("Y-m-d H:i:s"),
        );

        //根据appid查询bu
        // $bu_id = $this->isBu(self::$appId);
        $user_info['bu_id'] = self::$bu_id;

        $this->handleNewUser( $user_info );  //保存用户信息
        $this->handleMapOpenidAndUninid( $user_info ); //保存用户关联的openid 和 unionid



        //查询用户是否注册完成
        $isRegister = $this->isRegister($user_info);
        // if($isRegister['name']){
        $user_info['id'] = $isRegister['id'];
        $user_info['name'] = $isRegister['name'];
        $user_info['bu_id'] = $isRegister['is_bu_id'];
        $user_info['work_id'] = $isRegister['company_work_type_id'];
        // }else{
        //     $user_info['id'] = $isRegister['id'];
        //     session(self::$appId."user_oauth",$user_info);
        //     $this->redirect('gelanfu-view/register/index.html#/');
        // }
        //保存用户会话信息
        session(self::$appId."user_oauth",$user_info);

        //保存微信配置信息
        $newconfig['appid'] = self::$appId;
        $newconfig['appsecret'] = self::$appSecret;
        $newconfig['bu_id'] = self::$bu_id;
        session('wxconfig_'.self::$type,$newconfig);

        $info['type'] = self::$type;
        $info['view'] = self::$url;
        if(self::$activity_id){
            $info['activity_id'] = self::$activity_id;
        }
        if(self::$is_open){
            $info['is_open'] = self::$is_open;
        }
        if (self::$on_activity_id){
            $info['on_activity_id'] = self::$on_activity_id;
        }
        if (self::$question_id){
            $info['question_id'] = self::$question_id;
        }
        // dump($info['view']);exit();
        $this->redirect( "Index/index" ,$info);
    }

    /**
     *查询用户是否注册完成
     */
    private function isRegister(&$user_info)
    {
        $obj = Db('member');
        $info = $obj->where(array("unionid"=>$user_info['unionid']))->find();
        return $info;
    }

    /*
  *  保存用户信息
  */
    // private function isBu($appId)
    // {
    //     $obj = Db('bu');
    //     $info = $obj->where(array('appid'=>$appId))->value('id');
    //     return $info;
    // }

    /*
    *  保存用户信息
    */
    private function handleNewUser( &$user_info ) {
        $obj = Db('member');
        $unionid =$obj->where(array("unionid"=>$user_info['unionid']))->find();
        //保存用户新
        if(empty($unionid)){
            $obj->insert( ['unionid'=>$user_info['unionid'],'city'=>$user_info['city'],'nickname'=>$user_info['nickname'],'sex'=>$user_info['sex'],'headimgurl'=>$user_info['headimgurl'], 'create_time'=>Date("Y-m-d H:i:s", time()),'is_bu_id'=>self::$bu_id] );
        }
    }

    /*
    *  保存用户关联的openid 和 unionid
    */
    private function handleMapOpenidAndUninid( &$user_info ) {
        $obj = Db('member_openid_unionid');
        $unionid =$obj->where(array("unionid"=>$user_info['unionid'],'bu_id'=>self::$bu_id))->find();
        //保存用户新
        if(empty($unionid)){
            $obj->insert( ['unionid'=>$user_info['unionid'], 'openid'=>$user_info['openid'], 'bu_id'=>self::$bu_id,'create_time'=>Date("Y-m-d H:i:s", time())] );
        }
    }

}
