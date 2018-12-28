<?php

namespace app\admin\controller;

use think\Db;
use \think\Loader;
use app\admin\model\BuModel;
use app\index\controller\Index;

class Wxmenu extends Base
{
    public function index() {
        $params = input('param.');
        if (isset($params['bu_type'])) {
            $appsec = Db::table('xk_bu_app_secret')->where('type', $params['bu_type'])->find();
            $index = new Index;
            $token = $index->getAccessToken($appsec['appid'] , $appsec['appsecret'] ,$params['bu_type']);
            $url = 'https://api.weixin.qq.com/cgi-bin/menu/get?access_token='.$token;
            $res = json_decode($this->http_Curl($url), true)['menu'];
            foreach ($res['button'] as $k1 => $v1) {
                
                if (!empty($v1['sub_button'])) {
                    foreach ($res['button'][$k1]['sub_button'] as $k2 => $v2) {
                        unset($res['button'][$k1]['sub_button'][$k2]['sub_button']);
                    }
                } else {
                    unset($res['button'][$k1]['sub_button']);
                }
            }
            $this->assign("data", $res);
            $this->assign("bu_type", $params['bu_type']);
            return $this->fetch('setmenu');
        } else {
            $BuModel = new BuModel;
            $data = $BuModel->select();
            $this->assign("url", url('index'));
            $this->assign("budata", $data);
            return $this->fetch();
        }
    }

    public function setmenu () {
        $bu_type = input('param.bu_type');
        $menuConf = urldecode($_POST['menuConf']);
        $appsec = Db::table('xk_bu_app_secret')->where('type', $bu_type)->find();
        $index = new Index;
        $token = $index->getAccessToken($appsec['appid'] , $appsec['appsecret'] ,$bu_type);
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$token;
        $res = $this->http_Curl($url,$menuConf);
        echo $res;
    }   

    function http_Curl($url,$paramArray = array(),$method = 'POST'){
        $ch = curl_init();
        if ($method == 'POST')
        {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $paramArray);
        }
        else if (!empty($paramArray))
        {
            $url .= '?' . http_build_query($paramArray);
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT,10);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if (false !== strpos($url, "https")) {
            // 证书
            // curl_setopt($ch,CURLOPT_CAINFO,"ca.crt");
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,  false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  false);
        }
        $resultStr = curl_exec($ch);
        curl_close($ch);

        return $resultStr;
    }
}