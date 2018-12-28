<?php
/**
 * Created by PhpStorm.
 * User: juplus-06
 * Date: 2018/4/2
 * Time: 16:12
 */

namespace app\admin\controller;



use app\admin\model\ActivityModel;
use app\admin\model\AppsecretModel;
use app\admin\model\BuModel;
use app\admin\model\LabelModel;
use app\admin\model\QrcodeModel;
use app\index\controller\Wxqrcode;
use app\index\controller\Index;

class Label extends Base
{
    public function index()
    {
        $param = input('param.');

        $size = 10;
        $where = [];

        $obj = new LabelModel();

        $selectResult = $obj->getBuBywhere($where, $size);

        $return['total'] = $obj->getAllUsers($where);  //总数据

        $return['rows'] = $selectResult;
        $return['page'] = $selectResult->render();
//        dump($return);exit();
        $this->assign('return',$return);
        return $this->fetch();
    }



    public function add()
    {
        $obj = new LabelModel();

        if (request()->isPost()) {
            $param = input('post.');
            $param['creater_time'] = date('Y-m-d H:i',time());
            return json($obj->inster($param));
        } else {

//            $this->assign('work', $work->where('id', $id)->find()->toArray());
            return $this->fetch();
        }
    }


}