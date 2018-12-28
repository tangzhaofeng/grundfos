<?php

namespace app\admin\model;

use think\Db;
use think\Exception;
use think\exception\ErrorException;
use think\Model;
use app\admin\model\IntegralgoodsModel;

class IntegralorderModel extends Model
{
    protected  $table = 'xk_integral_order';

    /**
     * 根据条件查询订单信息
     * @param $id
     */
    public function getDataByWhere($where, $size,$sence = null,$bu_id = null) {
        if( !request()->isAjax())
            return $this->where($where)->order('id desc')->paginate($size,false,[
                'query' => ['type' => $sence,'bu_id' => $bu_id]
            ]);
        else
            //ajax分页请求
            $roles = input("post.roles");
        return $this->where($where)
            ->order('id desc')->paginate(10,false,[
                'type'     => 'Bootstrap',
                'var_page' => 'page',
                'page' =>input('param.page'),
                'path'=>'javascript:AjaxPage([PAGE]);',
                'query' => ['type' => $sence]
            ]);
    }

    /**
     * 根据搜索条件获取所有的用户数量
     * @param $where
     */
    public function getDataCountByWhere($where) {
        return $this->where($where)->count();
    }
     /**
     * 下单
     * @param $id
     */
    public function placeOrder($param) {
        try{
            $redis = new \Redis;
            $redis->connect('127.0.0.1', 6379);
            $redis->auth("redis");
            $key = 'glf_all_project'.$param['member_id'];
            $dayKey = 'glf_'.$param['member_id']."__".$param['integral_goods_id'];
            //每人每天，每件商品只能购买一次，判断,另外一个月只能换5次
            $buy = $redis->get($key);
            $dayBuy = $redis->get($dayKey);
            if ($buy && $buy > 4) {
                throw  new \Exception('一个月只能兑换五次商品');
            }
            if ($dayBuy) {
                throw  new \Exception('每天只能兑换一次该商品');
            }
            //一次只能购买一件，写死！
            $param['number'] = 1;
            
            //开启事务
            Db::startTrans();
            //积分商品信息
            $goods =  Db('integral_goods')->where('id',$param['integral_goods_id'])->find();
            //判断库存，如果不够则返回信息
            if($goods['number'] <= $param['number']){
                throw  new \PDOException('库存不足');
            }
            //消费积分
            $integral = $goods['integral']*$param['number'];
            //查询当前用户信息，判断积分是否足够购买
            $member = Db('member')->where('id',$param['member_id'])->find();
            if(!($member['integral'] >= $integral)){
                throw  new \PDOException('积分不足');
            }
            //完善订单信息
            $param['integral'] = $goods['integral'];
            $param['name'] = $goods['name'];
            $param['cover'] = $goods['cover'];
            $param['bu_id'] = $goods['bu_id'];
            $param['create_time'] = time();
            //订单入库
            $this->save($param);
            //减少用户积分
            Db('member')->where('id',$param['member_id'])->setDec('integral',$integral);
            //增加积分记录
            $data['now'] = $member['integral']-$integral;
            $data['integral'] =  $integral;
            $data['action'] = 2;
            $data['title'] = $goods['name'];
            $data['member_id'] = $param['member_id'];
            $data['create_time'] = date('Y-m-d H:i:s',time());
            Db('member_integral')->insert($data);
            
            /**
             * 修改前端库存
            */
            $intel_goods = new IntegralgoodsModel;
            $intel_goods->where('id', $param['integral_goods_id'])->setDec('number',$param['number']);

            /**
             * 添加redis数据，每人每天，每件商品只能购买一次
             */
            if (!$buy){
                $buy = 1;
            } else {
                $buy++;
            }
            $time = strtotime(date("Y-m",time()).'+1 month') - time();  //今月剩余秒数
            $redis->set($key, $buy);
            $redis->expire($key, $time);
            /**
             * 每次一种商品只能换一次
             */
            $dayTime = strtotime(date("Y-m-d",time()).'+1 day') - time();  //今日剩余秒数
            $redis->set($dayKey, 1);
            $redis->expire($dayKey, $dayTime);

            //提交事务
            Db::commit();
            return ['code' => 1, 'data' => '', 'msg' => '下单成功', 'id'=>$this->id ];
        }catch( \Exception $e){
            //提交失败 回滚事务 返回错误信息
            Db::rollback();
            return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

    /**
     * 编辑角色信息
     * @param $param
     */
    public function edit($param)
    {
        try{
            $data = [];
            if (isset($param['content'])){
                $data['content'] = $param['content'];
            }
            $data['status'] = $param['status'];
            $result =  $this->save($data, ['id' => $param['id']]);

            if(false === $result){
                // 验证失败 输出错误信息
                return ['code' => 0, 'data' => '', 'msg' => $this->getError()];
            }else{

                return ['code' => 1, 'data' => '', 'msg' => '编辑成功'];
            }
        }catch( PDOException $e){
            return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
        }
    }


}