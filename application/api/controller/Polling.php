<?php
/**
 * Created by PhpStorm.
 * User: juplus-06
 * Date: 2018/4/27
 * Time: 15:15
 */

namespace app\api\controller;


use app\admin\model\ActivitymemberModel;
use app\admin\model\ActivityModel;
use app\admin\model\MemberModel;
use app\admin\model\TemplateModel;
use app\index\controller\SendOut;
use think\console\command\make\Model;
use think\Controller;
use think\Db;

class Polling extends Controller
{
    public function index()
    {
        //得到所有报名了,活动开始小于一天的用户数据
        $obj = new Db();
        $result = $obj::query('select  p.*,m.member_id,a.id as activity_id,a.bu_id,a.time,a.title from xk_activity_member m
JOIN xk_activity a ON m.activity_id = a.id
JOIN xk_bu_app_secret p ON p.bu_id = a.bu_id 
where if((unix_timestamp(a.time)-'.time().') < 86400,1=1,1=2) and m.auto_remind = 1 and a.line = 1');
    //    dump($result);exit();

        //推送
        $send = new SendOut();
        $obj = new ActivityModel();
        $template = new TemplateModel();

        $member = new MemberModel();
        foreach ($result as $k=>$v){
            //获取当前公众号的openid
        
            $param['id'] = $v['member_id'];
            $param['bu_id'] = $v['bu_id'];
            $openid = $member->getOpenid($param);

            $actitvity = $obj->where('id',$v['activity_id'])->find();

            $data = array();
            $data['first'] = array('value'=>'活动预定成功提醒', 'color'=>'#0A0A0A');
            $data['keyword1'] =array('value'=>$actitvity['title'], 'color'=>'#11497c');
            $data['keyword2'] = array('value'=>$actitvity['time'], 'color'=>'#11497c');
            $data['keyword3'] = array('value'=>$actitvity['city'], 'color'=>'#11497c');
            $data['remark'] = array('请准时参加','color'=>'#0A0A0A');

            //获取当前场景的模板id
            $templateId = $template->where('template_name','活动报名成功')->where('bu_id',$v['bu_id'])->find();

            $info['appsecret'] = $v['appsecret'];
            $info['appid'] = $v['appid'];

            $data = $send->sendTemplateMessage($data,$openid['openid'],$templateId['templateid'],'','#FF0000',$info,$v['type']);
            //发送成功更改状态
            if ($data['errcode'] == 0){
                ActivitymemberModel::update(['auto_remind'=>'2'],['activity_id'=>$v['activity_id'],'member_id'=>$v['member_id']]);
            }

        }



    }

}