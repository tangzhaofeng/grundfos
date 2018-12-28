<?php
/**
 * Created by PhpStorm.
 * User: juplus-06
 * Date: 2018/4/2
 * Time: 16:12
 */

namespace app\admin\controller;


use app\admin\model\BuModel;
use app\admin\model\CompanyModel;
use app\admin\model\CompanyworkModel;
use app\admin\model\MemberquestionnaireinfotypeModel;
use app\admin\model\MemberQuestionnaireModel;
use app\admin\model\QuestionnaireinfoModel;
use app\admin\model\QuestionnaireinfotypeModel;
use app\admin\model\QuestionnaireModel;
use think\Db;
use think\Exception;

class Questionnaire extends Base
{
    public function index()
    {
        $param = input('param.');

        $size = 10;
        $where = [];

        $obj = new QuestionnaireModel();

        $selectResult = $obj->getBuBywhere($where, $size);

        $return['total'] = $obj->getAllUsers($where);  //总数据

        $return['rows'] = $selectResult;
        $return['page'] = $selectResult->render();
        
        $this->assign('return', $return);
        return $this->fetch();
    }

    public function edit()
    {
        $type = new CompanyModel();
        $bu = new BuModel();
        $work = new CompanyworkModel();

        $id = input('param.id');
        if (request()->isPost()) {
            $param = input('post.');
            return json($work->edit($param));
        } else {
            $bulist = collection($bu->all())->toArray();

            $typelist = collection($type->all())->toArray();

            $list = array();
            $i = 0;
            $o = 0;
            foreach ($bulist as $k => $v) {
                foreach ($typelist as $key => $val) {
                    if ($v['id'] == $val['bu_id']) {
                        $list[$o]['bu_name'] = $v['bu_name'];
                        $list[$o]['id'] = $v['id'];
                        $list[$o][$i]['type_name'] = $val['type_name'];
                        $list[$o][$i]['id'] = $val['id'];
                    } else {
                        $list[$o]['bu_name'] = $v['bu_name'];
                        $list[$o]['id'] = $v['id'];
                    }
                    $i++;
                }
                $o++;
            }
            $this->assign('list', $list);

            $this->assign('work', $work->where('id', $id)->find()->toArray());
            return $this->fetch();
        }
    }

    public function add()
    {
        $quest = new QuestionnaireModel();
        $quest_info = new QuestionnaireinfoModel();
        $quest_info_type = new QuestionnaireinfotypeModel();
        $bu = new BuModel();
        $work = new CompanyworkModel();
        if (request()->isPost()) {
//            try{
            Db::startTrans();
            $param = input('post.');
            //添加主表
            $reulte = $quest->inster([
                'description' => $param['description'],
                'success_info' => $param['success_info'],
                'bu_id' => $param['bu_id'],
                'title' => $param['title'],
                'creater_time' => date('Y-m-d h:i:s', time())
            ]);
            //如果主表添加成功,在添加副表
            if ($reulte['code'] == 1) {
                $info_id = array();
                $sum = $param['info_sum'];//获取一共有几个问题

                for ($a = 1; $a <= $sum; $a++) {//就行每个问题插入
                    $type = $param['type' . $a];
                    $info_name = $param['info_name' . $a];
                    $is_select = $param['is_select' . $a];
                    $info = $quest_info->insert([
                        'questionnaire_id' => $reulte['id'],
                        'type' => $type,
                        'info_name' => $info_name,
                        'is_select' => $is_select
                    ], '', true);
//                        if ($info['code'] == 1){
                    $info_id[$a] = $info;
//                        }
                }

                for ($a = 1; $a <= $sum; $a++) {
                    if ($param['type' . $a] != 3) {
                        foreach ($param['content' . $a] as $k => $v) {
                            $quest_info_type->insert([
                                'questionnaire_info_id' => $info_id[$a],
                                'content' => $v
                            ]);
                        }
                    }
                }

            }


//                dump($param);exit();
//                $info = $work->insert($param);
            Db::commit();
            return json(['code' => 1, 'data' => '', 'msg' => '添加成功']);
//            }catch (Exception $e){
//                return '错误';
//                Db::rollback();
//            }

        } else {
            $bulist = collection($bu->all())->toArray();

            $this->assign('bulist', $bulist);
            return $this->fetch();
        }
    }

    /**
     * 删除
     */
    public function delete()
    {
        $id = $id = input('param.id');
        $obj = new QuestionnaireModel();
        $activity = $obj->where('id', $id)->find();
        //如果删除成功也删除微信中的标签
        $info = $obj->del($id);
        return json($info);

    }

