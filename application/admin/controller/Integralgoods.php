<?php

namespace app\admin\controller;
use app\admin\model\AppsecretModel;
use app\admin\model\BuModel;
use app\admin\model\GoodscommodityModel;
use app\admin\model\IntegralgoodsModel;
use app\admin\model\IntegralorderModel;
use app\admin\model\MemberModel;
use think\Db;
use think\Exception;
use \think\Loader;
class Integralgoods extends Base
{
    // list 
    public function index() {
        $param = input('param.');
        $size = 10;
        $where = [];
        if (isset($param['searchText']) && !empty($param['searchText'])) {
            $where['name'] = ['like', '%' . trim($param['searchText']) . '%'];
        }

		if (isset($param['bu_id']) && !empty($param['bu_id'])) {
            $where['bu_id'] = $param['bu_id'];
        }

        $obj = new IntegralgoodsModel();
        $selectResult = $obj->getDataByWhere($where, $size);

        $return['searchText'] = !empty($param['searchText'])?trim($param['searchText']):null;
        $return['total'] = $obj->getDataCountByWhere($where);  //总数据
        $return['rows'] = $selectResult;
        $return['page'] = $selectResult->render();

        $this->assign('return',$return);
		//部门列表
		$department = Db('bu')->order('id desc')->select();
		$this->assign("department",$department);
        $this->assign("url",url('index'));
        if(request()->isAjax()){
			 return json($return);
		}else{
			$type = input("param.type");
            $this->assign("type",$type);
			return isset($type)?$this->fetch("data"):$this->fetch();
		}
   
            
    }

   //Add
    public function add()
    {
        if(request()->isPost()){
            $param = input('post.');
            $param['description'] = $_POST['description'];
            $obj = new IntegralgoodsModel();
			$ret = $obj->insert($param);
            return json($ret);
        }else{
            //得到传过来的bu_id参数
			$group_id = input('param.bu_id');
			//查询所有部门
            $department = Db('bu')->order('id desc')->select();
            $this->assign("department",$department);
            return $this->fetch();
        }
    }

	
    //编辑
    public function edit()
    { 
        $obj = new IntegralgoodsModel();
        if(request()->isPost()){
            $param = input('post.');
            $param['description'] = $_POST['description'];
            $obj = new IntegralgoodsModel();
            return json( $obj->edit($param) );
        }else{
			$id = input('param.id');
            //查询所有部门
            $department = Db('bu')->order('id desc')->select();
			//查询当前商品信息
            $data = $obj->getOneData($id);
            $this->assign("department",$department);
            $this->assign("data",$data);
            return $this->fetch();
        }
    }

    //删除
    public function delete()
    {
        $id = input('param.id');
        $obj = new IntegralgoodsModel();
		$ret = $obj->del($id);
		//删除上传的文件
		if($ret['code']==1){ 
			if(file_exists($ret['data']['cover'])){
				@unlink($ret['data']['cover']);
			}
			if(file_exists($ret['data']['list_img'])){
				@unlink($ret['data']['list_img']);
			}
		}
        return json($ret);
    }

