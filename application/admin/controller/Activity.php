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
use app\admin\model\CompanyModel;
use app\admin\model\CompanyworkModel;
use app\admin\model\GroupingModel;
use app\admin\model\MemberModel;
use app\index\controller\Redpack;
use app\index\controller\Group;

class Activity extends Base
{
    /**
     * 列表页
     */
    public function index()
    {
        $param = input('param.');

        $size = 10;
        $where = [];

        $obj = new ActivityModel();
        $bu = new BuModel();

        $list = array();
        $selectResult = $obj->getBuBywhere($where, $size);
//        foreach ($selectResult as $k=>$v){
//            $list[$k] = $v;
//            if ($k == 'bu_id'){
//               $bu_id = explode(',',$v['bu_id']);
//
//               $bu_data = $bu->where(['id'=>['in',$bu_id]])->select();
//
//                $buname = '';
//               foreach ($bu_data as $key=>$val){
//                    $buname .= $val['bu_name']." ";
//               }
//                $list[$k]['bu_name'] = $buname;
//            }
//        }

        $bu = collection($bu->all())->toArray();

        $return['total'] = $obj->getAllUsers($where);  //总数据

        $return['rows'] = $selectResult;

        $return['page'] = $selectResult->render();

        $this->assign('bu',$bu);
        $this->assign('return',$return);
        return $this->fetch();
    }

    /**
     * 不同bu的列表
     */
    public function buList()
    {
        //得到不同的bu
        $bu_id = input('param.bu_id');

        $size = 10;
        $where = ['bu_id'=>$bu_id];

        $obj = new ActivityModel();
        $bu = new BuModel();

        $selectResult = $obj->getBuBywhere($where, $size);

        $bu = collection($bu->all())->toArray();

        $return['total'] = $obj->getAllUsers($where);  //总数据

        $return['rows'] = $selectResult;

        $return['page'] = $selectResult->render();

        $this->assign('bu',$bu);
        $this->assign('return',$return);
        return $this->fetch('activity/index');
    }

    /**
     * 修改
     */
    public function edit()
    {
        $obj = new ActivityModel();
        $bu = new BuModel();
        $app_secret = new AppsecretModel();
        $id = input('param.id');

        if(request()->isPost()){
            $content = $_POST['content'];
            $param = input('post.');
            $list = array();
            foreach ($param as $k => $v){
                if (is_array($v)){
                    $list[$k] = implode(",",$v);
                }else{
                    $list[$k] = $v;
                }

                if($k == 'time' || $k == 'time_end'){
                    $new = strtotime($v);
                    $list[$k] = date('Y-m-d H:i',$new);
                }
            }
            //    dump($list);exit();
            unset($list['content1']);
            $list['content'] = $content;
            $info = $obj->edit($list);

            if ($info['code'] == 1){
                //添加私密url链接
                $type = $app_secret->where('bu_id',$param['bu_id'][0])->value('type');

                if ($param['line'] == 1){
                    $url_open = 'https://www.juplus.cn/glf/index/index?type='.$type.'&view=/activity/activityDetail_xia.html&activity_id=';
                }else{
                    $url_open = 'https://www.juplus.cn/glf/index/index?type='.$type.'&view=/activity/activityDetail_shang.html&activity_id=';
                }
                if ($param['is_open'] == 2){

                    //添加私密url链接
                    $url = $url_open.$param['id'].'&is_open=1';
                    $obj->where('id',input('post.id'))->Update(['open_url'=>$url]);
                }else{
                    $url = $url_open.$param['id'];
                    $obj->where('id',input('post.id'))->Update(['url'=>$url]);
                    $obj->where('id',input('post.id'))->Update(['open_url'=>'']);
                }
            }
            return json($info);
        }else{
            $type = new CompanyModel();
            $work = new CompanyworkModel();
            $bulist = collection($bu->all())->toArray();
            $arr = array();

            $a = 0;
            foreach ($bulist as $k => $v){
                $bulist[$k]['type'] = [];
                foreach ($bulist[$k]['type'] as $key => $value){
                    $bulist[$k]['type'][$key]['work'] = [];
                }
            }

            $this->assign('bulist',$bulist);
            $this->assign('activity',$obj->where('id',$id)->find());

            return $this->fetch();
        }
    }

