<?php

namespace app\api\controller;
use app\admin\model\IntegralgoodsModel;
use app\admin\model\IntegralorderModel;
use app\admin\model\MemberaddressModel;
use app\admin\model\MemberintegralModel;
use app\admin\model\MemberModel;
use think\cache\driver\Redis;
use think\Controller;
use think\Db;


class Integral extends Controller
{
    private $config;
    private $member_info;

    private $integraltype = [
      '1' => '注册会员',
      '2' => '报名活动',
      '3' => '活动签到',
      '4' => '邀请好友',
      '5' => '收藏样本',
      '6' => '分享赢取',
      '7' => '观看直播',
      '8' => '观看视频'
    ];

    function __construct()
    {
        parent::__construct();

       $type = session('type');
       $this->config = session('wxconfig_'.$type);
        $this->member_info = session($this->config['appid']."user_oauth");
    }

    /**
         * 获取积分商城所有商品
     *  return json
     */
    function getGoodsAll() {
        //得到当前本这个号的bu_id
        $bu_id = $this->config['bu_id'];
        $param = $this->request->param();
        $page['pageNow'] = $param['pageNow'];
        $page['pageSize'] = $param['pageSize'];
        //增加条件
        $where['bu_id'] = $bu_id;
        //获取数据
        $model = new IntegralgoodsModel();
        $selectResult = $model->listByWhere($where, $page);
        return json($selectResult);
    }


    /**
     * 获取商品详情 参数：id
     *  return json
     */
    function getGoodsById() {
        //得到当前商品id
        $id = $this->request->param('id');
        //获取数据
        $model = new IntegralgoodsModel();
        $selectResult = $model->getOneData($id);
        return json($selectResult);
    }


    /**
     * 获取用户积分消费记录
     *  return json
     */
    function getIntegralRecord() {
        //得到参数
        $param = $this->request->param();
        //操作类型 1-获取 2-兑换 0全部',
        $action = $param['action'];
        $page['pageNow'] = $param['pageNow'];
        $page['pageSize'] = $param['pageSize'];
        $where = [];
        //得到用户id
        $where['member_id']  = $this->member_info['id'];
//        print_r($where['member_id']);die;
        if($action){
            $where['action'] = $action;
        }
        //获取数据
        $model = new MemberintegralModel();
        $selectResult = $model->listByWhere($where,$page);
        if ($selectResult['list']){
            foreach ($selectResult['list'] as $k=>$v){
                if (mb_strlen($selectResult['list'][$k]['title']) == 1) {
                    $selectResult['list'][$k]['title'] = $this->integraltype[$v['title']];
                }
            }
        }

        //获取用户信息
        $selectResult['sum_integral'] =  Db('member')->where('id',$where['member_id'])->value('integral');;
        return json($selectResult);
    }


    /**
     * 兑换商品（下单）
     *  return json
     */
    function placeOrder() {
        $param = input('post.');
        $param['member_id'] = $this->member_info['id'];
        if(!$param['member_id']){
            return json(['code'=>0,'msg'=>'当前没有用户登录']);
        }
        //验证
//        $array = ['integral_goods_id','number','username','phone','address'];
//        foreach ($array as $v){
//            if(!isset($param[$v]) || !$param[$v]){
//                return json(['code'=>0,'msg'=>$v.'不能为空']);
//            }
//        }
//        测试数据
//        $param['member_id'] = 758;
//        $param['integral_goods_id'] = 3;
//        $param['number'] = 2;
//        $param['username'] = '测试';
//        $param['phone'] = '18702309136';
//        $param['address'] = "上海市";
        $param['order_code'] = $this->create_order_sn();
        //调用下单模型
        $model = new IntegralorderModel();
        return json($model->placeOrder($param));
    }

//生成唯一订单号
    function create_order_sn(){
        @date_default_timezone_set("PRC");
        //订购日期
        $order_date = date('Y-m-d');
        //订单号码主体（YYYYMMDDHHIISSNNNNNNNN）
        $order_id_main = date('YmdHis') . rand(10000000,99999999);
        //订单号码主体长度
        $order_id_len = strlen($order_id_main);
        $order_id_sum = 0;
        for($i=0; $i<$order_id_len; $i++){
            $order_id_sum += (int)(substr($order_id_main,$i,1));
        }
        //唯一订单号码（YYYYMMDDHHIISSNNNNNNNNCC）
        return $order_id_main . str_pad((100 - $order_id_sum % 100) % 100,2,'0',STR_PAD_LEFT);
    }
}
?>