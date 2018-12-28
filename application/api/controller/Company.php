<?php
/**
 * Created by PhpStorm.
 * User: juplus-06
 * Date: 2018/4/10
 * Time: 13:49
 */

namespace app\api\controller;
header("Access-Control-Allow-Origin: https://www.juplus.cn");
header('Access-Control-Allow-Methods:GET, POST, OPTIONS');
use app\admin\model\ActivityModel;
use app\admin\model\CompanyModel;
use app\admin\model\CompanyworkModel;
use think\Exception;

class Company extends Base
{

    /**
     * 获取公司类型与
     */
    public function getCompany()
    {
        try{
            $type = session('type');
            $wxconfig = session('wxconfig_'.$type);

            $type = new CompanyModel();
            $work = new CompanyworkModel();

            //公司类型数据
            $typelist = collection($type->all(['bu_id'=>$wxconfig['bu_id']]))->toArray();
            //职位数据
            $worklist= collection($work->all())->toArray();

            //组合公司与职位
            $list = array();
            // $i = 0;
            // $o = 0;
            // foreach ($typelist as $k => $v){
            //     foreach ($worklist as $key => $val){
            //         if ($v['id'] == $val['company_id']){
            //             $list[$o]['type_name'] = $v['type_name'];
            //             $list[$o]['id'] = $v['id'];
            //             $list[$o]['postion'][$i]['work_type_name'] = $val['work_type_name'];
            //             $list[$o]['postion'][$i]['id'] = $val['id'];
            //         }else{
            //             $list[$o]['type_name'] = $v['type_name'];
            //             $list[$o]['id'] = $v['id'];
            //         }
            //         $i++;
            //     }
            //     $o++;
            // }

            foreach($typelist as $k=>$v){
                $typelist[$k]['postion'] = collection($work->all(['company_id'=>$v['id']]))->toArray();
            }

            $info['data'] = $typelist;
            $info['code'] = 1;
            $info['msg'] = '成功';

            return json($info);
        }catch (Exception $e){
            return errorjson();
        }
    }
}