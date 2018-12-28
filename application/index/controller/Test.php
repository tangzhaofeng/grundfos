<?php
/**

https://sp.juplus.cn/module.php/core/authenticate.php?as=default-sp-test&phone=13001845690
https://sp.juplus.cn/module.php/core/authenticate.php?as=default-sp-test&phone=13004787328
https://sp.juplus.cn/module.php/core/authenticate.php?as=default-sp-test&phone=13005378992
https://sp.juplus.cn/module.php/core/authenticate.php?as=default-sp-test&phone=13005944415


13004787328






 * Created by PhpStorm.
 * User: juplus-06
 * Date: 2018/4/23
 * Time: 15:30
 */

namespace app\index\controller;
use think\Controller;
use think\Db;


class Test extends Controller
{

	 /**
     *  生成用户 生成user.bat文件 用于导入AD (SSO)
     **/
    public function test()
    {
		$ret = Db("engineer")->select();
		if(is_file("/www/html/jue/glf/user.txt")){
			unlink("/www/html/jue/glf/user.txt");
		}
		$myfile = fopen("user.txt", "w") or die("Unable to open file!");
		foreach($ret as $k=>$v){
			//dump($v['phone']);exit;
			$phone = $v['phone'];
			$txt = 'dsadd user "cn='.$phone.', ou=glf,dc=crm, dc=local"  -pwd '.$phone.' -display '.$phone.' -mobile '.$phone.' -fn '.$phone.'  -disabled no ';
			fwrite($myfile, $txt);
			fwrite($myfile, PHP_EOL);
			
			Db('member')->where("tel",$phone)->update(['is_in_idp'=>1]);
		}
		fclose($myfile);
		$this->download("/www/html/jue/glf/user.txt");
    }

    /**
     *  生成用户 生成user.bat文件 用于导入AD (SSO)
     **/
    public function index()
    {
		//#SELECT * from xk_company_work_type where work_type_name like "%安装工程师%"
		$ret = Db('xk_member')->query("select m.tel,m.id from xk_member as m LEFT JOIN xk_member_openid_unionid as u on u.unionid=m.unionid  where u.is_cancel=1 and m.company_work_type_id in (2,5,16) and m.tel>0 group by m.tel ");
		//dump(count($ret));exit;
		if(is_file("/www/html/jue/glf/user.txt")){
			unlink("/www/html/jue/glf/user.txt");
		}
		
		$myfile = fopen("user.txt", "w") or die("Unable to open file!");
		$obj = Db('member');
		foreach($ret as $k=>$v){
			$phone = $v['tel'];
			
			$obj->where(['id'=>$v['id']])->update(['is_in_idp'=>1]);
			$txt = 'dsadd user "cn='.$phone.', ou=glf,dc=crm, dc=local"  -pwd '.$phone.' -display '.$phone.' -mobile '.$phone.' -fn '.$phone.'  -disabled no ';
			fwrite($myfile, $txt);
			fwrite($myfile, PHP_EOL);
		}
		fclose($myfile);
		$this->download("/www/html/jue/glf/user.txt");
    }
	
	
	  //文件下载
    function download($filename) { 
 
        if (false == file_exists($filename)) { 
            return false; 
        } 
         
        // http headers 
        header('Content-Type: application-x/force-download'); 
        header('Content-Disposition: attachment; filename="' . basename($filename) .'"'); 
        header('Content-length: ' . filesize($filename)); 
     
        // for IE6 
        if (false === strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 6')) { 
            header('Cache-Control: no-cache, must-revalidate'); 
        } 
        header('Pragma: no-cache'); 
             
        // read file content and output 
        return readfile($filename);; 
    } 



}