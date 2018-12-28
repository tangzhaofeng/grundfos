<?php

namespace app\admin\model;

use think\Model;

class MemberinvitationModel extends Model
{

    protected $table = "xk_member_invitation";

    public function getBuBywhere($where, $size)
    {
        $data = [];
        if (isset($where['bu_id'])){
            $data['bu_id'] = $where['bu_id'];
        }

        return $this->alias('q')
//                ->join('xk_activity a','a.id = q.activity_id')
               ->join('xk_bu b','b.id = q.bu_id')
               ->field(['q.*','b.bu_name'])
                ->where($data)->order('q.id desc')->paginate($size);
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

    public function del($id) {
        try{
            $data = $this->where('id', $id)->find();
            $this->where('id', $id)->delete();
            return ['code' => 1, 'data' => '', 'msg' => '删除成功','id'=>$id, 'data'=>$data];
        }catch( PDOException $e){
            return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
        }
    }


}