        public function info()
    {
        //获取id
        $id = input('param.id');

        //获取问卷情况
        $quest = new QuestionnaireModel();

        $zhu = $quest->where('id', $id)->find();

        $this->assign('zhu', $zhu);
        $this->assign('id', $id);
        return $this->fetch();

    }

    public function getinfo()
    {
        $id = input('param.id');

        //获取问卷情况
        $quest = new QuestionnaireModel();
        $quest_info = new QuestionnaireinfoModel();
        $quest_info_type = new QuestionnaireinfotypeModel();
        $member_quest_info_type = new MemberquestionnaireinfotypeModel();

        $zhu = $quest->where('id', $id)->find();
        $info = $quest_info->getInfo($zhu['id']);//获取所有info副表数据

        //对用户的提交进行百分之分析
        $data = array();

        foreach ($info as $k => $v) {
            $type_info = $quest_info_type->getType($v['id']);
            $info[$k]['info'] = $type_info;//添加答案

            $count_sum = '';//添加对此答案的选择数

            foreach ($type_info as $key => $val) {
                $sum = $member_quest_info_type->getSum($val['id']);
                $info[$k]['info'][$key]['type_sum'] = $sum;
                $count_sum += $sum;
            }
            $info[$k]['count'] = $count_sum;
            foreach ($info[$k]['info'] as $ke => $va) {//计算百分比
                if ($count_sum) {
                    if ($va['type_sum']) {
//                                        var_dump($va['type_sum']);exit();
                        $info[$k]['info'][$ke]['value'] = round($va['type_sum'] / $count_sum * 100);
                    } else {
                        $info[$k]['info'][$ke]['value'] = 0;
                    }
                }
            }
        }

        return json($info);
    }

    /**
     * 查看全部用户回答的文本问题
     */
    public function text()
    {
        $quest = new QuestionnaireModel();

        $id = input('param.id');

        $size = 10;

        $selectResult = $quest->infoGetBuBywhere($id, $size);

        $return['total'] = $quest->infoGetAllUsers($id);  //总数据

        $return['rows'] = $selectResult;
        $return['page'] = $selectResult->render();
//        dump($return);exit();
        $this->assign('return', $return);
//        $data = $quest->getText($id);
        return $this->fetch('/questionnaire/content');
    }

    /**
     * 查看全部用户的回答
     */
    public function member_info()
    {
        $param = input('param.');

        $size = 10;
        $where = [];

        $this->export($param);

        if ($param['id']){
            $where['id'] = $param['id'];
        }

        $obj = new MemberQuestionnaireModel();

        $selectResult = $obj->getBuBywhere($where, $size);
//        dump($selectResult->items()[0]);exit();
        $return['total'] = $obj->getAllUsers($where);  //总数据

        $return['rows'] = $selectResult;
        $return['page'] = $selectResult->render();

        $this->assign('id',$param['id']);
        $this->assign('return', $return);
        return $this->fetch('/questionnaire/memberinfo');
    }

    /**
     * 查看单个用户的填写
     */
    public function answer()
    {
        $member_quest = new MemberQuestionnaireModel();
        $quest = new QuestionnaireModel();
        $quest_info = new QuestionnaireinfoModel();
        $quest_info_type = new QuestionnaireinfotypeModel();
        $id = input('param.id');//回答的记录id
        $member_id = input('param.member_id');//用户id

        $data = $member_quest->getMemberAnswer($id);

        $questionnaire_id = $data[0]['questionnaire_id'];//得到问卷主表id

        $quest = $quest->getInfo($questionnaire_id);//获取主表数据

        $quest_info = $quest_info->getInfo($quest['id']);//获取副表info数据

        foreach ($quest_info as $key => $val) {//组合数据
            $quest_info[$key]['type_info'] = $quest_info_type->getType($val['id']);

            foreach ($quest_info[$key]['type_info'] as $e => $a) {
                foreach ($data as $ke => $va) {
                    if ($va['questionnaire_info_type_id'] == $a['id']) {
                        $quest_info[$key]['type_info'][$e]['is_on'] = 1;
                    }
                }
            }
        }

        foreach ($quest_info as $key => $value) {
            foreach ($data as $k => $v) {
                if ($v['questionnaire_info_id'] == $value['id'] && $v['type'] == 3) {
                    $quest_info[$key]['content'] = $v['content'];
                }
            }

        }

//        return json($quest_info);
        $this->assign('quest_info', $quest_info);
        return $this->fetch();
    }

