<?php
/*
 *  Copyright (c) 2014 The CCP project authors. All Rights Reserved.
 *
 *  Use of this source code is governed by a Beijing Speedtong Information Technology Co.,Ltd license
 *  that can be found in the LICENSE file in the root of the web site.
 *
 *   http://www.yuntongxun.com
 *
 *  An additional intellectual property rights grant can be found
 *  in the file PATENTS.  All contributing project authors may
 *  be found in the AUTHORS file in the root of the source tree.
 */
namespace app\api\controller;



class SendSMS {
    //主帐号
    public $accountSid = '8a48b5514b0b8727014b23cfa43610d3';
    //主帐号Token
    public $accountToken= '35b176c2430c48a69013c76dfb6ef95d';
    //应用Id
    public $appId='8a216da862cc8f910162d143edc5027a';
    //请求地址，格式如下，不需要写https://
    public $serverIP= 'app.cloopen.com';
    //请求端口
    public $serverPort='8883';
    //REST版本号
    public $softVersion='2013-12-26';

    function send($to,$datas,$tempId)
    {
//        return 00000;
        // 初始化REST SDK
//        vendor('CCP_REST_DEMO_PHP_v27r1.SDK.CCPRestSDK');
//        global $accountSid,$accountToken,$appId,$serverIP,$serverPort,$softVersion;
        $rest = new Ccsdk($this->serverIP,$this->serverPort,$this->softVersion);
//        return 11222;
        $rest->setAccount($this->accountSid,$this->accountToken);
        $rest->setAppId($this->appId);
        // 发送模板短信
        // echo "Sending TemplateSMS to $to <br/>";
        $result = $rest->sendTemplateSMS($to,$datas,$tempId);

        if($result == NULL ) {
            echo "result error!";

        }
        if($result->statusCode!=0) {
            // return returninfos('2','短信获取错误');
            // echo "error code :" . $result->statusCode . "<br>";
            // echo "error msg :" . $result->statusMsg . "<br>";
            // TODO 添加错误处理逻辑
            return 2;
        }else{
            // 获取返回信息
            // $smsmessage = $result->TemplateSMS;
            // echo "dateCreated:".$smsmessage->dateCreated."<br/>";
            // echo "smsMessageSid:".$smsmessage->smsMessageSid."<br/>";
            return 1;
        }

    }

}

