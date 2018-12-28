<?php

namespace app\admin\controller;
use app\admin\model\CustomerModel;
use think\Db;
class Customer extends Base
{
    //用户列表
    public function index() {
        $param = input('param.');
        $this->export($param);
        $size = 10;
        $where = [];
        if (isset($param['searchText']) && !empty($param['searchText'])) {
            $where['customerCode|customerName'] = ['like', '%' . trim($param['searchText']) . '%'];	
        }
        $obj = new CustomerModel();
        $selectResult = $obj->getDataByWhere($where, $size);
        
        $return['searchText'] = !empty($param['searchText'])?trim($param['searchText']):null;
        $return['total'] = $obj->getDataCountByWhere($where);  //总数据
        $return['rows'] = $selectResult;
        $return['page'] = $selectResult->render();
        
        $this->assign('return',$return);
        return $this->fetch();
    }


    //添加用户 
    public function add()
    {
        if(request()->isPost()){
            $param = input('param.');
            $param['createTime'] = Date('Y-m-d',time());
             $param['customerCode'] = uniqid();
            $user = new CustomerModel();
            return json($user->insert($param) );
        }else{
             return $this->fetch();
        }
    }

    //编辑角色
    public function edit()
    {
        
        $user = new CustomerModel();
        if(request()->isPost()){
            $param = input('param.');
            return json( $user->edit($param) );
        }else{
           
            $this->assign('data',$user->getOneData( input('param.id') ));
            return $this->fetch();
        }
    }

    //删除角色
    public function delete()
    {
        $id = input('param.id');
        $obj = new CustomerModel();
        return json($obj->del($id));
    }

     //导出
    public function export(&$param)
    {
       if (isset($param['export']) &&  $param['export']==true) {
            //组装数据
            $data = Db("customer")->order("id desc")->field("id,customerCode,customerName,createTime")->select();
            $title =[ ['id','customerCode','customerName','Create time'] ] ;
            $data = array_merge($title,$data);
            //导出数据
            $Event = \think\Loader::controller('admin/Common');
            $Event->export_xls($data,"Customer.xls");   
            exit();
        }
    }

    //统计
    public function statistics() {
        $size = 10;
        $where = [];
        if (isset($param['searchText']) && !empty($param['searchText'])) {
            $where['customerCode|customerName'] = ['like', '%' . trim($param['searchText']) . '%'];	
        }
        $obj = new CustomerModel();
        $selectResult = $obj->getDataByWhere($where, $size);
        
        $return['searchText'] = !empty($param['searchText'])?trim($param['searchText']):null;
        $return['total'] = $obj->getDataCountByWhere($where);  //总数据
        $return['rows'] = $selectResult;
        $return['page'] = $selectResult->render();
        
        $this->assign('return',$return);
        return $this->fetch();
    }

}
