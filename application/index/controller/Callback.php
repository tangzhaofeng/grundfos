<?php

namespace app\index\controller;
use think\Controller;
use think\Request;
use think\Cache;
use think\Db;


class CallBack extends Controller
{
    private $appid = 'wx846178961ef0ca8b';                 //微信公众号APPID
    private $appsecret = 'cf0eff7e06f50680d3a4d4304f16d26d';             //密匙
    private $url = 'https://www.juplus.cn/glf/index/Callback/login';       //微信回调地址


    public function start()
    {

        $curl = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $this->appid . '&redirect_uri=' . urlencode($this->url) . '&response_type=code&scope=snsapi_userinfo&state=123#wechat_redirect';
        header('location:' . $curl);


    }

    /**
     * 登录
     */
    public function login()
    {
        header('Content-Type: text/html; charset=UTF-8');
        $code = $_GET['code'];
        $access_token = $this->getUserAccessToken($code);

        $userInfo = $this->getUserInfo($access_token);
        $this->handleNewUser( $userInfo );  //保存用户信息
        $user = Db('member')->where(['unionid'=>$userInfo['unionid']])->find();
        cache::set($user['id'],$user['id']);
        if(!empty($user)){
            $userId = $user['id'];
        }else{
            $userId = Db('member')->getLastInsID();
        }
        $l =  'https://www.juplus.cn/glf/gelanfu-view/zhongqiu/index.html?id='.$userId;
        header('location:'.$l);

        //var_dump($userInfo);
    }

    /**
     * 获取授权token
     * @param $code
     * @return bool|string
     */
    private function getUserAccessToken($code)
    {
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$this->appid&secret=$this->appsecret&code=$code&grant_type=authorization_code";
        $res = file_get_contents($url);
        return json_decode($res);
    }
    /**
     * 获取用户信息
     * @param $accessToken
     * @return mixed
     */
    private function getUserInfo($accessToken)
    {
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token=$accessToken->access_token&openid=$accessToken->openid&lang=zh_CN";
        $userInfo = file_get_contents($url);
        return json_decode($userInfo, true);
    }




    /*
   *  保存普通用户信息
   */
    private function handleNewUser( $userInfo ) {
        $obj = Db('member');
        $unionid =$obj->where(array("unionid"=>$userInfo['unionid']))->find();

        //保存用户新
        if(empty($unionid)){
            $name = base64_encode($userInfo['nickname']);
            $obj->insert( ['openid'=>$userInfo['openid'],'unionid'=>$userInfo['unionid'],'city'=>$userInfo['city'],'nickname'=>$name,'sex'=>$userInfo['sex'],'headimgurl'=>$userInfo['headimgurl'], 'create_time'=>Date("Y-m-d H:i:s", time()),'is_bu_id'=>2] );
        }
    }
    /**
     * 是否摇过
     */
    public function status()
    {
        $param = input('param.');
        $obj = Db('draw');
        $model = Db('member');
        $member = $obj->where(['openid'=>$param['id']])->find();
      //  $data = ['id'=>11229,'url'=>$this->get_img(),'nickname'=>'灵梦','headimgurl'=>'http://thirdwx.qlogo.cn/mmopen/vi_32/YQBwClu5f6oay1yHm9VV2azD0oPzAUASWe2Gnbqmxpicw7B8K3Rnr1NhKhicrmlaRVtaic2ubY5GLbuhZGh2umm7Q/132']; //cache::get($param['id'].'1');

        $data = cache::get($param['id']);
/*
        if(!empty($member) && $member['status'] != 2 ){
            return json(['code'=>1,'msg'=>'已摇','data'=>$data]);
        }
        $m = $model->where(['id'=>$param['id']])->find();
        $nickname = base64_decode($m['nickname']);
        if( $member['name'] == '' || $member['tel'] == '' || $member['adress'] == ''){
            if($member['status'] == 2 ){
                return json(['code'=>'true','msg'=>'中奖用户','data'=>$data]);

            }
        }*/
        return json(['code'=>'false','msg'=>'未中奖','data'=>$data]);


    }
    /**
     * 接收图片昵称
     */
    public function getImage()
    {
        $param = input('param.');
        cache($param['id'], $param, 300000);

    }
    public function setUrl()
    {
        $param = input('param.');
        $data = cache::get($param['id']);
        $model = Db('member');
        $member = $model->where(['id'=>$param['id']])->find();
        $this->upload($member['headimgurl'],$param);
        $file ='https://www.juplus.cn/glf/public/zhongqiu/'.$param['id'].'.png';
        $data['headimgurl'] =$file;
        return json($data);
    }

