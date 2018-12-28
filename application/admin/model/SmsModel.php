<?php

namespace app\admin\model;

use think\Model;

class SmsModel extends Model
{

    protected $table = "xk_SMS";

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

    /**
     * 插入信息
     */
    public function insert($param) {
        try{
            $result = $this->save($param);
            if(false === $result){
                // 验证失败 输出错误信息
                return returninfos('2',$this->getError());
            }else{
              
            }
        }catch( PDOException $e){
            return returninfos('2',$e->getMessage());
        }
    }

}