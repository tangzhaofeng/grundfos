<?php

namespace app\admin\controller;
use app\admin\model\VideoModel;
use think\Db;
use \think\Loader;
class Video extends Base
{
    //Video list
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
     
        $obj = new VideoModel();
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

  


   //Video add
    public function add()
    {
        if(request()->isPost()){
            $param = input('post.');
            $param['create_time'] = Date('Y-m-d H:i:s',time());
			//求文件占用的空间
            $file_url = ltrim($param['file_url'],'/');
			if(file_exists($file_url)){
				$Event = \think\Loader::controller('admin/Common');
				$param['file_size'] = $Event->file_size($file_url);
			}
          
            $obj = new VideoModel();
			$ret = $obj->insert($param);
            return json($ret);
        }else{
             return $this->fetch();
        }
    }

    //编辑视频 
    public function edit()
    { 
        $obj = new VideoModel();
        if(request()->isPost()){
            $param = input('param.');
            $file_url = ltrim($param['file_url'],'/');
			if(file_exists($file_url)){
				$Event = \think\Loader::controller('admin/Common');
				$param['file_size'] = $Event->file_size($file_url);
			}
            return json( $obj->edit($param) );
        }else{
            $id = input('param.id');
            $this->assign('data',$obj->getOneData($id));
            return $this->fetch();
        }
    }

    //删除视频 
    public function delete()
    {
        $id = input('param.id');
        $obj = new VideoModel();
		$ret = $obj->del($id);
		//删除上传的文件
		if($ret['code']==1){ 
			if(file_exists($ret['data']['file_url'])){
				@unlink($ret['data']['file_url']);
			}
			if(file_exists($ret['data']['poster'])){
				@unlink($ret['data']['poster']);
			}
		}
        return json($ret);
    }

    //导出
    public function export(&$param)
    {
       if (isset($param['export']) &&  $param['export']==true) {
            $data = Db("video")->order("id desc")->field("id,customerCode,title,file_size,views,language,create_time")->select();
            $customer = Db("customer");
            foreach($data as $k=>$v){
                $data[$k]['language'] = getLanguage( $v['language']-1 );
                $ret = $customer->where('customerCode',$v['customerCode'])->find();
                $data[$k]['customerCode'] = $ret['customerName'];
            }
            
            $title =[ ['Video id','customerName','Title','Occupied Space','Views','Language','Create time'] ] ;
            $data = array_merge($title,$data);
            $Event = \think\Loader::controller('admin/Common');
            $Event->export_xls($data,"video.xls");   
            exit();
        }
    }

}
