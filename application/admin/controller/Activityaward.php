<?php

namespace app\admin\controller;
use app\admin\model\ActivityAwardModel;
use think\Db;

class Activityaward extends Base
{
    //FAQs list
    public function index() {
        $param = input('param.');
        //导出
        $this->export($param);
		
        //show list
        $size = 10;
        $where = [];
        if (isset($param['searchText']) && !empty($param['searchText'])) {
            $where['title|desc'] = ['like', '%' . trim($param['searchText']) . '%'];	
        }
        
        if (isset($param['language']) && !empty($param['language'])) {
            $where['language'] = $param['language'];	
        }
        $obj = new ActivityAwardModel();
        $selectResult = $obj->getDataByWhere($where, $size);
        
        $return['searchText'] = !empty($param['searchText'])?trim($param['searchText']):null;
        $return['language'] = !empty($param['language'])?trim($param['language']):0;
        $return['total'] = $obj->getDataCountByWhere($where);  //总数据
        $return['rows'] = $selectResult;
        $return['page'] = $selectResult->render();
        $this->assign('return',$return);
        if(request()->isAjax())
            return json($return);
        else
            return $this->fetch();
    }


     //FAQs add
    public function add()
    {
        if(request()->isPost()){
            $param = input('param.');
            $param['create_time'] = Date('Y-m-d H:i:s',time());
            $obj = new ActivityAwardModel();
			$ret = $obj->insert($param);
            return json($ret);
        }else{
             return $this->fetch();
        }
    }

   //FAQs edit
    public function edit()
    { 
        $obj = new ActivityAwardModel();
        if(request()->isPost()){
            $param = input('param.');
            return json( $obj->edit($param) );
        }else{
            $id = input('param.id');
            $this->assign('data',$obj->getOneData($id));
            return $this->fetch();
        }
    }

    //删除
    public function delete()
    {
        $id = input('param.id');
        $obj = new ActivityAwardModel();
		$ret = $obj->del($id);
        return json($ret);
    }

    //清空所有数据
    public function  deleteAll()
    {
        $obj = new ActivityAwardModel();
        $obj->where("1=1")->delete();
    }
	/*
	//导出
	public function export(&$param)
   {
       if (isset($param['export']) &&  $param['export']==true) {
            $data = Db("active_award_play_user")->where(['status'=>1])->field("
			name,phone,position,
			case award 
				when -1 then '黑玫瑰茶'
				when 1 then '护肤礼盒'
				when 2 then '护肤礼盒'
				when 3 then '精油套装'
				when 4 then '大闸蟹礼盒'
				when 5 then '本草清液'
				when 6 then '商务夹克'
				when 7 then '铁皮石斛粉'
				when 8 then '凯迪手表'
				when 9 then '茶叶礼盒'
				when 10 then '大红袍礼盒'
				when 11 then '洪：华为荣耀10'
				when 12 then '华为荣耀10'
				when 13 then '华为荣耀10'
				when 14 then '娄：华为MT10'
				when 15 then '需：华为MT10'
			else '其他' end,
			case is_got
				when 0 then '被取消'
				when 1 then '已领取'
			else '其他' end	
			")->order("award asc")->select();
            $Event = \think\Loader::controller('admin/Common');
            $Event->export_xls($data,"中奖人员.xls");   
            exit();
        }
    }
	*/
    public function export(&$param)
   {
       if (isset($param['export']) &&  $param['export']==true) {
            $data = Db("active_award_play_user")->where(['status'=>1])->field("
			name,phone,position,
			case award 
				when -1 then '特等奖'
				when 1 then '一等奖'
				when 2 then '二等奖'
				when 3 then '三等奖'
				when 4 then '四等奖'
			else '其他' end,
			case is_got
				when 0 then '被取消'
				when 1 then '已领取'
			else '其他' end	
			")->order("award asc")->select();
            $Event = \think\Loader::controller('admin/Common');
            $Event->export_xls($data,"中奖人员.xls");   
            exit();
        }
    }

	
	//导入
    public function import()
    {
       if(request()->isPost()){
            //获取文件
			$file = input("post.file_url");
			$Event = \think\Loader::controller('admin/Common');
            $data = $Event->import_excel($file); 
			$obj = Db("active_award_play_user");
			
			foreach($data as $k=>$v){
				
				if(isset($v[4]) && $k!=0) {
					$openid = $obj->where("openid",$v[4])->field('openid')->find();
					if(empty($openid)){
						$myData['name'] = $v[0];
						$myData['phone'] =$v[1]; 
						$myData['email'] = $v[2];
						$myData['company'] = $v[3] ;
						$myData['position'] = $v[4] ;
						$myData['openid'] = $v[5];
						$myData['create_time'] = Date("Y-m-d H:i:s",time());
						$obj->insert($myData);
					}
					
				}
			}	
            return json(['status'=>1,'msg'=>'ok']);
        } else {
            return $this->fetch();
        }
    }
	
	
	
	
}
