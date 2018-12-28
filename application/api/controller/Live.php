<?php
/**
 * Created by PhpStorm.
 * User: juplus-06
 * Date: 2018/5/31
 * Time: 14:17
 */

namespace app\api\controller;
use app\admin\model\BuModel;
use app\admin\model\CategoryModel;
use app\admin\model\CompanyModel;
use app\admin\model\CompanyworkModel;
use app\admin\model\LivecommentModel;
use app\admin\model\LiveModel;
use app\admin\model\LiveSignUpModel;
use app\admin\model\MemberintegralModel;
use app\admin\model\MemberModel;
use app\admin\model\ProgramappsecretModel;
use app\admin\model\ProgramformidModel;
use app\admin\model\SmsModel;
use app\admin\model\VideoModel;
use app\index\controller\Curl;
use app\index\controller\Wylive;
use think\Controller;
use think\Db;
use app\admin\model\GoodsModel;
use app\admin\controller\Messagesend;
use app\index\controller\Index;
use app\admin\model\AppsecretModel;
use app\api\controller\Sms;
use CCP_REST_DEMO_PHP_v27r1\Demo\SendTemplateSMS;


header('Access-Control-Allow-Methods: GET, POST, PUT,DELETE,OPTIONS');
header('Access-Control-Allow-Origin : *');
header("Access-Control-Allow-Headers", "*");
header('Access-Control-Allow-Credentials: true');


class Live extends Controller
{
    private $wylive;
    private $memberModel;
    // private $appid = 'wx010914c57edaaeef';
    // private $appsecret = '736a41f7172f712286cfef2c86cb5cf9';
    private $appid = 'wx8e369739124fdeb8';
    private $appsecret = 'e8a8a06dbbe327295d4084594e0e3adc';
    private $curl;
    function __construct()
    {
        parent::__construct();
        $this->wylive = new Wylive();
        $this->memberModel = new MemberModel();
        $this->curl = new Curl();
    }
    /**
     * 创建频道
     */
    public function channelCreate()
    {
        if (request()->port()){
            $param = input('post.');
            $result = $this->wylive->channel_add($param['name']);
            return json($result);
        }
    }
    /**
     * 频道删除
     */
    public function channelDel()
    {
        if (request()->port()){
            $param = input('post.');
            $result = $this->wylive->channel_delete($param['cid']);
            return json($result);
        }
    }
    /**
     * 直播完毕回调地址
     */
    public function liveEnd()
    {
        if (request()->port()){
            $param = input('post.');
            $result = $this->wylive->live_end($param['recordClk']);
            return json($result);
        }
    }
    /**
     * 频道更新
     */
    public function channelUp()
    {
        if (request()->port()){
            $param = input('post.');
            $result = $this->wylive->channel_update($param['name'],$param['cid']);
            return json($result);
        }
    }
    /**
     * 获取频道信息
     */
    public function channelGet()
    {
        if (request()->port()){
            $param = input('post.');
            $result = $this->wylive->channel_get($param['cid']);
            return json($result);
        }
    }
    /**
     *  获取频道列表
     *   records int 单页记录数，默认值为10    否
     *   pnum    int 要取第几页，默认值为1 否
     *   ofield  String  排序的域，支持的排序域为：ctime（默认）  否
     *   sort    int 升序还是降序，1升序，0降序，默认为desc  否
     */
    public function ChannelList()
    {
        if (request()->port()){
            $param = input('post.');
            $result = $this->wylive->channel_list();
            return json($result);
        }
    }
    /**
     * 重新获取推流地址
     */
    public function channelReset()
    {
        if (request()->port()){
            $param = input('post.');
            $result = $this->wylive->channel_reset($param['cid']);
            return json($result);
        }
    }
    /**
     * 暂停频道
     */
    public function channelPause()
    {
        if (request()->port()){
            $param = input('post.');
            $result = $this->wylive->channel_pause($param['cid']);
            return json($result);
        }
    }
    /**
     * 批量暂停频道
     */
    public function channelPauseList()
    {
        if (request()->port()){
            $param = input('post.');
            $result = $this->wylive->channel_pauselist($param['cidlist']);
            return json($result);
        }
    }
    /**
     * 恢复频道
     */
    public function channelResume()
    {
        if (request()->port()){
            $param = input('post.');
            $result = $this->wylive->channel_resume($param['cid']);
            return json($result);
        }
    }
    /**
     * 批量恢复频道
     */
    public function channelResumeList()
    {
        if (request()->port()){
            $param = input('post.');
            $result = $this->wylive->channel_resumelist($param['cidlist']);
            return json($result);
        }
    }
    /**
     * 获取频道的视频地址
     */
    public function channelVideoList()
    {
        if (request()->port()){
            $param = input('post.');
            $result = $this->wylive->channel_videolist($param['cid']);
            return json($result);
        }
    }

