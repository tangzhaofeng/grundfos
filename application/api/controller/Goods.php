<?php

namespace app\api\controller;
use app\admin\model\CategoryModel;
use app\admin\model\GoodsrelationModel;
use app\admin\model\GoodssampleimgModel;
use app\admin\model\GoodssampleModel;
use app\admin\model\GoodvideoModel;
use think\Controller;
use think\Db;
use app\admin\model\GoodsModel;
use app\index\controller\Index;
use app\admin\model\MemberintegralModel;
use app\api\controller\Live;
use think\Exception;
header('Access-Control-Allow-Origin:*');
class Goods extends Controller
{
    private $config;

    function __construct()
    {
        parent::__construct();

       $type = session('type');
       $this->config = session('wxconfig_'.$type);
       $this->member_info = session($this->config['appid']."user_oauth");
    }

    public function aadd()
    {
        if (request()->isPost()){
            $param = input('post.');
            dump($param);
        }
    }

    /**
     * 获取产品类别
     *  return json
     */
    function getGoodsCategory() {

        $bu_id = $this->config['bu_id'];
//        $bu_id = input('bu_id');
        $category = Db('category');
        $param = input('param.');

        //判断是否是IND与RBS,这两个只需要2级搜索
        if ($bu_id == 1 || $bu_id == 4){
            if (empty($param['parent_id'])){
                $param['parent_id'] = 0;
                $group = $this->toSql($param['parent_id'],$bu_id);
                //  $group = $category->where('parent_id',$param['parent_id'])->where('group_id',$bu_id)->field("id,name")->order('id desc')->select();
            }else{
                $group = $this->toSql($param['parent_id'],$bu_id);
                //  $group = $category->where('parent_id',$param['parent_id'])->where('group_id',$bu_id)->field("id,name")->order('id desc')->select();
            }
         
        }else{

            if (!empty($param['parent_id'])){

            }else{
                $param['parent_id'] = 0;
            }

            $group = $category->where('parent_id',$param['parent_id'])->where('group_id',$bu_id)->field("id,name")->order('order_num desc')->select();
        }
        return json($group);
    }


    function toSql($parent_id,&$bu_id)
    {
        $category = Db('category');
        return $category->where('parent_id',$parent_id)
            ->where('group_id',$bu_id)
            ->field("id,name")
            ->order('order_num desc')
            ->select();
    }

    /**
     * 获取所有子级
     *  return json
     */
    public function _children($data, $parent_id, $isClear = True)
    {
        static $res = [];
        if ($isClear) {
            $res = [];
        }
        foreach ($data as $k => $v) {
            if ($v['parent_id'] == $parent_id) {
                $res[] = $v['id'];
                $this->_children($data, $v['id'], false);
            }
        }
        return $res;
    }


    /**
     * 获取产品列表
     *  return json
     */
    function getGoods() {
        $param = input('param.');
        //得到当前本这个号的bu_id
        $bu_id = $this->config['bu_id'];
        $size = 10;
        $where = [];
        if (isset($param['searchText']) && !empty($param['searchText'])) {
            // $where['title|desc'] = ['like', '%' . trim($param['searchText']) . '%'];
        }
        if (isset($param['category_id']) && !empty($param['category_id'])) {
            //先获取所有分类
            $category = Db('category')->where('group_id',$bu_id)->field("id,name,parent_id")->select();
            //递归查询该分类下的所有子级
            $category_chidren = $this->_children($category,$param['category_id']);
            //把当前父级也加入进去
            $category_chidren[] = $param['category_id'];
            //增加条件 用In查询
            $where['category'] = ['in',$category_chidren];
        }else{
            $where['group_id'] = $bu_id;
        }
        if(isset($param['is_group'])){
            $where['is_group'] = $param['is_group'];
        }
        if (isset($param['title'])) {
            $where['title'] = ['like','%'.$param['title'].'%'];
            // $where['keyword'] = ['like','%'.$param['title'].'%'];
        }
        $obj = new GoodsModel();
        $selectResult = $obj->getDataByWhere($where, $size);
        //$return['total'] = $obj->getDataCountByWhere($where);//总数据
        $return['rows'] = $selectResult;
        $return['page'] = $selectResult->render();
        $return['bu_id'] = $bu_id;
        $return['type'] = session('type');
        // $return['member_info'] =  $this->member_info;
        return json($return);
    }

    /**
     * 判断是否在本公众号
     **/
    public function isSearchOn(&$title,&$bu_id)
    {
        $obj = new GoodsModel();
        $where['title'] = ['like', '%' . $title . '%'];;
        $info = $obj->where($where)->find();
        if ($info['group_id'] == $bu_id){
            return $info;
        }
        return false;
    }

    /**
     * 返回带参数二维码
     */
    public function sendQrcode()
    {
        try{
        $goods_id = input('param.id');

        //得到带参数二维码
        $wx_config = $this->config;
        //   dump($wx_config);exit();
        $qrcode = new Index();
        $qrcode_img = $qrcode->qrCode($wx_config['appid'],$wx_config['appsecret'],session('type'),'2_'.$goods_id);
        
               return returninfos('1','获取二维码成功',$qrcode_img);
        }catch (Exception $e){
            return returninfos('0','系统错误');
        }
    }