    //积分商城订单
    public function order(){
        $param = input('param.');
        $size = 10;
        $where = [];
        if (isset($param['searchText']) && !empty($param['searchText'])) {
            $where['name'] = ['like', '%' . trim($param['searchText']) . '%'];
        }
        if (isset($param['bu_id']) && !empty($param['bu_id'])) {
            $where['bu_id'] = $param['bu_id'];
        }
        //获取订单信息
        $obj = new IntegralorderModel();

        $type = input("param.type");
        $bu_id = input("param.bu_id");
        $this->assign("bu_id",$bu_id);
        $selectResult = $obj->getDataByWhere($where, $size,$type,$bu_id);

        $return['searchText'] = !empty($param['searchText'])?trim($param['searchText']):null;
        $return['total'] = $obj->getDataCountByWhere($where);  //总数据
        $return['rows'] = $selectResult;
        $return['page'] = $selectResult->render();
        $good = new IntegralgoodsModel();
        $member = new MemberModel();
        foreach ($return['rows'] as $k=>$v){
            $info = $good->where('id',$v['integral_goods_id'])->find();
            $member_info = $member
            ->alias('m')
            ->join('xk_company_type ct', 'm.company_id = ct.id')
            ->join('xk_company_work_type cw', 'm.company_work_type_id = cw.id')
            ->where('m.id',$v['member_id'])
            ->field(['m.*', 'ct.type_name', 'cw.work_type_name'])
            ->find();
            $return['rows'][$k]['is_city'] = $info['is_city'];
            $return['rows'][$k]['member_name'] = $member_info['name'];
            $return['rows'][$k]['headimgurl'] = $member_info['headimgurl'];
            $return['rows'][$k]['num_integral'] = $member_info['integral'];
            $return['rows'][$k]['telnum'] = $member_info['tel'];
            $return['rows'][$k]['type_name'] = $member_info['type_name'];
            $return['rows'][$k]['work_type_name'] = $member_info['work_type_name'];
            // $return['rows'][$k]['member_id'] = $member_info['id'];
            //兑换次数
            $temp = $obj->where('member_id',$v['member_id'])
            ->where('integral_goods_id',$v['integral_goods_id'])
            ->column('number');
            $return['rows'][$k]['getLiftNum'] = array_sum($temp);
        }
        $this->assign('return',$return);
        //部门列表
        $department = Db('bu')->order('id desc')->select();
        $this->assign("department",$department);
        $this->assign("url",url('order'));
        if(request()->isAjax()){
            return json($return);
        }else{
            $this->assign("type",$type);
            return isset($type)?$this->fetch("order"):$this->fetch('index') ;
        }
    }

    /**
     * 发送不需要地址的礼物
     */
    public function send()
    {
       if(request()->isPost()){

       }else{
           //获取虚拟商品
            $commodity = new GoodscommodityModel();
            $commall = collection($commodity->where('is_top',1)->select())->toArray();
            foreach ($commall as $k=>$v){//查找喜欢本父级是否有下一级
                $count = count(collection($commodity->where('pathid',$v['id'])->where('status',1)->select())->toArray());
                $commall[$k]['count'] = $count;
            }

            $this->assign('commall',$commall);
           return $this->fetch();
       }
    }

    public function sendon()
    {
        $appse = new AppsecretModel();
//        try{
            DB::startTrans();
            $id = input('param.id');
            $order = new IntegralorderModel();
            $integral = new GoodscommodityModel();
            $oninfo = $order->where('id',$id)->find();//订单详情
            $ondata = $integral->where('is_top',1)->where('name',$oninfo['name'])->find();//获取父级
            //获取虚拟商品
            $onongood = $integral->where('pathid',$ondata['id'])->where('status',1)->count();
//                dump($onongood);
//                 dump($oninfo['number']);exit();
            if ($onongood < $oninfo['number']){
                return ['msg'=>'商品数量不足'];
            }
            // for ($i=0;$i<$oninfo['number'];$i++){
                $info = $order->where('id',$id)->find();//订单详情
                //根据商品名来发送
                $data = $integral->where('is_top',1)->where('name',$info['name'])->find();//获取父级
                //获取虚拟商品
                $ongoods = $integral->where('pathid',$data['id'])->where('status',1)->limit($oninfo['number'])->order('rand()')->select();
//                if (!empty($ongood)){
//                    return ['msg'=>'商品数量不足'];
//                }
                //拼接返回用户序列码
                $name = "恭喜您，成功兑换{$info['name']}\n\n";
                foreach($ongoods as $key => $good){
                    $num = $key+1;
                    $name .= "卡{$num} : ".$good['name']."\n\n";
                }
                $name .='卡券信息请妥善保管，感谢您的支持~';
                //开始微信发送
                //获取微信配置
                $config = $appse->where('bu_id',$info['bu_id'])->find();

                $message = new Messagesend();
                $param = ['bu_id'=>$info['bu_id'],'content'=>$name];
                $res = $message->oneSendText($info['member_id'],$param,$config);
                if ($res['errcode'] == 0){
                    foreach($ongoods as $key => $good){
                        $integral->where('id', $good['id'])->update(['status'=>2]);
                        
                    }
                    $order->where('id', $id)->update(['status'=>2]);
                    $order->commit();
                    $integral->commit();

                    //记录发送时间
                    $tyqorder = new IntegralorderModel;
                    $tyqorder->where('id', $id)->update(['send_time' => time()]);
                    return json(['code' => 1, 'data' => '', 'msg' => '编辑成功']);
                }else{
                    return json(['code' => 0, 'data' => '', 'msg' => '发送失败,错误信息:'.json_encode($res)]);
                }
                
            // }

//        }catch (Exception $e){
//            DB::rollback();
//            return json(['code' => 0, 'data' => '', 'msg' => '发送失败1']);
//        }
    }

