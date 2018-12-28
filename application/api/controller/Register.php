<?php
/**
 * Created by PhpStorm.
 * User: juplus-06
 * Date: 2018/4/10
 * Time: 15:50
 */

namespace app\api\controller;

use app\admin\model\ActivitymemberModel;
use app\admin\model\ActivityModel;
use app\admin\model\GroupingModel;
use app\admin\model\MembergroupModel;
use app\admin\model\MemberintegralModel;
use app\admin\model\MemberinvitationModel;
use app\admin\model\MemberModel;
use app\admin\model\SmsModel;
use app\admin\model\TemplateModel;
use app\api\model\AppsecretModel;
use app\index\controller\Group;
use app\api\controller\Sms;
use app\index\controller\SendOut;
use CCP_REST_DEMO_PHP_v27r1\Demo\SendTemplateSMS;
use think\Db;
use think\Exception;



class Register extends Base
{
    private $member_info;
    private $obj;
    private $activity_member;
    private $app_secret;
    private $config;

    public function __construct()
    {
        // parent::__construct();
        $this->obj = new ActivityModel();
        $this->activity_member = new ActivitymemberModel();
        $type = session('type');
        $this->config = session('wxconfig_'.$type);
        $this->member_info = session($this->config['appid']."user_oauth");
        $this->app_secret = new AppsecretModel();
    }

    /**
     * 用户注册填写信息
     */
    public function register()
    {
        //验证是否post提交
        if (request()->isPost()){
            try{
                //开启事物
                Db::startTrans();

                $member = new MemberModel();
                $app_secret = new AppsecretModel();
                $param = input('post.');
                $activity_id = false;
                if($param['activity_id']){
                    $activity_id = $param['activity_id'];

                }
                unset($param['activity_id']);
                unset($param['code']);

                $type = session('type');
                if (!$type) {
                    return ['code' => 3]; 
                }
                $wxconfig = session('wxconfig_'.$type);
                $view = session('view');

                //获取微信用户session匹配数据库是member表
                $user_oauth =  session($wxconfig['appid'].'user_oauth');
                $param['unionid'] = $user_oauth['unionid'];
                //查询注册时的微信公众号
                $bu_id = $app_secret->where('type',$type)->find();
                if (!isset($param['is_bu_id'])) {
                    $param['is_bu_id'] = $bu_id['bu_id'];
                }
                //记录用户操作
                $this->operation(3,'注册',$user_oauth['id']);

                //发送模板
                $this->sendtemplate($user_oauth['id'],$bu_id['bu_id'],$param,'',$wxconfig,session('type'),1);
                
                //+如果已经注册
                $is_reg = Db::table('xk_member_integral')->where('title', 1)->where('member_id', $user_oauth['id'])->value('id');
                if (!empty($is_reg)){
                    return ['code' => 1];
                }
                //-已经注册
                
                //添加用户积分
                $integral = $this->memberAddIntegral('1',50,$user_oauth['id']);
                $param['integral'] = $integral;
                $is_sign = 0;
                $param['create_time'] = date('Y-m-d H:i:s', time());
                if (isset($param['question_id'])){//判断是否有问卷调查id
                    unset($param['from_member_id']);
                    unset($param['goscan']);
                    $view = '/questionnaire/index.html';
                    unset($param['question_id']);
                    $info = $member->edit($param,$view,$type,$wxconfig['appid'],$is_sign);
                    //提交事物
                    Db::commit();
                    return $info;
                }

                //如果有activity_id送入则自动完成报名,但只有line为1线下是会自动报名
                if($activity_id){
                    $a = new ActivityModel();
                    $activity = $a->where('id',$activity_id)->find();
                    if($activity['line'] == 1){
                        //报名
                        $this->RegisterSign($activity_id);
                        //报名活动分组
                        $this->addGroup($user_oauth['id'],'',$wxconfig['bu_id'],$wxconfig,$activity_id);
                        //发送模板
                        $this->sendtemplate($user_oauth['id'],$bu_id['bu_id'],$param,$activity_id,$wxconfig,session('type'),2);
                        $view = '/activity/activityDetail_xia.html';
                       $is_sign = 1;
                        if ($param['goscan']){//从签到页面进入,自动签到,并最后返回到签到页面
                            $contrMember = new Member();
                            $contrMember->contrScan($activity_id);
                            $view = '/activity/signSuccess.html';
                            $is_sign = 3;
                            
                        }

                        unset($param['from_member_id']);
                        unset($param['goscan']); 
                  
                     $info = $member->edit($param,$view,$type,$wxconfig['appid'],$is_sign);
                        //提交事物
                       Db::commit();
                    //    dump($info);exit();
                       return $info;
                    }else{
                        $view = '/activity/activityDetail_shang.html';
                    }
                }
                //如果有推荐人id则进行推荐人表添加
                if (isset($param['from_member_id'])) {
                    if($param['from_member_id']) {
                        //添加推荐人积分
                        $this->memberAddIntegral(4,50,$param['from_member_id']);
                        //添加推荐人与推进用户记录
                        $this->addInvitation($param['from_member_id'],$user_oauth['id'],$this->config['bu_id']);
                        unset($param['goscan']);
                        unset($param['question_id']); 
                        unset($param['from_member_id']);
                        $info = $member->edit($param,$view,$type,$wxconfig['appid'],0,1);
                        $this->addGroup($user_oauth['id'],'用户推荐',$wxconfig['bu_id'],$wxconfig);//用户分组
                        Db::commit();
                        return $info;
                    }
                }
                //更新匹配信息
                unset($param['from_member_id']);
                unset($param['goscan']);
                unset($param['question_id']);
                $info = $member->edit($param,$view,$type,$wxconfig['appid'],$is_sign);
                //提交事物
                Db::commit();
                return $info;
            }catch (Exception $e){
                // Db::rollback();
                return returninfos('0',$e->getMessage());
            }
        }
    }

