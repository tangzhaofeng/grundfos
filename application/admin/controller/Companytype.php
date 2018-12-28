<?php
/**
 * Created by PhpStorm.
 * User: juplus-06
 * Date: 2018/4/2
 * Time: 16:12
 */

namespace app\admin\controller;


use app\admin\model\BuModel;
use app\admin\model\CompanyModel;

class Companytype extends Base
{
    public function index()
    {
        $param = input('param.');

        $size = 10;
        $where = [];

        $obj = new CompanyModel();

        $selectResult = $obj->getBuBywhere($where, $size);

        $return['total'] = $obj->getAllUsers($where);  //总数据

        $return['rows'] = $selectResult;
        $return['page'] = $selectResult->render();

        $this->assign('return',$return);
        return $this->fetch();
    }

    public function edit()
    {
        $bu = new BuModel();
        $type = new CompanyModel();
        $id = input('param.id');

        if(request()->isPost()){
            $param = input('post.');
            return json($type->edit($param));
        }else{
            $bulist = collection($bu->all())->toArray();
            $this->assign('bulist',$bulist);

            $type_data = $type->where('id',$id)->find()->toArray();
            $this->assign('type_data',$type_data);
            return $this->fetch();
        }
    }

    public function add()
    {
        $bu = new BuModel();
        $type = new CompanyModel();

        if (request()->isPost()){
            $param = input('post.');
            return json($type->inster($param));
        }else{
            $bulist = collection($bu->all())->toArray();

            $this->assign('bulist',$bulist);

            return $this->fetch();
        }
    }


}