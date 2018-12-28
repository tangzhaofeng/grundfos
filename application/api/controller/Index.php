<?php

namespace app\index\controller;
use app\admin\model\MembergroupModel;
use app\admin\model\MemberModel;
use app\admin\model\MemberopenunionModel;
use app\admin\model\UrlinspectModel;
use app\api\model\BuModel;
use think\Controller;
use think\Db;
use think\Cache;
use app\api\model\AppsecretModel;
use app\api\model\AccessTokenModel;
use app\api\model\JsapiticketModel;
use think\Session;

class Index extends Controller
{

    function __construct()
    {
        parent::__construct();
    }
    /**
     * 清楚缓存
     */
    function rmChche(&$wxconfig,&$type)
    {
        // dump(session('wxconfig_'.$type));exit();
    //    Session::delete($wxconfig['appid'].'user_oauth');
    //    Session::delete('wxconfig_'.$type);
    //    echo '正在清楚缓存请稍后';
    //    exit();
    }

    //首页
    function index() {
        header('Content-Type:text/plain;charset=utf-8');
        $app = new AppsecretModel();
        $oldview = input('param.view');//获取跳转的视图
        $view = str_replace('/',',',$oldview);
        $type = input('param.type');//公众号标签
        //获取微信配置
        $wxconfig = $app->where('type',$type)->find();
        //清楚缓存
        $this->rmChche($wxconfig,$type);
        session('view',$oldview);
        session('type',$type);

        $param = array();
        $param['appId'] = $wxconfig['appid'];
        $param['appSecret'] = $wxconfig['appsecret'];
        $param['bu_id'] = $wxconfig['bu_id'];
        $param['type'] = $type;
        $param['url'] = $view;
        if(input('param.is_wx')){
            $param['is_wx'] = input('param.is_wx');
        }
        if(input('param.activity_id')){
            $param['activity_id'] = input('param.activity_id');
        }
        //is_open代表是否是私密与公开
        if(input('param.is_open')){
            $param['is_open'] = input('param.is_open');
        }
        //扫码完成签到的id
        if(input('param.on_activity_id')){
            $param['on_activity_id'] = input('param.on_activity_id');
        }

        //判断有微信用户seesion则跳转 无用户则session则进入授权
        if(!Session::has($wxconfig['appid'].'user_oauth')){
            $this->redirect( "Wechat/login",$param);
        }
        //判断有当然微信配置是否完成 无则进入授权
        if(!Session::has('wxconfig_'.$type)){
            $this->redirect( "Wechat/login" ,$param);
        }
        //view:跳转的界面 type:查询的不同bu的appid与appSecret
        $newview = $this->conversion($oldview);
        //判断在注册白名单中的,不在不需要一定注册
        $result = $this->isUrl($newview);
        if($result){
            //检查用户是否已经注册完成
            $this->isRegister(session($wxconfig['appid'].'user_oauth'),$wxconfig['appid'],$param);
        }

        $h_url = '/glf/gelanfu-view';
        if(input('param.activity_id')){
            if(input('param.code')){
                $this->redirect($h_url.$newview.'?activity_id='.input('param.activity_id').'&is_open='.input('param.is_open').'&code='.input('param.code'));
            }
            $this->redirect($h_url.$newview.'?activity_id='.input('param.activity_id').'&is_open='.input('param.is_open'));
        }
        //签到专属
        if (input('param.on_activity_id')){
            $this->redirect($h_url.$newview.'?on_activity_id='.input('param.on_activity_id'));
        }
        //邀请好友
        if (input('param.from_member_id')){
            $this->redirect($h_url.$newview.'?from_member_id='.input('param.from_member_id'));
        }
        $this->redirect($h_url.$newview);
    }

    /**
     * 转化传入view参数
     */
    function conversion($oldview)
    {
        $newview = str_replace(',','/',$oldview);
        if (!strstr($newview,strval('html'))) {
            $newview = $newview.'.html';
        }
        return $newview;
    }

    /**
     * 检查url是否在注册白名单中
     */
    function isUrl(&$newview)
    {
        $url_lin = new UrlinspectModel();
        return $url_lin->where('view_url',$newview)->find();
    }

    /**
     * 检查用户是否注册完成
     */
    function isRegister($member_info,$appid,$param)
    {
        $id = $member_info['id'];
        $obj = MemberModel::where('id',$id)->find();

        if(!$obj['name']){

            if(!empty($param['activity_id'])){

                $this->redirect('gelanfu-view/register/index.html?activity_id='.$param['activity_id'].'&is_open='.$param['is_open']);
            }
            $this->redirect('gelanfu-view/register/index.html#/');
        }else{
            //如果没有buid添加buid
            if(!$member_info['bu_id']){
                $old_member_info = $member_info;
                $old_member_info['bu_id'] = $obj['is_bu_id'];
                session($appid.'user_oauth',$old_member_info);
            }
        }
    }


