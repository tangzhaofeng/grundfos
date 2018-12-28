<?php
/**
 * Created by PhpStorm.
 * User: juplus-06
 * Date: 2018/4/11
 * Time: 10:07
 */

namespace app\api\controller;

use app\admin\model\ActivitymemberModel;
use app\admin\model\MemberinvitationModel;
use app\admin\model\MemberModel;
use app\admin\model\ActivityModel;
use app\index\controller\Index;
use think\Cookie;
use think\Exception;

class Member extends Base
{
    private $member_info;
    private $obj;
    private $activity;
    private $config;

    public function __construct()
    {
        parent::__construct();
        $type = session('type');
        $this->config = session('wxconfig_' . $type);
        $this->member_info = session($this->config['appid'] . "user_oauth");
        $this->obj = new MemberModel();
        $this->activity = new ActivityModel();
    }

    /**
     * 获取用户基本信息
     */
    public function getMemberInfo()
    {
        try {
            $id = $this->member_info['id'];
            $info = $this->obj->where('id', $id)
                ->field('id,name,headimgurl')
                ->find();
            $info['type'] = session('type');
            return returninfos('1', '获取用户信息成功', $info);
        } catch (Exception $e) {
            return returninfos('0', '系统错误');
        }

    }

    /**
     * 获取用户详细信息
     */
    public function getMemberDetailed()
    {
        try {

            $id = $this->member_info['id'];
            $info = $this->obj->detailed($id);
            return $info;
        } catch (Exception $e) {
            return returninfos('0', '系统错误');
        }
    }

    /**
     * 保存用户修改信息
     */
    public function saveEdit()
    {
        try {
            if (request()->isPost()) {
                $param = input('post.');
                $param['id'] = $this->member_info['id'];
                $info = $this->obj->interEdit($param);
                return $info;
            }
        } catch (Exception $e) {
            return returninfos('0', '系统错误');
        }
    }

    /**
     * 获取用户参加的展会
     */
    public function getMemberActivity()
    {
        try {
            //根据用户id查询出用户参与的活动
            $id = $this->member_info['id'];

            //todo 是否已经参加
            $todo = input('param.todo');
            //bu_id查询当前的微信公众号
            $bu_id = $this->member_info['bu_id'];

            $info = $this->obj->getActivity($id, $todo, $bu_id);

            return $info;
        } catch (Exception $e) {
            return returninfos('0', '系统错误');
        }
    }

    /**
     * 获取用户的展会详情
     */
    public function getActivityInfo()
    {
        try {
            //根据展会id获取详情
            $id = input('param.id');
            $info = $this->activity->getInfo($id);

            return $info;
        } catch (Exception $e) {
            return returninfos('0', '系统错误');
        }
    }

    /**
     * 存储所有 cookie
     */
    public function setCookie()
    {
        try {
            if (request()->isPost()) {
                $cookename = input('post.cookiename');
                $cookevalue = input('post.cookievalue');
                Cookie::set($cookename, $cookevalue);
            }
            return returninfos('1', '设置cookie成功');
        } catch (Exception $e) {
            return returninfos('0', '系统错误');
        }
    }


    /**
     * 微信扫一扫签到
     */
    public function scan()
    {
//        try {
            if (request()->isPost()) {
                $activity_id = input('post.activity_id');
                $obj = new ActivitymemberModel();

                $this->scanSing($activity_id);//报名

                //记录用户操作
                $record = Db('record');
                $record->insert(['category' => 5, 'content' => '线下活动参加', 'creater_time' => date('Y-m-d H:i:s', time()),
                    'member_id' => $this->member_info['id'], 'category_id' => $activity_id
                ]);

                $config = $this->config;
                $config['type'] = session('type');
                //查询用户是否关注了公众号
                $index = new Index();
                $status = $index->isFollow($this->member_info['id'], $config);
                if ($status == 0) {
                    //获取到当前活动的带参数二维码
                    $qrcode = $this->activityIdQrcode($activity_id);
                    return $obj->scanUp($activity_id, $this->member_info['id'],$qrcode);
                }

                return $obj->scanUp($activity_id, $this->member_info['id']);
            }
//        } catch (Exception $e) {
//            return returninfos('0', '系统错误');
//        }
    }