    public function upload($url, $param,$path = 'public/zhongqiu/')
    {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
            $file = curl_exec($ch);
            curl_close($ch);
           // $filename = pathinfo($url, PATHINFO_BASENAME);
            $filename =$param['id'].'.png';// pathinfo($url, PATHINFO_BASENAME);
            $resource = fopen($path . $filename, 'a');
            fwrite($resource, $file);
            fclose($resource);

}



    /**
     * 摇一摇判断次数
     */
    public function shake()
    {
        $param = input('param.');
        $obj = Db('draw');
        $model = Db('member');
        $unionid =$obj->where(array("openid"=>$param['id']))->find();

        if(empty($unionid)){
            $obj->insert (['openid' => $param['id']],['create_time'=>Date("Y-m-d H:i:s", time())]);
        }
        $status = $obj->where(['status' => 2])->find();
        $array = [['prize_class' => 0, 'prize_name' => '谢谢参与', 'prize_rank' => 9999],
            ['prize_class' => 1, 'prize_name' => '获得水泵', 'prize_rank' => 1],
        ];
        $finds = ['prize_class', 'prize_name', 'prize_rank'];
        $member = $model->where(['id' => $param['id']])->find();
        $nickname = base64_decode($member['nickname']);
        //判断是否是注册用户
        if (empty($member['tel'])) {
          $data = ['code' => 0, 'url' => $this->get_img(), 'nickname' => $nickname, 'headimgurl' => $member['headimgurl']];
          return json($data);
         }
        //判断是否是公司人员
        if (strpos($member['company_name'], '格兰富') !== false) {
            $data = ['code' => 0, 'url' => $this->get_img(), 'nickname' => $nickname, 'headimgurl' => $member['headimgurl']];
            return json($data);
        }
        //判断用户是否是经销商
        if ($member['company_id'] !== 6 && $member['company_id'] !== 85) {
            $data = ['code' => 0, 'url' => $this->get_img(), 'nickname' => $nickname, 'headimgurl' => $member['headimgurl']];
            return json($data);
        }

            //判断是否有人抽到奖励
            if (empty($status)) {

                foreach ($array as $key => $value) {
                    $arr[$value[$finds[0]]] = $value[$finds[2]];

                }
                $id = $this->get_rand($arr);
                if ($id == 0) {
                    $data = ['code' => 0, 'url' => $this->get_img(), 'nickname' => $nickname, 'headimgurl' => $member['headimgurl']];
                    return json($data);
                }

                $data = ['code' => 1, 'url' => $this->get_img(), 'nickname' => $nickname, 'headimgurl' => $member['headimgurl']];
                return json($data);
            }

            $data = ['code' => 0, 'url' => $this->get_img(), 'nickname' => $nickname, 'headimgurl' => $member['headimgurl']];
            return json($data);


    }
    /**
     * 随机抽出一张图片
     */
      function get_img()
     {
         $num = mt_rand(1,20);
         $url = 'https://www.juplus.cn/glf/gelanfu-view/zhongqiu/static/img/'.$num.'.png';
         return $url;
     }



