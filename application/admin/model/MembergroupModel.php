<?php

namespace app\admin\model;

use think\Model;

class MembergroupModel extends Model
{

    protected $table = "xk_member_group";

    public function getBuBywhere($where, $size)
    {
        return $this->alias('q')
//                ->join('xk_activity a','a.id = q.activity_id')
//                ->join('xk_bu b','b.id = q.bu_id')
//                ->field(['q.*','a.title','b.bu_name'])
                ->where($where)->order('q.id desc')->paginate($size);
    }

    public function getAllUsers($where)
    {
        return $this->where($where)->count();
    }

    /**
     * 获取此分组下面的所有用户
     */
    public function getMember($group_id)
    {
        return collection(
            $this->alias('mg')
                ->join('xk_member m','m.id=mg.member_id')
                ->where('mg.group_id',$group_id)
                ->order('m.create_time desc')
                ->field(['mg.member_id','m.name','m.unionid'])
                ->Distinct(true)
                ->select()
        )->toArray();

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

    public function edit($param)
    {
        try{

            $result =  $this->save($param, ['id' => $param['id']]);

            if(false === $result){
                // 验证失败 输出错误信息
                return ['code' => 0, 'data' => '', 'msg' => $this->getError()];
            }else{

                return ['code' => 1, 'data' => '', 'msg' => '编辑成功'];
            }
        }catch( PDOException $e){
            return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
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