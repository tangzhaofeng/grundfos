<?php

namespace app\admin\controller;
use app\admin\model\FeedbackModel;
use think\Db;
use \think\Loader;
class Feedback extends Base
{
    //banner list
    public function index() {
        $param = input('param.');
        $size = 10;
        $where = [];
        if (isset($param['searchText']) && !empty($param['searchText'])) {
            $where['userId'] = ['like', '%' . trim($param['searchText']) . '%'];	
        }
        
        $obj = new FeedbackModel();
        $selectResult = $obj->getDataByWhere($where, $size);
        
        $return['searchText'] = !empty($param['searchText'])?trim($param['searchText']):null;
        $return['total'] = $obj->getDataCountByWhere($where);  //总数据
        $return['rows'] = $selectResult;
        $return['page'] = $selectResult->render();
        $this->assign('return',$return);
        return $this->fetch();
    }


   //Video add
    public function add()
    {
        if(request()->isPost()){
            $param = input('post.');
            $param['create_time'] = Date('Y-m-d H:i:s',time());
            $obj = new FeedbackModel();
            return json($obj->insert($param));
        }else{
             return $this->fetch();
        }
    }

    //编辑视频 
    public function edit()
    { 
        $obj = new FeedbackModel();
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
        $obj = new FeedbackModel();
        return json($obj->del($id));
    }
}
