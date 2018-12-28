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

use think\Cache;

class Index extends Base
{
    public function index() {	//获取服务器详细信息
        $param = input("param.");
        if(request()->isAjax()){
            if(isset($param['language']) && $param['language']>=0 ){
                session("language", $param['language']);
                return json(['code'=>1,'msg'=>'ok','language'=>getLanguage($param['language']) ]);
            }
        }
        //获取列表语言
        $language = getLanguage( -1 );
        $this->assign("language",$language);
        return $this->fetch('/index');
    }
	
	public function getDiskInfo(){
		$info = [
			'disk_available_space'=>round( ((disk_total_space("/")-disk_free_space("."))/(1024*1024*1024)) ,1),
			'disk_total_space'=>round((disk_total_space("/")/(1024*1024*1024)),1),
			'percent'=>disk_total_space("/")/disk_free_space(".")
		];
		return $info;
	}

	public function clear()
    {
        Cache::clear();
    }


  

}
