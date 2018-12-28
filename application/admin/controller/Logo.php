<?php

namespace app\admin\controller;
use app\admin\model\LogoModel;
use think\Db;
use \think\Loader;
class Logo extends Base
{
    //banner list
    public function index() {
        $param = input('param.');
        $size = 10;
        $where = [];
        if (isset($param['searchText']) && !empty($param['searchText'])) {
            //$where['title|desc'] = ['like', '%' . trim($param['searchText']) . '%'];	
        }
        if (isset($param['id']) && !empty($param['id'])) {
            $where['customerCode'] = $param['id'];	
        }
     
        if (isset($param['language']) && !empty($param['language'])) {
            $where['language'] = $param['language'];	
        }
        $obj = new LogoModel();
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


   //Video add
    public function add()
    {
        if(request()->isPost()){
            $param = input('post.');
            $param['create_time'] = Date('Y-m-d H:i:s',time());
            $obj = new LogoModel();
            return json($obj->insert($param));
        } else {
             return $this->fetch();
        }
    }

    //编辑视频 
    public function edit()
    { 
        $obj = new LogoModel();
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
        $obj = new LogoModel();
		
		$ret = $obj->del($id);
		//删除上传的文件
		if($ret['code']==1){ 
			if(file_exists($ret['data']['poster'])){
				@unlink($ret['data']['poster']);
			}
		}
        return json($ret);
    }
}
