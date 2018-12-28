<?php

namespace app\admin\model;

use think\exception\PDOException;
use think\Model;

class QuestionnaireModel extends Model
{

    protected $table = "xk_questionnaire";

    public function getBuBywhere($where, $size)
    {
        return $this->alias('q')
            ->join('xk_bu b','q.bu_id=b.id')
            ->field(['q.*,b.bu_name','b.bu_type'])
            ->where($where)->order('id desc')->paginate($size);
    }

    public function getAllUsers($where)
    {
        return $this->where($where)->count();
    }


    public function del($id) {
        try{
            $data = $this->where('id', $id)->find();
            $result = $this->where('id', $id)->delete();
            if ($result){//
                //获取所有的infoid
                $info_id = collection(Db('questionnaire_info')->where('questionnaire_id',$id)->field('id')->select())->toArray();
                foreach ($info_id as $k=>$v){
                    Db('questionnaire_info_type')->where('questionnaire_info_id',$v['id'])->delete();
                    Db('member_questionnaire_info_type')->where('questionnaire_info_id',$id)->delete();
                }
                Db('questionnaire_info')->where('questionnaire_id',$id)->delete();
                Db('member_questionnaire')->where('questionnaire_id',$id)->delete();
                $data = ['code' => 1, 'data' => '', 'msg' => '删除成功','id'=>$id, 'data'=>$data];
            }else{
                $data =  ['code' => 0, 'data' => '', 'msg' => '删除失败','id'=>$id, 'data'=>$data];
            }
            return $data;
        }catch( PDOException $e){
            return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
        }
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


    public function infoGetBuBywhere($id, $size)
    {
        return $this->alias('q')
            ->join('xk_member_questionnaire mq','mq.questionnaire_id=q.id')
            ->join('xk_member_questionnaire_info_type mi','mi.questionnaire_member_id=mq.id')
            ->join('xk_member m','mq.member_id=m.id')
            ->where('q.id',$id)
            ->where('mi.type',3)
            ->field(['q.id','mi.content','mq.member_id','mq.creater_time','m.name','m.nickname','m.headimgurl'])->paginate($size);
    }

    public function infoGetAllUsers($id)
    {
        return $this->alias('q')
            ->join('xk_member_questionnaire mq','mq.questionnaire_id=q.id')
            ->join('xk_member_questionnaire_info_type mi','mi.questionnaire_member_id=mq.id')
            ->join('xk_member m','mq.member_id=m.id')
            ->where('q.id',$id)
            ->where('mi.type',3)
            ->field(['q.id','mi.content','mq.member_id','mq.creater_time','m.name','m.nickname','m.headimgurl'])->count();
    }


    /**
     * 获取所有的文本回答
     */
    public function getText($id)
    {
        return collection($this->alias('q')
                    ->join('xk_member_questionnaire mq','mq.questionnaire_id=q.id')
                    ->join('xk_member_questionnaire_info_type mi','mi.questionnaire_member_id=mq.id')
                    ->join('xk_member m','mq.member_id=m.id')
                    ->where('q.id',$id)
                    ->where('mi.type',3)
                    ->field(['mi.content','mq.member_id','mq.creater_time','m.name','m.nickname','m.headimgurl'])
                    ->select())->toArray();
    }

    /**
     * 查询出全部部门与职员
     */
    public function type_work()
    {
//        $bu = collection($this->all())->toArray();
//        foreach ($bu as $k=>$v){
//
//        }
        return $this->alias('b')
            ->join('xk_company_type t','t.bu_id = b.id')
            ->join('xk_company_work_type w','w.company_id = t.id')
            ->field(['b.*','t.type_name','w.id as work_id','w.work_type_name'])
            ->order('b.id desc')
            ->where('t.status = 1')
            ->where('w.status = 1')
            ->select();
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