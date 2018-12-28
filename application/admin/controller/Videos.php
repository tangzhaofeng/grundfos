<?php
/**
 * Created by PhpStorm.
 * User: juplus-06
 * Date: 2018/4/2
 * Time: 16:12
 */

namespace app\admin\controller;



use app\admin\model\ActivitymemberModel;
use app\admin\model\ActivityModel;
use app\admin\model\ActivityshareModel;
use app\admin\model\AppsecretModel;
use app\admin\model\BuModel;
use app\admin\model\CategoryModel;
use app\admin\model\CompanyModel;
use app\admin\model\CompanyworkModel;
use app\admin\model\GoodsModel;
use app\admin\model\GoodsrelationModel;
use app\admin\model\GoodssampleimgModel;
use app\admin\model\GoodssampleModel;
use app\admin\model\GoodvideoModel;
use app\admin\model\GroupingModel;
use app\admin\model\MemberModel;
use app\index\controller\Redpack;
use app\index\controller\Group;

class Videos extends Base
{
    /**
     * 列表页
     */
    public function index()
    {
        $param = input('param.');

        $size = 10;
        $where = [];

        $obj = new GoodvideoModel();
        $bu = new BuModel();
        $relation = new GoodsrelationModel();

        $good = new GoodsModel();
        $list = array();
        $selectResult = $obj->getBuBywhere($where, $size);

        $bu = collection($bu->all())->toArray();

        $total = $obj->getAllUsers($where);  //总数据



        $return['total'] = $total;
        foreach ($selectResult->items() as $k=>$v){
             $id = $v->getdata();
             $info = collection($relation->where('relation_id',$id['id'])->where('type',2)->field(['good_id'])->select())->toArray();
             $titleall = '';
             foreach ($info as $key=>$value){
                 $title = $good->where('id',$value['good_id'])->field(['title'])->find();
                 $titleall .= ','.$title['title'];
             }
            $v->data('title',$titleall);
        }
        $return['rows'] = $selectResult;

        $return['page'] = $selectResult->render();

        $this->assign('bu',$bu);
        $this->assign('return',$return);
        return $this->fetch();
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
                'sum' => $count,
                'sample_name' => $param['title'],
                'creater_time' => date('Y-m-d h:s:i',time()),

            ]);

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
     * 分配
     */
    public function distribution()
    {
        if(request()->isPost()){
            $param = input('post.');
            $relation = new GoodsrelationModel();
            foreach ($param['good_id'] as $k=>$v){
                $relation->insert([
                    'relation_id' => $param['id'],
                    'type' => 2,
                    'good_id' => $v,
                    'creater_time' => date('Y-m-d h:i:s',time())
                ]);
            }
            return json(['code'=>1,'msg'=>'分配成功','data'=>'']);
        }else{
            $param = input('param.');
            $bu = new BuModel();
            $bulist = collection($bu->select())->toArray();
            $this->assign('bulist',$bulist);
            $this->assign('id',$param['id']);
            return $this->fetch();
        }
    }

    /**
     * 修改图片
     */
    public function editimg()
    {
        $video = new GoodvideoModel();
        if(request()->isPost()){
            $param = input('post.');

            $result = $video->idEditImg($param);
            return json($result);
        }else{
            $param = input('param.');
            $info = $video->where('id',$param['id'])->find();
            $this->assign('data',$info);
            return $this->fetch();
        }
    }


    /**
     * 分配列表
     */
    public function distributionlist()
    {
        $param = input('param.');

        $size = 10;
        $where = [];

        $obj = new GoodsrelationModel();
        $bu = new BuModel();
//        $relation = new GoodsrelationModel();
        $good = new GoodsModel();
        $list = array();

        $where['relation_id'] = $param['id'];
        $where['type'] = 2;
        $selectResult = $obj->getBuBywhere($where, $size);

        $bu = collection($bu->all())->toArray();

        $total = $obj->getAllUsers($where);  //总数据


//        dump($selectResult->items()[0]->getdata());exit();
        foreach ($selectResult->items() as $k=>$v){
            $good_info = $good->where('id',$v->getdata()['good_id'])->field(['id','title'])->find();
//            dump($v->getdata()['good_id']);exit();
            $v->data('title',$good_info['title']);
            $v->data('id',$good_info['id']);
        }
        $return['total'] = $total;

        $return['rows'] = $selectResult;

        $return['page'] = $selectResult->render();

        $this->assign('bu',$bu);
        $this->assign('return',$return);
        return $this->fetch();
    }

    /**
     * 分配删除
     */
    public function dislistdelete()
    {
        $param = input('param.');
        $obj = new GoodsrelationModel();
        $result = $obj->del($param);
        return json($result);
    }

    /**
     * 新增视频
     */
    public function add()
    {
        if (request()->isPost()){
            $param = input('post.');
            $video = new GoodvideoModel();
            $result = $video->insert([
                'video_name' => $param['title'],
                'video_dir' => $param['img'][0],
                'get_img' => $param['get_img'],
                'creater_time' => date('Y-m-d h:i:s',time())
            ]);
            return json($result);
        }else{
            $this->assign('good_id',input('param.id'));
            return $this->fetch();
        }
    }

    /**
     * 获取商品
     */
    public function getgood()
    {
        $param = input('param.');
        $good = new GoodsModel();
        $good_list = collection($good->where('group_id',$param['bu_id'])->field(['id','category','title'])->select())->toArray();
        foreach ($good_list as $k=>$v){
//            $info = $category->where('id',$v['category'])->field(['name'])->find();
//            $good_list[$k]['title'] = $info['name'].'--'.$v['title'];
            $info = $this->allCategory($v['category']);
            $good_list[$k]['title'] = $info['name'].':  '.$v['title'];
        }
        return $good_list;

    }

    /**
     * 取总分类
     */
    public function allCategory($id)
    {
        $category = new CategoryModel();

        $result = $category->where('id',$id)->field(['id','parent_id','name'])->find();

        if ($result['parent_id'] != 0){
            $paren = $this->allCategory($result['parent_id']);
            $result['name'] = $paren['name'].'--'.$result['name'];
        }

        return $result;

    }

    /**
     * 样本删除
     */
    public function sampdelete()
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
        //并删除关联表
        $relation = new GoodsrelationModel();
        $relation->where('relation_id',$ret['data']['id'])->where('type',2)->delete();
        return json($ret);
    }

    /**
     * 全部视频删除
     */
    public function allsampdelete()
    {
        $id = input('param.id');
        $obj = new GoodvideoModel();
        $idarr = collection($obj->field(['id'])->select())->toArray();
        foreach ($idarr as $k=>$v) {
            $ret = $obj->del($v['id']);
            //删除上传的文件
            if($ret['code']==1){//查询出img表循环删除
                if(file_exists($ret['data']['video_dir'])){
                    @unlink($ret['data']['video_dir']);
                }
            }
            //并删除关联表
            $relation = new GoodsrelationModel();
            $relation->where('relation_id',$ret['data']['id'])->where('type',2)->delete();
        }
        return json($ret);
    }

    /**
     * excel导出
     */
    public function export(&$param)
    {
        $obj = new GoodvideoModel();
        $data = collection($obj->field("id", "video_name", "click_num", "creater_time")->select());  //总数据
        
        $title =[ 'A'=>'ID','B'=>'视频名称','C'=>'点击量','D'=>'创建时间'];
        $this->export_xls($data,$title);
        exit();
    }

    public function export_xls($data,$title)
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

        $fileName = '视频报表'.date('Y-m-d H:i:s',time());

        $objWrite = \PHPExcel_IOFactory::createWriter($obj, 'Excel2007');
        $obj->getActiveSheet()->getColumnDimension('A')->setWidth(30);
        $obj->getActiveSheet()->getColumnDimension('B')->setWidth(30);
        $obj->getActiveSheet()->getColumnDimension('C')->setWidth(30);
        $obj->getActiveSheet()->getColumnDimension('D')->setWidth(30);
        $obj->getActiveSheet()->getColumnDimension('E')->setWidth(30);
        $obj->getActiveSheet()->getColumnDimension('F')->setWidth(30);
        $obj->getActiveSheet()->getColumnDimension('G')->setWidth(30);

        header('pragma:public');
        header("Content-Disposition:attachment;filename=$fileName.xls");
        $objWrite->save('php://output');exit;
    }


}