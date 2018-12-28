<?php

namespace app\api\controller;
use think\Controller;
use app\api\controller\AliSmsHelper;

class Sms extends Controller{
	function index(){
		$ret = $this->sendSms("17621921067", json_encode(['code'=>1234]), "SMS_148861553");
		dump($ret);
	}
       /**
     * 发送短信
     */
    function sendSms($phone, $data, $templateCode) {
        // 参数集合
        $params = array ();

        // fixme 必填：是否启用https
        $security = false;

        // fixme 必填: 请参阅 https://ak-console.aliyun.com/ 取得您的AK信息
        $accessKeyId = "LTAIhEhhX6amCm7S";
        $accessKeySecret = "Kwa81If5WzBvmlHJjux0i2qMbQQWzf";

         // fixme 必填: 短信接收号码
        $params["PhoneNumbers"] = $phone;

        // fixme 必填: 短信签名，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
        $params["SignName"] = "格兰富";

        // fixme 必填: 短信模板Code，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
        $params["TemplateCode"] = $templateCode;

        // fixme 可选: 设置模板参数, 假如模板中存在变量需要替换则为必填项
        $params['TemplateParam'] = $data;

        // *** 需用户填写部分结束, 以下代码若无必要无需更改 ***
        if(!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
            $params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);
        }

		
        // 初始化SignatureHelper实例用于设置参数，签名以及发送请求
        $helper = new AliSmsHelper();

        try {
            $content = $helper->request(
                $accessKeyId,
                $accessKeySecret,
                "dysmsapi.aliyuncs.com",
                array_merge($params, array(
                    "RegionId" => "cn-hangzhou",
                    "Action" => "SendSms",
                    "Version" => "2017-05-25",
                )),
                $security
            );
        } catch (\Exception $e) {
            return 2;
        }
        return $content;
    }
}