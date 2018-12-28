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
use app\admin\model\CompanyworkModel;
use think\Db;

class Companywork extends Base
{
    public function index()
    {
        $param = input('param.');

        $size = 10;
        $where = [];

        $obj = new CompanyworkModel();

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
        $type = new CompanyModel();
        $bu = new BuModel();
        $work = new CompanyworkModel();

        $id = input('param.id');
        if(request()->isPost()){
            $param = input('post.');
            return json($work->edit($param));
        }else{
            $bulist = collection($bu->all())->toArray();

            $typelist= collection($type->all())->toArray();

            $list = array();
            $i = 0;
            $o = 0;
            foreach ($bulist as $k => $v){
                foreach ($typelist as $key => $val){
                    if ($v['id'] == $val['bu_id']){
                        $list[$o]['bu_name'] = $v['bu_name'];
                        $list[$o]['id'] = $v['id'];
                        $list[$o][$i]['type_name'] = $val['type_name'];
                        $list[$o][$i]['id'] = $val['id'];
                    }else{
                        $list[$o]['bu_name'] = $v['bu_name'];
                        $list[$o]['id'] = $v['id'];
                    }
                    $i++;
                }
                $o++;
            }

            $this->assign('list',$list);

            $this->assign('work',$work->where('id',$id)->find()->toArray());
            return $this->fetch();
        }
    }

    public function add()
    {
        $type = new CompanyModel();
        $bu = new BuModel();
        $work = new CompanyworkModel();
        if (request()->isPost()){
             $param =input('param.');
             return json($work->insert($param));
        }else{

            $bulist = collection($bu->all())->toArray();

            $typelist= collection($type->all())->toArray();

            $list = array();
            $i = 0;
            $o = 0;
            foreach ($bulist as $k => $v){
                foreach ($typelist as $key => $val){
                    if ($v['id'] == $val['bu_id']){
                        $list[$o]['bu_name'] = $v['bu_name'];
                        $list[$o]['id'] = $v['id'];
                        $list[$o][$i]['type_name'] = $val['type_name'];
                        $list[$o][$i]['id'] = $val['id'];
                    }else{
                        $list[$o]['bu_name'] = $v['bu_name'];
                        $list[$o]['id'] = $v['id'];
                    }
                    $i++;
                }
                $o++;
            }
//            dump($list);exit();
            $this->assign('list',$list);
            return $this->fetch();
        }
    }


}