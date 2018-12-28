<?php

namespace app\index\controller;
use think\Controller;
use think\Db;
class Index extends Controller
{

	function __construct(){
		parent::__construct();
        $this->check_login();//验证登陆
		$this->savePvUv(); //pvUv记录
	}

	function check_login(){
		$openid = session("user_oauth.openid");
		if(empty($openid)){
			$this->redirect(url('Wechat/wechatLogin',['my_redirect_uri'=>'http://www.juplus.cn/cdm/']));
		}
	}
	
	//检查可否开始游戏
	function check_start(){
		$openid = session("user_oauth.openid");
		
		//判断是否领取折扣券
		$student = Db("student")->where('openid',$openid)->find();
		$discount = "83折";
		if($student["last_question"]==1){
			$discount = "77折";
		}
		
		if(!empty($student)){ //折扣券
			return json(['code'=>1,'msg'=>"您已经领取折扣券 你的折扣是: ".$discount. " 感谢您的参与 谢谢！",
				"discount"=>$discount ]);
		}
		//判断是否打过题
		$ret = Db("student_cert")->where('openid',$openid)->find();
		if(!$ret){
			return json(['code'=>2,'msg'=>"请先答题  谢谢"]);
		}
		//只是提交了答题  没有提交个人信息
		if(empty($student)){
			return json(['code'=>3,'msg'=>"您已经答过题但是没有个人新 请填写  谢谢"]);
		}
		
		return json(['code'=>0,'msg'=>"ok"]);
	}


	//检查可否开始游戏
	function check_get_award(){
		$openid = session("user_oauth.openid");
		$student = Db("student")->where('openid',$openid)->find();
		if($student['use_status']==1){
			return json(['code'=>1,'msg'=>"你的折扣已被使用"]);
		}
		
		$param = input("param.");
		if(isset($param['status']) && !empty($param['status'])){
			$ret = Db("student")->where("id",$student['id'])->update(['use_status'=>1]);
			if($ret){
				return json(['code'=>1,'msg'=>"你的折扣已成功被使用"]);
			}else{
				return json(['code'=>0,'msg'=>"服务器繁忙  请稍候再试！"]);
			}
		}
	
	}
	
	
	
	//手机端
    public function index() {
		$Event = \think\Loader::controller('index/Wechat');
		$this->assign("signPackage",$Event->getSignPackage() );//微信分享
        return $this->fetch();
    }


	  //是否手机
    function is_phone( $phone ){
        return preg_match("/^1[34578]{1}\d{9}$/", $phone);
    }
	//报名
	public function baoming() {
		$param = input("param.");
		$obj = Db("student");
		$openid = session("user_oauth.openid");
		$ret = $obj->where("openid",$openid)->find();
		if($ret){
			return json(['code'=>6,'msg'=>"您的信息已经提交成功 感谢你的参与 谢谢"]);
		}
		if(!isset($param['name']) || empty($param['name'])){
			return json(['code'=>2,'msg'=>"请输入您的姓名 谢谢"]);
		}
		if(!isset($param['age']) || !preg_match("/^\d*$/",$param['age'])){
			return json(['code'=>4,'msg'=>"请输入正确的年龄 谢谢"]);
		}
		if(!isset($param['phone']) || !$this->is_phone($param['phone'])){
			return json(['code'=>5,'msg'=>"请输入正确的电话 谢谢"]);
		}
		$param['create_time'] = Date("Y-m-d H:i:s",time());
		$param['openid'] = $openid ;

		//计算折扣
		$dati = Db("student_cert")->where('openid',$openid)->find();
		$arr = json_decode($dati['answer'], true);
		$discount = "83折";
		$param['last_question'] = 0 ;
		if(isset($arr["4"]) && $arr["4"]==1){
			$discount = "77折";
			$param['last_question'] = 1 ;
		}
		//dump($param);exit;
		
		$id = $obj->insertGetId($param);
		return json(['code'=>1,'msg'=>"ok",'id'=>$id,'discount'=>$discount ]);
	}



	//答题
	function dati() {
		$openid = session("user_oauth.openid");
		$obj = Db("student_cert");
		$ret = $obj->where('openid',$openid)->find();
		if($ret){
			return json(['code'=>6,'msg'=>"您已经答过题不能再次答题  谢谢"]);
		}
		$param = input("param.");
		$param['openid'] = $openid;
		$param['create_time'] = Date("Y-m-d H:i:s",time());
		
		// {"1":"A","2":"B","3":"C","4":"B"} ......  //这时提交的答题json  1表示第一题 提交的答案为a    以此类推.......
		$id = $obj->insertGetId($param);
		if($id>0){
			return json(['code'=>1,'msg'=>"答题提交成功 感谢您的参与 谢谢"]);
		}else{
			return json(['code'=>2,'msg'=>"服务器繁忙  请稍候再试！"]);
		}
	}


	
	
	
	
	
	
	

	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	//保存用户的浏览记录pv uv
	function savePvUv(){
		$obj = Db("view");
		$today_view =$this->get_today_view();
		if($today_view){
			$obj->where('id', $today_view['id'])->setInc('pv'); //更新pv 没刷+1
			$uv = session("uv");
			if(!$uv){
				session("uv",1);
				$obj->where('id', $today_view['id'])->setInc('uv'); //更新pv 没刷+1
			}
		}else{
			$obj->insert(['pv'=>1,'uv'=>1,'create_time'=>Date("Y-m-d H:i:s",time())]); //添加pv uv
		}
	}

	//查询今天记录（也就是大于昨天）
	function get_today_view(){
		$today_view = cache('today_view');
		if(empty($today_view)){
			cache('today_view',Db("view")->where('create_time','>',Date("Y-m-d",strtotime("-1 day")))->find());
		}
		return cache('today_view');
	}




}
?>