<?php

namespace app\admin\model;

use think\Model;

class ActivityshareModel extends Model
{

    protected $table = "xk_activity_share";

    public function getBuBywhere($where, $size)
    {
        return $this
            ->alias('c')
            ->join('xk_member b','b.id = c.member_id')
            ->join('xk_company_work_type w','w.id = b.company_work_type_id','Left')
            ->join('xk_company_type t','t.id = b.company_id','Left')
            ->join('xk_bu u','u.id = b.is_bu_id')
            ->field(['c.*','b.name','w.work_type_name','t.type_name','u.bu_name','b.name',
                'b.nickname',
                'b.sex',
                'b.headimgurl',
                'b.tel',
                'b.email',
                'b.id as member_id'
            ])
            ->where($where)
            ->order('c.id desc')
            ->paginate($size);
    }

    public function getAllUsers($where)
    {
        return $this->alias('c')->join('xk_member b','b.id = c.member_id')->where($where)->count();
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

                return ['code' => 1, 'data' => '', 'msg' => '添加成功', 'faq_id'=>$this->id ];
            }
        }catch( PDOException $e){

            return ['code' => -2, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

    public function edit($param)
    {
        try{

            $result =  $this->validate('RoleValidate')->save($param, ['id' => $param['id']]);

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

    public function del($param) {
        try{
            $data = $this->where('activity_id',$param['activity_id'])->where('member_id',$param['member_id'])->find();
            $this->where('activity_id',$param['activity_id'])->where('member_id',$param['member_id'])->delete();
            return ['code' => 1, 'data' => '', 'msg' => '删除成功','id'=>$param['activity_id'], 'data'=>$data];
        }catch( PDOException $e){
            return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

    /**
     * 线上活动分享照片
     */
    public function saveLine($param)
    {
        try{
            //等待确认在加验证
            $result = $this->save($param);
            if(false === $result){
                // 验证失败 输出错误信息
                return returninfos('2','提交失败');
            }else{
                return returninfos('1','提交成功');
            }
        }catch( PDOException $e){
            return returninfos('2',$e->getMessage());
        }
    }

    /**
     * 红包发送修改修改状态值
     */
    public function editStart($param)
    {
        try{
            //等待确认在加验证
            $result = $this->where($param)->update(['status'=>2, 'send_time' => date('Y-m-d H:i:s', time())]);
            if(false === $result){
                // 验证失败 输出错误信息
                return returninfos('2','失败');
            }else{
                return returninfos('1','红包发送成功');
            }
        }catch( PDOException $e){
            return returninfos('2',$e->getMessage());
        }
    }
}