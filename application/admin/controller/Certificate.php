<?php

namespace app\admin\controller;
use app\admin\model\CertificateModel;
use think\Db;
class Certificate extends Base
{
    //Welcome list
    public function index() {
        $param = input('param.');
        //导出
        $this->export($param);
        //show list
        $size = 10;
        $where = [];
        if (isset($param['searchText']) && !empty($param['searchText'])) {
            $where['customerCode|title'] = ['like', '%' . trim($param['searchText']) . '%'];	
        }
        if (isset($param['id']) && !empty($param['id'])) {
            $where['customerCode'] = $param['id'];	
        }

        if (isset($param['language']) && !empty($param['language'])) {
            $where['language'] = $param['language'];	
        }
      
        $obj = new CertificateModel();
        $selectResult = $obj->getDataByWhere($where, $size);
        
        $return['searchText'] = !empty($param['searchText'])?trim($param['searchText']):null;
        $return['total'] = $obj->getDataCountByWhere($where);  //总数据
        $return['rows'] = $selectResult;
        $return['page'] = $selectResult->render();
        
        $this->assign('return',$return);
        if(isset($param['id'])){
            $this->assign('customer',Db("customer")->where('customerCode',$param['id'])->find());
        }
        return $this->fetch();
    }


   //Welcome add
    public function add()
    {
        if(request()->isPost()){
            $param = input('post.');
            $param['create_time'] = Date('Y-m-d H:i:s',time());
            $obj = new CertificateModel();
            return json($obj->insert($param));
        }else{
             return $this->fetch();
        }
    }

    //编辑视频 
    public function edit()
    { 
        $obj = new CertificateModel();
        if(request()->isPost()){
            $param = input('param.');
            return json( $obj->edit($param) );
        } else {
            $id = input('param.id');
            $this->assign('data',$obj->getOneData($id));
            return $this->fetch();
        }
    }

    //删除视频 
    public function delete()
    {
        $id = input('param.id');
        $obj = new CertificateModel();
        return json($obj->del($id));
    }


     //导出
    public function export(&$param)
    {
       if (isset($param['export']) &&  $param['export']==true) {
            //组装数据
            $data = Db("cert")->where('customerCode',$param['customerCode'])->field("id,customerCode,title,language,create_time")->order("id desc")->select();
            $ret = Db("customer")->where('customerCode',$param['customerCode'])->find();
            foreach($data as $k=>$v){
                $data[$k]['language'] = getLanguage( $v['language']-1 );
                $data[$k]['customerCode'] = $ret['customerName'];
            }
            $title =[ ['id','customerName','Welcome letter','Language','Create time'] ] ;
            $data = array_merge($title,$data);
            //导出数据
            $Event = \think\Loader::controller('admin/Common');
            $Event->export_xls($data,$ret['customerName']."  Certificate.xls");   
            exit();
        }
    }
}
