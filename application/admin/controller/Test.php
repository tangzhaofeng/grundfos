<?php
// +----------------------------------------------------------------------
// | snake
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 http://baiyf.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: NickBai <1902822973@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use app\admin\model\ActivityshareModel;
use think\Db;

class Test extends Base
{
    public function index() {	//获取服务器详细信息
       $ret = Db("activity_member")->where("activity_id=232 and sign_time>0")->select();
	   $obj = Db("member_group");
	   $where = [];
	   foreach($ret as $k=>$v){
		   $where['bu_id'] = 3;
		   $where['member_id'] = $v['member_id'];
		   $where['group_id'] = 82;
		   $result = $obj->where($where)->find();
		   if(empty($result)){
			   $where['creater_time'] = $v['sign_time'];
			   $obj->insert($where);
		   }
	   }
    }
	
	public function getpic() {
		// $sm = new ActivityshareModel;
		// $data = collection($sm->where(["activity_id" => 263])
		// ->alias('sm')
		// ->join('xk_member m', 'm.id = sm.member_id')
		// ->field(['m.tel', 'sm.img'])
		// ->select()
		// )->toArray();
		
		// foreach ($data as $key => $val) {
		// 	$this->dealimg($val['img'], $val['tel'].'_'.$key);
		// }
		// echo "复制完毕！";
	}

	public function dealimg($str, $tel) {
		// $arr = explode(',', $str);
		// foreach($arr as $val) {
		// 	copy('./'.$val,"./actimg/".$tel.'.jpg');
		// }
	}
}
