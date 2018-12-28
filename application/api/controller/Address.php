<?php

namespace app\api\controller;
use app\admin\model\IntegralgoodsModel;
use app\admin\model\IntegralorderModel;
use app\admin\model\MemberaddressModel;
use app\admin\model\MemberintegralModel;
use think\cache\driver\Redis;
use think\Controller;
use think\Db;


class Address extends Controller
{
    private $config;
    private $member_info;
    function __construct()
    {
        parent::__construct();

       $type = session('type');
       $this->config = session('wxconfig_'.$type);
        $this->member_info = session($this->config['appid']."user_oauth");
    }

    /**
     * 获取用户默认收货地址
     *  return json
     */
    function getMemberAddressDefault() {
        //得到当前用户id
        $member = $this->member_info['id'];
        //加入status条件 status  1:默认 2：不默认 0：全部
        //获取数据
        $model = new MemberaddressModel();
        $selectResult = $model->getMemberAddressDefault($member);
        return json($selectResult);
    }
    /**
     * 获取用户所有收货地址
     *  return json
     */
    function getMemberAddress(){
        $member = $this->member_info['id'];
        $where = [];
        $where['member_id'] = $member;
        //获取数据
        $model = new MemberaddressModel();
        $selectResult = $model->listByWhere($where,[],'status asc');
        return json($selectResult);
    }

    /**
     * 增加或者修改收货地址 参数：username:收货人姓名 phone:收货人电话 city:收货人城市 address:收货人详细地址
     *  return json
     */
    function addOrEdit(){
        $member = $this->member_info['id'];
        $param = input('post.');
        $param['member_id'] = $member;
        //验证
        $array = ['username','phone','city','address'];

        //测试数据
//        $param['username'] = '测试';
//        $param['phone'] = '18702309136';
//        $param['city'] =  '上海市闵行区';
//        $param['address'] =  '602';
//        $param['member_id'] = 1;
        //验证
        foreach ($array as $v){
            if(!isset($param[$v]) || !$param[$v]){
                return json(['code'=>0,'msg'=>$v.'不能为空']);
            }
        }
        $obj = new MemberaddressModel();
        if($param['id']){
            $ret = $obj->edit($param);
        }else{
            $ret = $obj->insert($param);
        }
        return json($ret);
    }

    /**
     * 修改收货地址 参数：id：地址id  username:收货人姓名 phone:收货人电话 city:收货人城市 address:收货人详细地址
     *  return json
     */
    function edit(){
        $param = input('post.');
        //验证
        $array = ['username','phone','city','address'];

        //测试数据
//        $param['username'] = '测试222222';
//        $param['phone'] = '18702309136';
//        $param['city'] =  '上海市闵行区';
//        $param['address'] =  '602';
//        $param['id'] = 1;
        //验证
        foreach ($array as $v){
            if(!isset($param[$v]) || !$param[$v]){
                return json(['code'=>0,'msg'=>$v.'不能为空']);
            }
        }
        $obj = new MemberaddressModel();
        $ret = $obj->edit($param);
        return json($ret);
    }

    /**
     * 删除收货地址 参数：id：
     *  return json
     */
    function del(){
        $id = input('param.id');
        $obj = new MemberaddressModel();
        $ret = $obj->del($id);
        return json($ret);
    }


    /**
     * 设置默认收货地址 参数id
     *  return json
     */
    function setDefault(){
        $member = $this->member_info['id'];
        $id = input('param.id');
        $obj = new MemberaddressModel();
        $ret = $obj->setDefault($id,$member);
        return json($ret);
    }

}
?>