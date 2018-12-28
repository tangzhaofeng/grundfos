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

class Media extends Controller
{

    /**
     * 新增临时素材
     */
    public function temporary($config = [],$media_type = '',$fileurl = '')
    {
        //获取当前最新token
        $token = $this->stAccessToken($config['appid'],$config['appsecret'],$config['type']);

        //接口
        $api = "https://api.weixin.qq.com/cgi-bin/media/upload?access_token=".$token."&type=image";

        //使用http请求方式传输文件form方式,$fileurl文件的绝对地址
        $data['media'] = $this::addFile('/www/html/jue'.$fileurl);
        //  dump($fileurl);exit();
       $result = json_decode($this->http_post_media($api,$data));
//        dump($token);exit();
        if (empty($result->media_id)){
            return ['code'=>0];
        }else{
            return ['code'=>1,'media_id'=>$result->media_id];
        }
    }

    //curl请求,n个不行,这个是可以行的
    private function http_post_media($url,$data)
    {
         $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_URL, $url);
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }

        /**
     * 上传文件
     * @param $filename 文件名+路径
     * @return \CURLFile|string 返回可直接用于Curl发送的模式
     * PHP5.5以后，将废弃以@文件名的方式上传文件。
     */
    public static function addFile($filename){
        return class_exists('\CURLFile') ? new \CURLFile($filename) : '@'.$filename;
    }


    /**
     * 获取最新accsee_token
     */
    public function stAccessToken($appid,$appsecret,$type)
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
     * 获取最新accsee_token
     */
    public function getAccessToken($appid,$appsecret,$type)
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
    function saveAccessToken($appid,$appsecret,$type)
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

}