<?php

namespace app\admin\model;

use think\exception\PDOException;
use think\Model;

class QuestionnaireinfoModel extends Model
{

    protected $table = "xk_questionnaire_info";

    public function getBuBywhere($where, $size)
    {
        return $this->name('bu')
            ->where($where)->order('id desc')->paginate($size);
    }

    public function getAllUsers($where)
    {
        return $this->where($where)->count();
    }

    public function getInfo($q_id)
    {
        return collection($this->where('questionnaire_id',$q_id)->select())->toArray();
    }

    /**
     * 获取所有的type
     */
    public function getType($id)
    {
        return collection(
            $this->alias('i')
                 ->join('xk_questionnaire_info_type t','t.questionnaire_info_id=i.id')
                 ->where('i.id',$id)
                 ->select()
        )->toArray();
    }

}