<?php

namespace app\admin\model;

use think\exception\PDOException;
use think\Model;

class MemberquestionnaireinfotypeModel extends Model
{

    protected $table = "xk_member_questionnaire_info_type";

    public function getBuBywhere($where, $size)
    {
        return $this->alias('q')
            ->join('xk_bu b','q.bu_id=b.id')
            ->field(['q.*,b.bu_name'])
            ->where($where)->order('id desc')->paginate($size);
    }

    public function getAllUsers($where)
    {
        return $this->where($where)->count();
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
     * 统计回答的个数
     */
    public function getSum($id)
    {
        return count(collection($this->where('questionnaire_info_type_id',$id)->select())->toArray());
    }

    /**
     * 获取本用户的回答
     */
    public function getMemberType($id)
    {
        return collection($this->alias('t')
                               ->join('xk_questionnaire_info_type it','it.id=t.questionnaire_info_type_id','LEFT')
                               ->field(['t.*','it.content as type_content'])
                               ->where('questionnaire_member_id',$id)->select())->toArray();
    }

    /**
     * 查询所有的用户文本回答
     */
    public function getText($id)
    {
        return collection($this
//                    ->join('xk_member_questionnaire qm','qm.id=q.questionnaire_member_id','left')
//                    ->join('xk_member m','qm.member_id = m.id','left')
                    ->where('questionnaire_info_type_id',$id)
//                    ->field(['q.*'])
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