<?php

namespace app\admin\model;

use think\Model;

class IntegralgoodsModel extends Model
{
    protected  $table = 'xk_integral_goods';
     /**
     * 根据管理员id获取角色信息
     * @param $id
     */
    public function getOneData($id) {
        return $this->where('id', $id)->find();
    }

    /**
     * 根据条件查询订单信息
     * @param $id
     */
    public function getDataByWhere($where, $size,$sence = null) {
        if( !request()->isAjax())
            return $this->where($where)->order('id desc')->paginate($size,false,[
                'query'=>['type'=>'goods_list'],
            ]);
        else
            //ajax分页请求
            $roles = input("post.roles");
        return $this->where($where)
            ->order('id desc')->paginate(10,false,[
                'type'     => 'Bootstrap',
                'var_page' => 'page',
                'page' =>input('param.page'),
                'path' => 'javascript:AjaxPage([PAGE]);',
                'query' => ['type' => 'goods_list']
            ]);
    }

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

                return ['code' => 1, 'data' => '', 'msg' => '添加成功', 'id'=>$this->id ];
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
			$data = $this->where('id', $id)->find();
            $this->where('id', $id)->delete();
            return ['code' => 1, 'data' => '', 'msg' => '删除成功','id'=>$id, 'data'=>$data];
        }catch( PDOException $e){
            return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
        }
    }


}