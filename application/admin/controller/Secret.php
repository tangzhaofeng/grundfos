<?php

namespace app\admin\controller;

use think\Db;
use app\admin\model\ActivitymemberModel;
use app\admin\model\ActivityModel;
use app\admin\model\AppsecretModel;
use app\admin\model\BuModel;
use app\admin\model\GroupingModel;
use app\admin\model\MembergroupModel;
use app\admin\model\MemberModel;
use app\admin\model\MemberopenunionModel;
use app\admin\model\MessageModel;
use app\admin\model\TemplateModel;
use app\index\controller\Index;

// ini_set('max_execution_time',0);
// ignore_user_abort(true);

class Secret extends Base
{
    public function getRedNumber()
    {
        $param = input('param.');
        $activity_id = $param['id'];
        $data = Db::table('xk_activity_share')
        ->alias('a')
        ->join('xk_member m', 'a.member_id = m.id')
        ->where('a.activity_id', '=', $activity_id)
        ->where('a.status', '=', 2)
        ->group('a.member_id')
        ->field(['m.name', 'm.tel', 'a.img', 'a.create_time', 'count(*)'])
        ->select();
        $title =[ 'A'=>'用户姓名','B'=>'电话','C'=>'图片','D'=>'报名时间','E'=>'领取红包数'];
        $this->export_xls($data,$title);
    }
    /**
     * excel导出
     */
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

        $fileName = '活动红包领取名单'.date('Y-m-d H:i:s',time());

        $objWrite = \PHPExcel_IOFactory::createWriter($obj, 'Excel2007');
        $obj->getActiveSheet()->getColumnDimension('A')->setWidth(30);
        $obj->getActiveSheet()->getColumnDimension('B')->setWidth(30);
        $obj->getActiveSheet()->getColumnDimension('C')->setWidth(30);
        $obj->getActiveSheet()->getColumnDimension('D')->setWidth(30);
        $obj->getActiveSheet()->getColumnDimension('E')->setWidth(30);

