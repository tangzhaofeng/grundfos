<?php
/**
 * Created by PhpStorm.
 * User: juplus-06
 * Date: 2018/4/2
 * Time: 16:12
 */

namespace app\admin\controller;


use app\admin\model\AppsecretModel;
use app\admin\model\BuModel;
use app\admin\model\CompanyModel;
use app\admin\model\CompanyworkModel;
use app\admin\model\GroupingModel;
use app\admin\model\MembergroupModel;
use app\admin\model\MemberModel;
use app\admin\model\MemberopenunionModel;
use app\admin\model\MemberintegralModel;
use think\Db;

class Member extends Base
{
    public function index()
    {
        $obj = new GroupingModel();
        $grouping = collection($obj->all(['status'=>1,'bu_id'=>input('param.bu_id')]))->toArray();
//        $param = input('param.');
//
//        $size = 10;
//        $where = [];
//
//        $obj = new MemberModel();
//
//        $selectResult = $obj->getBuBywhere($where, $size);
//
//        $return['total'] = $obj->getAllUsers($where);  //总数据
//
//        $return['rows'] = $selectResult;
//        $return['page'] = $selectResult->render();
        $this->assign('bu_id',input('param.bu_id'));
        $this->assign('grouping',$grouping);
        return $this->fetch();
    }

    public function index2()
    {
        $param = input('param.');

        $bu_id = $param['bu_id'];
        $where = [];
        $size = 10;
        $obj = new MemberModel();
        $data = '';
        if (isset($param['date'])){
            $data = $param['date'];
            $where['date'] = $param['date'];
        }
        if (isset($param['date'])){
            $where['enddate'] = date("Y-m-d",strtotime($param["date"]." +1 month"));
        }
        //查询未注册与已注册的
        $isonisreg = 0;//未关注注册人数
        $isregiand = 0;//注册并关注人数

        $isnotisreg = 0;//未注册

        //分步查询先查询grouping_id下有多少用户
        if (empty($param['grouping_id'])){
            $grouping_id = 0;
            $this->assign('grouping_id',0);
            $arrmember = $obj->getArrMemberId($bu_id,$where);
        }else{
            $grouping_id = $param['grouping_id'];
            $this->assign('grouping_id',$grouping_id);
            $arrmember = $this->getGroupMember($grouping_id);
        }

        $member_unid = new MemberopenunionModel();
        $appsecret = new AppsecretModel();
        foreach ($arrmember as $k=>$v){
            //查询用户是否关注了公众号
//            $config = $appsecret->where('id',$param['bu_id'])->find();
//            $index = new \app\index\controller\Index();
//            $status = $index->isFollow($v['member_id'], $config);
//            if ($status == 0) {
//                $member_unid->save(['is_cancel'=>0],['unionid'=>$v['unionid'],'bu_id'=>$param['bu_id']]);
//            }else{
//                $member_unid->save(['is_cancel'=>1],['unionid'=>$v['unionid'],'bu_id'=>$param['bu_id']]);
//            }

            if($v['name']){
                $isonisreg++;
                $result = $member_unid->where('bu_id',$param['bu_id'])
                        ->where('unionid',$v['unionid'])->value('is_cancel');
                if($result == 1){
                    $isregiand++;
                }
            }else{
                $isnotisreg++;
            }
        }

        if (isset($param['searchText'])){
            $where['tel'] = $param['searchText'];
            $this->assign('searchText',$param['searchText']);
        }

        $selectResult = $obj->getBuBywhere($where, $size,$arrmember);
        //dump($selectResult->items());exit();
        $return['total'] = count($arrmember);  //总数据
        $return['rows'] = $selectResult;
        
        
        $tempdata = $obj->getGroupBu(isset($param['grouping_id']) ? $param['grouping_id'] : 0,$param['bu_id'],isset($param['date']) ? $param['date'] : '');
        $isonisreg = count($tempdata);
        if (!$isonisreg) {
            $return['page'] = '';
        } else {
            $return['page'] = $selectResult->render();
        }
        $this->assign('isonisreg',$isonisreg);
        $this->assign('isnotisreg',$isnotisreg);
        $this->assign('isregiand',$isregiand);
        $this->assign('date',$data);
        $this->assign('return',$return);
        $this->assign('bu_id',$bu_id);
        return $this->fetch();
    }