    /**
     * 新增
     */
    public function add()
    {
        $obj = new ActivityModel();
        $bu = new BuModel();
        $app_secret = new AppsecretModel();
        if (request()->isPost()){
            $param = input('post.');
            $content = $_POST['content'];
            $list = array();
            foreach ($param as $k => $v){
                if (is_array($v)){
                    $list[$k] = implode(",",$v);
                }else{
                    $list[$k] = $v;
                }
                if($k == 'time' || $k == 'time_end'){
                    $new = strtotime($v);
                    $v = date('Y-m-d H:i',$new);
                }
            }
            unset($list['content1']);
            $list['content'] = $content;
            $info = $obj->inster($list);
                if ($info['code'] == 1){

                    $type = $app_secret->where('bu_id',$param['bu_id'][0])->value('type');

                    if ($param['line'] == 1){
                        $url_open = 'https://www.juplus.cn/glf/index/index?type='.$type.'&view=/activity/activityDetail_xia.html&activity_id=';
                    }else{
                        $url_open = 'https://www.juplus.cn/glf/index/index?type='.$type.'&view=/activity/activityDetail_shang.html&activity_id=';
                    }


                    //添加私密url链接

                    if ($param['is_open'] == 2){
                        $url = $url_open.$info['faq_id'].'&is_open=1';
                        $obj->where('id',$info['faq_id'])->Update(['open_url'=>$url]);
                    }else{
                        $url = $url_open.$info['faq_id'];
                        $obj->where('id',$info['faq_id'])->Update(['url'=>$url]);
                    }

                }


            //如果活动创建成果,微信新增标签
            if ($info['code'] == 1) {
                //获取微信信息
                $config = $app_secret->where('bu_id',$param['bu_id'][0])->find();

                //添加分组
                $group = new Group();
                $in = $group->createGroup($config,$param['title']);
                if (isset($in['group']['id'])){
                    $grup = new GroupingModel();
                    $grup->insert(['wx_id'=>$in['group']["id"],
                        'grouping_name'=>$param['title'],
                        'creater_time'=>date('Y-m-d H:i:s',time()),
                        'bu_id'=>$param['bu_id'][0],
                        'activity_id'=>$info['faq_id']
                    ]);
                }
            }

            return json($info);
        }else{
            $type = new CompanyModel();
            $work = new CompanyworkModel();
            $bulist = collection($bu->all())->toArray();

            // 目前bu单选注释多选
            foreach ($bulist as $k => $v){
                $bulist[$k]['type'] = [];
                foreach ($bulist[$k]['type'] as $key => $value){
                    $bulist[$k]['type'][$key]['work'] = [];
                }
            }

            $this->assign('bulist',$bulist);
            return $this->fetch();
        }
    }

    /**
     * 删除
     */
    public function delete()
    {
        $id =  $id = input('param.id');
        $obj = new ActivityModel();
        $activity = $obj->where('id',$id)->find();
        //如果删除成功也删除微信中的标签
        $info = $obj->del($id);
        if ($info['code'] == 1){
            $app_secret = new AppsecretModel();
            $config = $app_secret->where('bu_id',$activity['bu_id'])->find();//获得微信配置

            //获取微信标签id,并删除标签
            $groupModel = new GroupingModel();
            $wx_id = $groupModel->where('activity_id',$id)->find();
            //删除标签表
            $data = $groupModel->del($wx_id['id']);
            if ($data['code'] == 1){
                $group = new Group();
                $group->deleteGroup($config,$wx_id['wx_id']);
            }

        }
        
        return json($info);
    }

    /**
     * 线上分享删除
     */
    public function infoDelete()
    {
        $param = input('param.');
        //查询出当前上传图片
        $activty_share = new ActivityshareModel();
        $data = collection($activty_share->where('activity_id',$param['activity_id'])->where('member_id',$param['member_id'])->select())->toArray();
        $result = $activty_share->del($param);
        //循环删除图片
        $dir = '/data/www/jue/glf';
        foreach($data as $k=>$v){
            $img = explode(',',$v['img']);
            foreach ($img as $kk=>$vv){
                unlink($dir.$vv);
            }
        }
        return json($result);
    }

    /**
     * 查看详情
     */
    public function info()
    {
        $bu = new BuModel();
        $activity = new ActivityModel();
        $id = input('param.id');

        //根本id查询活动是否是线上或线下
        $line = $activity->where('id',$id)->value('line');

        if ($line == 1){
            //线下活动
            $param = input('param.');

            $this->export($param);

            $size = 10;
            $where = ['activity_id'=>$id];

            $obj = new ActivitymemberModel();
            $selectResult = $obj->getBuBywhere($where, $size);
            $return['total'] = $obj->getAllUsers($where);  //总数据
            $return['rows'] = $selectResult;
            $return['page'] = $selectResult->render();

            $bulist = collection($bu->all())->toArray();
            //获取签到的总人数
            $on_sign = $obj->onSign($id,2);
            //获取报名总人数
            $not_sign = $obj->onSign($id,1);

            $this->assign('on_sign',$on_sign);
            $this->assign('not_sign',$not_sign);
            $this->assign('return',$return);
            $this->assign('bulist',$bulist);
            $this->assign('activity',$activity->where('id',$id)->find());

            return $this->fetch();

        }else{
            //线上活动
            $param = input('param.');

            $this->export($param);

            $size = 10;
            $where = ['activity_id'=>$id];

            $obj = new ActivityshareModel();
            $selectResult = $obj->getBuBywhere($where, $size);
            $return['total'] = $obj->getAllUsers($where);  //总数据
            $return['rows'] = $selectResult;
            $return['page'] = $selectResult->render();

            $bulist = collection($bu->all())->toArray();

            $this->assign('return',$return);
            $this->assign('bulist',$bulist);
            $this->assign('activity',$activity->where('id',$id)->find());
         
            return $this->fetch('activity/lineinfo');
        }

    }

