<?php

namespace app\admin\model;

use think\Model;

class IntergralgoodsModel extends Model
{
    protected  $table = 'xk_intergral_goods';
     /**
     * 根据管理员id获取角色信息
     * @param $id
     */
    public function getOneData($id) {
        return $this->where('id', $id)->find();
    }

     public function getDataByWhere($where, $size) {
        if( !request()->isAjax())
            return $this->where($where)->order('id desc')->paginate($size);
        else
            //ajax分页请求
            $roles = input("post.roles");    
            return $this->where($where)
                        ->order('id desc')->paginate(10,false,[
                            'type'     => 'Bootstrap',
                            'var_page' => 'page',
                            'page' =>input('param.page'),
                            'path'=>'javascript:AjaxPage([PAGE]);',
                        ]);
    }

     /**
     * 根据搜索条件获取所有的用户数量
     * @param $where 
     */
    public function getDataCountByWhere($where) {
        return $this->where($where)->count();
    }
    /**
     * 插入商品信息
     * @param $param
     */
    public function insert($param) {
        try{
             //等待确认在加验证 
            $result = $this->validate('IntegralgoodsValidate')->save($param);
            if(false === $result){
                // 验证失败 输出错误信息
                return ['code' => -1, 'data' => '', 'msg' => $this->getError()];
            }else{

                return ['code' => 1, 'data' => '', 'msg' => '添加成功', 'video_id'=>$this->id ];
            }
        }catch( PDOException $e){

            return ['code' => -2, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

    /**
     * 编辑商品信息
     * @param $param
     */
    public function edit($param)
    {
        try{

            $result =  $this->validate('IntegralgoodsValidate')->save($param, ['id' => $param['id']]);

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


    /**
     * 删除
     * @param $id
     */
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