    //导出
    public function export()
    {
        $quest = new QuestionnaireModel();
        $quest_info = new QuestionnaireinfoModel();
        $quest_info_type = new QuestionnaireinfotypeModel();

        $param = input('param.');
        $title_name = $quest->where('id',$param['id'])->find();//问卷名
        if (isset($param['export']) && $param['export'] == true) {
            $quest = $quest->getInfo($param['id']);//获取主表数据
            $quest_info = $quest_info->getInfo($quest['id']);//获取副表info数据

            foreach ($quest_info as $key => $val) {//组合数据
                $quest_info[$key]['type_info'] = $quest_info_type->getType($val['id']);
            }
            //查询数据出现次数
            $search = function(&$data, $value) {
                $sum = 0;
                foreach ($data as $val) {
                    if (in_array($value, $val)) {
                        $sum++;
                    }
                }
                return $sum;
            };
            $title = array();
            $data = [];
            $titleIndex = 2;
            $allData = $this->memberdata($param['id']);
            $title[] = '总人数';
            $allnum = count($allData);
            $data[0][] = $allnum;
            $data[1][] = '比例：';
            foreach ($quest_info as $k=>$v){
                if ($v['type'] == 3){
                    $data[$titleIndex][] = $v['info_name'].'答案：';
                    foreach ($allData as $val) {
                        $data[$titleIndex][] = $val["content_{$v['id']}"];
                    }
                    $titleIndex++;
                    continue;
                }
                foreach ($v['type_info'] as $key=>$val){
                    $title[] = $v['info_name'].'答案：'.$val['content'];
                    $data[0][]  = $search($allData, $val['content']);
                    $data[1][]  = (round($search($allData, $val['content']) / $allnum, 2)).'%';
                }
            }
            
            $this->export_xls($data,$title,$title_name);
            exit;

        }
    }

