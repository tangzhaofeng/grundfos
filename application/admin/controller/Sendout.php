<?php
/**
 * Created by PhpStorm.
 * User: juplus-06
 * Date: 2018/4/2
 * Time: 16:12
 */

namespace app\admin\controller;



use app\admin\model\AutoreplyModel;
use app\admin\model\BuModel;

class Sendout extends Base
{

    public function index()
    {
        $obj = new BuModel();
        $bu_list = collection($obj->all())->toArray();

        $this->assign('bu_list',$bu_list);
        return $this->fetch();
    }

    public function index1()
    {

        $param = input('param.');

        $size = 10;
        $where = [];

        $obj = new AutoreplyModel();
        $bu = new BuModel();

        $where['bu_id'] = $param['bu_id'];

        $selectResult = $obj->getBuBywhere($where, $size);

        $return['total'] = $obj->getAllUsers($where);  //总数据

        //得到bu名字
        foreach ($selectResult as $k=>$v){
            $arr = explode(',',$v['bu_id']);
            $bu_name = array();
            foreach ($arr as $key=>$value){
                $bu_name[] = $bu->where('id',$value)->value('bu_name');
            }
            $selectResult[$k]['bu_name'] = implode(',',$bu_name);
        }

        $return['rows'] = $selectResult;

        $return['page'] = $selectResult->render();

        $this->assign('return',$return);
        return $this->fetch();
    }

    public function edit()
    {
        $bu = new BuModel();
        $obj = new AutoreplyModel();

        if(request()->isPost()){
            $param = input('post.');
//            dump($param);exit();
            $param['bu_id'] = implode(',',$param['bu_id']);
            return json($obj->edit($param));
        }else{
            $id = input('param.id');
            $send = $obj->where('id',$id)->find();

            $send['bu_id'] = explode(',',$send['bu_id']);

            $this->assign('send',$send);

            $bu = collection($bu->all())->toArray();
            $this->assign('bu',$bu);
            return $this->fetch();
        }
    }

    public function add()
    {
        $obj = new AutoreplyModel();
        $bu = new BuModel();
        if (request()->isPost()){
            $param = input('post.');
            $param['bu_id'] = implode(',',$param['bu_id']);
            return json($obj->inster($param));
        }else{
            $bu = collection($bu->all())->toArray();
            $this->assign('bu',$bu);
            return $this->fetch();
        }

    }

    /**
     * 删除
     */
    public function delete()
    {
        $id =  $id = input('param.id');
        $obj = new AutoreplyModel();

        return json($obj->del($id));
    }


}