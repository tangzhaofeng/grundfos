<?php

namespace app\api\controller;

use app\index\controller\Redpack;
use think\Controller;
use app\index\controller\Index;
use think\Db;

class Foreignapi extends Controller
{
    // public function getToken() {
    //     try {
    //         $appid = '2aBaSavwkNGzX3ynIGRFaqM1JpyOU6IV';
    //         $secret = 'dwJw4^vLjM*qo7Sn';
    //         if (isset($_REQUEST['signature'])) {
    //             $signature = $_REQUEST['signature'];
    //             if ((time() - $_REQUEST['timestamp']) > 30) {
    //                 return json(['error' => 30010, 'errormsg' => '请求时间太久远']);
    //             }
    //             if ($signature == md5($appid.$secret.$_REQUEST['timestamp'])) {
    //                 $redis = new \Redis;
    //                 $redis->connect('127.0.0.1', 6379);
    //                 $redis->auth("redis");
    //                 //统计次数
    //                 $date = date('Y-m-d',time());
    //                 $number = $redis->get('glf_api_'.$date);
    //                 if ($number !== null) {
    //                     if ($number >= 20) {
    //                         return json(['error' => 30015, 'errormsg' => '今日请求次数已满']);
    //                     }
    //                     $redis->set('glf_api_'.$date, $number+1);
    //                 } else {
    //                     $redis->set('glf_api_'.$date, 0);
    //                     $redis->expire('glf_api_'.$date, 3600*25);
    //                 }
    //                 //获取token
    //                 $token = $redis->get('Foreignapi_glf_token');
    //                 if (!$token) {
    //                     $token = md5(md5('sult'.time()));
    //                     $redis->set('Foreignapi_glf_token', $token);
    //                     $redis->expire('Foreignapi_glf_token', 7200);
    //                 }
    //                 return json(['error' => 0, 'token' => $token]);
    //             } else {
    //                 return json(['error' => 30070, 'errormsg' => '数字签名错误']);
    //             }
    //         }else {
    //             return json(['error' => 30001, 'errormsg' => '未做数字签名']);
    //         }
    //     } catch (\Exception $e) {
    //         return json(['error' => 30000, 'errormsg' => '未知错误']);
    //     }
    // }

    public function getInfo() {
        try {
            $code = $_REQUEST['code'];
            $openidurl = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=wx846178961ef0ca8b&secret=cf0eff7e06f50680d3a4d4304f16d26d&code={$code}&grant_type=authorization_code";
            $res = json_decode($this->http_Curl($openidurl,[],'GET'), true);
            // dump($res);
            $openid = $res['openid'];
            $index = new Index;
            $token = $index->getAccessToken('wx846178961ef0ca8b', 'cf0eff7e06f50680d3a4d4304f16d26d','a');
            $infourl = "https://api.weixin.qq.com/cgi-bin/user/info?access_token={$token}&openid={$openid}";
            $res = json_decode($this->http_Curl($infourl,[],'GET'), true);
            return json(['error' => 0, 'data' => $res]);
        } catch (\Exception $e) {
            return json(['error' => 30052, 'errmsg' => $res['errmsg']]);
        }
    }

    public function sendRedpacket() {
        try {
            $openid = $_REQUEST['openid'];
            $timestamp = $_REQUEST['timestamp'];
            $signature = $_REQUEST['signature'];
            $code = $_REQUEST['code'];
            $money = $_REQUEST['money'];
            $appid = '2aBaSavwkNGzX3ynIGRFaqM1JpyOU6IV';
            $secret = 'dwJw4^vLjM*qo7Sn';
            if ($money > 20) {
                throw new \Exception;
            }
            if ($signature && 
                ($signature == md5($appid.$code.$secret.$timestamp)) &&
                (time() - $timestamp < 30000)
            ) {
                $redpack = new Redpack();
                $info = $redpack->index($openid,$money);//发送红包
                if($info['err_code'] == 'SUCCESS'){
                    //流水入库
                    Db::table('xk_foreign_redpacket')->insert([
                        'time' => date('Y-m-d H:i:s' ,time()),
                        'money' => $money,
                        'openid' => $openid,
                    ]);
                    return json(['error' => 0]);
                } else {
                    return json(['error' => 30090, 'errormsg' => $info]);
                }
            } else {
                return json(['error' => 30001, 'errormsg' => '鉴权错误：'.md5($appid.$code.$secret.$timestamp)]);
            } 
        } catch (\Exception $e) {
            return json(['error' => 30052, 'errormsg' => '未知错误']);
        }
        
    }

    protected function http_Curl($url,$paramArray = array(),$method = 'POST'){
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