    /**
     * 获取分组下面的所有用户
     */
    public function getGroupMember($grouping_id = '')
    {
        $group = new MembergroupModel();
        //获取到所有的用户id
        return $group->getMember($grouping_id);

    }

    /**
     *修改
     */
    public function edit()
    {
        $obj = new MemberModel();
        $type = new CompanyModel();
        $work = new CompanyworkModel();
        $id = input('param.id');

        if(request()->isPost()){

            $param = input('post.');
            return json($obj->adminedit($param));

        }else{

            //用户数据
            $member_data = $obj->where('id',$id)->find()->toArray();
            //公司类型数据
            $typelist = collection($type->all(['bu_id'=>$member_data['is_bu_id']]))->toArray();
            //职位数据
            $worklist= collection($work->all())->toArray();

            //组合公司与职位
            $list = array();
            $i = 0;
            $o = 0;
            foreach ($typelist as $k => $v){
                foreach ($worklist as $key => $val){
                    if ($v['id'] == $val['company_id']){
                        $list[$o]['type_name'] = $v['type_name'];
                        $list[$o]['id'] = $v['id'];
                        $list[$o][$i]['work_type_name'] = $val['work_type_name'];
                        $list[$o][$i]['id'] = $val['id'];
                    }else{
                        $list[$o]['type_name'] = $v['type_name'];
                        $list[$o]['id'] = $v['id'];
                    }
                    $i++;
                }
                $o++;
            }

            $this->assign('list',$list);
            $this->assign('member_data',$member_data);
            return $this->fetch();
        }
    }

    /**
     * 用户单独发送
     */
    public function send()
    {
        if (request()->isPost()){
            $param = input('post.');

            $appse = new AppsecretModel();
            //获取微信配置
            $config = $appse->where('bu_id',$param['bu_id'])->find();

            $mess = new Messagesend();
            if ($param['type'] == 1){
                //对每个用户就行发送
                $param['content'] = $_POST['content'];
                $member_id[0]['member_id'] = $param['id'];
                $info = $mess->sendText($member_id,$param,$config);
                $param['media'] = '';
            }else{//发送图片
                $reulst = $mess->sendMedia($param['bu_id'],$param['id'],$param['media']);
            }
            return json(['code'=>1,'msg'=>'发送成功','data'=>'']);
        }else{
            $param = input('param.');
            $this->assign('bu_id',$param['bu_id']);
            $this->assign('id',$param['id']);
            return $this->fetch();
        }
    }

