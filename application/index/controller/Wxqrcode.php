<?php
/**
 * Created by PhpStorm.
 * User: juplus-06
 * Date: 2018/4/23
 * Time: 15:30
 */

namespace app\index\controller;
use app\admin\model\MemberModel;
use app\admin\model\UrlinspectModel;
use think\Controller;
use think\Db;
use think\Cache;
use app\api\model\AppsecretModel;
use app\api\model\AccessTokenModel;
use app\api\model\JsapiticketModel;
use think\Session;

class Wxqrcode extends Controller
{

    /**
     * 带参数二维码生成
     **/
    public function index($appid,$appsecret,$config)
    {
        //获取access_token
        $access_toekn = $this->getAccessToken($appid,$appsecret,$config);

        //获取ticket
        $ticket = $this->getQrTicket($access_toekn);

        //获取二维码地址
        $qrcode = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.$ticket;
   
        $dow = ROOT_PATH . 'public' . DS . 'uploads/';

        $file = $dow.uniqid().'.jpg';
        $ch = curl_init($qrcode);

        $fp = fopen($file,'wb');
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);

        return $file;
    }

    /**
     * 获取二维码ticket
     */
    function getQrTicket($token)
    {
        //expire_seconds:有效时间 action_name:临时与永久 action_info:参数
        $qrcode = '{"action_name": "QR_LIMIT_SCENE", "action_info": {"scene": {"scene_str": "test"}}}';
        $result = curl_post('https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$token,$qrcode);
        $result = json_decode($result,true);
        return urldecode($result['ticket']);
    }

    /**
     * 获取最新accsee_token
     */
    public function getAccessToken(&$appid,&$appsecret,&$type)
    {
        $token = new AccessTokenModel();
        $old_accsee = $token->where('app_secret_id',$type)->order('id desc')->group('id')->find();
        // dump($old_accsee);exit();
        if ($old_accsee){
            //如果有运算时间超过2小时重新获取
            if ((time()-$old_accsee['time']) > 7160){
                return $this->saveAccessToken($appid,$appsecret,$type);
                exit();
            }else{
                return $old_accsee['token'];
            }
        }else{
            return $this->saveAccessToken($appid,$appsecret,$type);
            exit();
        }
    }
    /**
     * 获取accsee_token并存储
     */
    function saveAccessToken(&$appid,&$appsecret,&$type)
    {
        $token = new AccessTokenModel();
        $access = curl_get('https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appid.'&secret='.$appsecret);
        $info = json_decode($access);

        $token->save([
            'token' => $info->access_token,
            'app_secret_id' => $type,
            'time' => time()
        ]);
        return $info->access_token;
    }


    /**
     *从微信服务器获取图片
     **/
    function doWechatPic($serverId)
    {
        $type = session('type');
        $wxconfig = session('wxconfig_'.$type);
        $url = input('param.url');

        //获取access_token
        $access_token = $this->getAccessToken($wxconfig['appid'],$wxconfig['appsecret']);
        //通过接口获取图片流
        $pic_url = "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token={$access_token}&media_id={$serverId}";
        $filebody = file_get_contents($pic_url);

        return $filebody;
    }



}