    public function oldexport()
    {
        $quest = new QuestionnaireModel();
        $quest_info = new QuestionnaireinfoModel();
        $quest_info_type = new QuestionnaireinfotypeModel();

        $param = input('param.');
        $title_name = $quest->where('id',$param['id'])->find();//问卷名
        if (isset($param['export']) && $param['export'] == true) {
            $quest = $quest->getInfo($param['id']);//获取主表数据
            $quest_info = $quest_info->getInfo($quest['id']);//获取副表info数据

            foreach ($quest_info as $key => $val) {//组合数据
                $quest_info[$key]['type_info'] = $quest_info_type->getType($val['id']);
            }
            $cellName = function($flag) {
                $flag += 5; 
                $res = '';
                $cell = array('','A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
                while ($flag){
                    if ($flag % 26 == 0 ){
                        $res .= 'Z';
                        $flag = (int) ($flag / 26) - 1;
                        continue;
                    }
                    $res .= $cell[$flag % 26];
                    $flag = (int) ($flag / 26);
                }
                return strrev ( $res );
            };
            $title =[ 'A'=>'用户id','B'=>'用户姓名','C'=>'用户号码','D'=>'提交时间'];
            $info = array();
            foreach ($quest_info as $k=>$v){
                $sm = '';
                foreach ($v['type_info'] as $key=>$val){
                    $sm .= $key.':'.$val['content'].',';
                }
                if ($v['type'] == 1){
                    $text = '单选';
                }elseif ($v['type'] == 2){
                    $text = '多选';
                }else{
                    $text = '文本';
                }
                $info[$cellName($k)] = $text.' '.$v['info_name'].'('.$sm.')';
            }
            $on_title = array_merge($title,$info);//标题

            //用户回答了问题的用户数据
            $data = $this->oldmemberdata($param['id']);
//            return json($data);

            $this->export_xls($data,$on_title,$title_name);
            exit();
        }
    }

    /**
     * 处理用户数据
     */
    public function memberdata($id)
    {
        $member_quest = new MemberQuestionnaireModel();
        $member_quest_type = new MemberquestionnaireinfotypeModel();
        $quest = new QuestionnaireModel();
        $data = $member_quest->MemberAnswer($id);
        $info = array();
        foreach ($data as $k=>$v){//获取用户数据
            $info[$k]['member_id'] = $v['member_id'];
            $info[$k]['name'] = $v['name'];
            $info[$k]['tel'] = $v['tel'];
            $info[$k]['creater_time'] = $v['creater_time'];
            $type = $member_quest_type->getMemberType($v['id']);//获取本用户的回答
            $arr = array();
            foreach ($type as $key=>$val){
                if ($val['type'] == 1){//处理单选问题
                    $info[$k]['danxuan_'.$val['questionnaire_info_id']] = $val['type_content'];
                }elseif ($val['type'] == 2){//处理多选问题
                    
                    $info[$k]['duoxuan_'.$val['questionnaire_info_id']][] = $val['type_content'];
                }else{//处理文本回答
                    $info[$k]['content_'.$val['questionnaire_info_id']] = $val['content'];
                }
            }
        }
        $res = [[]];
        foreach ($info as $ke=>$va){//处理多选问题的答案
            foreach ($va as $k=>$v){
                if (is_array($v)){
                    $i = 0;
                    $index = 0;
                    $fillCount = Db::table('xk_questionnaire_info_type')->where('questionnaire_info_id', (int) explode('_', $k)[1])->count();
                    foreach ($info[$ke][$k] as $key => $val) {
                        $res[$ke][$k."_{$index}"] = $val;
                        $index++;
                    }
                    
                } else {
                    $res[$ke][$k] = $v;
                }
            }
        }
        unset($info);
        return $res;
    }

    public function oldmemberdata($id) 
    {
        $member_quest = new MemberQuestionnaireModel();
        $member_quest_type = new MemberquestionnaireinfotypeModel();
        $quest = new QuestionnaireModel();
        $data = $member_quest->MemberAnswer($id);
        $info = array();
        foreach ($data as $k=>$v){//获取用户数据
            $info[$k]['member_id'] = $v['member_id'];
            $info[$k]['name'] = $v['name'];
            $info[$k]['tel'] = $v['tel'];
            $info[$k]['creater_time'] = $v['creater_time'];
            $type = $member_quest_type->getMemberType($v['id']);//获取本用户的回答
            $arr = array();
            foreach ($type as $key=>$val){
                if ($val['type'] == 1){//处理单选问题
                    $info[$k]['danxuan_'.$val['questionnaire_info_id']] = $val['type_content'];
                }elseif ($val['type'] == 2){//处理多选问题
                    $info[$k]['duoxuan_'.$val['questionnaire_info_id']][] = $val['type_content'];
                }else{//处理文本回答
                    $info[$k]['content_'.$val['questionnaire_info_id']] = $val['content'];
                }
            }
        }
        foreach ($info as $ke=>$va){//处理多选问题的答案
            foreach ($va as $k=>$v){
                if (is_array($v)){
                    $info[$ke][$k] = implode(',',$v);
                }
            }
        }
        return $info;
    }


    /**
     * @param $data
     * @param $title
     * 最终导出数据
     */
    public function export_xls($data,$title,$title_name)
    {
        vendor('Classes.PHPExcel');
        //单元格标识
        // $cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ');
        //计算单元格标识
        $cellName = function($flag) {
            $flag += 1; 
            $res = '';
            $cell = array('','A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
            while ($flag){
                if ($flag % 26 == 0 ){
                    $res .= 'Z';
                    $flag = (int) ($flag / 26) - 1;
                    continue;
                }
                $res .= $cell[$flag % 26];
                $flag = (int) ($flag / 26);
            }
            return strrev ( $res );
        };

        $obj = new \PHPExcel();
        $obj->getActiveSheet(0)->setTitle('Sheet1'); //设置sheet名称
        $_row = 1;
        if($title){
            $_cnt = count($title);
            $obj->getActiveSheet(0)->mergeCells('A'.$_row.':'.$cellName($_cnt-1).$_row);   //合并单元格
            $obj->setActiveSheetIndex(0)->setCellValue('A'.$_row, '数据导出：'.date('Y-m-d H:i:s'));  //设置合并后的单元格内容
            $_row++;
            $i = 0;
            foreach($title AS $v){   //设置列标题
                $obj->setActiveSheetIndex(0)->setCellValue($cellName($i).$_row, $v);
                $i++;
            }
            $_row++;
        }


        if($data){
            $i = 0;
            foreach($data AS $_v){
                $j = 0;
                foreach($_v AS $_cell){
                    $obj->getActiveSheet(0)->setCellValue($cellName($j) . ($i+$_row), $_cell);
                    $j++;
                }
                $i++;
            }
        }

        $fileName = $title_name['title'].date('Y-m-d H:i:s',time());

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