    /**
     * 用户分组
     */
    public function addGroup($member_id,$group_name = '',$bu_id,$config,$activity_id = '')
    {
        //获取用户当前公众号的openid
        $config['type'] = session('type');
        $member = new MemberModel();
        $param['id'] = $this->member_info['id'];
        $param['bu_id'] = $this->config['bu_id'];
        $openid = $member->getOpenid($param);

        $grouping = new GroupingModel();//获取标签id
        if ($activity_id){
            $grouping_info = $grouping->where('activity_id',$activity_id)->where('bu_id',$bu_id)->find();
        }else{
            $grouping_info = $grouping->where('grouping_name',$group_name)->where('bu_id',$bu_id)->find();
        }

        $config = $this->config;
        $config['type'] = session('type');
        $gruop = new Group();
        $data = $gruop->editUserGroup($config,$openid['openid'],$grouping_info['wx_id']);
        //  dump($data);exit();
        if ($data['errcode'] == 0){
            //添加标签关联
            $member_group = new MembergroupModel();
            $member_group->save(['bu_id'=>$bu_id,'member_id'=>$member_id,'group_id'=>$grouping_info['id']]);
        }

    }

    /**
     * 记录推荐人信息,并到推荐人
     */
    public function addInvitation($from_member_id,$id,$bu_id)
    {
        $obj = new MemberinvitationModel();
        $obj->inster(['member_id'=>$from_member_id,'to_member_id'=>$id,
            'creater_time'=>date('Y-m-d H:i:s',time()),'bu_id'=>$bu_id
        ]);
    }


    /**
     * 记录用户操作
     */
    public function operation($category,$content,$member_id)
    {
        $record = Db('record');
        $record->insert(['category'=>$category,'content'=>$content,'creater_time'=>date('Y-m-d H:i:s',time()),
            'member_id'=>$member_id
        ]);
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
        $this->addIntegral($data);//添加记录
        return $integral;
    }


