<?php

namespace app\admin\model;

use think\exception\PDOException;
use think\Model;

class LivecommentModel extends Model
{

    protected $table = "xk_live_comment";

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

    public function inster($param)
    {
        try{
            //等待确认在加验证
            $result = $this->save($param);
            if(false === $result){
                // 验证失败 输出错误信息
                return ['code' => -1, 'data' => '', 'msg' => ''];
            }else{

                return ['code' => 1, 'data' => '', 'msg' => '','id'=>$this->id];
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

    public function del($id) {
        try{
            $data = $this->where('id', $id)->find();
            $this->where('id', $id)->delete();
            return ['code' => 1, 'data' => '', 'msg' => '删除成功','id'=>$id, 'data'=>$data];
        }catch( PDOException $e){
            return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
        }
    }



    /**
     * 检车是否已经报名
     */
    public function isReg($param)
    {
        try{
            $info = $this
                ->where('live_id',$param['live_id'])
                ->where('member_id',$param['member_id'])
                ->where('type',3)
                ->find();
            if ($info){
                return ['code' => 1, 'data' => '', 'msg' => ''];
            }else{
                return ['code' => 0, 'data' => '', 'msg' => ''];
            }
        }catch (PDOException $e){
            return ['code' => -2, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

    /**
     * 检测是否已经点赞
     */
    public function isLove($param)
    {
        try{
            $info = $this
                ->where('live_id',$param['live_id'])
                ->where('member_id',$param['member_id'])
                ->where('type',2)
                ->find();
            if ($info){
                return ['code' => 1, 'data' => '', 'msg' => ''];
            }else{
                return ['code' => 0, 'data' => '', 'msg' => ''];
            }
        }catch (PDOException $e){
            return ['code' => -2, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

    /**
     * 获取直播不为空的评论
     */
    public function getCommentList($param)
    {
//        try{
            $info = collection(
                $this
                ->alias('c')
                ->join('xk_member m','m.id = c.member_id')
                ->join('xk_live l','l.id = c.live_id')
                ->where('l.cid',$param)
                ->where('c.type',1)
                ->field(['c.*','m.name','m.headimgurl'])
                ->select())->toArray();
                return ['code' => 1, 'data' => $info, 'msg' => "获取成功"];
//        }catch (PDOException $e){
//            return ['code' => -2, 'data' => '', 'msg' => $e->getMessage()];
//        }
    }

}