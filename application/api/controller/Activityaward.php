<?php
/**
 * Created by PhpStorm.
 * User: juplus-06
 * Date: 2018/4/9
 * Time: 16:05
 */
namespace app\api\controller;
use think\Db;
use think\Cache;

class Activityaward extends Base
{

    public function __construct()
    {
        parent::__construct();
    }

	/*
	*  获取参与抽奖的用户
	*/
	function getActiveUser() {
	
		$ret = Db("active_award_play_user")->where(['status'=>1,'is_got'=>1])->field("name,phone,award")->select();
		// dump($ret);exit();
		
		$data = [];
		foreach($ret as $k=>$v){
			if($v['award']!=0){
				$data[$v['award']][] = $v;
			}
		}

		$play_user = Db("active_award_play_user")->where(['status'=>0])->select();
		// dump($play_user);exit();
		return json( ['user'=>$play_user,'gotAward'=>$data] );
	}
	
	
	/*
	* 确认抽中奖品
	* return  hson
	*/
	function confirmGotAward() {
		$id = input("post.id");
		$is_got = input("post.is_got");
		$obj = Db("active_award_play_user");
		$ret = $obj->where("id",$id)->find();
		if(!empty($ret)){
			$obj->where("id",$id)->update(['is_got'=>$is_got]);
			return json(["status"=>"success",'msg'=>"操作成功"]);
		} else {
			return json(["status"=>"error",'msg'=>"领取失败"]);
		}
	}
	
	
	function getRoundAwardUser(){
		$award = input("post.type");
		$ret = Db()->query("select * from xk_active_award_play_user where status=0 order by rand() LIMIT 1");
		//处理抽中的用户
		if(!empty($ret)){
			Db("active_award_play_user")->where("id",$ret[0]['id'])->update(['award'=>$award,'status'=>1]); //设置中奖状态
			return json(['status'=>"success",'user'=>$ret,'msg'=>'ok']);
		} else {
			return json(['status'=>"error",'user'=>[],'msg'=>'用户已经被抽完了']);
		}
	}
	
	/*
	function getRoundAwardUser(){
		$award = input("post.type");
		
		//特别情况(预订的用户)
		if($award==11){
			$result = Db("active_award_play_user")->where(['name'=>"叶伟丽"])->find();
			if($result['status']!=1){
				Db("active_award_play_user")->where("id",$result['id'])->update(['award'=>$award,'status'=>1]); //设置中奖状态
				return json(['status'=>"success",'user'=>[$result],'msg'=>'ok']);
			}
			
			$result = Db("active_award_play_user")->where(['name'=>"陆赞扬"])->find();
			if($result['status']!=1){
				Db("active_award_play_user")->where("id",$result['id'])->update(['award'=>$award,'status'=>1]); //设置中奖状态
				return json(['status'=>"success",'user'=>[$result],'msg'=>'ok']);
			}
		}
		
		
		$ret = Db()->query("select * from xk_active_award_play_user where name not in ('叶伟丽','陆赞扬')  and status=0 order by rand() LIMIT 1");
		//处理抽中的用户
		if(!empty($ret)){
			Db("active_award_play_user")->where("id",$ret[0]['id'])->update(['award'=>$award,'status'=>1]); //设置中奖状态
			return json(['status'=>"success",'user'=>$ret,'msg'=>'ok']);
		} else {
			return json(['status'=>"error",'user'=>[],'msg'=>'用户已经被抽完了']);
		}
	}
	
	/*
	* 随机获取抽中奖的用户
	*/
	/*
	function getRoundAwardUser(){
		$ret = $this->getDataFromTable("xk_active_award_play_user",0,1);
		//处理抽中的用户
		if(!empty($ret)){
			$obj = Db();
			//获取设置的中奖产品
			$prize_arr = $this->getSetAward([]);
			//根据数组中字段v的设置中奖概率 中奖yes 未中奖NO  
			$result = $this->getRandByArrayField($prize_arr);
			$ret[0]["award"] = $result["yes"];
			//处理你的其他逻辑
			$this->handAward($ret);
		}	
		return json($ret);
	}
	*/
	