    /**
     * 计算概率，返回id
     */
    function get_rand($arr)
    {
        $proSum = array_sum($arr);//概率总和

        if($proSum === 0 ){
            return $proSum;
        }
        foreach ($arr as $key=>$val){
           // $randNum = mt_rand(1, $proSum);
            $randNum = mt_rand(1, 10000);
            if ($randNum <= $val) {
                $result = $key;
                break;
            } else {
                $proSum -= $val;
            }
        }
        unset($arr);
        return $result;

    }


    public function zhJiang()
    {
        $param = input('param.');
        $obj = Db('draw');
        $obj->where(['openid'=>$param['id']])->setField('status','2');

    }

    /**
     * 未中奖用户和中奖用户信息保存
     */
    public function setInfo()
    {
        $param = input('param.');

        $obj = Db('draw');
        $status = $obj->where(['openid'=>$param['id']])->find();
        if($status['status'] == 2){
                if(count($param) == 4){
                    $bool = Db('draw')->where('openid',$param['id'])->update(['name'=>$param['name']]);
                    $bool = Db('draw')->where('openid',$param['id'])->update(['tel'=>$param['tel']]);
                    $bool = Db('draw')->where('openid',$param['id'])->update(['adress'=>$param['adress']]);
                    $bool = Db('draw')->where('openid',$param['id'])->update(['create_time'=>Date("Y-m-d H:i:s", time())]);
                    return $bool;
                }
        }
          return  0;
    }

    /**
     * 此AccessToken   与 getUserAccessToken不一样
     * 获得AccessToken
     * @return mixed
     */
    private function getAccessToken()
    {
        // 获取缓存

        // 缓存不存在-重新创建
        if (empty($access)) {
            // 获取 access token
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->appid}&secret={$this->appsecret}";

            $accessToken = file_get_contents($url);

            $accessToken = json_decode($accessToken);
            // 保存至缓存
            $access = $accessToken->access_token;
        }
        return $access;
    }

    /**
     * 获取JS证明
     * @param $accessToken
     * @return mixed
     */
    private function _getJsapiTicket($accessToken)
    {

        // 获取缓存
        // 缓存不存在-重新创建
        if (empty($ticket)) {
            // 获取js_ticket
            $url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token='.$accessToken.'&type=jsapi';
          //  header('location:' . $url);

            $jsTicket = file_get_contents($url);
            //var_dump($jsTicket);exit();

            $jsTicket = json_decode($jsTicket);
            // 保存至缓存
            $ticket = $jsTicket->ticket;
        }
        return $ticket;
    }

    /**
     * 获取JS-SDK调用权限
     */
    public function shareAPi()
    {
        $param = input('param.');
       // $data = explode("?",$param['url']);
        header("Access-Control-Allow-Origin:*");
        // 获取accesstoken
        $accessToken = $this->getAccessToken();
        //var_dump($accessToken);exit;
       // $accessToken = '13_4H-7o6dr35cBEvbjMPszON2Oyt7YzVSoEy392_7ySngTZbHQXHZ146gCS9-n6iY6p2lI_4ksj_7XmZF5q6j-491-GmOcZzit0QR6rssi7bEyZThDZi0CMYeHcBkcaRUMqTmLqQ6egQrj6gb2LUFjAHAXGR';
        // 获取jsapi_ticket
        $jsapiTicket = $this->_getJsapiTicket($accessToken);
       // var_dump($jsapiTicket);exit();
        // -------- 生成签名 --------
        $wxConf = [
            'jsapi_ticket' => $jsapiTicket,
            'noncestr' => '123456',
            'timestamp' => time(),
            'url' => $param['url'],  //这个就是你要自定义分享页面的Url啦
        ];
        $string1 = sprintf('jsapi_ticket=%s&noncestr=%s&timestamp=%s&url=%s', $wxConf['jsapi_ticket'], $wxConf['noncestr'], $wxConf['timestamp'], $wxConf['url']);

        $wxConf['signature'] = sha1($string1);
        $wxConf['appid'] = $this->appid;
        return json($wxConf);
    }

}