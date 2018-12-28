<?php

namespace app\admin\controller;
use app\admin\model\BuModel;
use app\admin\model\GoodscommodityModel;
use app\admin\model\IntegralgoodsModel;
use app\admin\model\IntegralorderModel;
use think\Db;
use \think\Loader;
class commodity extends Base
{
    /**
     * 虚拟商品
     */
    public function index()
    {
        $param = input('param.');
        $size = 10;
        $where = [];
        if (isset($param['searchText']) && !empty($param['searchText'])) {
            $where['name'] = ['like', '%' . trim($param['searchText']) . '%'];
        }

        if (isset($param['commodityid']) && !empty($param['commodityid'])) {
            $where['pathid'] = $param['commodityid'];
        }

        $obj = new GoodscommodityModel();
       ;
        $commodityid = input("param.commodityid");
        //echo $commodityid;
        $selectResult = $obj->getDataByWhere($where, $size,$commodityid);

        $return['searchText'] = !empty($param['searchText'])?trim($param['searchText']):null;
        $return['total'] = $obj->getDataCountByWhere($where);  //总数据
        $return['rows'] = $selectResult;
        $return['page'] = $selectResult->render();

        $this->assign('return',$return);
        //部门列表
        $department = $obj->where('is_top',1)->order('id desc')->select();
        $this->assign("url",url('index'));
        $this->assign("department",$department);
        $this->assign("commodityid",$commodityid);
        return isset($commodityid) ? $this->fetch("commoditylist"):$this->fetch() ;
        //return $this->fetch();
    }

    /**
     * 添加虚拟商品主级
     */
    public function commodityAdd()
    {
        if(request()->isPost()){
            $param = input('post.');
            $param['is_top'] = 1;
            $param['creater_time'] = date('Y-m-d h:i:s',time());

            $obj = new GoodscommodityModel();
            $ret = $obj->insert($param);
            return json($ret);
        }else{
            //得到传过来的bu_id参数
            return $this->fetch('commodityAdd');
        }
    }

    /**
     * 添加虚拟商品
     */
    public function commodityPathAdd()
    {
        if(request()->isPost()){
            $param = input('post.');
            $param['is_top'] = 2;
            $param['status'] = 1;
            $param['creater_time'] = date('Y-m-d h:i:s',time());

            $obj = new GoodscommodityModel();
            $ret = $obj->insert($param);
            return json($ret);
        }else{
            //得到传过来的bu_id参数
            $this->assign('commodityid',input('param.commodityid'));
            return $this->fetch('commoditypathadd');
        }
    }

    /**
     * 删除
     */
    public function delete()
    {
        $id =  $id = input('param.id');
        $obj = new GoodscommodityModel();
        $info = $obj->del($id);
        return json($info);
    }

    /**
     * excel导入
     */
    public function exceladd()
    {
        $param = input('param.');
        if(request()->isPost()){
            $param = input('post.');
            $comm = new Common();

            $info = $comm->import_excel($param['excel']);
//            dump($info);exit();
            unset($param['excel']);
            $i= 1;
            foreach ($info as $k=>$v){
                $obj = new GoodscommodityModel();
                $param['is_top'] = 2;
                $param['status'] = 1;
                $param['creater_time'] = date('Y-m-d h:i:s',time());
                $param['name'] = $v[0];
                $ret = $obj->insert($param);
                $i++;
            }

            return json($ret);
        }else{
            //得到传过来的bu_id参数
            $this->assign('commodityid',input('param.commodityid'));
            return $this->fetch('commodityexceladd');
        }
    }
    
    //批量删除
    public function deleteBykind() {
        $param = input('param.');
        Db::table("xk_integral_commodity")->where('pathid',$param['commodityid'])->where('status', 2)->where('is_top', 2)->delete();
        echo '<h2>删除成功！</h2>';
    }
}