    /**
     * 发送红包
     */
    public function sendPacket()
    {
        if(request()->isPost()){
            $redpack = new Redpack();
            $member = new MemberModel();
            $activity = new ActivityModel();
            $share = new ActivityshareModel();

            //查询该分享的是否已经发送红包
            $share_id = input('post.share_id');
            $is_share = $share->where('id',$share_id)->value('status');
            if($is_share == 2){
                return returninfos(2,'红包已发送');
            }

            //先验证次活动是否是RBS公众号发起的,诺无则不能发送红包
            $activity_id = input('post.activity_id');

            $is_rbs = $activity->where('id',$activity_id)->value('bu_id');

            $activity_name = $activity->where('id',$activity_id)->value('title');

            if ($is_rbs != 1){
                return returninfos(2,'此活动不是rbs部门'); //表示此活动不是rbs发出
            }

            //查询用户 对openid表进行匹配 获取rbs的openid
            $member_id = input('post.member_id');
            $param['id'] = $member_id;
            $param['bu_id'] = 1;

            $openid = $member->getOpenid($param);
            if(!$openid){
                return returninfos(2,'openid空'); //表示没有rbs的openid
            }
            $send = new Messagesend();//发送推文
            $config = Db('bu_app_secret')->where('bu_id',1)->find();
            $type = 1;
            $sedmember[] = ['member_id'=>$member_id];
            $money = $activity->where('id',$activity_id)->value('money');//获取红包额度
            $info = $redpack->index($openid['openid'],$money);//发送红包
            if($info['err_code'] == 'SUCCESS'){
                $param['content'] = $activity_name.':上传图片已通过审核，请注意查收格兰富的微信红包';
                $send->sendText($sedmember,$param,$config,$type);
                return $share->editStart(['id'=>$share_id]);
            }else{
                return returninfos(2,'发送红包失败'.json_encode($info));
            }


        }
    }


    /**
     * excel导出
     */
    public function export(&$param)
    {
        if (isset($param['export']) &&  $param['export']==true) {
            $obj = new ActivitymemberModel();
            $bu_id = ActivityModel::where('id',$param['id'])->value('bu_id');
            $line = ActivityModel::where('id',$param['id'])->value('line');
            $asm = new ActivityshareModel;
            if ($line == 2) {
                $data = collection($asm->alias('t')
                ->join('xk_member m','m.id = t.member_id')
                ->join('xk_company_work_type w','w.id = m.company_work_type_id','Left')
                ->join('xk_company_type v','v.id = m.company_id','Left')
                ->where('t.activity_id', $param['id'])
                ->field(['m.name','m.tel','m.email','w.work_type_name','v.type_name','t.create_time','t.send_time'])
                ->select()
                )->toArray();
                $title =['A'=>'用户姓名','B'=>'电话','C'=>'邮箱','D'=>'公司名','E'=>'职位','F'=>'上传时间','G'=>'发送时间'];
            } else {
                $data = $obj->getAcMember($param['id'],$bu_id);
                $title =[ 'A'=>'用户姓名','B'=>'电话','C'=>'邮箱', '1' => '公司类型','D'=>'公司名','E'=>'职位','F'=>'openid','G'=>'报名时间','H'=>'签到时间'];
            }
            $this->export_xls($data,$title);
            exit();
        }
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

        $fileName = '活动报名名单'.date('Y-m-d H:i:s',time());

        $objWrite = \PHPExcel_IOFactory::createWriter($obj, 'Excel2007');
        $obj->getActiveSheet()->getColumnDimension('A')->setWidth(30);
        $obj->getActiveSheet()->getColumnDimension('B')->setWidth(30);
        $obj->getActiveSheet()->getColumnDimension('C')->setWidth(30);
        $obj->getActiveSheet()->getColumnDimension('D')->setWidth(30);
        $obj->getActiveSheet()->getColumnDimension('E')->setWidth(30);
        $obj->getActiveSheet()->getColumnDimension('F')->setWidth(30);
        $obj->getActiveSheet()->getColumnDimension('G')->setWidth(30);
        $obj->getActiveSheet()->getColumnDimension('H')->setWidth(30);

        header('pragma:public');
        header("Content-Disposition:attachment;filename=$fileName.xls");
        $objWrite->save('php://output');exit;
    }


}