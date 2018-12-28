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
use app\index\controller\Group;
use app\index\controller\Wxqrcode;
use app\index\controller\Index;

class Grouping extends Base
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
        $where['bu_id'] = $param['bu_id'];

        $obj = new GroupingModel();

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
        $obj = new GroupingModel();
        $app = new AppsecretModel();

        if (request()->isPost()) {
            $param = input('post.');
            $param['creater_time'] = date('Y-m-d H:i',time());

            $relout = $obj->inster($param);
            //如果成功插入则进行微信添加分组
            if($relout['code'] == 1){
                //获取微信信息
                $config = $app->where('bu_id',$param['bu_id'])->find();

                //添加分组
                $group = new Group();
                $info = $group->createGroup($config,$param['grouping_name']);

                $obj->save(['wx_id'=>$info['group']["id"]],$relout['id']);

            }

            return json($relout);
        } else {
            $bu = new BuModel();
            $bu_list = collection($bu->all())->toArray();

            $this->assign('bu_list',$bu_list);
            return $this->fetch();
        }
    }

      /**
     * 删除
     */
    public function delete()
    {
        $id =  $id = input('param.id');
        $obj = new GroupingModel();
        $app = new AppsecretModel();
        $qrcode = new DataqrcodeModel();

        $data = $obj->where('id',$id)->find();

        //获取微信配置
        $config= $app->where('id',$data['bu_id'])->find();

        //查询是否有带参数二维码使用本分组
        $info = $qrcode->where('grouping_id',$id)->find();
        if ($info['id']){
            return json(['code' => 0, 'data' => '', 'msg' => '无法删除,还有使用本分组的二维码']);
        }

        $result = $obj->del($id);

        //如果删除成功则一起删除公众号中的分组
        if ($result['code'] == 1){
            $group = new Group();
            $group->deleteGroup($config,$data['wx_id']);
        }
        
        return json($result);
    }


}