<?php

namespace app\admin\controller;
use app\admin\model\BuModel;
use app\admin\model\GoodsModel;
use app\admin\model\GoodssampleimgModel;
use app\admin\model\GoodssampleModel;
use app\admin\model\GoodvideoModel;
use think\Db;
use \think\Loader;
class Goods extends Base
{
    // list
    public function index() {
        $param = input('param.');
        $size = 10;
        $where = [];
        if (isset($param['searchText']) && !empty($param['searchText'])) {
            $where['title'] = ['like', '%' . trim($param['searchText']) . '%'];	
        }
		if (isset($param['department_id']) && !empty($param['department_id'])) {
            $where['group_id'] = $param['department_id'];
        }
		
		
        $obj = new GoodsModel();
        $selectResult = $obj->getDataByWhere($where, $size);
        $return['searchText'] = !empty($param['searchText'])?trim($param['searchText']):null;
        $return['total'] = $obj->getDataCountByWhere($where);  //总数据
        $return['rows'] = $selectResult;
        $return['page'] = $selectResult->render();
        $this->assign('return',$return);

		//部门列表
		$department = Db('bu')->order('id desc')->select();
		$this->assign("department",$department);
		
        if(request()->isAjax()){
			 return json($return);
		}else{
			$type = input("param.type");
			return isset($type)?$this->fetch("data"):$this->fetch() ;
		}
   
            
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
          
            $obj = new GoodsModel();
			$ret = $obj->insert($param);
            return json($ret);
        }else{
			$group_id = input('param.group_id');
			$this->assign('option', $this->getCategoryOption($group_id,0));
            return $this->fetch();
        }
    }
	
	/*
	* 获取类别
	* return otpion html
	*/
	function getCategoryOption($group_id,$selected_id){
		//类别列表
		$Event = \think\Loader::controller('admin/category');
		$category = $Event->getCategory($group_id);
		//tree struct
		$data = $Event->makeTree($category);
		//option html
		$option = $Event->treeOptionList($data,$selected_id);
		return $option;
	}
	
    //编辑
    public function edit()
    { 
        $obj = new GoodsModel();
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
			//类别列表
			$group_id = input('param.group_id');
			$data = $obj->getOneData($id);
			$this->assign('option', $this->getCategoryOption($group_id,$data['category']));
            $this->assign('data', $data);
            return $this->fetch();
        }
    }

    //删除视频 
    public function delete()
    {
        $id = input('param.id');
        $obj = new GoodsModel();
		$ret = $obj->del($id);
		//删除上传的文件
		if($ret['code']==1){ 
			if(file_exists($ret['data']['file_url'])){
				@unlink($ret['data']['file_url']);
			}
			if(file_exists($ret['data']['poster'])){
				@unlink($ret['data']['poster']);
			}
			if(file_exists($ret['data']['pdf_file_url'])){
				@unlink($ret['data']['pdf_file_url']);
			}
		}
        return json($ret);
    }

    /**
     * 新增视频
     */
    public function videoadd()
    {
        if (request()->isPost()){
            $param = input('post.');
            $video = new GoodvideoModel();
            $result = $video->insert([
                'video_name' => $param['title'],
                'video_dir' => $param['img'][0],
                'good_id' => $param['good_id'],
                'creater_time' => date('Y-m-d h:i:s',time())
            ]);
            return json($result);
        }else{
            $this->assign('good_id',input('param.id'));
            return $this->fetch();
        }
    }

    /**
     * 视频列表
     */
    public function videolist()
    {
        $param = input('param.');
        $size = 10;
        $where = [];
//        if (isset($param['searchText']) && !empty($param['searchText'])) {
//            $where['title'] = ['like', '%' . trim($param['searchText']) . '%'];
//        }
        if (isset($param['id'])) {
            $where['good_id'] = $param['id'];
        }


        $obj = new GoodvideoModel();
        $selectResult = $obj->getDataByWhere($where, $size);
        $return['searchText'] = !empty($param['searchText'])?trim($param['searchText']):null;
        $return['total'] = $obj->getDataCountByWhere($where);  //总数据
        $return['rows'] = $selectResult;
        $return['page'] = $selectResult->render();
        $this->assign('return',$return);

        return $this->fetch() ;
    }


    /**
     * 视频删除
     */
    public function videodelete()
    {
        $id = input('param.id');
        $obj = new GoodvideoModel();
        $ret = $obj->del($id);
        //删除上传的文件
        if($ret['code']==1){//查询出img表循环删除
                if(file_exists($ret['data']['video_dir'])){
                    @unlink($ret['data']['video_dir']);
                }
        }
        return json($ret);
    }

    /**
     * 视频改名
     */
    public function setVideoName()
    {
        $param = input('param.');
        $obj = new GoodvideoModel();
        if($param['thisvalue']){
            $result = $obj->edit([
                'id' => $param['thisid'],
                'video_name' => $param['thisvalue']
            ]);
        }
    }

    /**
     * 样本改名
     */
    public function setSampleName()
    {
        $param = input('param.');
        $obj = new GoodssampleModel();
        if($param['thisvalue']){
            $result = $obj->edit([
                'id' => $param['thisid'],
                'sample_name' => $param['thisvalue']
            ]);
        }
    }

    /**
     * 样本列表
     */
    public function samplelist()
    {
        $param = input('param.');
        $size = 10;
        $where = [];
//        if (isset($param['searchText']) && !empty($param['searchText'])) {
//            $where['title'] = ['like', '%' . trim($param['searchText']) . '%'];
//        }
        if (isset($param['id'])) {
            $where['good_id'] = $param['id'];
        }


        $obj = new GoodssampleModel();
        $selectResult = $obj->getDataByWhere($where, $size);
        $return['searchText'] = !empty($param['searchText'])?trim($param['searchText']):null;
        $return['total'] = $obj->getDataCountByWhere($where);  //总数据
        $return['rows'] = $selectResult;
        $return['page'] = $selectResult->render();
        $this->assign('return',$return);

        return $this->fetch() ;
    }

    /**
     * 新增样本
     */
    public function sampleadd()
    {
        if (request()->isPost()){
            $samp = new GoodssampleModel();
            $param = input('post.');
            $count = count($param['img']);

            $result = $samp->insert([ //先添加主表数据
                'good_id' => $param['good_id'],
                'sum' => $count,
                'sample_name' => $param['title'],
                'creater_time' => date('Y-m-d h:s:i',time()),

            ]);

//            dump($result);exit();
            if ($result['code'] == 1){
                foreach ($param['img'] as $k=>$v){
                    $sampimg = new GoodssampleimgModel();
                    $sampimg->insert([
                        'sample_id' => $result['id'],
                        'img' => $v
                    ]);
                }

            }
            return json($result);
        }else{
            $this->assign('good_id',input('param.id'));
            return $this->fetch();
        }
    }

    /**
     * 样本删除
     */
    public function sampdelete()
    {
        $id = input('param.id');
        $obj = new GoodssampleModel();
        $ret = $obj->del($id);
        //删除上传的文件
        if($ret['code']==1){//查询出img表循环删除
            $sampimg = new GoodssampleimgModel();
            $array = collection($sampimg->where('sample_id',$ret['data']['id'])->select())->toArray();
            foreach ($array as $k=>$v){
                $sampimg->del($ret['data']['id'],$v['img']);
                if(file_exists($v['img'])){
                    @unlink($v['img']);
                }
            }
        }
        return json($ret);
    }

	   //列表中编辑排序
    public function setnumber()
    {
        $param = input('post.');
        $obj = new GoodsModel();
		if($param['thisvalue']){
  			$obj->save(['order_num'=>$param['thisvalue']],['id'=>$param['thisid']]);
		}else{
			$obj->save(['order_num'=>999999],['id'=>$param['thisid']]);
		}
    }

    //列表中编辑排序
    public function settotal()
    {
        $param = input('post.');
        $obj = new GoodsModel();
        if($param['total']){
            $obj->save(['total'=>$param['total']],['id'=>$param['thisid']]);
        }
    }

    //列表中编辑排序
    public function setclick()
    {
        $param = input('post.');
        $obj = new GoodsModel();
        if($param['click']){
            $obj->save(['click'=>$param['click']],['id'=>$param['thisid']]);
        }
    }


    /**
     * excel导出
     */
    public function export()
    {
        $param = input('param.');
        if (isset($param['export']) &&  $param['export']==true) {

            $obj = new GoodsModel();
            $bu_name = BuModel::where('id',$param['bu_id'])->value('bu_name');//获取到当前bu名字

            if (!isset($param['date'])){
                $param['date'] = '';
            }

            $data = $obj->excelDate($param['bu_id']);

            $title =[ 'A'=>'ID','B'=>'标题','C'=>'描述','D'=>'创建时间','E'=>'收藏数量','F'=>'点击次数','G'=>'关键字描述逗号隔开'];
            $this->export_xls($data,$title,$bu_name);
            exit();
        }
    }

    public function export_xls($data,$title,$bu_name)
    {
        vendor('Classes.PHPExcel');
        //单元格标识
        $cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ');

        $obj = new \PHPExcel();
        $obj->getActiveSheet(0)->setTitle('Sheet1'); //设置sheet名称
        $_row = 1;
        if($title){
            $_cnt = count($title);
            $obj->getActiveSheet(0)->mergeCells('A'.$_row.':'.$cellName[$_cnt-1].$_row);   //合并单元格
            $obj->setActiveSheetIndex(0)->setCellValue('A'.$_row, '数据导出：'.date('Y-m-d H:i:s'));  //设置合并后的单元格内容
            $_row++;
            $i = 0;
            foreach($title AS $v){   //设置列标题
                $obj->setActiveSheetIndex(0)->setCellValue($cellName[$i].$_row, $v);
                $i++;
            }
            $_row++;
        }


        if($data){
            $i = 0;
            foreach($data AS $_v){
                $j = 0;
                foreach($_v AS $_cell){
                    $obj->getActiveSheet(0)->setCellValue($cellName[$j] . ($i+$_row), $_cell);
                    $j++;
                }
                $i++;
            }
        }

        $fileName = $bu_name.'产品数据'.date('Y-m-d H:i:s',time());

        $objWrite = \PHPExcel_IOFactory::createWriter($obj, 'Excel2007');
        $obj->getActiveSheet()->getColumnDimension('A')->setWidth(30);
        $obj->getActiveSheet()->getColumnDimension('B')->setWidth(60);
        $obj->getActiveSheet()->getColumnDimension('C')->setWidth(100);
        $obj->getActiveSheet()->getColumnDimension('D')->setWidth(30);
        $obj->getActiveSheet()->getColumnDimension('E')->setWidth(30);
        $obj->getActiveSheet()->getColumnDimension('F')->setWidth(30);
        $obj->getActiveSheet()->getColumnDimension('G')->setWidth(30);

        header('pragma:public');
        header("Content-Disposition:attachment;filename=$fileName.xls");
        $objWrite->save('php://output');exit;
    }
}
