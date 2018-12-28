<?php
namespace app\index\controller;

use think\Controller;
use think\Loader;
use think\Db;

class Menu extends Controller
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
        //  dump(self::$is_wx);exit();
        //配置微信公众号
        vendor("LaneWeChat.config");

        \weChatConfig::setAppId(self::$appId); //juplus wxb42ad5150aacbee4
        \weChatConfig::setAppSecret(self::$appSecret); //juplus 8b023a4ec3bfb58ecaf1b96b95b2e1ba

        //自动载入函数载入相关操作类
        vendor("LaneWeChat.wechat");

    }

    //获取菜单信息
    function getMenu(){
       
		$ret = \LaneWeChat\Core\Menu::getMenu();
	   dump($ret);
       
    }



}