	/*
	* 获取设置的奖品
	* param $where array 根据条件筛选设置的奖品
	* return  array
	*/
	function getSetAward($where) {
		return Db("active_set_award")->where($where)->select();
	}
	
	
	//处理抽中奖的content
	private function handAward($array){
		//修改参与完游戏的用户
		$obj = Db("active_award_play_user");
		foreach($array as $k=>$v){
			//修改改用户中奖
			//$obj->where("id",$v['id'])->update(["award"=>$v['award'],'status'=>1]);
		}
	}
	

	

	
	
	
	
	
	
	
	
	
	//=====================【一下为工具函数】========================
	
	/*
	*高效随机查询（sql优化-order by rand）
	*====返回数据库表中随机n条数据  15万行数据 花费时间 0.014970 秒 === 【测试ok 记：返回存在的数据】
	*param $table 表示要查询的表（需表的全名） 
	*param $status 条件  status 0表示没有抽奖 1表示已经抽奖
	*param $returnNum 随机返回的列数
	*
	*/
	private function getDataFromTable($table,$status=0,$returnNum=1){
		return Db()->query("SELECT * FROM
							`$table` AS t1
							JOIN (
								SELECT
									ROUND(
										RAND() * (
											(SELECT MAX(id) FROM `$table`) - (SELECT MIN(id) FROM `$table`)
										) + (SELECT MIN(id) FROM `$table`)
									) AS id
							) AS t2
							WHERE
								t1.status=0 and t1.id >= t2.id  
							ORDER BY
								t1.id
							LIMIT $returnNum");
	}
	
	
	
	/* 
	 * 奖项数组 
	 * 是一个二维数组，记录了所有本次抽奖的奖项信息， 
	 * 其中id表示中奖等级，prize表示奖品，v表示中奖概率。 
	 * 注意其中的v必须为整数，你可以将对应的 奖项的v设置成0，即意味着该奖项抽中的几率是0， 
	 * 数组中v的总和（基数），基数越大越能体现概率的准确性。 
	 * 本例中v的总和为100，那么平板电脑对应的 中奖概率就是1%， 
	 * 如果v的总和是10000，那中奖概率就是万分之一了。 
	 *  
	 */  
	/*===================================================================
	$prize_arr = array(   
		'0' => array('id'=>1,'prize'=>'平板电脑','v'=>1),   
		'1' => array('id'=>2,'prize'=>'数码相机','v'=>5),   
		'2' => array('id'=>3,'prize'=>'音箱设备','v'=>10),   
		'3' => array('id'=>4,'prize'=>'4G优盘','v'=>12),   
		'4' => array('id'=>5,'prize'=>'10Q币','v'=>22),   
		'5' => array('id'=>6,'prize'=>'下次没准就能中哦','v'=>50),   
	);   
	 /*=================================================================== 
	/* 
	 * 每次前端页面的请求，PHP循环奖项设置数组， 
	 * 通过概率计算函数get_rand获取抽中的奖项id。 
	 * 将中奖奖品保存在数组$res['yes']中， 
	 * 而剩下的未中奖的信息保存在$res['no']中， 
	 * 最后输出json个数数据给前端页面。 
	 */  
	 //根据数组中个数组字段的概率获取
	function getRandByArrayField($prize_arr){
		foreach ($prize_arr as $key => $val) {   
			$arr[$val['id']] = $val['v'];   
		}   
		$rid = $this->get_rand($arr); //根据概率获取奖项id   
		  
		$res['yes'] = $prize_arr[$rid-1]['prize']; //中奖项   
		unset($prize_arr[$rid-1]); //将中奖项从数组中剔除，剩下未中奖项   
		shuffle($prize_arr); //打乱数组顺序   
		for($i=0;$i<count($prize_arr);$i++){   
			$pr[] = $prize_arr[$i]['prize'];   
		}   
		$res['no'] = $pr;
		return $res;
	}
	
	
	function get_rand($proArr) {   
		$result = '';   
		//概率数组的总概率精度   
		$proSum = array_sum($proArr);   
	   
		//概率数组循环   
		foreach ($proArr as $key => $proCur) {   
			$randNum = mt_rand(1, $proSum);   
			if ($randNum <= $proCur) {   
				$result = $key;   
				break;   
			} else {   
				$proSum -= $proCur;   
			}   
		}   
		unset ($proArr);   
		return $result;   
	}  
	
	
	
	
	
	
	
	
	

}