    /**
     * 根据活动id得到标签id,穿建临时带参数二维码
     */
    public function activityIdQrcode($activity_id)
    {
        $group_member = Db('grouping')->where('activity_id',$activity_id)->find();//分组id
        $index = new Index();
        return $index->qrCode($this->config['appid'],$this->config['appsecret'],session('type'),'2_'.$group_member['id'],1);
    }


    /**
     * 微信扫一扫签到,给未报名的用户进行报名
     */
    public function scanSing($activity_id)
    {
        $sing = new Activity();
        $sing->scanActivitySignUp($activity_id);
    }

    /**
     * 微信扫一扫签到
     */
    public function contrScan($activity_id)
    {
            $obj = new ActivitymemberModel();

            //记录用户操作
            $record = Db('record');
            $record->insert(['category'=>5,'content'=>'线下活动参加','creater_time'=>date('Y-m-d H:i:s',time()),
                'member_id'=>$this->member_info['id'],'category_id'=>$activity_id
            ]);

            return $obj->scanUp($activity_id,$this->member_info['id']);
    }

    /**
     * 获取用户邀请的好友
     */
    public function getInvited()
    {
        try{
            $invitation = new MemberinvitationModel();
            $member_in = new MemberModel();
            $member_id = $this->member_info['id'];
            //获取到用户要求的好友
            $member = collection($invitation->where('member_id',$member_id)->select())->toArray();
            $list = [];
            foreach ($member as $k=>$v){
                $list[$k] = $member_in->where('id',$v['to_member_id'])->find();
            }
            $data['list'] = $list;
            $data['type'] = session('type');
            $data['from_member_id'] = $member_id;

            return json(['code'=>1,'msg'=>'获取成功','data'=>$data]);
        }catch (Exception $e){
            return json(['code'=>0,'msg'=>'系统错误']);
        }
    }

    /**
     * 判断用户是否注册完成
     */
    public function isLogin()
    {
        try{
            $name = $this->obj->where('id',$this->member_info['id'])->find();

            //判断是否关注,如果没有关注则进行推出带参数二维码
            $info = $this->isFollow();
            if ($info->subscribe == 0){
                //获取带参数二维码
                $index = new Index();
                $qrcode_url = $index->qrCode($this->config['appid'],$this->config['appsecret'],session('type'),'3_'.input('param.from_member_id'),1);
                return json(['code'=>3,'msg'=>$qrcode_url]);
            }
            if ($name['name']){
                return json(['code'=>1,'msg'=>session('type')]);
            }
            return json(['code'=>2,'msg'=>'未注册','data'=>['to_member_id'=>$name['id'],'type'=>session('type')]]);
        }catch (Exception $e){
            return json(['code'=>0,'msg'=>'系统错误']);
        }
    }

    /**
     * 判断用户是否注册完成
     */
    public function getIsLogin()
    {
        try{
            $name = $this->obj->where('id',$this->member_info['id'])->find();

            if ($name['name']){
                return json(['code'=>1,'msg'=>session('type')]);
            }
            return json(['code'=>2,'msg'=>'未注册','data'=>['to_member_id'=>$name['id'],'type'=>session('type')]]);
        }catch (Exception $e){
            return json(['code'=>0,'msg'=>'系统错误']);
        }
    }

    /**
     * 判断用户是否关注
     */
    public function isFollow()
    {
        //获取openid
        $openid = $this->obj->getOpenid(['id'=>$this->member_info['id'],'bu_id'=>$this->config['bu_id']]);
        //获取token
        $index = new Index();
        $token = $index->getAccessToken($this->config['appid'],$this->config['appsecret'],session('type'));
        //查询是否关注
        $info = curl_get('https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$token.'&openid='.$openid['openid']);
        return json_decode($info);
    }


}