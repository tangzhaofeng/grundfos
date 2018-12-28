<?php
/**
 * Created by PhpStorm.
 * User: juplus-06
 * Date: 2018/4/2
 * Time: 16:12
 */

namespace app\admin\controller;

header('Content-Type:text/html; charset=utf-8');

use app\admin\model\ActivitymemberModel;
use app\admin\model\ActivityModel;
use app\admin\model\AppsecretModel;
use app\admin\model\BuModel;
use app\admin\model\GroupingModel;
use app\admin\model\MembergroupModel;
use app\admin\model\MemberModel;
use app\admin\model\MemberopenunionModel;
use app\admin\model\MessageModel;
use app\admin\model\TemplateModel;
use app\index\controller\Media;
use think\Db;

class Messagesend extends Base
{
    public function index()
    {
        $obj = new BuModel();
        $bu_list = collection($obj->all())->toArray();

        $this->assign('bu_list',$bu_list);
        return $this->fetch();
    }

    public function index1()
    {
        $param = input('param.');

        $size = 10;
        $where = [];

        if (isset($param['bu_id'])){
            $where['bu_id'] = $param['bu_id'];
        }

        $obj = new MessageModel();

        $selectResult = $obj->getBuBywhere($where, $size);

        foreach ($selectResult as $k=>$v){
            $activity_id = explode(',',$v['group_id']);
            foreach ($activity_id as $key=>$val){
                $selectResult[$k]['activity_name'] = ActivityModel::where('id',$val)->value('title');
            }
        }

        $return['total'] = $obj->getAllUsers($where);  //总数据

        $return['rows'] = $selectResult;
        $return['page'] = $selectResult->render();

        $this->assign('return',$return);
        $this->assign('bu_id',$param['bu_id']);
        return $this->fetch();
    }

    public function edit()
    {
        $obj = new BuModel();
        $id = input('param.id');
//        dump($obj->where('id',$id)->find());exit();
        if(request()->isPost()){
            $param = input('post.');
            return json($obj->edit($param));
        }else{
            $id = input('param.id');
            $this->assign('courseCode',$obj->where('id',$id)->find());
            return $this->fetch();
        }
    }

    public function add()
    {
        $appse = new AppsecretModel();
        if(request()->isPost()){
            $param = input('post.');

            //如果有选择活动标签则推送标签用户,如果没有选择就推送全部门
            $activity = new ActivityModel();
            $am = new MembergroupModel();
            $member = new MemberModel();
            $mou = new MemberopenunionModel();

            //获取微信配置
            $config = $appse->where('bu_id',$param['bu_id'])->find();

            if (isset($param['grouping_id'])){
                //遍历有此标签的用户
                foreach ($param['grouping_id'] as $k=>$v){
                    $member_id = collection($am->where('group_id',$v)->group('member_id')->select())->toArray();
                    // $member_id = collection($am->alias('a')
                    // ->join('xk_grouping b', 'a.group_id = b.id')
                    // ->where('a.group_id', $v)
                    // ->group('a.member_id')
                    // ->field(['member_id', 'activity_id'])
                    // ->select())->toArray();
                    // foreach ($member_id as $k => $v){
                    //     $res = $bm->where('member_id', $v['member_id'])
                    //                 ->where('activity_id', $v['activity_id'])
                    //                 ->where('status', 2)
                    //                 ->select();
                    //     if (!$res){
                    //         unset($member_id[$k]);
                    //     }
                    // }
                    //type发送类型 1发送文本 2发送模板消息 3发送图片
                    if ($param['type'] == 1){
                        //对每个用户就行发送
                        $param['content'] = $_POST['content'];
                        $this->sendText($member_id,$param,$config);
                        $param['template_content'] = '';
                        $param['template_id'] = '';
                        $param['template_url'] = '';
                        $param['media'] = '';
                    }elseif($param['type'] == 2){
                        $templateId = $this->sendTemplate($member_id,$param,$config);
                        //如果有模板发送的内容
                        $template_content = implode('-',$param['template_content']);
                        $template_content = '头部:'.$param['first'].',内容:'.$template_content.',尾部:'.$param['remark'];
                        $param['template_content'] = $template_content;
                        $param['template_id'] = $templateId;
                        $param['template_url'] = $param['url'];
                        $param['media'] = '';
                    }else{//发送图片
                        $param['template_content'] = '';
                        $param['template_id'] = '';
                        $param['template_url'] = '';
                        $reulst = $this->sendMedia($param['bu_id'],$member_id,$param['media']);
                        // dump($reulst);exit();
                    }

                    //插入数据库记录
                    $grouping_id = implode(',',$param['grouping_id']);
                    $param['group_id'] = $grouping_id;

                    $message = new MessageModel();
                    $result = $message->inster($param);
                    return json($result);
                }
            }else{
                $member_id = collection($member->where('bu_id',$param['bu_id'])->select())->toArray();
                    if ($param['type'] == 1){
                        //对每个用户就行发送
                        $param['content'] = $_POST['content'];
                        $this->sendText($member_id,$param,$config);
                        $param['template_content'] = '';
                        $param['template_id'] = '';
                        $param['template_url'] = '';
                        $param['media'] = '';
                    }elseif($param['type'] == 2){
                        $templateId = $this->sendTemplate($member_id,$param,$config);
                        //如果有模板发送的内容
                        $template_content = implode('-',$param['template_content']);
                        $template_content = '头部:'.$param['first'].',内容:'.$template_content.',尾部:'.$param['remark'];
                        $param['template_content'] = $template_content;
                        $param['template_id'] = $templateId;
                        $param['template_url'] = $param['url'];
                        $param['media'] = '';
                    }else{//发送图片
                        $param['template_content'] = '';
                        $param['template_id'] = '';
                        $param['template_url'] = '';
                        $reulst = $this->sendMedia($param['bu_id'],$member_id,$param['media']);
                        // dump($reulst);exit();
                    }
                return ['code' => 1, 'data' => '', 'msg' => '发送成功' ];
            }
        }else{
            $obj = new GroupingModel();
            $bu_id = input('param.bu_id');

            $group_list = collection($obj->where('bu_id',$bu_id)->select())->toArray();

            $this->assign('group_list',$group_list);
            $this->assign('bu_id',$bu_id);
            return $this->fetch();
        }
    }