    /**
     * 根据code来获取到用户数据
     */
    public function getWxUnionid()
    {
        if (request()->port()){
            $param = input('post.');
            $api = 'https://api.weixin.qq.com/sns/jscode2session?appid='.$this->appid.'&secret='.$this->appsecret.'&js_code='.$param['code'].'&grant_type=authorization_code';
            $result = $this->curl->callWebServer($api);//获取到 session_key 和 openid 与Unionid
            // echo json_encode($result);exit;
            if (!isset($result['errcode'])){
                $info = $this->memberModel->unionidInfo($result['unionid']);//如果用户没有注册则自动完成基本信息注册
                $info['openid'] = $result['openid'];
                if ($info){
                    session('user_info',$info);
                    return json($info);
                }else{
                    $data = $this->autoReginster($param['userInfo'],$result);
                    $unionid = $this->memberModel->unionidInfo('',$data['faq_id']);
                    session('user_info',$unionid);
                    return json($data);
                }
            }else{
                return json(['code' => 0, 'data' => '', 'msg' => '获取失败']);
            }
        }
    }

    /**
     * 自动基本信息注册
     */
    public function autoReginster($userinfo,$result)
    {
        return $this->memberModel->saveOn([
            'nickname' => $userinfo['nickName'],
            'headimgurl' => $userinfo['avatarUrl'],
            'is_program' => 2,
            'create_time' => date('Y-m-d h:i:s',time()),
            'unionid' => $result['unionid'],
            'live_openid' => $result['openid'],
            'city' => $userinfo['city'],
            'provice' => $userinfo['province']
        ]);
    }

    /**
     * 评论存储
     */
    public function saveComment()
    {
        if (request()->port()){
            $livecomment = new LivecommentModel();
            $param = input('post.');
            $data['creater_time'] = date('Y-m-d h:i:s',time());
            $data['live_id'] = $param['live_id'];
            $data['content'] = $param['content'];
            $data['member_id'] = $param['member_id'];
            $data['type'] = 1;
            $result = $livecomment->inster($data);
            return json($result);
        }
    }

    /**
     * 删除评论
     */
    public function delComment()
    {
        if (request()->isPost()){
            $param = input('post.');
            $param = json_decode(array_flip($param)[""]);//转换特殊字符传入
            $livecomment = new LivecommentModel();
            $result = $livecomment->del($param->id);
            return json($result);
        }
    }

    /**
     * 根据用户unionid获取到用户信息
     */
    public function getMember($unionid)
    {
//        if (request()->port()){
//            $param = input('post.');
//            $param['creater_time'] = date('Y-m-d h:i:s',time());
            $result = $this->memberModel->unionidInfo($unionid);
            return json($result);
//        }
    }