        header('pragma:public');
        header("Content-Disposition:attachment;filename=$fileName.xls");
        $objWrite->save('php://output');exit;
    }

    public function test(){
        // $file = '/glf/in.xlsx';
        // $data = $this->import_excel($file);
        // // dump($data);
        // //$data xls解析后的数组
        // /**
        //  * [0] => string(8) "微信ID"
        //  * [1] => string(12) "微信昵称"
        //  * [2] => string(6) "头像"
        //  * [3] => string(3) "市"
        //  * [4] => string(6) "名字"
        //  * [5] => string(9) "手机号"
        //  * [6] => string(6) "性别"
        //  * [7] => string(12) "公司名称"
        //  * [8] => string(12) "公司分类"
        //  * [9] => string(12) "角色分类"
        // */
        // $member = new MemberModel;
        // $memberopenid = new MemberopenunionModel;
        // $data = array_slice($data,1,count($data)-1);
        // foreach ($data as $val) {
        //     //只有关注才能导入
        //     $info = $this->getInfo((string) $val[0]);
            
        //     if ($info['subscribe']) {
        //         if ($member->where('unionid', $info['unionid'])->value('id')){
        //             continue;
        //         }
        //         $company_type_id = Db::table('xk_company_type')->where('type_name', (string) $val[8])->value('id');
        //         $company_work_type_id = Db::table('xk_company_work_type')
        //             ->where('work_type_name', (string) $val[9])
        //             ->where('company_id', $company_type_id)
        //             ->value('id');
        //         if ((string) $val[8] == '家庭用户') {
        //             $company_type_id = null;
        //             $company_work_type_id = 143;
        //         }
        //         //入库
        //         $member->insert([
        //             'name' => (string) $val[4],
        //             'tel' => (string) $val[5],
        //             'company_work_type_id' => $company_work_type_id,
        //             'company_id' => $company_type_id,
        //             'nickname' => base64_encode($info['nickname']),
        //             'city' => $info['city'],
        //             'sex' => $info['sex'],
        //             'create_time' => date('Y-m-d H:i:s', $info['subscribe_time']),
        //             'unionid' => $info['unionid'],
        //             'integral' => 50,
        //             'headimgurl' => $info['headimgurl'],
        //             'company_name' => (string) $val[7],
        //             'is_bu_id' => 1,
        //         ]);
        //         $memberopenid->insert([
        //             'openid' => $info['openid'],
        //             'unionid' => $info['unionid'],
        //             'create_time' => date('Y-m-d H:i:s', $info['subscribe_time']),
        //             'bu_id' => 1,
        //             'is_cancel' => 1,
        //         ]);
        //     }
        // }
    }

    public function getInfo($openid) {
        $appid = 'wx846178961ef0ca8b';
        $secret = 'cf0eff7e06f50680d3a4d4304f16d26d';
        $index = new Index;
        $token = $index->getAccessToken($appid, $secret,'a');
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token={$token}&openid={$openid}&lang=zh_CN";
        $res = json_decode($this->http_Curl($url,[],'GET'), true);
        return $res;
    }

    public function setMenu(){
        $appid = 'wx846178961ef0ca8b';
        $secret = 'cf0eff7e06f50680d3a4d4304f16d26d';
        $index = new Index;
        $token = $index->getAccessToken($appid, $secret,1);
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$token;
       $param = '    
         {"button": [
            {
                "name": "近期活动",
                "sub_button": [
                    {
                        "type": "view",
                        "name": "活动报名",
                        "url": "https://www.juplus.cn/glf/index/index/index?type=a&view=/activity/activity_xia.html",
                        "sub_button": []
                    },
                    {
                        "type": "view",
                        "name": "分享赢取",
                        "url": "https://www.juplus.cn/glf/index/index/index?type=a&view=/activity/activity_shang.html",
                        "sub_button": []
                    },
                    {
                        "type": "view",
                        "name": "神手安装工",
                        "url": "http://a2designing.cn/grundfos/azgGame",
                        "sub_button": []
                    }
                ]
            },
            {
                "name": "产品信息",
                "sub_button": [
                    {
                        "type": "view",
                        "name": "产品及方案",
                        "url": "https://www.juplus.cn/glf/index/index/index?type=a&view=/proSummary/index.html",
                        "sub_button": []
                    },
                    {
                        "type": "view",
                        "name": "安装工学院",
                        "url": "http://www.juplus.cn/glf/index/sso",
                        "sub_button": []
                    }
                ]
            },
            {
                "name": "关于我们",
                "sub_button": [
                    {
                        "type": "view",
                        "name": "京东商城",
                        "url": "https://mall.jd.com/index-762859.html",
                        "sub_button": []
                    },
                    {
                        "type": "view",
                        "name": "公司介绍",
                        "url": "https://mp.weixin.qq.com/s/f4XFb9KT8v_c6FBUiR9C7w",
                        "sub_button": []
                    },
                    {
                        "type": "view",
                        "name": "联系我们",
                        "url": "https://www.sobot.com/chat/h5/index.html?sysNum=8d8cac4e24c04f77af3a51a3e291f50f",
                        "sub_button": []
                    },
                    {
                        "type": "view",
                        "name": "会员中心",
                        "url": "https://www.juplus.cn/glf/index/index/index?type=a&view=/personal/index.html",
                        "sub_button": []
                    },
                    {
                        "type": "view",
                        "name": "格兰富展厅",
                        "url": "http://grundfos.juplus.cn/RBS/",
                        "sub_button": []
                    }
                ]
            }
        ]
    }';
         // echo $param;
        $res = $this->http_Curl($url,$param);
        echo $res;
    }

    function http_Curl($url,$paramArray = array(),$method = 'POST'){
        $ch = curl_init();
        if ($method == 'POST')
        {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $paramArray);
        }
        else if (!empty($paramArray))
        {
            $url .= '?' . http_build_query($paramArray);
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT,10);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if (false !== strpos($url, "https")) {
            // 证书
            // curl_setopt($ch,CURLOPT_CAINFO,"ca.crt");
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,  false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  false);
        }
        $resultStr = curl_exec($ch);
        curl_close($ch);

        return $resultStr;
    }

    //导入xlsx
    function import_excel($file){
        // 判断文件是什么格式
		$file = $_SERVER["DOCUMENT_ROOT"].$file; //绝对路径
        $type = pathinfo($file);
        $type = strtolower($type["extension"]);
        if ($type == 'xlsx') { 
            $type = 'Excel2007'; 
        } elseif ($type == 'xls') { 
            $type = 'Excel5'; 
        } elseif ($type == 'csv') {
            $type = 'csv';
        }
        ini_set('max_execution_time', '0');
        Vendor('PHPExcel.PHPExcel');
		$type ="Excel2007";
        // 判断使用哪种格式
        $objReader = \PHPExcel_IOFactory::createReader($type);
        $objPHPExcel = $objReader->load($file);
        $sheet = $objPHPExcel->getSheet(0);
        // 取得总行数
        $highestRow = $sheet->getHighestRow();
        // 取得总列数
        $highestColumn = $sheet->getHighestColumn();
        //循环读取excel文件,读取一条,插入一条
        $data=array();
        //从第一行开始读取数据
        for($j=1;$j<=$highestRow;$j++){
            //从A列读取数据
            for($k='A';$k<=$highestColumn;$k++){
                // 读取单元格
                $tmp=$objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();
                //不去空值
                $data[$j][] = $tmp;
            }
        }
        return $data;
    }

    //把数据导出
    function out_xls($data,$filename='simple.xls'){
        ini_set('max_execution_time', '0');
        Vendor('PHPExcel.PHPExcel');
        Vendor('PHPExcel.PHPExcel.Worksheet.Drawing');
        Vendor('PHPExcel.PHPExcel.Writer.Excel2007');

        $filename=str_replace('.xls', '', $filename).'.xls';
        $phpexcel = new \PHPExcel();
        
        $phpexcel->getProperties()
            ->setCreator("Maarten Balliauw")
            ->setLastModifiedBy("Maarten Balliauw")
            ->setTitle("Office 2007 XLSX Test Document")
            ->setSubject("Office 2007 XLSX Test Document")
            ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
            ->setKeywords("office 2007 openxml php")
            ->setCategory("Test result file");
        $phpexcel->getActiveSheet()->fromArray($data);
        $phpexcel->getActiveSheet()->setTitle('Sheet1');
        $phpexcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=$filename");
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0
        $objwriter = \PHPExcel_IOFactory::createWriter($phpexcel, 'Excel5');
        $objwriter->save('php://output');
        exit;
    }
}