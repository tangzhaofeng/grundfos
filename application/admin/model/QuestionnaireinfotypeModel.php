<?php

namespace app\admin\model;

use think\exception\PDOException;
use think\Model;

class QuestionnaireinfotypeModel extends Model
{

    protected $table = "xk_questionnaire_info_type";

    public function getBuBywhere($where, $size)
    {
        return $this->name('bu')
            ->where($where)->order('id desc')->paginate($size);
    }

    public function getAllUsers($where)
    {
        return $this->where($where)->count();
    }

    /**
     * 获取属于info的type
     */
    public function getType($id)
    {
        return collection($this->where('questionnaire_info_id',$id)->select())->toArray();
    }


}