    /**
     * 小程序获取直播列表
     */
    public function getLiveList()//先获取一遍数据库的直播,再获取一遍网易云直播列表,进行对比
    {
       
        if (request()->port()){ 
            /**
            * 根据unionid 获取 用户关注的bu_id
            */
            $param = input('post.');
            $live = new LiveModel();
            $union_id = $param['id'];
            // $union_id = 'orEkNxIIySN6EDnLIsWUCGtNysW0';
            $res = Db::table('xk_member_openid_unionid')->where('unionid', $union_id)->where('is_cancel', 1)->group('bu_id')->field(['bu_id'])->select();
            $bu_id = '';
            $bu_id_array = [];
            foreach ($res as $key => $val) {
                if ($key == 0){
                    $bu_id .= $val['bu_id'];
                }else {
                    $bu_id .= ','.$val['bu_id'];
                }
                $bu_id_array[] = $val['bu_id'];
            }
            if ($param['status'] == 3){
                $video = new VideoModel();
                $info = collection($video->select())->toArray();
                foreach ($info as $k=>$v){
                    $map['bu_id'] = ['in',$bu_id];
                    $data = $live->where('cid',$v['cid'])->where($map)->find();
                    if (empty($data)){
                        unset($info[$k]);
                        continue;
                    }
                    $member = new MemberModel();
                    $member_info = $member->where('id',$data['member_id'])->find();
                    $info[$k]['live_img'] = $data['live_img'];
                    $info[$k]['name'] = $member_info['name'];
                    $info[$k]['headimgurl'] = $member_info['headimgurl'];
                    $info[$k]['start_time'] = $data['start_time'];
                    $info[$k]['engineer'] = $data['engineer'];
                    $info[$k]['headimg'] = $data['headimg'];
                    $info[$k]['title'] = $data['title'];
                }
            }else{
                $info = $live->getOn($param['status']);
                foreach ($info as $k=>$v){
                    if (!in_array($v['bu_id'], $bu_id_array)){
                        unset($info[$k]);
                        continue;
                    }
                    $result = $this->wylive->channel_get($v['cid']);//再次对比
                    if ($result['code'] == 200){
                        $info[$k]['is_live'] = 1;
                        $info[$k]['live_status'] = $result['ret']['status'];
                    }else{
                        $info[$k]['is_live'] = 0;
                    }
                }
            }
            $info = array_values($info);
            return json(['code' => 1, 'data' => $info, 'msg' => '获取信息成功']);
        }
    }

    /**
     * 进入直播间检测
     */
    public function livePcTesting()
    {
        if (request()->port()){
            //根据id来检测直播是否开始,估计cid判断直播是否存在
            $param = input('post.');
            $param = json_decode(array_flip($param)[""]);//转换特殊字符传入

            $live = new LiveModel();
            $info = $live->getOne($param->id);
            $result = $this->wylive->channel_get($param->cid);//再次对比
            if ($result['code'] == 200){
                $info['is_live'] = 1;
                $info['live_status'] = $result['ret']['status'];
            }else{
                $info['is_live'] = 0;
            }

            $info['user_info'] = session('user_info');
            return json(['code'=>1,'msg'=>'获取成功','data'=>$info]);
        }
    }

    /**
     * 进入直播间检测
     */
    public function liveTesting()
    {
        if (request()->port()){
            //根据id来检测直播是否开始,估计cid判断直播是否存在
            $param = input('post.');
            /**
             * 获取用户id并加入分组
             */
            $union_id = $param['unionid'];
            $member_id = Db::table('xk_member')->where('unionid',$union_id)->value('id');
            $res = Db::table('xk_live_group')->where(['live_id' => $param['id'], 'member_id' => $member_id])->find();
            if (!$res){
                Db::table('xk_live_group')->insert(['live_id' => $param['id'], 'member_id' => $member_id]);
            }

            $live = new LiveModel();
            $info = $live->getOne($param['id']);
            $result = $this->wylive->channel_get($param['cid']);//再次对比
            if ($result['code'] == 200){
                $info['is_live'] = 1;
                $info['live_status'] = $result['ret']['status'];
            }else{
                $info['is_live'] = 0;
            }

            $info['user_info'] = session('user_info');
            return json(['code'=>1,'msg'=>'获取成功','data'=>$info]);
        }
    }

    /**
     * 未开始直播,报名
     */
    public function liveSignUp()
    {
        if (request()->port()){
            $param = input('post.');
            $livecomment = new LivecommentModel();
            $result = $livecomment->inster([
                'live_id' => $param['live_id'],
                'member_id' => $param['member_id'],
                'creater_time' => date('Y-m-d H:i:s',time()),
                'type' => 3
            ]);
            return json($result);
        }
    }

    /**
     * 小程序注册
     */
    public function programRegin()
    {
        if (request()->port()){
            $param = input('post.');
//            dump($param);exit();
            unset($param['bu_id']);
            unset($param['code']);
            $this->memberAddIntegral(1,50,$param['id']);
            $param['create_time'] = date('Y-m-d H:i:s', time());
            return join($this->memberModel->programEdit($param));
        }
    }