    /**
     * excel导出
     */
    public function export()
    {
        $param = input('param.');
        if (isset($param['export']) &&  $param['export']==true) {

            $obj = new MemberModel();
            $bu_name = BuModel::where('id',$param['bu_id'])->value('bu_name');//获取到当前bu名字
            $type = new CompanyModel();
            $bu_type = $type->where('bu_id',$param['bu_id'])->select();//得到当前bu所有的公司类型

            if (!isset($param['date'])){
                $param['date'] = '';
            }else{
                // $param['date'] = $param['date'];
            }

            $data = $obj->getGroupBu($param['grouping_id'],$param['bu_id'],$param['date']);

            // $array = array();
            // foreach ($bu_type as $kk=>$vv){
            //     $array[$kk] = $vv['type_name'];
            // }
            //对比公司类型,如果不是则剔除当前类型
            // foreach ($data as $k=>$v){
            //     if (!in_array($v['type_name'],$array)){
            //         $data[$k]['type_name'] = '';
            //         $data[$k]['work_type_name'] = '';
            //     }
            // }
            
            $title =[ 'A'=>'用户姓名','B'=>'电话','C'=>'邮箱','D'=>'公司名','E'=>'公司类型','F'=>'职位','G'=>'积分','H'=>'注册时间'];
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

        $fileName = $bu_name.'用户数据'.date('Y-m-d H:i:s',time());

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

    //积分视图
    public function sorce()
    {
        $param = input('param.');
        $this->integraltype = [
            '1' => '注册会员',
            '2' => '报名活动',
            '3' => '活动签到',
            '4' => '邀请好友',
            '5' => '收藏样本',
            '6' => '分享赢取',
            '7' => '观看直播',
            '8' => '观看视频'
        ];
        //获取数据
        $model = new MemberintegralModel();
        $selectResult['list'] = $model->where('member_id', $param['member_id'])->order('create_time Desc')->paginate(
            10,
            false,
            [
                'query' => [
                    'member_id' => $param['member_id']
                ]
            ]
        );
        $sorce = Db::table('xk_member')->where('id', $param['member_id'])->value('integral');
        $page = $selectResult['list']->render();
        if ($selectResult['list']){
            foreach ($selectResult['list'] as $k=>$v){
                if (mb_strlen($selectResult['list'][$k]['title']) == 1) {
                    $selectResult['list'][$k]['title'] = $this->integraltype[$v['title']];
                }
            }
        }
        $this->assign('list', $selectResult['list']);
        $this->assign('page', $page);
        $this->assign('sorce', $sorce);
        $this->assign('name', $param['name']);
        $this->assign('member_id', $param['member_id']);
        return $this->fetch();
    }

    //添加积分
    public function addSorce() {
        if (request()->isPost()) {
            try {
                DB::startTrans();
                $param = input('post.');
                //加减
                $action = $param['deal'] == 1 ? 1 : 2;
                $member_id = $param['member_id'];
                $MemberModel = new MemberModel;
                $MemberintegralModel = new MemberintegralModel;
                //查询现在积分
                $oldsorce = $MemberModel->where('id', $member_id)->value('integral');
                //需要操作的积分
                $sorce = $param['sorce'];
                //操作理由
                $season = $param['season'];
                //添加/减少积分
                $nowsorce = $param['deal'] == 1 ? $oldsorce + $sorce : $oldsorce - $sorce;
                $MemberModel->where('id', $member_id)->update(['integral' => $nowsorce]);
                //添加明细
                $MemberintegralModel->insert([
                    'member_id' => $member_id,
                    'title' => $season,
                    'action' => $action,
                    'now' => $nowsorce,
                    'integral' => $sorce,
                    'create_time' => date('Y-m-d H:i:s', time()),
                ]);
                DB::commit();
                return json(['code' => 1]);
            } catch (\Exception $e) {
                DB::rollback();
                return json(['code' => 0, 'msg' => $e->getLine()]);
            }
            
        } else {
            $param = input('param.');
            $this->assign('param', $param);
            return $this->fetch();
        }
    }
    
    public function userTimeEvent()
    {
        $id = input('param.id');
        $grouping = new MembergroupModel;
        $member = new MemberModel;
        $res = $grouping->alias('mg')
            ->join('xk_grouping g', 'g.id = mg.group_id')
            ->where('mg.member_id', $id)
            ->where('mg.creater_time', 'not null')
            ->field(['g.grouping_name', 'mg.creater_time', 'mg.bu_id'])
            ->order('mg.creater_time');
        if (isset($_POST['bu_id'])) {
            $res = collection($res->whereIn('mg.bu_id', $_POST['bu_id'])->select())->toArray();
        } else {
            $res = collection($res->select())->toArray();
        }
        $create_time = $member->alias('m')
        ->join('xk_company_work_type w','w.id = m.company_work_type_id', 'Left')
        ->join('xk_company_type v','v.id = m.company_id', 'Left')
        ->where('m.id', $id)
        ->field(['m.*', 'w.work_type_name','v.type_name'])
        ->find();
        $this->assign('events', $res);
        $this->assign('info', $create_time);
        $this->assign('bu_ids', ['' , 'RBS', 'CBS', 'WU', 'IND']);
        return $this->fetch();
    }
}