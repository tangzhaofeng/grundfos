<?php

namespace app\admin\model;

use think\Model;
use think\Db;

class CustomerModel extends Model
{
    protected  $table = 'xk_customer';
     /**
     * 根据管理员id获取角色信息
     * @param $id
     */
    public function getOneData($id) {
        return $this->where('customerCode', $id)->find();
    }

     public function getDataByWhere($where, $size) {
        return $this->where($where)
                    ->order('id desc')->paginate($size)->each(function($item, $index){
                        $videoCount = Db::query("select count(*) as count from xk_video where FIND_IN_SET('".$item['customerCode']."',customerCode)");
                        $item['videoCount'] =  $videoCount[0]['count'];
                       
                        $faqCount = Db::query("select count(*) as count from xk_faq where FIND_IN_SET('".$item['customerCode']."',customerCode)");
                        $item['faqCount'] =  $faqCount[0]['count'];

                        $documentsCount = Db::query("select count(*) as count  from xk_document where FIND_IN_SET('".$item['customerCode']."',customerCode)");
                        $item['documentsCount'] =  $documentsCount[0]['count'];

                        $webinarsCount = Db::query("select count(*) as count from xk_webinars where FIND_IN_SET('".$item['customerCode']."',customerCode)");
                        $item['webinarsCount'] =  $webinarsCount[0]['count'];
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
     * 插入seller信息
     * @param $param
     */
    public function insert($param) {
        try{
            $result = $this->save($param);
            if(false === $result){
                // 验证失败 输出错误信息
                return ['code' => -1, 'data' => '', 'msg' => $this->getError()];
            }else{

                return ['code' => 1, 'data' => '', 'msg' => '添加成功'];
            }
        }catch( PDOException $e){

            return ['code' => -2, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

    /**
     * 编辑角色信息
     * @param $param
     */
    public function edit($param)
    {
        try{

            $result =  $this->validate('RoleValidate')->save($param, ['customerCode' => $param['id']]);

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
            $this->where('Id', $id)->delete();
            return ['code' => 1, 'data' => '', 'msg' => '删除成功','id'=>$id];
        }catch( PDOException $e){
            return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
        }
    }


}