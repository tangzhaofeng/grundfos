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
use app\admin\model\DataqrcodeModel;
use app\admin\model\GroupingModel;
use app\admin\model\LabelModel;
use app\admin\model\QrcodeModel;
use app\index\controller\Wxqrcode;
use app\index\controller\Index;

class Dataqrcode extends Base
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

        if(isset($param['bu_id'])){
            $where['bu_id'] = $param['bu_id'];
        }

        $obj = new DataqrcodeModel();

        $selectResult = $obj->getBuBywhere($where, $size);

        $return['total'] = $obj->getAllUsers($where);  //总数据

        $return['rows'] = $selectResult;
        $return['page'] = $selectResult->render();

        $this->assign('bu_id',$param['bu_id']);
        $this->assign('return',$return);
        return $this->fetch();
    }

    //活动的带参数二维码
    public function add()
    {
        $activot = new ActivityModel();
        $obj = new DataqrcodeModel();

        if (request()->isPost()) {
            $param = input('post.');


            //得到bu_id获取appid与appsecret加载得到带参数二维码
            $app_secret = new AppsecretModel();
            $wx_config = $app_secret->where('bu_id',$param['bu_id'])->find();

            //得到带参数二维码
            $param['creater_time'] = date('Y-m-d H:i:s',time());
            $param['push'] = $_POST['push'];
            $info = $obj->inster($param);

            if ($info['code'] == -1){
                return json($info);
            }

            $qrcode = new Index();
            $qrcode_img = $qrcode->qrCode($wx_config['appid'],$wx_config['appsecret'],$wx_config['type'],'2_'.$info['id']);
//          $param['qrcode_img'] = $qrcode_img;

            $obj->where('id',$info['id'])->Update(['qrcode_img'=>$qrcode_img]);
            return json($info);
        } else {
            $label = new LabelModel();
            $grouping = new GroupingModel();
            $bu = new BuModel();

//            $label_list = collection($label->all())->toArray();
            $grouping_list = collection($grouping->all(['bu_id'=>input('param.bu_id')]))->toArray();
            $bu_list = $bu->where('id',input('param.bu_id'))->find();

//            $this->assign('label_list', $label_list);
            $this->assign('grouping_list',$grouping_list);
            $this->assign('bu_list',$bu_list);
            $this->assign('bu_id',input('param.bu_id'));

            return $this->fetch();
        }
    }

    /**
     * 删除
     */
    public function delete()
    {
        $id =  $id = input('param.id');
        $obj = new DataqrcodeModel();
   
        $result = $obj->del($id);
        return json($result);
    }
}