    /**
     * 点击喜欢按钮
     */
    public function clickLove()
    {
        if (request()->port()){
            $param = input('post.');
            $livecomment = new LivecommentModel();
            $live = new LiveModel();
            $result = $live->addClick($param);
            if ($result['code'] == 1){
                $result = $livecomment->inster([
                    'live_id' => $param['live_id'],
                    'member_id' => $param['member_id'],
                    'creater_time' => date('Y-m-d h:i:s',time()),
                    'type' => 2
                ]);
            }
            return join($result);
        }
    }

    /**
     * 进入直播间获取是否点赞过
     */
    public function getOnClickLove()
    {
        if (request()->port()){
            $param = input('post.');
            $livecomment = new LivecommentModel();
            $result = $livecomment->isLove($param);
            return join($result);
        }
    }

    /**
     * 检测是否已经报名
     */
    public function isRegin()
    {
        if (request()->port()){
            $param = input('post.');
            $livecomment = new LivecommentModel();
            $result = $livecomment->isReg($param);
            return join($result);
        }
    }

    /**
     * 查看积分
     */
    public function getLntegralList()
    {
        if (request()->port()){
            $param = input(
                'post.');

//            if (){
//
//            }
//            return join($result);
        }
    }

    /**
     * 增加积分
     */
    public function addLntegral()
    {
        if (request()->port()){
            $param = input('post.');
            $result = $this->memberAddIntegral(7,50,$param['member_id'],$param['live_id']);

            if (false == $result){
                return join(['code'=>0]);
            }else{
                return join(['code'=>1]);
            }
        }
    }

    /**
     * 用户增加积分
     */
    public function memberAddIntegral($title,$num,$id,$title_id = '')
    {
        $member = new MemberModel();
        //获取到当前积分
        $integral = $member->where('id',$id)->value('integral');
        //模拟注册获得100积分
        $integral = $integral+$num;
        //积分获取记录
        $data = ['member_id'=>$id,'title'=>$title,'action'=>1,'now'=>$integral,'integral'=>$num,
            'title_id'=>$title_id
        ];
        $member->save(['integral'=>$integral],['id'=>$id]);//对用用户表的积分增加
        $info = $this->addIntegral($data);//添加记录
        return $info;
    }
    /**
     * 添加用户获取积分记录
     */
    public function addIntegral($param)
    {
        $obj = new MemberintegralModel();
        $param['create_time'] = date('Y-m-d H:i:s',time());
        return $obj->save($param);
    }


    /**
     * 获取用户报名的直播
     */
    public function getMemberLiveList()
    {
        if (request()->port()){
            $live = new LiveModel();
            $param = input('post.');
            if ($param['status'] == 4){//status如果是4则是直播频道
                $info = collection($live->where('member_id',$param['member_id'])->whereNotIn('status','3')->select())->toArray();
                $member = new MemberModel();
                foreach ($info as $k=>$v){
                    $member_info = $member->where('id',$param['member_id'])->find();
                    $info[$k]['name'] = $member_info['name'];
                    $info[$k]['headimgurl'] = $member_info['headimgurl'];
                }
            }else{
                if ($param['status'] == 3){
                    $video = new VideoModel();
                    $info = collection($video->select())->toArray();
                    foreach ($info as $k=>$v){
                        $data = $live->where('cid',$v['cid'])->find();
                        $member = new MemberModel();
                        $member_info = $member->where('id',$data['member_id'])->find();
                        $info[$k]['live_img'] = $data['live_img'];
                        $info[$k]['name'] = $member_info['name'];
                        $info[$k]['headimgurl'] = $member_info['headimgurl'];
                        $info[$k]['start_time'] = $data['start_time'];
                        $info[$k]['engineer'] = $data['engineer'];
                        $info[$k]['headimg'] = $data['headimg'];
                        $info[$k]['title'] = $data['title'];
                    }
                }else{
                    $info = $live->getOnMember($param['status'],$param['member_id']);
                    foreach ($info as $k=>$v){
                        $result = $this->wylive->channel_get($v['cid']);//再次对比
                        if ($result['code'] == 200){
                            $info[$k]['is_live'] = 1;
                            $info[$k]['live_status'] = $result['ret']['status'];
                        }else{
                            $info[$k]['is_live'] = 0;
                        }
                    }
                }
            }

            return json(['code' => 1, 'data' => $info, 'msg' => '获取信息成功']);
        }
    }

