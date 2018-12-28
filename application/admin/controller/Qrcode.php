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
use app\admin\model\QrcodeModel;
use app\index\controller\Wxqrcode;
use app\index\controller\Index;

class Qrcode extends Base
{
    public function index()
    {
        $param = input('param.');

        $size = 10;
        $where = [];

        $obj = new QrcodeModel();

        $selectResult = $obj->getBuBywhere($where, $size);

        $return['total'] = $obj->getAllUsers($where);  //总数据

        $return['rows'] = $selectResult;
        $return['page'] = $selectResult->render();
//        dump($return);exit();
        $this->assign('return',$return);
        return $this->fetch();
    }

    //活动的带参数二维码
    public function add()
    {
        $activot = new ActivityModel();
        $bu = new BuModel();
        $obj = new QrcodeModel();

        if (request()->isPost()) {
            $param = input('post.');
            //根据活动id得到buid
            $bu_id = $activot->where('id',$param['activity_id'])->value('bu_id');

            //得到bu_id获取appid与appsecret加载得到带参数二维码
            $app_secret = new AppsecretModel();
            $wx_config = $app_secret->where('bu_id',$bu_id)->find();

            //得到带参数二维码
            $qrcode = new Index();

            $qrcode_img = $qrcode->qrCode($wx_config['appid'],$wx_config['appsecret'],$wx_config['type'],'1_'.$param['activity_id']);

            $param['creater_time'] = date('Y-m-d H:i:s',time());
            $param['qrcode_img'] = $qrcode_img;
            $param['bu_id'] = $bu_id;
            // dump($param);exit();
            return json($obj->inster($param));
        } else {
            $activity_list = $activot->getAll();
            $bu_list = collection($bu->all())->toArray();

            $list = array();
            $i = 0;
            $o = 0;
            foreach ($bu_list as $k => $v) {
                foreach ($activity_list as $key => $val) {
                    if ($v['id'] == $val['bu_id']) {
                        $list[$o]['bu_name'] = $val['bu_name'];
                        $list[$o]['bu_id'] = $val['bu_id'];
                        $list[$o][$i]['activity_name'] = $val['title'];
                        $list[$o][$i]['activity_id'] = $val['activity_id'];
                    }
                    $i++;
                }
                $o++;
            }
//            dump($list);
//            exit();
            $this->assign('list', $list);

//            $this->assign('work', $work->where('id', $id)->find()->toArray());
            return $this->fetch();
        }
    }


}