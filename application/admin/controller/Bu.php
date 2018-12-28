<?php
/**
 * Created by PhpStorm.
 * User: juplus-06
 * Date: 2018/4/2
 * Time: 16:12
 */

namespace app\admin\controller;



use app\admin\model\BuModel;

class Bu extends Base
{
    public function index()
    {
        $param = input('param.');

        $size = 10;
        $where = [];

        $obj = new BuModel();

        $selectResult = $obj->getBuBywhere($where, $size);

        $return['total'] = $obj->getAllUsers($where);  //总数据

        $return['rows'] = $selectResult;
        $return['page'] = $selectResult->render();
//        dump($return);exit();
        $this->assign('return',$return);
        return $this->fetch();
    }

    public function edit()
    {
        $obj = new BuModel();
        $id = input('param.id');
//        dump($obj->where('id',$id)->find());exit();
        if(request()->isPost()){
            $param = input('post.');
            return json($obj->edit($param));
        }else{
            $id = input('param.id');
            $this->assign('courseCode',$obj->where('id',$id)->find());
            return $this->fetch();
        }
    }


}