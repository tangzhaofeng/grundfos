<?php

namespace app\admin\controller;
use app\admin\model\CourseModel;
use think\Db;
use \think\Loader;
class Course extends Base
{
    private $video; 
	private $document; 
	private $faq; 
	private $webinars; 

	/**
	*  初始化程序
	**/
	function __construct()
	{
		parent::__construct();
		$this->video	= Db('video');
		$this->document = Db('document');
		$this->faq      = Db('faq');
		$this->webinars = Db('webinars');
	}


    public function index() {
        $param = input('param.');
        $size = 10;
        $where = [];
        if (isset($param['searchText']) && !empty($param['searchText'])) {
            $where['courseCode'] = ['like', '%' . trim($param['searchText']) . '%'];	
        }
        if (isset($param['id']) && !empty($param['id'])) {
            $where['customerCode'] = $param['id'];	
        }
       
        $obj = new CourseModel();
        $selectResult = $obj->getDataByWhere($where, $size);
        
        $return['searchText'] = !empty($param['searchText'])?trim($param['searchText']):null;
        $return['total'] = $obj->getDataCountByWhere($where);  //总数据
        $return['rows'] = $selectResult;
        $return['page'] = $selectResult->render();
        $this->assign('return',$return);
        return $this->fetch();
    }


   //course add 
    public function add()
    {
        $obj = new CourseModel();
        if(request()->isPost()){
            $param['bookingCourse'] = input('post.bookingCourse');
            $param['createTime'] = Date('Y-m-d H:i:s',time());
            $param['courseCode'] = date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
            return json($obj->insert($param));
        } else {
             return $this->fetch();
        }
    }

     //course edit
    public function edit()
    {
        $obj = new CourseModel();
        if(request()->isPost()){
            $param = input('post.');
            return json($obj->edit($param));
        }else{
             $courseCode = input('param.courseCode');
             $this->assign('courseCode',json_encode( $this->getBOOKING_COURSE($courseCode) ));
             return $this->fetch();
        }
    }

    //删除
    public function delete()
    {
        $id = input('param.id');
        $obj = new CourseModel();
        return json($obj->del($id));
    }


    //获取该课程是否选中
   	//根据课程id(BOOKING_COURSE)编号查询所有Data
	function getBOOKING_COURSE(&$courseCode)
	{
		$bookingCourse = Db("course")->where("courseCode",$courseCode)->field("bookingCourse")->find();
        if(empty($bookingCourse)){
            return  [];
        }
		$bookingCourse = explode(",", rtrim($bookingCourse["bookingCourse"],','));
		$data = [];
        foreach($bookingCourse as $key=>$v){
            $id = substr($v,1); //去掉第一个字符
            switch($v[0]){
                case "v":  //视频
                    $ret = $this->video->where("id",$id)->field("id")->find();
                    if($ret){
                        $data['video'][] = $id;
                    }
                    break;
                case "d":  //doucment
                     $ret = $this->document->where("id",$id)->field("id")->find();
                    if($ret){
                        $data['documents'][] = $id;
                    }
                     break;
                case "f":  //faqs
                    $ret = $this->faq->where("id",$id)->field("id")->find();
                    if($ret){
                        $data['faq'][] = $id;
                    }
                    break;
                case "w":  //webinars
                    $ret = $this->webinars->where("id",$id)->field("id")->find();
                    if($ret){
                        $data['webinars'][] = $id;
                    }
                    break;
            }
        }
        return $data;
	}
 

}
