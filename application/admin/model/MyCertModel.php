<?php

namespace app\admin\model;

use think\Model;
use think\Db;
class MyCertModel extends Model
{
    protected  $table = 'xk_user_cert';
     /**
     * 根据管理员id获取角色信息
     * @param $id
     */
    public function getOneData($id) {
        return $this->where('id', $id)->find();
    }

     public function getDataByWhere($where, $size) {
        return $this->where($where)
                    ->order('id desc')->paginate($size)->each(function($item,$index){
						$cert = Db('cert')->where("id",$item['certId'])->find();
						$item['certId'] = $cert['title'];
					});
    }

     /**
     * 根据搜索条件获取所有的用户数量
     * @param $where 
     */
    public function getDataCountByWhere($where) {
        return $this->where($where)->count();
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