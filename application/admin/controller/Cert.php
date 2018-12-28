<?php

namespace app\admin\controller;
use app\admin\model\CustomerModel;
use app\admin\model\UserModel;
use app\admin\model\MyCertModel;

use think\Db;
class Cert extends Base
{
    //用户列表
    public function index() {
        $param = input('param.');
        //导出
        $this->export($param);
        //show list
        $param['type'] = isset($param['type'])?$param['type']:0;
        
       

        if(isset($param['type']) && $param['type']==='my_cert'){
            $this->assign('return',$this->my_cert());
            return $this->fetch('my_cert');
        }elseif(isset($param['type']) && $param['type']==='my_cert_delete'){
            return $this->my_cert_delete();
        } else {
            $obj = new CustomerModel();
            
            $this->assign('customer',$obj->getOneData($param['id']));
            $this->assign('return',$this->list_cert());
            return $this->fetch();
        } 
    }


    /****
    *==========管理=================
    * @2017-12-2 
    */
    function list_cert(){
        $param = input('param.');
        $size = 10;
        $where = [];
        $where['CustomerCode'] = $param['id']; 
        if (isset($param['searchText']) && !empty($param['searchText'])) {
            $where['customerCode'] = ['like', '%' . trim($param['searchText']) . '%'];	
        }
        $obj = new UserModel();
        $selectResult = $obj->getDataByWhere($where, $size);
        $data['searchText'] = !empty($param['searchText'])?trim($param['searchText']):null;
        $data['total'] = $obj->getDataCountByWhere($where); //总数据
        $data['rows'] = $selectResult;
        $data['page'] = $selectResult->render();
        return $data;
    }

    //删除
    public function delete() {
        $id = input('param.cert_id');
        $obj = new CertModel();
        return json( $obj->del($id) );
    }

     //导出
    public function export(&$param)
    {
       if (isset($param['export']) &&  $param['export']==true) {
            //组装数据
            $data = Db("user")->where('customerCode',$param['customerCode'])->order("id desc")->select();
            $ret = Db("customer")->where('customerCode',$param['customerCode'])->find();
            foreach($data as $k=>$v){
                $data[$k]['customerCode'] = $ret['customerName'];
            }
            $title =[ ['id','customerName','User id','Role','Create time'] ] ;
            $data = array_merge($title,$data);
            //导出数据
            $Event = \think\Loader::controller('admin/Common');
            $Event->export_xls($data,$ret['customerName']." user.xls");   
            exit();
        }
    }



      /****
    *==========管理=================
    * @2017-12-2 
    */
    function my_cert(){
        $param = input('param.');
        $size = 5;
        $where = [];
        $where['userId'] = $param['uid']; 
      
        $obj = new MyCertModel();
        $selectResult = $obj->getDataByWhere($where, $size);
        $data['searchText'] = !empty($param['searchText'])?trim($param['searchText']):null;
        $data['total'] = $obj->getDataCountByWhere($where); //总数据
        $data['rows'] = $selectResult;
        $data['page'] = $selectResult->render();
        return $data;
    }
    
    
     //删除
    public function my_cert_delete() {
        $id = input('param.id');
        $obj = new MyCertModel();
        return json( $obj->del($id) );
    }
    



}
