<?php

namespace app\admin\model;

use think\Model;

class AutokeyModel extends Model
{

    protected $table = "xk_auto_reply";

    public function getBuBywhere($where, $size)
    {
        return $this->alias('a')

            ->where($where)->order('id ','desc')->paginate($size);
    }

    public function getAllUsers($where)
    {
        return $this->where($where)->count();
    }


}