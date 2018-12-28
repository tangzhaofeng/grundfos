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
use think\Db;

class Template extends Base
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

        $obj = new TemplateModel();

        $selectResult = $obj->getBuBywhere($where, $size);


        $return['total'] = $obj->getAllUsers($where);  //总数据

        $return['rows'] = $selectResult;
        $return['page'] = $selectResult->render();

        $this->assign('return',$return);
        $this->assign('bu_id',$param['bu_id']);
        return $this->fetch();
    }


    public function add()
    {
        $appse = new AppsecretModel();
        if(request()->isPost()){
            $param = input('post.');

        }else{
            $param = input('param.');

            $size = 10;
            $where = [];

            if (isset($param['bu_id'])){
                $where['bu_id'] = $param['bu_id'];
            }

            $obj = new TemplateModel();


            //先更新数据库模板信息表
            $tmp = new Messagesend();
            $info = $tmp->getTemplate($param['bu_id'])['template_list'];
            unset($info[0]);
                foreach ($info as $k=>$v){
                    $reulte = $obj->where('bu_id',$param['bu_id'])->where('templateid',$v['template_id'])->find();
//                    foreach ($selectResult as $key=>$val){
                    if (!$reulte){
                        $obj->insert(['templateid'=>$v['template_id'],'bu_id'=>$param['bu_id'],
                            'template_name'=>$v['title'],'template_content'=>$v['content']
                        ]);
                    }

            }
            $selectResult = $obj->getBuBywhere($where, $size);
            $return['total'] = $obj->getAllUsers($where);  //总数据

            $return['rows'] = $selectResult;
            $return['page'] = $selectResult->render();

            $this->assign('return',$return);
            $this->assign('bu_id',$param['bu_id']);
            return $this->fetch('index1');

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
            $send->sendTemplateMessage($data,$openid['openid'],$param['template_id'],'','#FF0000',$config,$config['type']);
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