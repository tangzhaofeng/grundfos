<?php

namespace app\index\controller;
use think\Controller;
use think\Db;

class Api extends Controller
{
	private $video; 
	private $document; 
	private $faq; 
	private $webinars; 
	private $customer; 
	private $logo; 
	private $banner; 
	private $welcome;
	private $course;

	/**
	*  初始化程序
	**/
	function __construct()
	{
		parent::__construct();
		$this->course 	= Db('course');
		$this->video	= Db('video');
		$this->document = Db('document');
		$this->faq      = Db('faq');
		$this->webinars = Db('webinars');
		$this->logo 	= Db('logo');
		$this->banner 	= Db('banner');
		$this->welcome 	= Db('welcome');
	}

	//get contents
    public function getContents() {
		$param = input('param.');
		$this->checkAccess($param);
		$data = [];
		$where['is_public'] = 1; //公开
		$where['language'] 	= (isset($param['language']) && !empty($param['language']))?$param['language']:1; // 1英文 2中文
		//只需要该ytpe类型 根据id获取
		if(isset($param['type']) && in_array($param['type'], ["webinars","video","document","faq"])){
			switch($param['type']){
				case 'webinars':
					return json($this->getWebinars($where, $param));  
					break;
			}
		}

		//返回该User对应的数据
		$customerCode = "JPUKB1000"; //customer 
		$courseCode  = "BOOKING_COURSE"; //courseCode BOOKING_COURSE
		$where['customerCode'] = $customerCode; //公开
		return json([
			'logo'=>$this->getLogo($where),
			'banner'=>$this->getBanner($where),
			'welcome'=>$this->getWelcome($where),
			'bookCourse'=>$this->getBOOKING_COURSE($courseCode)
		]);
    }

	//根据课程id(BOOKING_COURSE)编号查询所有Data
	function getBOOKING_COURSE(&$courseCode)
	{
		$bookingCourse = $this->course->where("courseCode",$courseCode)->field("bookingCourse")->find();
		$bookingCourse = explode(",",$bookingCourse["bookingCourse"]);
		$data[] ="";
		foreach($bookingCourse as $k=>$v){
			$where['id'] = substr($v,1);
			if(empty($where['id'])){
				continue;
			}
			switch($v[0]){
				case "v":  //视频
					$data['video'][] = $this->getVideo($where);
				break;
				case "d":  //视频
					$data['document'][] = $this->getDocument($where);
				break;
				case "f":  //视频
					$data['faq'][] = $this->getFAQs($where);
				break;
				case "w":  //视频
					$data['webinars'][] = $this->getWebinars($where);
				break;
			}
		}
		return $data;
	}

	//检查获取的参数正确否
	function checkAccess(&$param) {
		
		if(empty($param['webinarId'])){
			return ['code'=>0,'msg'=>'webinarId is require'];
		}
		if(empty($param['name'])){
			return ['code'=>0,'msg'=>'name is require'];
		}
		if(empty($param['company'])){
			return ['code'=>0,'msg'=>'company is require'];
		}
		if(empty($param['position'])){
			return ['code'=>0,'msg'=>'position is require'];
		}
		if(!preg_match("/^\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/",$param['email'])){
			return ['code'=>0,'msg'=>"email isn't correct format"];
		}
		if(!preg_match("/^1[34578]{1}\d{9}$/",$param['phone'])){
			return ['code'=>0,'msg'=>"phone isn't correct format"];
		}
		//检查今天是否同一手机或邮箱提交 
		$createTime = Date("Y-m-d",strtotime("-1 day"));
		$ret = Db("webinar_user")->query("select COUNT(*) as count from xk_webinar_user where webinarId='".$param['webinarId']."' and phone='".$param['phone']."' or email='".$param['email']."' and createTime>'$createTime' limit 1");
		if($ret[0]['count']>2){
			return ['code'=>0,'msg'=>"The submission is too frequent. Please try again later"];
		}
		return ['code'=>1,'msg'=>'access ok' ];
	}

   //
	public function mapWebinarUser(){
		if(request()->isPost()){
			$param = input("post.");
			$ret = $this->checkAccess($param);
			
			if($ret['code']==0){
				return json($ret);
			} else {
				$param['createTime'] = Date("Y-m-d H:i:s",time());
				Db('webinar_user')->insert($param);
				return json(['code'=>1,'msg'=>"ok"]);
			}
		}
	}


	//反馈
	public function feedback(){
		if(request()->isPost()){
			$obj = Db("feedback");
			$param = input("post.");
			if(!isset($param['feedback']) || !in_array($param['feedback'],[1,2,3])){
				return json(['code'=>0,'msg'=>"无效的参数feedback"]);
			}
			$param['userId'] = "userId";  //
			$param['createTime'] = Date("Y-m-d H:i:s", time());  //需要获取
			$ret = $obj->query("select COUNT(*) as count from xk_feedback where userId='".$param['userId']."' and createTime>'".$param['createTime']."' limit 1");

			if($ret[0]['count']>=1){
				return json( ['code'=>0,'msg'=>"The submission is too frequent. Please try again later"] );
			}
			$param['createTime'] = Date("Y-m-d H:i:s",time());
			$obj->insert($param);
			return json(['code'=>1,'msg'=>"ok"]);
		}
	}


	//获取视频
	protected function getVideo(&$where)
	{
		if(isset($where['id']) && !empty($where['id']))
			return $this->video->where('id',$where['id'])->find(); //只获取当前文件
		else
			return $this->video->where($where)->order("id desc")->select(); //获取文件列表
	}

	//获取Document
	protected function getDocument(&$where)
	{
		if(isset($where['id']) && !empty($where['id']))
			return $this->document->where('id',$where['id'])->find(); //只获取当前文件
		else
			return $this->document->where($where)->order("id desc")->select(); //获取文件列表
	}

	//获取FAQs
	protected function getFAQs(&$where)
	{
		if(isset($where['id']) && !empty($where['id']))
			return $this->faq->where('id',$where['id'])->find(); //只获取当前文件
		else
			return $this->faq->where($where)->order("id desc")->select(); //获取文件列表
	}

	//获取webinars
	protected function getWebinars(&$where)
	{
      if(isset($where['id']) && !empty($where['id']))
      	return $this->webinars->where('id',intval($where['id']))->find();
      else
      	return $this->webinars->where($where)->order("id desc")->select();
    }

	//获取Logo
	protected function getLogo(&$where)
	{
      	return $this->logo->where($where)->find();
    }

	//获取Banner
	protected function getBanner(&$where)
	{
      	return $this->banner->where($where)->order("id desc")->select();
    }

	//获取Welcome
	protected function getWelcome(&$where)
	{
      	return $this->welcome->where($where)->find();
    }

}
?>