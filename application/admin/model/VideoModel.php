<?php

namespace app\admin\model;

use think\exception\PDOException;
use think\Model;
use think\Db;

class VideoModel extends Model
{

    protected $table = "xk_video";

    public function getBuBywhere($where, $size)
    {
        return $this->alias('l')
            ->join('xk_member m','m.id = l.member_id')
            ->field(['l.*','m.name','m.nickname','m.headimgurl'])
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
            if (!isset($param['origUrl'])) {
                $param['origUrl'] = 'http://vodiskrgtra.vod.126.net/vodiskrgtra/'.$param['orig_video_key'];
            }
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



    /**
     * 获取所有的直播
     */
    public function getOn($status)
    {
        try{
            return collection($this->alias('l')
                ->join('xk_member m','l.member_id=m.id')
                ->field(['l.*','m.name','m.headimgurl'])
                ->where('l.status',$status)
                ->select())->toArray();
        }catch (PDOException $e){
            return ['code' => -2, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

    /**
     * 获取单个的直播
     */
    public function getOne($id)
    {
        try{
            return $this->alias('l')
                ->join('xk_member m','l.member_id=m.id')
                ->field(['l.*','m.name','m.headimgurl'])
                ->where('l.id',$id)
                ->find();
        }catch (PDOException $e){
            return ['code' => -2, 'data' => '', 'msg' => $e->getMessage()];
        }
    }


    /**
     * 获取用户报名的直播
     */
    public function getOnMember($status,$member_id = '')
    {
        try{
            return collection($this->alias('l')
                ->join('xk_live_comment m','l.id=m.live_id')
                ->join('xk_member mm','mm.id=l.member_id')
                ->field(['l.*','mm.name','mm.headimgurl'])
                ->where('l.status',$status)
                ->where('m.type',3)
                ->where('m.member_id',$member_id)
                ->Distinct(true)
                ->select())->toArray();
        }catch (PDOException $e){
            return ['code' => -2, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

    /**
     * 新增点赞数量
     */
    public function addClick($param)
    {
        try{
            $info = $this->where('id',$param['live_id'])->find();
            $newclick = $info['click']+1;
            $result = $this->save(['click'=>$newclick],['id'=>$param['live_id']]);
            if(false === $result){
                // 验证失败 输出错误信息
                return ['code' => 0, 'data' => '', 'msg' => ''];
            }else{

                return ['code' => 1, 'data' => '', 'msg' => ''];
            }
        }catch (PDOException $e){
            return ['code' => -2, 'data' => '', 'msg' => $e->getMessage()];
        }
    }


}