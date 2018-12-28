<?php

namespace app\admin\controller;
use app\admin\model\WebinaruserModel;

use think\Db;
class Webinaruser extends Base
{
    /****
    *==========================
    * @2017-12-2 2215601330@qq.com
    */
    public function index() {
        $param = input('param.');
        //导出
        $this->export($param);
        //show list
        $size = 10;
        $where = [];
        if (isset($param['searchText']) && !empty($param['searchText'])) {
            $where['name|company|position|phone|email'] = ['like', '%' . trim($param['searchText']) . '%'];	
        }
        if (isset($param['id']) && !empty($param['id'])) {
            $where['webinarId'] = $param['id'];	
        }

        $obj = new WebinaruserModel();
        $selectResult = $obj->getDataByWhere($where, $size);
        
        $return['searchText'] = !empty($param['searchText'])?trim($param['searchText']):null;
        $return['total'] = $obj->getDataCountByWhere($where);  //总数据
        $return['rows'] = $selectResult;
        $return['page'] = $selectResult->render();
        $this->assign('return',$return);

        $this->assign('webinar',Db("webinars")->where("id",$param['id'])->find());
        return $this->fetch();
    }

    //删除
    public function delete() {
        $id = input('param.id');
        $obj = new WebinaruserModel();
        $ret = $obj->del($id);
        Db("view")->where('create_time',Date("Y-m-d",strtotime($ret['ret']['createTime'])))->setDec('webinar_baoming'); //更新pv 没刷+1
        return json($ret );
    }

    public function export(&$param)
    {
       if (isset($param['export']) &&  $param['export']==true) {
            $data = Db("webinar_user")->where("webinarId",$param['webinarId'])->order("id desc")->field("id,webinarId,name,company,position,email,phone,createTime")->select();
            $webinar = Db("webinars")->where('id', $param['webinarId'] )->find();
            foreach($data as $k=>$v) {
                $data[$k]['webinarId'] = $webinar['title'];
            }
            $title =[ ['Id','Webinar title','Name','Company','Position','Email','Phone','Create time'] ] ;
            $data = array_merge($title,$data);
            $Event = \think\Loader::controller('admin/Common');
            $Event->export_xls($data,"Webinar ". $webinar['title']." user.xls");   
            exit();
        }
    }
}
