<?php

namespace app\admin\controller;
use app\admin\model\WebinarsModel;
use think\Db;
use \think\Loader;
class Webinars extends Base
{
    //用户列表
    public function index() {
        $this->assign('return',$this->_list());
        $center = input("param.center");
        if(request()->isAjax()){
            return json($this->_list());
        } else {
            if($center)
                return $this->fetch("index2");
            else
                return $this->fetch();
        }
    }

    public function index2() {
        $this->assign('return',$this->_list());
        return $this->fetch();
    }

    function _list(){
        $param = input('param.');
        //导出
        $this->export($param);

        //show list
        $size = 10;
        $where = [];
        if (isset($param['searchText']) && !empty($param['searchText'])) {
            $where['title'] = ['like', '%' . trim($param['searchText']) . '%'];
        }
       
        if (isset($param['language']) && !empty($param['language'])) {
            $where['language'] = $param['language'];	
        }
        $obj = new WebinarsModel();
        $selectResult = $obj->getDataByWhere($where, $size);

        $return['searchText'] = !empty($param['searchText'])?trim($param['searchText']):null;
        $return['language'] = !empty($param['language'])?trim($param['language']):0;
        $return['total'] = $obj->getDataCountByWhere($where);  //总数据
        $return['rows'] = $selectResult;
        $return['page'] = $selectResult->render();
        return  $return;
    }

    //添加活动
    public function add()
    {
       if(request()->isPost()){
            $param = input('post.');
            $obj = new WebinarsModel();
			$ret = $obj->insert($param);
            return json($ret);
        }else{
             return $this->fetch();
        }
    }

    //编辑活动
    public function edit()
    {
        $obj = new WebinarsModel();
        if(request()->isPost()){
            $param = input('post.');
            return json( $obj->edit($param) );
        }else{
            $id = input('param.id');
            $this->assign('data',$obj->getOneData($id));
            return $this->fetch();
        }
    }

    //删除活动
    public function delete()
    {
        $id = input('param.id');
        $obj = new WebinarsModel();
		$ret = $obj->del($id);
        //减少关联
       $this->setDecBaoming($ret['data']['customerCode']);

        return json($ret);
    }

    function setDecBaoming(&$customerCode){
        $customerCode = explode(",",rtrim($customerCode,','));
		$customer = Db("customer");
		foreach($customerCode as $k=>$v){
			$customer->where("customerCode",$v)->setDec('baoming',1); //更新views
		}
    }

       //导出
    public function export(&$param)
    {
       if (isset($param['export']) &&  $param['export']==true) {
            $data = Db("webinars")->order("id desc")->field("id,customerCode,title,'venue','desc',views,language,'start_time','end_time',create_time")->select();
            $customer = Db("customer");
            foreach($data as $k=>$v){
                $data[$k]['language'] = getLanguage( $v['language']-1 );
                $ret = $customer->where('customerCode',$v['customerCode'])->find();
                $data[$k]['customerCode'] = $ret['customerName'];
            }
         
            $title =[ ['Webinars id','Customer name','Title','Venue','Description','Views','Language','Start time','End time','Create time'] ] ;
            $data = array_merge($title,$data);
            $Event = \think\Loader::controller('admin/Common');
            $Event->export_xls($data,"Webinars.xls");   
            exit();
        }
    }
    
}
