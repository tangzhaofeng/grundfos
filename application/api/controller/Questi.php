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
use app\admin\model\MemberquestionnaireinfotypeModel;
use app\admin\model\MemberQuestionnaireModel;
use app\admin\model\QuestionnaireinfoModel;
use app\admin\model\QuestionnaireinfotypeModel;
use app\admin\model\QuestionnaireModel;
use app\index\controller\Index;
use think\Cookie;
use think\Exception;

class Questi extends Base
{
    private $member_info;
    private $obj;
    private $activity;
    private $config;

    public function __construct()
    {
        parent::__construct();
        $type = session('type');
        $this->config = session('wxconfig_'.$type);
        $this->member_info = session($this->config['appid']."user_oauth");
        $this->obj = new QuestionnaireModel();
        $this->activity = new ActivityModel();
    }

    /**
     * 获取问卷信息
     */
    public function getQuesti()
    {
        try{
            $questi_id = input('param.id');
            $info_model = new QuestionnaireinfoModel();
            $type_model = new QuestionnaireinfotypeModel();

            $data = array();
            //获取主表数据
            $questi = $this->obj->getInfo($questi_id);

            $data['id'] = $questi['id'];
            $data['title'] = $questi['title'];
            $data['creater_time'] = $questi['creater_time'];
            $data['bu_id'] = $questi['bu_id'];
            $data['description'] = $questi['description'];
            $data['success_info'] = $questi['success_info'];
            //获取info副表数据
            $info = $info_model->getInfo($questi['id']);

            //获取最后info表type_info数据
            foreach ($info as $k=>$v){
                $info[$k]['info_type'] = $type_model->getType($v['id']);
            }
            //最后拼接
            $data['info'] = $info;
            
            $data['type'] = session('type');
            return json(['code'=>1,'msg'=>'获取成功','data'=>$data]);
        }catch (Exception $e){
            return json(['code'=>0,'msg'=>'系统错误']);
        }
    }

    /**
     * 获取问卷信息
     */
    public function saveQuesti()
    {
        $memberquest = new MemberQuestionnaireModel();
        $memberquestinfotype = new MemberquestionnaireinfotypeModel();
        try{
            if(request()->isPost()){
                $param = input('post.');//获取全部数据
                $member = $this->member_info;//当前用户数据

                //添加主表
                $member_quest = $memberquest->inster([
                    'member_id'=>$member['id'],
                    'questionnaire_id'=>$param['id'],
                    'creater_time'=>date('Y-m-d H:i:s',time())
                ]);
                //循环添加副表
                foreach ($param['list'] as $k=>$v){
                    $data = [
                        'questionnaire_member_id'=>$member_quest['id'],
                        'questionnaire_info_id'=>$v['info_id'],
                        'creater_time'=>date('Y-m-d H:i:s',time()),
                        'type'=>$v['type']
                    ];
                    if ($v['type'] != 3){
                        if (is_array($v['type_id'])){//type等于3是文本
                            foreach ($v['type_id'] as $key => $value){
                                $data['questionnaire_info_type_id'] = $value;
                                $memberquestinfotype->insert($data);
                            }

                        }else{
                            $data['questionnaire_info_type_id'] = $v['type_id'];
                            $memberquestinfotype->insert($data);
                        }
                       
                    }else{
                        $data['content'] = $v['content'];
                        $memberquestinfotype->insert($data);
                    }
                }

                return json($member_quest);
            }
        }catch (Exception $e){
            return json(['code'=>0,'msg'=>'系统错误']);
        }
    }


}