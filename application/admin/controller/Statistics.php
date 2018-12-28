<?php

namespace app\admin\controller;

use app\admin\model\Node;
use think\Controller;

class Statistics extends Controller
{
     /**
     * Statistics
     * @return mixed
     */
    public function index()
    {
		$this->assign('cnzz',$this->cnzz()); //uv进60天内浏览量统计
		$this->assign('data',$this->totalAll());
        return $this->fetch();
    }

    //  $this->assign('cnzz',$this->cnzz());
	//pv uv进60天内浏览量统计
	function cnzz(){
		//取出最近30天浏览数据
		$beforeDays = 60; //days
		$ret =  Db('view')->query("SELECT * FROM xk_view where DATE_SUB(CURDATE(), INTERVAL $beforeDays DAY) <= date(create_time) order by id desc ");
		$day = [];
		$view = [];
		for($i=$beforeDays-1;$i>=0;$i--){
			$day[] = date("Y/m/d",strtotime("-$i day"));
			$pv_num = 0; //pv每天访问量
			$uv_num = 0; //uv每天访问量
			$video_view = 0; //video_view
			$document_view = 0; //faq_view
			$faq_view = 0; //faq_view
			$webinar_baoming = 0; //webinar_baoming
			$webinars_view = 0;
			foreach($ret as $k=>$v){
				if(date("Y/m/d",strtotime("-$i day")) == date("Y/m/d",strtotime($v['create_time'])) ){
					$pv_num = intval( $v['pv'] );
					$uv_num = intval( $v['uv'] );
					$video_view = intval( $v['video_view'] );
					$document_view = intval( $v['document_view'] );
					$faq_view = intval( $v['faq_view'] );
					$webinar_baoming = intval( $v['webinar_baoming'] );
					$webinars_view = intval( $v['webinars_view'] );
				}
			}
			$view['pv'][] = $pv_num;
			$view['uv'][] = $uv_num;
			$view['video_view'][] = $video_view;
			$view['document_view'][] = $document_view;
			$view['faq_view'][] = $faq_view;
			$view['webinar_baoming'][] = $webinar_baoming;
			$view['webinars_view'][] = $webinars_view;
		
		}
		
		return  [  'pvuv'=>[
					  ['name'=>'Pv','color'=>'#1ab394','data'=>$view['pv']],
					  ['name'=>'Uv','color'=>'#00abff','data'=>$view['uv']],
					  ['name'=>'Videos Viewing','color'=>' dark','data'=>$view['video_view']],
					  ['name'=>'Documents Viewing','color'=>'#c00','data'=>$view['document_view']],
					  ['name'=>'FAQs Viewing','color'=>'#422ad4','data'=>$view['faq_view']],
					  ['name'=>'Webinars Viewing','color'=>'orange','data'=>$view['webinars_view']],
					  ['name'=>'Webinars Sign-up','color'=>'blue','data'=>$view['webinar_baoming']]
				    ],
					'day'=>$day
			    ];
	}


	function totalAll(){
		$data['userCount'] = Db("user")->count(); //用户总数量
		$data['customerCount'] = Db("customer")->count(); //客户总数量
		$data['usercertCount'] = Db("user_cert")->count(); //获取取证书总数
		//评价反馈
		$feedback = Db("feedback");
		$data['feedback']['yes'] = $feedback->where('feedback',1)->count();
		$data['feedback']['average'] = $feedback->where('feedback',2)->count();
		$data['feedback']['no'] = $feedback->where('feedback',3)->count();
		return $data;
	}
 




}