    /**
     * 发送图片
     */
    public function sendMedia($bu_id = '',$member_id = [],$media)
    {
        $send = new \app\index\controller\SendOut();
        $med = new Media();
        //获取到微信配置
        $config = Db('bu_app_secret')->where('id',$bu_id)->find();

        //先进行新增临时素材
        $result = $med->temporary($config,'image',$media);//截取到mediaId
        //根据mediaId发送临时素材
//        dump($result);exit();
        if ($result['code'] == 1){
            $member = new MemberModel();

            foreach ($member_id as $key=>$val){
                $info['bu_id'] = $bu_id;
                $info['id'] = $val['member_id'];
                $openid = $member->getOpenid($info);
                $info = $send->image($openid['openid'],$result['media_id'],$config,$config['type']);

            }
            return true;
        }else{
            return false;
        }



    }

    /**
     * 发送模板消息
     */
    public function sendTemplate(&$member_id,&$param,&$config)
    {
        $send = new \app\index\controller\SendOut();
        $data = array();
        $data['first'] = array('value'=>$param['title_name'], 'color'=>'#0A0A0A');
        $a = 1;
        foreach ($param['template_content'] as $k=>$v){

            $data['keyword'.$a] =array('value'=>$v, 'color'=>'#11497c');
            $a++;
        }

        $data['remark'] = array($param['remark'],'color'=>'#0A0A0A');

        //获取当前场景的模板id
        $template = new TemplateModel();
        $templateId = $template->where('templateid',$param['template_id'])->where('bu_id',$param['bu_id'])->find();

        $member = new MemberModel();
        foreach ($member_id as $key=>$val){
            $info['bu_id'] = $param['bu_id'];
            $info['id'] = $val['member_id'];
            $openid = $member->getOpenid($info);
            $send->sendTemplateMessage($data,$openid['openid'],$param['template_id'],$param['url'],'#FF0000',$config,$config['type']);
        }
        return $templateId;
    }

    /**
     * 发送文本消息
     */
    public function sendText(&$member_id,&$param,&$config)
    {
        $member = new MemberModel();
        foreach ($member_id as $key=>$val){
            $info['bu_id'] = $param['bu_id'];
            $info['id'] = $val['member_id'];
            $openid = $member->getOpenid($info);
            $relute = \app\index\controller\SendOut::text($openid['openid'],$param['content'],$config,$config['type']);
        }
    }

    /**
     * 单独发送文本消息
     */
    public function oneSendText($member_id,$param,$config)
    {
        $member = new MemberModel();
            $info['bu_id'] = $param['bu_id'];
            $info['id'] = $member_id;
            $openid = $member->getOpenid($info);
            $relute = \app\index\controller\SendOut::text($openid['openid'],$param['content'],$config,$config['type']);
            return $relute;
    }

    public function getTemplate()
    {
        $appse = new AppsecretModel();
        $bu_id = input('param.bu_id');
        $config = $appse->where('bu_id',$bu_id)->find();
        $index = new \app\index\controller\Index();
        $token = $index->getAccessToken($config['appid'],$config['appsecret'],$config['type']);
        //查询模板
        $template_list = curl_get('https://api.weixin.qq.com/cgi-bin/template/get_all_private_template?access_token='.$token);
        return json_decode($template_list,true);
    }


}