    /**
     * 请求短信
     */
    public function getMsm()
    {
        // try{
        if (request()->isPost()){

            $phone = input('post.tel');
            //获取到微信用户信息并存储到数据库
            $info = input('post.');

            $data = rand(pow(10,(6-1)), pow(10,6)-1);
            $param =array();
            $param['datas'] = $data;
            $param['member_id'] = $info['member_id'];
            $param['create_time'] = time();
            $param['phone'] = $phone;
            $obj = new SmsModel();
            $obj->insert($param);

            // $sendsms = new SendSMS();
            // $info = $sendsms->send($phone,[$data],'243947');
            $sms = new Sms();
			$obj = $sms->sendSms($phone, json_encode(['code'=>$data]), "SMS_148861553");
            if($obj->Code=='OK'){
                return returninfos('1','获取成功',[$data,$param['create_time']]);
            }else{
                return returninfos('2','获取失败');
            }

        }

        // }catch (Exception $e){
        //     return returninfos('0','系统错误');
        // }
    }

    /**
     * 获取公众号与公司和职位
     */
    public function getBulist()
    {
        if (request()->isPost()){
            $param = input('post.');
            $bu = new BuModel();
            $bu_all = collection($bu->select())->toArray();
            if (isset($param['bu_id'])){
                $type = new CompanyModel();
                $work = new CompanyworkModel();

                //公司类型数据
                $typelist = collection($type->all(['bu_id'=>$param['bu_id']]))->toArray();
                //职位数据
                foreach($typelist as $k=>$v){
                    $typelist[$k]['postion'] = collection($work->all(['company_id'=>$v['id']]))->toArray();
                }

                $info['data'] = $typelist;
                $info['code'] = 1;
                $info['msg'] = '获取公司与职位成功';
                return json($info);
            }
            return json(['code'=>1,'msg'=>'获取bu部门成功','data'=>$bu_all]);
        }
    }

    /**
     * 开始直播
     */
    public function startLive()
    {
        if (request()->isPost()){
            $param = input('post.');
            $live = new LiveModel();
            $result = $live->edit(['status'=>1,'id'=>$param['id']]);
            return json($result);
        }
    }

    /**
     * 开始直播
     */
    public function startPcLive()
    {
        if (request()->isPost()){
            $param = input('post.');
            $param = json_decode(array_flip($param)[""]);//转换特殊字符传入
            $live = new LiveModel();
            $result = $live->edit(['status'=>1,'id'=>$param->id]);
            return json($result);
        }
    }

    /**
     * 结束直播
     */
    public function endLive()
    {
        if (request()->isPost()){
            $param = input('post.');
            $live = new LiveModel();
            $result = $live->edit(['status'=>3,'id'=>$param['id']]);
            return json($result);
        }
    }

    /**
     * 结束直播
     */
    public function endPcLive()
    {
        if (request()->isPost()){
            $param = input('post.');
            $param = json_decode(array_flip($param)[""]);//转换特殊字符传入
            $live = new LiveModel();
            $result = $live->edit(['status'=>3,'id'=>$param->id]);
            return json($result);
        }
    }


    /**
     * 回调地址,保存数据库,储存视频
     */
    public function saveVidos()
    {
        $param = input('param.');
        file_put_contents('tyqtemp', json_encode($param));
        $video = new VideoModel();
        $info = $video->where('vid',$param['vid'])->where('cid',$param['cid'])->find();
        if (!$info){
            $video->inster($param);
            @$s = file_get_contents($param['origUrl']);
            @$path = ROOT_PATH . 'public' . DS . 'uploads/'.$param['vid'].'.mp4';  //文件路径和文件名
            @file_put_contents($path, $s);
        }
    }

