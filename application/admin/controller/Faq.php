<?php

namespace app\admin\controller;
use app\admin\model\FaqModel;
use think\Db;
use \think\Loader;
class Faq extends Base
{
    //FAQs list
    public function index() {
        $param = input('param.');
        //导出
        $this->export($param);

        //show list
        $size = 10;
        $where = [];
        if (isset($param['searchText']) && !empty($param['searchText'])) {
            $where['title|desc'] = ['like', '%' . trim($param['searchText']) . '%'];	
        }
        
        if (isset($param['language']) && !empty($param['language'])) {
            $where['language'] = $param['language'];	
        }
        $obj = new FaqModel();
        $selectResult = $obj->getDataByWhere($where, $size);
        
        $return['searchText'] = !empty($param['searchText'])?trim($param['searchText']):null;
        $return['language'] = !empty($param['language'])?trim($param['language']):0;
        $return['total'] = $obj->getDataCountByWhere($where);  //总数据
        $return['rows'] = $selectResult;
        $return['page'] = $selectResult->render();
        $this->assign('return',$return);
        if(request()->isAjax())
            return json($return);
        else
             return $this->fetch();
    }


     //FAQs add
    public function add()
    {
        if(request()->isPost()){
            $param = input('param.');
            $param['create_time'] = Date('Y-m-d H:i:s',time());
            $obj = new FaqModel();
			$ret = $obj->insert($param);
            return json($ret);
        }else{
             return $this->fetch();
        }
    }

   //FAQs edit
    public function edit()
    { 
        $obj = new FaqModel();
        if(request()->isPost()){
            $param = input('param.');
            return json( $obj->edit($param) );
        }else{
            $id = input('param.id');
            $this->assign('data',$obj->getOneData($id));
            return $this->fetch();
        }
    }

    //删除
    public function delete()
    {
        $id = input('param.id');
        $obj = new FaqModel();
		$ret = $obj->del($id);
        return json($ret);
    }


   public function export(&$param)
    {
       if (isset($param['export']) &&  $param['export']==true) {
            $data = Db("faq")->order("id desc")->field("id,customerCode,title,views,language,create_time")->select();
            $customer = Db("customer");
            foreach($data as $k=>$v){
                $data[$k]['language'] = getLanguage( $v['language']-1 );
                $ret = $customer->where('customerCode',$v['customerCode'])->find();
                $data[$k]['customerCode'] = $ret['customerName'];
            }
            
            $title =[ ['FAQ id','Customer name','Question','Views','Language','Create time'] ] ;
            $data = array_merge($title,$data);
            $Event = \think\Loader::controller('admin/Common');
            $Event->export_xls($data,"FAQs.xls");   
            exit();
        }
    }
}