    //完成注册并报名
    public function RegisterSign($actitvity_id)
    {
        $member_id = $this->member_info['id'];
        $member_work = Db('member')->where('id',$member_id)->value('company_work_type_id');
//        $actitvity_id = input('post.id');

        //todo 目前因为无需注册也可报名关闭此功能 验证用户部门是否符合报名资格
        // $this->obj->isOpen($member_work,$actitvity_id);

        $info = $this->activity_member->signUp($member_id,$actitvity_id,$member_work);

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
     * 发送模板
     */
    public function sendtemplate($member_id,$bu_id,$param,$actitvity_id='',$wxconfig,$type,$info)
    {
        //获取用户当前公众号的openid
        $member = new MemberModel();
        $aparam['id'] = $member_id;
        $aparam['bu_id'] = $bu_id;
        $openid = $member->getOpenid($aparam);

        $template = new TemplateModel();
        //1为注册推送 2为报名推送
        if ($info == 1){

            //发送微信推送
            $send = new SendOut();
            $data = array();
            $data['first'] = array('value'=>'会员注册成功', 'color'=>'#0A0A0A');
            $data['keyword1'] =array('value'=>$param['name'], 'color'=>'#11497c');
            $data['keyword2'] = array('value'=>$param['tel'], 'color'=>'#11497c');
            $data['remark'] = array('恭喜您注册成为格兰富会员','color'=>'#0A0A0A');
            //获取当前场景的模板id
            $templateId = $template->where('template_name','会员注册成功')->where('bu_id',$bu_id)->find();

        }else {

            //获取活动详情
            $actitvity = $this->obj->where('id', $actitvity_id)->find();
            $send = new SendOut();
            $data = array();
            if($actitvity['line'] == 2){
                $actitvity['city'] = '格兰富家用泵公众号';
            }
            $data['first'] = array('value' => '活动预定成功提醒', 'color' => '#0A0A0A');
            $data['keyword1'] = array('value' => $actitvity['title'], 'color' => '#11497c');
            $data['keyword2'] = array('value' => $actitvity['time'], 'color' => '#11497c');
            $data['keyword3'] = array('value' => $actitvity['city'], 'color' => '#11497c');
            $data['remark'] = array('请准时参加', 'color' => '#0A0A0A');
            //获取当前场景的模板id
            $templateId = $template->where('template_name','活动报名成功')->where('bu_id',$bu_id)->find();
        }


        $send->sendTemplateMessage($data,$openid['openid'],$templateId['templateid'],'','#FF0000',$wxconfig,$type);
    }

    /**
     * 请求短信
     */
    public function getMsm()
    {
         try{
            if (request()->isPost()){
                $phone = input('post.tel');
                if (Db::table("xk_member")->where('tel', $phone)->count()){
                    return returninfos('3','手机号已被注册');
                }
                //获取到微信用户信息并存储到数据库
                $type = session('type');
                $wxconfig = session('wxconfig_'.$type);
                $user_oauth  = $this->member_info = session($this->config['appid']."user_oauth");

                $data = rand(pow(10,(6-1)), pow(10,6)-1);
                $param =array();
                $param['datas'] = $data;
                $param['member_id'] = $user_oauth['id'];
                $param['create_time'] = time();
                $param['phone'] = $phone;
                $obj = new SmsModel();
                $obj->insert($param);

				$sms = new Sms();
				$obj = $sms->sendSms($phone, json_encode(['code'=>$data]), "SMS_148861553");
				if($obj->Code=='OK')
					return returninfos('1','获取成功',[$data,$param['create_time']]);
				else
					return returninfos('2','获取失败');
					
				/*
                $sendsms = new SendSMS();
                $info = $sendsms->send($phone,[$data],'243947');
                // dump($info);exit();
                if($info == 1){
                    return returninfos('1','获取成功',[$data,$param['create_time']]);
                }else{
                    return returninfos('2','获取失败');
                }
				*/

            }

         }catch (Exception $e){
             return returninfos('0',$e->getMessage());
         }
    }

    /**
     * 修改信息
     */
    public function memverEdit()
    {
        //验证是否post提交
        if (request()->isPost()){
            try{
                $member = new MemberModel();
                $app_secret = new AppsecretModel();
                $param = input('post.');

                $type = session('type');
                $wxconfig = session('wxconfig_'.$type);
                //获取微信用户session匹配数据库是member表
                $user_oauth =  session($wxconfig['appid'].'user_oauth');
                $param['unionid'] = $user_oauth['unionid'];

                //查询注册时的微信公众号
                $bu_id = $app_secret->where('type',$type)->find();
                $param['is_bu_id'] = $bu_id['bu_id'];

                //更新匹配信息
                return $member->memverEdit($param);

            }catch (Exception $e){
                return returninfos('0','系统错误');
            }

        }
    }
}