    /**
     * 直播登入
     */
    public function login()
    {
        if (request()->isPost()){
            $param = input('post.');
            
            $param = json_decode(array_flip($param)[""]);//转换特殊字符传入
           
            $live = new LiveModel();
            $member = new MemberModel();
            $result = $live->where('tel',$param->tel)->where('pass',$param->pass)->find();
            if ($result){
                $member = $member->where('id',$result['member_id'])->find();
                $result['member_info'] = $member;
                return json(['code'=>1,'msg'=>'登入成功','data'=>$result]);
            }else{
                return json(['code'=>0,'msg'=>'密码或号码错误','data'=>'']);
            }

        }
    }

    /**
     * 获取直播评论
     */
    public function getComment()
    {
        if (request()->isPost()){
            $param = input('post.');
            $comment = new LivecommentModel();
            $result = $comment->getCommentList($param['cid']);
            return json($result);
        }
    }

    /**
     * 轮询查询报名的推送,提醒直播要开始了
     */
    public function polling()
    {

    }

    /**
     * 获取最新小程序accsee_token
     */
    public function getProgramAccessToken($type = 1)
    {   
        $appid  = $this->appid;
        $appsecret = $this->appsecret; 
        $token = new ProgramappsecretModel();
        $old_accsee = $token->where('app_secret_id',$type)->order('id desc')->group('id')->find();
        // dump($old_accsee);exit();
        if ($old_accsee){
            //如果有运算时间超过2小时重新获取
            if ((time()-$old_accsee['time']) > 7160){
                return $this->saveProgramAccessToken($appid,$appsecret,$type);
                exit();
            }else{
                return $old_accsee['token'];
            }
        }else{
            return $this->saveProgramAccessToken($appid,$appsecret,$type);
            exit();
        }
    }
    /**
     * 获取小程序accsee_token并存储
     */
    function saveProgramAccessToken($appid,$appsecret,$type)
    {
        $token = new ProgramappsecretModel();
        $access = curl_get('https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appid.'&secret='.$appsecret);
        $info = json_decode($access);
//        dump('https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appid.'&secret='.$appsecret);exit();
        $token->save([
            'token' => $info->access_token,
            'time' => time(),
            'app_secret_id' => $type,
        ]);
        return $info->access_token;
    }

    /**
     * 储存formid
     */
    public function saveFormid()
    {
        if (request()->isPost()){
            $param = input('post.');
            $form = new ProgramformidModel();
            $result = $form->inster([
                'formid' => $param['formid'],
                'time' => date('Y-m-d H:i:s',time()),
                'member_id' => $param['member'],
                'live_id' => $param['live_id'],
                'openid' => $param['openid'],
            ]);
            return json($result);
        }
    }

    /**
     * 根据时间服务器自动请求更改状态
     */
    public function changeState()
    {
        $param = input('param.');
        $newtime = date('Y-m-d H:i:s',time());//获取当前时间

        //获取状态为2,未开始的直播
        $live = new LiveModel();
        $info = $live->getStart();
        foreach ($info as $k=>$v){
            if (strtotime($v['start_time']) <= strtotime($newtime)){
                $live->edit(['status'=>1,'id'=>$v['id']]);
            }
            if ((strtotime($v['start_time'])-10*60) <= strtotime($newtime)){
                if ($v['issend']) {
                    continue;
                }
                // //原始通知方法
                // $appse = new AppsecretModel();
                // $members = collection(Db('live_comment')->where('live_id', $v['id'])->where('type', 3)->select())->toArray();
                // $config = $appse->where('bu_id',$v['bu_id'])->find();
                // $param = ['bu_id' => $v['bu_id'], 'content' => "尊敬的用户您好，您报名的{$v['title']}，即将开始，请及时参加观看~谢谢\n".'<a href="http://www.qq.com" data-miniprogram-appid="wx010914c57edaaeef" data-miniprogram-path="pages/authorization/authorization">点击进入格兰富微课堂</a>'];
                // //发送推文
                // $member = new MemberModel();
                // foreach ($members as $key=>$val){
                //     $info['bu_id'] = $param['bu_id'];
                //     $info['id'] = $val['member_id'];
                //     $openid = $member->getOpenid($info);
                //     $relute = \app\index\controller\SendOut::text($openid['openid'],$param['content'],$config,$config['type']);
                // }
                //使用消息模版通知方法
                $members = collection(Db('program_formid')->where('live_id', $v['id'])->select())->toArray();
                // print_r($members);
                foreach ($members as $key=>$val){
                    $openid = $val['openid'];
                    $PostParam = [
                        'touser' => $openid,
                        // 'template_id' => 'BoNeCo1ePPcsMyCUeOn1BCNidD9ciC8HRjDACAWKsuY',
                        'template_id' => 'wee8pP3DBryk4R2PVbDGZw0Z9KemXkc0tAnfCF0GK2A',
                        'form_id' => $val['formid'],
                        'page' => 'pages/auth/auth',
                        'data' => [
                            'keyword1' => ['value' => $v['title']],
                            'keyword2' => ['value' => $v['start_time']],
                            'keyword3' => ['value' => '您报名的活动即将开始，请点击下方进入小程序查看直播内容~'],
                        ]
                    ];
                    $res = curl_post('https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token='.$this->getProgramAccessToken(), json_encode($PostParam, JSON_UNESCAPED_UNICODE));
                    // print_r($res);
                }
                $live->edit(['issend'=>1,'id'=>$v['id']]);
            }
        }
        $result = $live->getStart();

        return json($result);
    }

    /**
     * 检查用户是否可发言
     */
    public function testing()
    {
        $param = input('param.');
        $member = new MemberModel();

        $member_info = $member->where('id',$param['id'])->find();

        if ($member_info['is_speak'] == 1){
            return json(['code' => 1,'msg' => '允许发言','data'=>'']);
        }else{
            return json(['code' => 0,'msg' => '不允许发言','data'=>'']);
        }
    }

    /**
     * 设置用户禁言
     */
    public function prohibit()
    {
        if (request()->isPost()){
            $param = input('post.');
            $member = new MemberModel();
            $result = $member->isProhibit($param);
            return $result;
        }
    }

    /**
     * 分享次数
     */
    public function shareNum()
    {
        if (request()->isPost()){
            $param = input('post.');
            $live = new LiveModel();
            $live->where('id =' . $param['id'])->setInc('forward');
        }
    }

    /**
     * 进入直播间观看的开始时间
     */
    public function startWatch()
    {
        if (request()->isPost()){
            $param = input('post.');
            $union_id = $param['unionid'];
            $member_id = Db::table('xk_member')->where('unionid',$union_id)->value('id');
            $live_group = Db::table('xk_live_group')->where('live_id',$param['id'])->where('member_id', $member_id)->update(['entertime' => time()]);
        }
        return json(['code'=>1]);
    }

    /**
     * 离开直播间，停止计算观看的时间
     */
    public function endWatch()
    {
        if (request()->isPost()){
            $param = input('post.');
            $union_id = $param['unionid'];
            $member_id = Db::table('xk_member')->where('unionid',$union_id)->value('id');
            $live_group = Db::table('xk_live_group')->where('live_id',$param['id'])->where('member_id', $member_id)->find();
            if ((int)$live_group['entertime']){
                $watchtime = time() - $live_group['entertime'] + $live_group['watchtime'];
                $live_group = Db::table('xk_live_group')->where('live_id',$param['id'])->where('member_id', $member_id)->update(['watchtime' => $watchtime]);
                $live_group = Db::table('xk_live_group')->where('live_id',$param['id'])->where('member_id', $member_id)->update(['entertime' => 0]);
            }
            return json(['code'=>1]);
        }
    }

    /**
     * 进入直播间，领取积分
     */
    public function enterLiveGetSorce()
    {
        $param = input('post.');
        $union_id = $param['unionid'];
        $member_id = Db::table('xk_member')->where('unionid',$union_id)->value('id');
        //+添加积分
        $Memberintegral = new MemberintegralModel();
        $is_add = $Memberintegral->where('title_id',$param['id'])->where('member_id', $member_id)->where('title', 7)->value('id');
        if (empty($is_add)){
            $result = $this->memberAddIntegral(7,50,$member_id,$param['id']);
        }
        //-添加积分
        return json(['code'=>1]);
    }

    /**
     * 获取全部直播列表
     */
    public function getAllLive() {
        $live = new LiveModel();
        $info = collection($live
        ->alias('l')
        ->join('xk_video v', 'l.cid = v.cid','LEFT')
        ->field(['l.*', 'v.origUrl'])
        ->select())
        ->toArray();
        return json($info);
    }
}