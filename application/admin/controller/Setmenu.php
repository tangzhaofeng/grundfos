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

class SetMenu extends Base
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
        $objWrite->save('php://output');
        exit;
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

    public function getMenu() {
        $appid = 'wxc36e00e3dfb8632c';
        $secret = 'ea7a5080b8ed92c37dec30b14ad1cbad';
        $index = new Index;
        $token = $index->getAccessToken($appid, $secret,'d');
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/get?access_token='.$token;
        $res = $this->http_Curl($url);
        echo $res;
    }

    public function setMenu(){
        $appid = 'wxc36e00e3dfb8632c';
        $secret = 'ea7a5080b8ed92c37dec30b14ad1cbad';
        $index = new Index;
        $token = $index->getAccessToken($appid, $secret,'d');
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$token;
        $param = '{
            "button": [
              {
                "name": "关于我们",
                "sub_button": [
                  {
                    "type": "view",
                    "name": "关于格兰富",
                    "url": "https://mp.weixin.qq.com/s/rYvseAXRfGk33oru0tko8Q"
                  },
                  {
                    "type": "view",
                    "name": "产品及方案",
                    "url": "https://www.juplus.cn/glf/index/index/index?type=d&view=/proSummary/index.html"
                  },
                  {
                    "type": "view",
                    "name": "联系我们",
                    "url": "https://www.sobot.com/chat/h5/index.html?sysNum=8d8cac4e24c04f77af3a51a3e291f50f"
                  },
                  {
                    "type": "view",
                    "name": "新闻中心",
                    "url": "https://mp.weixin.qq.com/mp/profile_ext?action=home&__biz=MzU0ODc0MzA5NQ==&scene=124#wechat_redirect"
                  },
                  {
                    "type": "view",
                    "name": "格兰富展厅",
                    "url": "http://grundfos.juplus.cn/IND"
                  }
                ]
              },
              {
                "type": "view",
                "name": "活动中心",
                "url": "https://www.juplus.cn/glf/index/index/index?type=d&view=/activity/activity_xia.html"
              },
              {
                "type": "view",
                "name": "会员中心",
                "url": "https://www.juplus.cn/glf/index/index/index?type=d&view=/personal/index.html"
              }
            ]
          }';
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
}