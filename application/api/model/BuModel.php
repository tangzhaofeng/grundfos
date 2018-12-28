<?php
/**
 * Created by PhpStorm.
 * User: juplus-06
 * Date: 2018/4/9
 * Time: 16:10
 */
namespace app\api\model;

use think\Model;

class BuModel extends Model
{
    protected $table = 'xk_bu';


    public function getBuBywhere($where, $size)
    {
        return $this->alias('c')
//            ->join('xk_bu b','b.id = c.bu_id')
//            ->field(['c.*','b.bu_name'])
            ->where($where)
//            ->order('c.id desc')
            ->paginate($size);
    }

    public function getAllUsers($where)
    {
        return $this->where($where)->count();
    }



}