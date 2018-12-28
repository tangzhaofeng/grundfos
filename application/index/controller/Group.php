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

class Group extends Controller
{

    /**
     * 创建分组
     **/
    public function createGroup($config,$groupName)
    {
        //获取access_token
       
        $access_toekn = $this->getAccessToken($config['appid'],$config['appsecret'],$config['type']);

        $queryUrl = 'https://api.weixin.qq.com/cgi-bin/groups/create?access_token='.$access_toekn;
        $data = '{"group":{"name":"'.$groupName.'"}}';

        return Curl::callWebServer($queryUrl, $data, 'POST');
    }


    /**
     * @descrpition 移动用户分组
     * @param $openid 要移动的用户OpenId
     * @param $to_groupid 移动到新的组ID
     * @return JSON {"errcode": 0, "errmsg": "ok"}
     */
    public function editUserGroup($config,$openid, $to_groupid){
        //获取ACCESS_TOKEN
        $access_toekn = $this->getAccessToken($config['appid'],$config['appsecret'],$config['type']);
        $queryUrl = 'https://api.weixin.qq.com/cgi-bin/tags/members/batchtagging?access_token='.$access_toekn;
        $data = '{"openid_list":["'.$openid.'"],"tagid":'.$to_groupid.'}';
        return Curl::callWebServer($queryUrl, $data, 'POST');

    }


    /**
     * 删除分组
     **/
    public function deleteGroup($config,$groupid)
    {
        //获取access_token
        $access_toekn = $this->getAccessToken($config['appid'],$config['appsecret'],$config['type']);

        $queryUrl = 'https://api.weixin.qq.com/cgi-bin/groups/delete?access_token='.$access_toekn;
        $data = '{"group":{"id":"'.$groupid.'"}}';
        return Curl::callWebServer($queryUrl, $data, 'POST');
    }


    /**
     * 获取二维码ticket
     */
    function getQrTicket($token)
    {
        //expire_seconds:有效时间 action_name:临时与永久 action_info:参数
        $qrcode = '{"expire_seconds": 604800, "action_name": "QR_STR_SCENE", "action_info": {"scene": {"scene_str": "test"}}}';
        $result = curl_post('https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$token,$qrcode);
        $result = json_decode($result,true);
        return urldecode($result['ticket']);
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