    /**
     * 发货
     */
    public function fahuo()
    {
        $good = new IntegralgoodsModel();
        $order = new IntegralorderModel();
        if (request()->isPost()){
            $param = input('post.');
            DB::startTrans();
            //查询出商品id
            $order_info = $order->where('id',$param['id'])->find();
            $good_info = $good->where('id',$order_info['integral_goods_id'])->find();
            //先减去
            if ($good_info['number'] < 0){
                return ['msg'=>'商品数量不足'];
            }
            $newsum = $good_info['number'] - 1;
            $result = $good->edit(['number'=>$newsum,'id'=>$good_info['id']]);
            if ($result['code'] == 1){
                $result = $order->edit(['status'=>2,'content'=>$param['content'],'id'=>$param['id']]);
                DB::commit();
                return json($result);
            }else{
                DB::rollback();
            }
        }else{
            $param = input('param.');
            $this->assign('id',$param['id']);
            return $this->fetch();
        }
    }

    /**
     * 虚拟商品
     */
    public function commodity()
    {
        $param = input('param.');
        $size = 10;
        $where = [];
        if (isset($param['searchText']) && !empty($param['searchText'])) {
            $where['name'] = ['like', '%' . trim($param['searchText']) . '%'];
        }

        if (isset($param['bu_id']) && !empty($param['bu_id'])) {
            $where['bu_id'] = $param['bu_id'];
        }

        $obj = new GoodscommodityModel();
        $selectResult = $obj->getDataByWhere($where, $size);

        $return['searchText'] = !empty($param['searchText'])?trim($param['searchText']):null;
        $return['total'] = $obj->getDataCountByWhere($where);  //总数据
        $return['rows'] = $selectResult;
        $return['page'] = $selectResult->render();

        $this->assign('return',$return);
        //部门列表
        $department = Db('bu')->order('id desc')->select();
        $this->assign("url",url('commodity'));
        $this->assign("department",$department);
        $type = input("param.type");
        $this->assign("type",$type);
        return isset($type)?$this->fetch("commoditygoods/commoditylist"):$this->fetch("commoditygoods/index") ;
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
     * 导出excel
     */
    public function excelout(){
        $bu_id = (int)input('param.bu_id');
        /**
         * 导出的数据库表
         */
        $order = new IntegralorderModel();
        $lastMonth = mktime(date('H'),date('i'),date('s'),date('m')-1,date('d'),date('Y'));
        $originData = $order->where("create_time>=".$lastMonth." AND bu_id=".$bu_id)->select();
        
        /**
         * excel帮助函数
         */
        $comm = new Common();
        $data = [];
        $data[] = [(date('m')-1).'月报表',null,null,null,null,null,null,null,null,null,null];
        $data[] = ['手机','用户名','订单号','数量','商品名称','积分','收货人','电话','地址','是否已发货','详情'];
        $member = new MemberModel();
        foreach($originData as $val){
            $val['status'] = $val['status'] == 1 ? '否' : '是';
            $member_info = $member->where('id',$val['member_id'])->find();
            $tel = Db::table('xk_member')->where('id', $val['member_id'])->value('tel');
            $data[] = [
                $tel,$member_info['name'],$val['order_code'],$val['number'],$val['name'],
                $val['integral'] * $val['number'],$val->username,$val->phone,$val->address,
                $val->status,$val->content
            ];
        }

        $comm->export_xls($data);
    }


    /**
     * 删除订单
     */
    public function deleteOrder() {
        if (request()->isPost()){
            $param = input('param.');
            Db::table('xk_integral_order')->where('id', $param['id'])->delete();
            return json(['code' => 1]);
        }
    }
}
