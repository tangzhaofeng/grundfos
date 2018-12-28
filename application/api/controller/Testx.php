<?php

namespace app\api\controller;
use app\admin\model\IntegralgoodsModel;
use app\admin\model\IntegralorderModel;
use app\admin\model\MemberaddressModel;
use app\admin\model\MemberintegralModel;
use app\api\model\MemberModel;
use think\Controller;
use think\Db;
use app\api\controller\Live;

class Testx extends Controller
{
    public function test() {
        $start = strtotime('2018-11-1');
        $end = strtotime('2018-12-1');
        $res = 
        collection(
        Db::table('xk_integral_order')->alias('io')
        ->join('xk_member m', 'io.member_id = m.id', 'LEFT')
        ->where('io.status', 2)
        ->where('io.bu_id', 2)
        ->where("io.name", "like" ,"%京东%")
        // ->where("io.name like '星巴克 50元电子星礼卡'")
        ->where('io.send_time', '>',$start)
        ->where('io.send_time', '<',$end)
        ->field(["m.name","m.tel","io.send_time","io.number","io.name as lift"])
        ->select()
        )->toArray();
        foreach($res as $key => $val) {
            $res[$key]['send_time'] = date("Y-m-d H:i:s", $val['send_time']);
        }
        $this->export_xls($res, ['姓名', '手机号', '发放时间','卡','张数'], "CBS 11月报表");
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

        $fileName = $title_name;

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