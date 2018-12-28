<?php

namespace app\admin\controller;
use think\Controller;

class Common extends Controller
{  
	protected $app = ''; //二级子目录项目用到该项目的文件夹名称
	
    //上传文件
	public function upload(){
        ini_set('default_socket_timeout', 3*60*60);  //3个小时   
        ini_set ('memory_limit', '3072M');
        // 获取表单上传文件 例如上传了001.jpg
        if (request()->file('file')){
            $file = request()->file('file');
        }else{
            $file = request()->file('imgFile');
        }
        // 移动到框架应用根目录/public/uploads/ 目录下
        $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
        if($info){
            $fname='/public/uploads/'.str_replace('\\','/',$info->getSaveName());
			
			$imgArr = explode(',', 'jpg,gif,png,jpeg,bmp,ttf,tif');
			$imgExt= strtolower($info->getExtension());
			$isImg = in_array($imgExt,$imgArr);

            //    if (request()->file('file')){
            //         $url = '/'.$info->getSaveName();
            //     }else{
                    $url = '/glf/public/uploads/'.$info->getSaveName();
                // }

            $data=array(
				'state' => 'SUCCESS',
				'url' => $url,
				'title' => $info->getFilename(),
				'original' => $info->getFilename(),
				'type' => '.' . $info->getExtension(),
				'size' => $info->getSize(),
                'error' => 0
			);
        }else{
            // 上传失败获取错误信息
            $data=array(
			    'state' => $info->getError(),
			);
        }
        return json( $data );
       
    }

    /**
     * 上传文件并新增临时素材,返回mediaId
     */
    public function mediaUpload(){
        ini_set('default_socket_timeout', 3*60*60);  //3个小时
        ini_set ('memory_limit', '3072M');
        // 获取表单上传文件 例如上传了001.jpg
        if (request()->file('file')){
            $file = request()->file('file');
        }else{
            $file = request()->file('imgFile');
        }
        //上传临时素材
        $messs = new Messagesend();

        $messs->sendMedia(1,'',$file);

        // 移动到框架应用根目录/public/uploads/ 目录下
        $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
        if($info){
            $fname='/public/uploads/'.str_replace('\\','/',$info->getSaveName());

            $imgArr = explode(',', 'jpg,gif,png,jpeg,bmp,ttf,tif');
            $imgExt= strtolower($info->getExtension());
            $isImg = in_array($imgExt,$imgArr);

            //    if (request()->file('file')){
            //         $url = '/'.$info->getSaveName();
            //     }else{
            $url = '/glf/public/uploads/'.$info->getSaveName();
            // }

            $data=array(
                'state' => 'SUCCESS',
                'url' => $url,
                'title' => $info->getFilename(),
                'original' => $info->getFilename(),
                'type' => '.' . $info->getExtension(),
                'size' => $info->getSize(),
                'error' => 0
            );
        }else{
            // 上传失败获取错误信息
            $data=array(
                'state' => $info->getError(),
            );
        }
        return json( $data );

    }
	
	/**
 * 保存64位编码图片
 */
 function saveBase64Image(){
		$base64_image_content = input("param.file");
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)){
		  //图片后缀
		  $type = $result[2];
		  if($type=='jpeg'){
				$type='jpg';
		  }
		  //保存位置--图片名
		  $image_name=date('His').str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT).".".$type;
		  $image_url = '/public/uploads/'.date('Ymd').'/'.$image_name;           
		  if(!is_dir(dirname('./'.$image_url))){
				 mkdir(dirname('./'.$image_url));
				chmod(dirname('./'.$image_url), 0777);
		  }
		  //解码
		  $decode=base64_decode(str_replace($result[1], '', $base64_image_content));
		  if (file_put_contents('./'.$image_url, $decode)){
				$data['code']='0';
				$data['imageName']=$image_name;
				$data['url']='/glf/'.$this->app.$image_url;
				$data['type']=$type;
				$data['msg']='保存成功！';
		  }else{
			$data['code']='1';
			$data['imgageName']='';
			$data['image_url']='';
			$data['type']='';
			$data['msg']='图片保存失败！';
		  }
        }else{
            $data['code']='1';
            $data['imgageName']='';
            $data['url']='';
            $data['type']='';
            $data['msg']='base64图片格式有误！';
        }       
        return json($data);
 }
	
	
	
	/**
    * 数组转xls格式的excel文件
    * @param  array  $data      需要生成excel文件的数组
    * @param  string $filename  生成的excel文件名
    *      示例数据：
    *        $data = array(
    *            array(NULL, 2010, 2011, 2012),
    *            array('Q1',   12,   15,   21),
    *            array('Q2',   56,   73,   86),
    *            array('Q3',   52,   61,   69),
    *            array('Q4',   30,   32,    0),
    *       );
    */
	//把数据导出
    function export_xls($data,$filename='simple.xls'){
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
	
	
	/**
    * 导入excel文件
    * @param  string $file excel文件路径
    * @return array        excel文件内容数组
    */
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
                //去空值
                if($tmp){
                $data[$j][] = $tmp;
                }
            }
        }
        return $data;
    }

	
       /**
        * 系统邮件发送函数
        * @param string $tomail 接收邮件者邮箱
        * @param string $name 接收邮件者名称
        * @param string $subject 邮件主题
        * @param string $body 邮件内容
        * @param string $attachment 附件列表
        * @return boolean
        * @author 2214601330 <2214601330@qq.com>
		*	public function email() {
		*		$toemail='2214601330@qq.com';
		*		$name='static7';
		*		$subject='QQ邮件发送测试';
		*		$content='恭喜你，邮件测试成功。';
		*		dump(send_mail($toemail,$name,$subject,$content));
		*	}
        */
        function send_mail($tomail, $name, $subject = '', $body = '', $attachment = null) {
            $mail = new \PHPMailer();           //实例化PHPMailer对象
            $mail->CharSet = 'UTF-8';           //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
            $mail->IsSMTP();                    // 设定使用SMTP服务
            $mail->SMTPDebug = 0;               // SMTP调试功能 0=关闭 1 = 错误和消息 2 = 消息
            $mail->SMTPAuth = true;             // 启用 SMTP 验证功能
            $mail->SMTPSecure = 'ssl';          // 使用安全协议
            $mail->Host = "smtp.163.com"; // SMTP 服务器
            $mail->Port = 465;                  // SMTP服务器的端口号
            $mail->Username = "15216842930";    // SMTP服务器用户名
            $mail->Password = "";     // SMTP服务器密码
            $mail->SetFrom('15216842930', '3325');
            $replyEmail = '';                   //留空则为发件人EMAIL
            $replyName = '';                    //回复名称（留空则为发件人名称）
            $mail->AddReplyTo($replyEmail, $replyName);
            $mail->Subject = $subject;
            $mail->MsgHTML($body);
            $mail->AddAddress($tomail, $name);
            if (is_array($attachment)) { // 添加附件
                foreach ($attachment as $file) {
                    is_file($file) && $mail->AddAttachment($file);
                }
            }
            return $mail->Send() ? true : $mail->ErrorInfo;
        }
	
  
	//返回文件大小  
	public function file_size($file) {  
		$arr    = array('Byte', 'KB', 'MB', 'GB', 'TB', 'PB');  
		$size 	= filesize($file);
		$e      = floor(log($size)/log(1024));  
		return number_format(($size/pow(1024,floor($e))),2,'.','').' '.$arr[$e];  
	}  

}