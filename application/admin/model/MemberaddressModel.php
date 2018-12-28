<?php

namespace app\admin\model;

use think\Db;
use think\Model;

class MemberaddressModel extends Model
{

    protected $table = "xk_member_address";


    //获取用户所有地址
    /**条件查询列表
     * @param array $where
     * @param array $pageData
     * @param string $order
     * @param string $field
     * @return mixed
     */
    public function listByWhere($where = [], $pageData = [], $order = 'id Asc', $field = '*')
    {

        $res['count'] = $this->conditionCascade($where, $order, $field)->count();
        if ($pageData)
            $res['list'] = $this->conditionCascade($where, $order, $field)
                ->page($pageData['pageNow'], $pageData['pageSize'])
                ->select();
        else
            $res['list'] = $this->conditionCascade($where, $order, $field)
                ->select();
        return $res;
    }

    /**
     * 构造级联条件
     * @param $where
     * @param $order
     * @param $field
     * @return $this
     */
    public function conditionCascade($where, $order, $field)
    {
        return $this->alias('a')
            ->where($where)
            ->order($order)
            ->field($field);
    }

    /**
     * 设置默认收货地址
     * @param $where
     */
    public function setDefault($id,$member_id) {
        try{
            //开启事务
            Db::startTrans();
            //修改当前用户默认地址为不默认
            Db('member_address')->where(['status'=>1,'member_id'=>$member_id])->update(['status'=>2]);
            //设置默认地址
            Db('member_address')->where(['id'=>$id])->update(['status'=>1]);
            //提交事务
            Db::commit();
            return ['code' => 1, 'data' => '', 'msg' => '设置成功', 'id'=>$id ];
        }catch( \Exception $e){
            //提交失败 回滚事务 返回错误信息
            Db::rollback();
            return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

    /**
     * 根据搜索条件获取所有的用户数量
     * @param $where
     */
    public function getDataCountByWhere($where) {
        return $this->where($where)->count();
    }


    //获取用户默认收货地址
    public function getMemberAddressDefault($uid) {
        $where['member_id'] = $uid;
        $where['status'] = 1;
        return $this->where($where)->find();
    }


    /**
     * 插入地址信息
     * @param $param
     */
    public function insert($param) {
        try{
            //查询该用户有没有默认地址
            $address = $this->where(['member_id'=>$param['member_id'],'status'=>1])->find();
            if(!$address){
                $param['status'] = 1;
            }
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

    /**
     * 编辑地址信息
     * @param $param
     */
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

    /**
     * 删除
     * @param $id
     */
    public function del($id) {
        try{
            $this->where('id', $id)->delete();
            return ['code' => 1, 'data' => '', 'msg' => '删除成功','id'=>$id];
        }catch( PDOException $e){
            return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
        }
    }
}