<?php

namespace app\admin\model;

use think\Model;

class MemberopenunionModel extends Model
{

    protected $table = "xk_member_openid_unionid";

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
     * 根据openid 查询出appid与appsecer
     */
    public function getAppSercer($openid)
    {
        return collection($this->alias('m')
                    // ->join('xk_bu_app_secret a','m.bu_id = a.bu_id')
                    // ->where('m.openid',$openid)
                    ->field(['m.bu_id'])
                    ->select())->toArray();
    }



}