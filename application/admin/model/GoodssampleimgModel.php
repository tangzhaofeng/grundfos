<?php

namespace app\admin\model;

use think\exception\PDOException;
use think\Model;

class GoodssampleimgModel extends Model
{
    protected  $table = 'xk_good_sample_img';
    /**
     * 根据管理员id获取角色信息
     * @param $id
     */
    public function getOneData($id) {
        return $this->where('id', $id)->find();
    }

    public function getDataByWhere($where, $size) {

        if(isset($where['is_group'])){
            if($where['is_group'] == 1){
                unset($where['is_group']);
                if( !request()->isAjax()){
                    return $this->where($where)->order('order_num asc')->group('title')->paginate($size);
                }else{
                    //ajax分页请求
                    $roles = input("post.roles");
                    return $this->where($where)
                        ->order('order_num asc')->group('title')->paginate(10,false,[
                            'type'     => 'Bootstrap',
                            'var_page' => 'page',
                            'page' =>input('param.page'),
                            'path'=>'javascript:AjaxPage([PAGE]);',
                        ]);
                }
            }
            unset($where['is_group']);
        }

        if( !request()->isAjax()){
            if(isset($where['title'])){
                 return $this->whereOr($where)->order('order_num asc')->group('title')->paginate($size);
            }else{
                return $this->whereOr($where)->order('order_num asc')->group('title')->paginate($size);
            }
        
        }else{
            //ajax分页请求
            $roles = input("post.roles");
            return $this->whereOr($where)
                ->order('order_num asc')->group('title')->paginate(10,false,[
                    'type'     => 'Bootstrap',
                    'var_page' => 'page',
                    'page' =>input('param.page'),
                    'path'=>'javascript:AjaxPage([PAGE]);',
                ]);
        }

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
            //等待确认在加验证
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


    /**
     * 删除
     * @param $id
     */
    public function del($sample_id,$img) {
        try{
            $data = $this->where('sample_id', $sample_id)->where('img',$img)->find();
            return ['code' => 1, 'data' => '', 'msg' => '删除成功'];
        }catch( PDOException $e){
            return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

    /**
     * 增加点击数量
     */
    public function addClick($id)
    {
        try{
            $oldclick = $this->where('id',$id)->value('click');
            $newclick = $oldclick+1;
            $param['click'] = $newclick;
            $result = $this->save($param,['id'=>$id]);
            if(false === $result){
                return ['code' => 0, 'data' => '', 'msg' => $this->getError()];
            }else{
                return ['code' => 1, 'data' => '', 'msg' => $this->getError()];
            }
        }catch (PDOException $e){
            return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

    /**
     * 产品导出的数据
     */
    public function excelDate($type)
    {
        try{
           return collection($this->where('group_id',$type)
                            ->field(['id','title','desc','create_time','total','click','keyword'])
                            ->select())->toArray();
        }catch (PDOException $e){
            return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

}