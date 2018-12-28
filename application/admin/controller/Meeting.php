<?php
/**
 * Created by PhpStorm.
 * User: juplus-06
 * Date: 2018/4/2
 * Time: 16:12
 */

namespace app\admin\controller;



use app\admin\model\ActivityModel;
use app\admin\model\ActivityshareModel;
use app\admin\model\BuModel;
use app\admin\model\MeetingModel;

class Meeting extends Base
{
    /**
     * 列表页
     */
    public function index()
    {
        $param = input('param.');

        $size = 10;
        $where = [];

        $obj = new MeetingModel();
        $bu = new BuModel();

        $list = array();
        $selectResult = $obj->getBuBywhere($where, $size);


        $bu = collection($bu->all())->toArray();

        $return['total'] = $obj->getAllUsers($where);  //总数据

        $return['rows'] = $selectResult;

        $return['page'] = $selectResult->render();

        $this->assign('bu',$bu);
        $this->assign('return',$return);
        return $this->fetch();
    }

    /**
     * 修改
     */
    public function edit()
    {
        $obj = new MeetingModel();
        $bu = new BuModel();
        $id = input('param.id');

        if(request()->isPost()){
            $param = input('post.');
            $list = array();
            foreach ($param as $k => $v){
                if (is_array($v)){
                    $list[$k] = implode(",",$v);
                }else{
                    $list[$k] = $v;
                }
            }
//            dump($list);exit();
            $info = $obj->edit($list);

            return json($info);
        }else{
            $bulist = collection($bu->all())->toArray();
            $this->assign('bulist',$bulist);
            $this->assign('meeting',$obj->where('id',$id)->find());
            return $this->fetch();
        }
    }

    /**
     * 新增
     */
    public function add()
    {
        $obj = new MeetingModel();
        $bu = new BuModel();
        if (request()->isPost()){
            $param = input('post.');
            $list = array();
            foreach ($param as $k => $v){
                if (is_array($v)){
                    $list[$k] = implode(",",$v);
                }else{
                    $list[$k] = $v;
                }
            }
            $info = $obj->inster($list);
            return json($info);
        }else{
            $bulist = collection($bu->all())->toArray();
            $this->assign('bulist',$bulist);
            return $this->fetch();
        }
    }

    /**
     * 删除
     */
    public function delete()
    {
        $id =  $id = input('param.id');
        $obj = new MeetingModel();

        return json($obj->del($id));
    }

    /**
     * 查看详情
     */
    public function info()
    {

        $bu = new BuModel();
        $activity = new MeetingModel();
        $id = input('param.id');

        $param = input('param.');

        $size = 10;
        $where = ['activity_id'=>$id];

        $obj = new ActivityshareModel();
        $selectResult = $obj->getBuBywhere($where, $size);
        $return['total'] = $obj->getAllUsers($where);  //总数据
        $return['rows'] = $selectResult;
        $return['page'] = $selectResult->render();

//        dump($return);exit();
        $bulist = collection($bu->all())->toArray();

        $this->assign('return',$return);
        $this->assign('bulist',$bulist);
        $this->assign('activity',$activity->where('id',$id)->find());



        return $this->fetch();
    }


}