    /**
     * 获取单个产品详情
     */
    public function getGoodInfo()
    {
        try{
        $goods_id = input('param.id');
        $obj = new GoodsModel();
        $info = $obj->where('id',$goods_id)->find();
               return returninfos('1','获取成功',$info);
        }catch (Exception $e){
            return returninfos('0','系统错误');
        }
    }

    /**
     * 读取是否多样本
     */
    public function sampleList()
    {
        // try{
            $good_sample = new GoodssampleModel();
            $good_sample_list = new GoodssampleimgModel();
            $relation = new GoodsrelationModel();
            $param = input('param.');
            //读取本产品关联的样本
            $relation_info = collection($relation->where('good_id',$param['good_id'])->where('type',1)->select())->toArray();
//读取样本列表是否只有单样本还是多样本
            $data = array();
            foreach ($relation_info as $k=>$v){
                $data[] = $good_sample->where('id',$v['relation_id'])->find();
            }

            foreach ($data as $k=>$v){
                $img = collection($good_sample_list->where('sample_id',$v['id'])->select())->toArray();
                $data[$k]['img'] = $img;
            }
            return json(['code'=>1,'msg'=>'获取成功','data'=>$data]);
        // }catch (Exception $e){
        //     return returninfos('0','系统错误');
        // }
    }

    /**
     * 获取产品的多视频
     */
    public function videoList()
    {
        try{
            $good = new GoodsModel();
            $param = input('param.');
            $video = new GoodvideoModel();
            $relation = new GoodsrelationModel();
            //读取本产品关联的样本
            $relation_info = collection($relation->where('good_id',$param['id'])->where('type',2)->select())->toArray();
            $good_info = $good->find($param['id']);
//读取样本列表是否只有单样本还是多样本
            $data = array();
            foreach ($relation_info as $k=>$v){
                $data[$k] = $video->where('id',$v['relation_id'])->find();
                $data[$k]['img'] = $good_info['poster'];
            }
            return json(['code'=>1,'msg'=>'获取成功','data'=>$data]);
        }catch (Exception $e){
            return returninfos('0','系统错误');
        }
    }


    /**
     * 判断是否有多高样本
     */
    public function isSurplus($good_id,$type)
    {
        $good = new GoodsModel();
        //增加产品的点击数量
        // dump('/glf/public/static/book/'.$type.'/'.$good_id);exit();
        //查找属于这个type的文件夹与下属的图片数量
        $dirArray = array();
        $files = array();
        $dir = '/www//html/jue/glf/public/static/book/'.$type.'/'.$good_id.'/';
        $i = $this->dir($dir);
        return json(['code'=>1,'data'=>$i,'msg'=>'获取成功']);
    }

    public function dir($dir)
    {
        $dirar = array();
        if(@$handle = opendir($dir)) { //注意这里要加一个@，不然会有warning错误提示：）
            
            while(($file = readdir($handle)) !== false) {
               
                if($file != ".." && $file != ".") { //排除根目录；
                //  dump($file);
                    if(is_dir($dir."/".$file)) { //如果是子文件夹，就进行递归
                        // $dirar[] = $this->dir($dir."/".$file);
                        $dirar[] = mb_convert_encoding($file,'UTF-8','gbk');
                    }else{
                        $dirar = '';
                    }
                }
            }
            closedir($handle);
            return $dirar;
        }
    }

    /**
     * 获取单个产品详情
     */
    public function getSum()
    {
        $param = input('param.');
        $good = new GoodsModel();
        $good_id = input('param.id');
        //增加产品的点击数量
        $info = $good->addClick($good_id);
        $type = input('param.type');

        //查找属于这个type的文件夹与下属的图片数量
        if (isset($param['surplus'])){
            $dir = '/www//html/jue/glf/public/static/book/'.$type.'/'.$good_id.'/'.$param['surplus'].'/';
        }else{
            $dir = '/www//html/jue/glf/public/static/book/'.$type.'/'.$good_id.'/';
        }
        if (!is_dir($dir)){
            return returninfos('0');
        }
        $dirArray = array();
        if (false != ($handle = opendir ($dir))) {
            $i=0;
            while ( false !== ($file = readdir ( $handle )) ) {
                 if ($file != "." && $file != "..") {
                    $dirArray[$i]=$file;
                    $i++;
                 }
            }
            //关闭句柄
            closedir ( $handle );
        }
         return returninfos('1',count($dirArray));
    }


    public function addsecre() {
        $param = input('post.');
        $member_id = $this->member_info['id'];
        $addobj = new Live();
         //+添加积分
        $Memberintegral = new MemberintegralModel();
        // dump($member_id);
        $is_add = $Memberintegral->where([
            'title_id' => $param['id'],
            'member_id'=> $member_id,
            'title' => 8
        ])->find();
         if (empty($is_add)){
            $result = $addobj->memberAddIntegral(8,20,$member_id,$param['id']);
        }
        //-添加积分
        
        //+添加点击量
        Db::table('xk_good_video')->where('id', $param['id'])->setInc('click_num');
        //-添加点击量
        return ;
    }
}
?>