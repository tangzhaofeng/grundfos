<?php

namespace app\api\controller;
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods:GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: X-PINGOTHER, Content-Type');
header('Access-Control-Max-Age: 86400');

use app\admin\model\MemberModel;
use app\api\model\AppsecretModel;
use think\Controller;
use think\Session;

class Base extends Controller
{

    public function _initialize()
    {
        // $type = session('type');
        // $wxconfig = session('wxconfig_'.$type);
        // $member_info = session($wxconfig['appid']."user_oauth");
        // dump($wxconfig);exit();
        //根据当前微信链接type获取不同bu的appid与appsecret
        //$type = input('param.type');
        //$App = new AppsecretModel();

       //$wxconfig = $App->where('type',$type)->find();

      //if (!Session::has($wxconfig['appid'].'user_oauth')){
    //            session('type',$type);
    //            session('url_'.$type,$this->get_url());
    //            session('wxconfig_'.$type,$wxconfig);
    //            $this->redirect("index/Index/index");
//        }
    }

    /**
     * 检查用户是否注册
     **/
    public function is_register()
    {
        $type = session('type');
        $wxconfig = session('wxconfig_'.$type);
        $member_info = session($wxconfig['appid']."user_oauth");

        $obj = new MemberModel();
        $result = $obj->where('id',$member_info['id'])->find();
        //查询name有无,若无则跳转注册
        
        if($result['name'] == ''){
            // $this->redirect('gelanfu-view/register/index.html#/');
            return 3;
        }else{
            return 1;
        }
    }

    public function get_url() {
        $sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
        $php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
        $path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
        $relate_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self.(isset($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : $path_info);
        return $sys_protocal.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '').$relate_url;
    }
}