    /**
     * 获取JSJDK配置
     */
    public function getWxConfig()
    {
        // dump(input('param.url'));exit();
        $type = session('type');
        $wxconfig = session('wxconfig_'.$type);
        $url = input('param.url');
        $type = session('type');
        if(strpos($url,'amp;') !==false){
            $url = str_replace('amp;','',$url);
        }

        //获取access_token
        $access_toekn = $this->getAccessToken($wxconfig['appid'],$wxconfig['appsecret'],$type);

        //获取jsapi_ticket
        $ticket = $this->getTicket($access_toekn,$type);

        //生成签名signature
        $noncestr=createNonceStr();//随机字符串
        $timestamp=time();//时间戳
        // dump($ticket.':'.$noncestr.':'.$timestamp.':'.$url);exit();
        $string='jsapi_ticket='.$ticket.'&noncestr='.$noncestr.'&timestamp='.$timestamp.'&url='.$url;//拼接signature并sha1

        $signature = sha1($string);

        $signPackage = array(
            "appId" =>$wxconfig['appid'],
            "nonceStr" =>$noncestr,
            "timestamp" => $timestamp,
            // "url"       => $url,
            "signature" => $signature
            // "string" => $string
        );
        //  dump($signPackage);exit();
        return returninfos('1','成功',$signPackage);
    }


    /**
     * 获取最新jsapi_ticket
     */
    public function getTicket(&$access_toekn,&$type)
    {
        $obj = new JsapiticketModel();
        $app_secret_id = session('app_secret_id');
        $old_ticket = $obj->where('type',$type)->order('id desc')->group('id')->find();

        if ($old_ticket){
            //如果有运算时间超过2小时重新获取
            if ((time()-$old_ticket['time']) > 7160){
                return $this->saveTicket($access_toekn,$app_secret_id,$type);
                exit();
            }else{
                return $old_ticket['ticket'];
            }
        }else{
            return $this->saveTicket($access_toekn,$app_secret_id,$type);
            exit();
        }

    }
    /**
     * 获取jsapi_ticket并存储
     */
    public function saveTicket(&$access_toekn,$app_secret_id,&$type)
    {
        $obj = new JsapiticketModel();
        $ticket = curl_get('https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token='.$access_toekn.'&type=jsapi');
        // dump($access_toekn);exit();
        $info = json_decode($ticket);
        $obj->save([
            'app_secret_id' => $app_secret_id,
            'ticket' => $info->ticket,
            'time' => time(),
            'type' => $type
        ]);
        return $info->ticket;
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









    /**
     * 带参数二维码生成
     **/
    public function qrCode($appid,$appsecret,$config,$scene,$is_long=0)
    {
        //获取access_token
        $access_toekn = $this->getAccessToken($appid,$appsecret,$config);
        // dump($access_toekn);exit();

        //获取ticket
        $ticket = $this->getQrTicket($access_toekn,$scene,$is_long);
        // dump($ticket);exit();

        //获取二维码地址
        $qrcode = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.$ticket;
        // dump($qrcode);exit();
        $dow = ROOT_PATH . 'public' . DS . 'uploads/';

        $name = uniqid().'.jpg';
        $file = $dow.$name;
        $ch = curl_init($qrcode);

        $fp = fopen($file,'wb');
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);

        return '/glf/public' . DS . 'uploads/'.$name;
    }

    /**
     * 获取二维码ticket
     */
    function getQrTicket($token,&$scene,$is_long)
    {
        //expire_seconds:有效时间 action_name:临时与永久 action_info:参数
        if ($is_long == 0){
            $qrcode = '{"action_name": "QR_LIMIT_SCENE", "action_info": {"scene": {"scene_str": "'.$scene.'"}}}';
        }else{
            $qrcode = '{"expire_seconds": 2592000, "action_name": "QR_SCENE", "action_info": {"scene": {"scene_str": "'.$scene.'"}}}';
        }

        $result = curl_post('https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$token,$qrcode);

        $result = json_decode($result,true);
        return urldecode($result['ticket']);
    }


    /**
     * 对未分组的添加的分组里面
     */
 function show()
   {
        $member = new MemberModel();
        $obj = new BuModel();
        $bu = collection($obj->all())->toArray();
        $openunion = new MemberopenunionModel();
        $app = new AppsecretModel();
        $group = new Group();
        $mem_group = new MembergroupModel();

        //查找未分组的用户
        $all_member = collection($member->all())->toArray();
//        dump($all_member);exit();
        //查询unionid与bu_id得到对应的openid
//        foreach ($bu as $k=>$v){

            $config = $app->where('bu_id',3)->find();
            $data = [];
            foreach ($all_member as $key=>$val){

                $openid = $openunion->where('bu_id',$config['id'])->where('unionid',$val['unionid'])->find();
               
                if($openid['bu_id']){
                     $data[$key]['openid'] = $openid['openid'];
                     $data[$key]['id'] = $val['id'];
                    // dump($openid);exit();
                //    $info = $group->editUserGroup($config,$openid['openid'],110);
                    //  dump($info);exit();
                    $groupModel = Db('grouping');
                    $group_id = $groupModel->where('bu_id',$config['id'])->where('wx_id',100)->value('id');

                    $mem_group->insert(['group_id'=>$group_id,'bu_id'=>$config['id'],'member_id'=>$val['id']]);
                    
//                    break;
                }
            }
            dump($data);exit();
//        }

    }

}
?>