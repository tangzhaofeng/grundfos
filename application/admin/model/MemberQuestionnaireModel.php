<?php

namespace app\admin\model;

use think\exception\PDOException;
use think\Model;

class MemberQuestionnaireModel extends Model
{

    protected $table = "xk_member_questionnaire";

    public function getBuBywhere($where, $size)
    {
        if ($where['id']){

            $data['q.questionnaire_id'] = $where['id'];
        }
        return $this->alias('q')
            ->join('xk_member m','q.member_id=m.id')
            ->field(['q.*','m.name','m.nickname','m.headimgurl'])
            ->where($data)->order('id desc')->paginate($size);
    }

    public function getAllUsers($where)
    {
        if ($where['id']){

            $data['q.questionnaire_id'] = $where['id'];
        }
        return $this->alias('q')
            ->join('xk_member m','q.member_id=m.id')
            ->field(['q.*','m.name','m.nickname','m.headimgurl'])
            ->where($data)->count();
    }

    /**
     * 查询单个问卷
     */
    public function getInfo($id)
    {
        return $this->alias('q')
//                    ->join('xk_questionnaire_info i','i.questionnaire_id = q.id')
                    ->where('q.id',$id)
                    ->find();
    }

    /**
     * 查询用户的回答与问题
     */
    public function getMemberAnswer($id)
    {
        return collection($this->alias('mq')
                    ->join('xk_member_questionnaire_info_type mi','mi.questionnaire_member_id=mq.id')
//                    ->field([''])
                    ->where('mq.id',$id)
                    ->select())->toArray();
    }

    /**
     * 查询用户的回答与问题
     */
    public function getAllMemberAnswer($id)
    {
        return collection($this->alias('mq')
            ->join('xk_member_questionnaire_info_type mi','mi.questionnaire_member_id=mq.id')
//                    ->field([''])
            ->where('mq.questionnaire_id',$id)
            ->select())->toArray();
    }

    /**
     * 查询用户的回答的主表
     */
    public function MemberAnswer($id)
    {
        return collection($this->alias('mq')
//            ->join('xk_member_questionnaire_info_type mi','mi.questionnaire_member_id=mq.id')
            ->join('xk_member m','m.id=mq.member_id')
            ->field(['mq.*','m.name','m.tel'])
            ->where('mq.questionnaire_id',$id)
            ->select())->toArray();
    }

    public function inster($param)
    {
        try{
            //等待确认在加验证
            $result = $this->save($param);
            if(false === $result){
                // 验证失败 输出错误信息
                return ['code' => -1, 'data' => '', 'msg' => $this->getError()];
            }else{

                return ['code' => 1, 'data' => '', 'msg' => '添加成功', 'id'=>$this->id ];
            }
        }catch( PDOException $e){

            return ['code' => -2, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

}