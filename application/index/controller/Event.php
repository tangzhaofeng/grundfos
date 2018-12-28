<?php
/**
 * Created by PhpStorm.
 * User: juplus-06
 * Date: 2018/4/19
 * Time: 16:12
 */

namespace app\index\controller;


use app\admin\controller\Member;
use app\admin\model\AppsecretModel;
use app\admin\model\AutoreplyModel;
use app\admin\model\MemberModel;
use app\admin\model\MemberopenunionModel;
use app\index\controller\ResponsePassive;
use think\Controller;

class Event extends Controller
{
    private static $wechat;
    private static $touser;
    private static $openid;
    private static $info;
    private static $token;
//   private $request; //请求数据
    /**
     * 初始化配置
     */
    public function __construct()
    {
        //根据openid获取不同的微信配置
        $open_un = new MemberopenunionModel();
        $wxconfig = new AppsecretModel();
//        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
//        $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
//        $openid = $postObj->FromUserName;
//        self::$openid = $openid;

        //自动载入函数载入相关操作类
        vendor("LaneWeChat.config");
        vendor("LaneWeChat.wechat");

        //配置微信公众号
//        \weChatConfig::setAppId($config['appid']); //juplus wxb42ad5150aacbee4
//        \weChatConfig::setAppSecret($config['appsecret']); //juplus 8b023a4ec3bfb58ecaf1b96b95b2e1ba
//        \weChatConfig::setUrl('https://www.juplus.cn/glf/index/event/event'); //WECHAT_URL
//        \weChatConfig::setToken(123456); //WECHAT_TOKEN
//        \weChatConfig::setKey('nRp7sSpi5j8BaTa2Mu2t2n47Os9eM7SgOopJQZ9a4zR'); //WECHAT_TOKEN
    }

    /**s
     * 微信事件
     **/
    public function event()
    {
        $wechat = new \LaneWeChat\Core\WeChat(123456,true);
        //首次使用需要注视掉下面这1行（27行），并打开最后一行（29行）
        $request = $wechat->run();
        self::$openid = $request['fromusername'];
       
        //得到所有公众号配置进行发送,只有openid正确的才能发送成功 注意:此项不能修改,没有完成注册的用户会无法得到消息
        $bu = new AppsecretModel();
        $wx_all = collection($bu->all())->toArray();
        $index = new Index();

        foreach ($wx_all as $k=>$v){

            $token = $index->getAccessToken($v['appid'],$v['appsecret'],$v['type']);
            self::$token = $token;
            $info = curl_get('https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$token.'&openid='.self::$openid);
            self::$info = $info;

            if (strpos($info,'errcode') == false){
                self::$wechat = $v;
                \weChatConfig::setAppId($v['appid']); //juplus wxb42ad5150aacbee4
                \weChatConfig::setAppSecret($v['appsecret']); //juplus 8b023a4ec3bfb58ecaf1b96b95b2e1ba
                \weChatConfig::setUrl('https://www.juplus.cn/glf/index/event/event'); //WECHAT_URL
                \weChatConfig::setToken(123456); //WECHAT_TOKEN
                \weChatConfig::setKey('nRp7sSpi5j8BaTa2Mu2t2n47Os9eM7SgOopJQZ9a4zR'); //WECHAT_TOKEN

                echo $this->eventSwitchType($request);
            }

        }




        //首次使用需要打开下面这一行（29行），并且注释掉上面1行（27行）。本行用来验证URL
        // $wechat->checkSignature();
    }

    /**
     * 分发请求
     **/
    public function eventSwitchType(&$request)
    {
//        dump($request);exit();
        switch ($request['msgtype']) {
            //事件
            case 'event':
                $request['event'] = strtolower($request['event']);
                switch ($request['event']) {
                    //关注
                    case 'subscribe':
                        //二维码关注
                        if(isset($request['eventkey']) && isset($request['ticket'])){
                            $data = self::eventQrsceneSubscribe($request,0);
                        }else{
                            //普通关注
                            $data = self::eventSubscribe($request);
                        }
                        break;
                    //扫描二维码 已关注时
                    case 'scan':
                        $data = self::eventQrsceneSubscribe($request,1);//scan表示是否关注
//                         $data = self::eventScan($request);//已关注时带参数二维码进入
                        break;
                    //地理位置
                    case 'location':
                        $data = self::eventLocation($request);
                        break;
                    //自定义菜单 - 点击菜单拉取消息时的事件推送
                    case 'click':
                        $data = self::eventClick($request);
                        break;
                    //自定义菜单 - 点击菜单跳转链接时的事件推送
                    case 'view':
                        $data = self::eventView($request);
                        break;
                    //自定义菜单 - 扫码推事件的事件推送
                    case 'scancode_push':
                        $data = self::eventScancodePush($request);
                        break;
                    //自定义菜单 - 扫码推事件且弹出“消息接收中”提示框的事件推送
                    case 'scancode_waitmsg':
                        $data = self::eventScancodeWaitMsg($request);
                        break;
                    //自定义菜单 - 弹出系统拍照发图的事件推送
                    case 'pic_sysphoto':
                        $data = self::eventPicSysPhoto($request);
                        break;
                    //自定义菜单 - 弹出拍照或者相册发图的事件推送
                    case 'pic_photo_or_album':
                        $data = self::eventPicPhotoOrAlbum($request);
                        break;
                    //自定义菜单 - 弹出微信相册发图器的事件推送
                    case 'pic_weixin':
                        $data = self::eventPicWeixin($request);
                        break;
                    //自定义菜单 - 弹出地理位置选择器的事件推送
                    case 'location_select':
                        $data = self::eventLocationSelect($request);
                        break;
                    //取消关注
                    case 'unsubscribe':
                        $data = self::eventUnsubscribe($request);
                        break;
                    //群发接口完成后推送的结果
                    case 'masssendjobfinish':
                        $data = self::eventMassSendJobFinish($request);
                        break;
                    //模板消息完成后推送的结果
                    case 'templatesendjobfinish':
                        $data = self::eventTemplateSendJobFinish($request);
                        break;
                    default:
                        return Msg::returnErrMsg(MsgConstant::ERROR_UNKNOW_TYPE, '收到了未知类型的消息', $request);
                        break;
                }
                break;
            //文本
            case 'text':

                $data = self::text($request);

                break;
            //图像
            case 'image':
                $data = self::image($request);
                break;
            //语音
            case 'voice':
                $data = self::voice($request);
                break;
            //视频
            case 'video':
                $data = self::video($request);
                break;
            //小视频
            case 'shortvideo':
                $data = self::shortvideo($request);
                break;
            //位置
            case 'location':
                $data = self::location($request);
                break;
            //链接
            case 'link':
                $data = self::link($request);
                break;
            default:
                return ResponsePassive::text($request['fromusername'], $request['tousername'], '收到未知的消息，我不知道怎么处理');
                break;
        }
        return $data;
    }



    /**
     * @descrpition 文本
     * @param $request
     * @return array
     */
    public static function text(&$request){
        $auto = new AutoreplyModel();
        $content = ' ';
//        if (self::$wechat['bu_id'] != 2){
            $content = $auto->where('key',$request['content'])->where('bu_id','like', '%' . self::$wechat['bu_id'] . '%')->value('content');

            //去除莫名其妙加上的amp;
            if (strpos($content,'amp;')) {
                $info = str_replace('amp;','',$content);
                $content = $info;
            }

            if(!$content){
                $content = '您的消息已收到，工作人员将尽快回复';
            }
            return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
//        }


    }

    /**
     * @descrpition 图像
     * @param $request
     * @return array
     */
    public static function image(&$request){
        $content = '收到图片';
        return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
    }

    /**
     * @descrpition 语音
     * @param $request
     * @return array
     */
    public static function voice(&$request){
        if(!isset($request['recognition'])){
            $content = '收到语音';
            return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
        }else{
            $content = '收到语音识别消息，语音识别结果为：'.$request['recognition'];
            return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
        }
    }

    /**
     * @descrpition 视频
     * @param $request
     * @return array
     */
    public static function video(&$request){
        $content = '收到视频';
        return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
    }

    /**
     * @descrpition 视频
     * @param $request
     * @return array
     */
    public static function shortvideo(&$request){
        $content = '收到小视频';
        return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
    }

    /**
     * @descrpition 地理
     * @param $request
     * @return array
     */
    public static function location(&$request){
        $content = '收到上报的地理位置';
        return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
    }

    /**
     * @descrpition 链接
     * @param $request
     * @return array
     */
    public static function link(&$request){
        $content = '收到连接';
        return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
    }

    /**
     * @descrpition 关注
     * @param $request
     * @return array
     */
    public static function eventSubscribe(&$request){
        //得到关注不同公众号的自动回复
        $auto = new AutoreplyModel();
        $text = $auto->where('key','关注')->where('bu_id','like', '%' . self::$wechat['bu_id'] . '%')->value('content');
        //如果已经注册会员的则不提示
//        if (self::isUpLogin()){
//            $content = "";
//        }else{
            $content = "欢迎您关注格兰富".$text."，<a href='https://www.juplus.cn/glf/index/index/index?type=".self::$wechat['type']."&view=/register/index.html'>欢迎您注册成为会员</a>";
//        }

        //获取到用户基本信息
        $index = new Index();
        $token = $index->getAccessToken(self::$wechat['appid'],self::$wechat['appsecret'],self::$wechat['type']);
        $member_info = curl_get('https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$token.'&openid='.$request['fromusername']);
        $user_info = json_decode($member_info,true);//获取到用户基本信息
        $user_info['bu_id'] = self::$wechat['bu_id'];

        $member_id = ResponsePassive::handleNewUser($user_info);
//        ResponsePassive::handleMapOpenidAndUninid($user_info);
        //记录用户操作
        $record = Db('record');
        $record->insert(['category'=>1,'content'=>'普通关注','creater_time'=>date('Y-m-d H:i:s',time()),
            'member_id'=>$member_id
        ]);

        return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
    }


    /**
     * 是否已经注册
     */
    public static function isUpLogin()
    {
        $obj = new MemberModel();

        $info = $obj->isOpenLogin(self::$openid);
        if ($info['name']){
            return true;
        }else{
            return false;
        }
    }

    /**
     * @descrpition 取消关注
     * @param $request
     * @return array
     */
    public static function eventUnsubscribe(&$request){
        $content = '';
        //取到用户unionid
        $app = Db('member_openid_unionid');
        $unionid =$app->where(array("openid"=>$request['fromusername'],"bu_id"=>self::$wechat['bu_id']))->find();
        $apps = new MemberopenunionModel();
        $apps->save(['is_cancel'=>0],['openid'=>$request['fromusername'],'bu_id'=>self::$wechat['bu_id']]);
        //根据unionid得到用户id
        $obj = Db('member');
        $id =$obj->where(array("unionid"=>$unionid['unionid']))->find();
        $record = Db('record');
        $record->insert(['category'=>7,'content'=>'取消关注','creater_time'=>date('Y-m-d H:i:s',time()),
            'member_id'=>$id['id'],'category_id'=>self::$wechat['bu_id']
        ]);

        return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
    }

    /**
     * @descrpition 扫描二维码关注（未关注时）
     * @param $request
     * @return array
     */
    public static function eventQrsceneSubscribe(&$request,$scan){
        /*
        *用户扫描带参数二维码进行自动分组
        *此处添加此代码是大多数需求是在扫描完带参数二维码之后对用户自动分组
        */
        $sceneid = str_replace("qrscene_","",$request['eventkey']);

        return ResponsePassive::view($request['fromusername'], $request['tousername'], $sceneid,0,self::$wechat,$scan);


        $sceneid = str_replace("qrscene_","",$request['eventkey']);
        //移动用户到相应分组中去,此处的$sceneid依赖于之前创建时带的参数
        if(!empty($sceneid)){
            \LaneWeChat\Core\UserManage::editUserGroup($request['fromusername'], $sceneid);
            $result=\LaneWeChat\Core\UserManage::getGroupByOpenId($request['fromusername']);
            //查看参数正确性
            $content = '欢迎您关注我们的微信，将为您竭诚服务。二维码Id:'.$result['groupid'];
        }else
            $content = '欢迎您关注我们的微信，将为您竭诚服务。';
        return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
    }

    /**
     * @descrpition 扫描二维码（已关注时）
     * @param $request
     * @return array
     */
    public static function eventScan(&$request){
        $content = '您已经关注了';
        return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
    }

    /**
     * @descrpition 上报地理位置
     * @param $request
     * @return array
     */
    public static function eventLocation(&$request){
        $content = '';
        return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
    }

    /**
     * @descrpition 自定义菜单 - 点击菜单拉取消息时的事件推送
     * @param $request
     * @return array
     */
    public static function eventClick(&$request){
        //获取该分类的信息
        $eventKey = $request['eventkey'];
        // $content = '收到点击菜单事件，您设置的key是' . $eventKey;
        return ResponsePassive::clickInfo($request['fromusername'], $request['tousername'], $eventKey);
    }

    /**
     * @descrpition 自定义菜单 - 点击菜单跳转链接时的事件推送
     * @param $request
     * @return array
     */
    public static function eventView(&$request){
        //获取该分类的信息
        $eventKey = $request['eventkey'];
        $content = '收到跳转链接事件，您设置的key是' . $eventKey;
        return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
    }

    /**
     * @descrpition 自定义菜单 - 扫码推事件的事件推送
     * @param $request
     * @return array
     */
    public static function eventScancodePush(&$request){
        //获取该分类的信息
        $eventKey = $request['eventkey'];
        $content = '收到扫码推事件的事件，您设置的key是' . $eventKey;
        $content .= '。扫描信息：'.$request['scancodeinfo'];
        $content .= '。扫描类型(一般是qrcode)：'.$request['scantype'];
        $content .= '。扫描结果(二维码对应的字符串信息)：'.$request['scanresult'];
        return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
    }

    /**
     * @descrpition 自定义菜单 - 扫码推事件且弹出“消息接收中”提示框的事件推送
     * @param $request
     * @return array
     */
    public static function eventScancodeWaitMsg(&$request){
        //获取该分类的信息
        $eventKey = $request['eventkey'];
        $content = '收到扫码推事件且弹出“消息接收中”提示框的事件，您设置的key是' . $eventKey;
        $content .= '。扫描信息：'.$request['scancodeinfo'];
        $content .= '。扫描类型(一般是qrcode)：'.$request['scantype'];
        $content .= '。扫描结果(二维码对应的字符串信息)：'.$request['scanresult'];
        return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
    }

    /**
     * @descrpition 自定义菜单 - 弹出系统拍照发图的事件推送
     * @param $request
     * @return array
     */
    public static function eventPicSysPhoto(&$request){
        //获取该分类的信息
        $eventKey = $request['eventkey'];
        $content = '收到弹出系统拍照发图的事件，您设置的key是' . $eventKey;
        $content .= '。发送的图片信息：'.$request['sendpicsinfo'];
        $content .= '。发送的图片数量：'.$request['count'];
        $content .= '。图片列表：'.$request['piclist'];
        $content .= '。图片的MD5值，开发者若需要，可用于验证接收到图片：'.$request['picmd5sum'];
        return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
    }

    /**
     * @descrpition 自定义菜单 - 弹出拍照或者相册发图的事件推送
     * @param $request
     * @return array
     */
    public static function eventPicPhotoOrAlbum(&$request){
        //获取该分类的信息
        $eventKey = $request['eventkey'];
        $content = '收到弹出拍照或者相册发图的事件，您设置的key是' . $eventKey;
        $content .= '。发送的图片信息：'.$request['sendpicsinfo'];
        $content .= '。发送的图片数量：'.$request['count'];
        $content .= '。图片列表：'.$request['piclist'];
        $content .= '。图片的MD5值，开发者若需要，可用于验证接收到图片：'.$request['picmd5sum'];
        return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
    }

    /**
     * @descrpition 自定义菜单 - 弹出微信相册发图器的事件推送
     * @param $request
     * @return array
     */
    public static function eventPicWeixin(&$request){
        //获取该分类的信息
        $eventKey = $request['eventkey'];
        $content = '收到弹出微信相册发图器的事件，您设置的key是' . $eventKey;
        $content .= '。发送的图片信息：'.$request['sendpicsinfo'];
        $content .= '。发送的图片数量：'.$request['count'];
        $content .= '。图片列表：'.$request['piclist'];
        $content .= '。图片的MD5值，开发者若需要，可用于验证接收到图片：'.$request['picmd5sum'];
        return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
    }

    /**
     * @descrpition 自定义菜单 - 弹出地理位置选择器的事件推送
     * @param $request
     * @return array
     */
    public static function eventLocationSelect(&$request){
        //获取该分类的信息
        $eventKey = $request['eventkey'];
        $content = '收到点击跳转事件，您设置的key是' . $eventKey;
        $content .= '。发送的位置信息：'.$request['sendlocationinfo'];
        $content .= '。X坐标信息：'.$request['location_x'];
        $content .= '。Y坐标信息：'.$request['location_y'];
        $content .= '。精度(可理解为精度或者比例尺、越精细的话 scale越高)：'.$request['scale'];
        $content .= '。地理位置的字符串信息：'.$request['label'];
        $content .= '。朋友圈POI的名字，可能为空：'.$request['poiname'];
        return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
    }

    /**
     * 群发接口完成后推送的结果
     *
     * 本消息有公众号群发助手的微信号“mphelper”推送的消息
     * @param $request
     */
    public static function eventMassSendJobFinish(&$request){
        //发送状态，为“send success”或“send fail”或“err(num)”。但send success时，也有可能因用户拒收公众号的消息、系统错误等原因造成少量用户接收失败。err(num)是审核失败的具体原因，可能的情况如下：err(10001), //涉嫌广告 err(20001), //涉嫌政治 err(20004), //涉嫌社会 err(20002), //涉嫌色情 err(20006), //涉嫌违法犯罪 err(20008), //涉嫌欺诈 err(20013), //涉嫌版权 err(22000), //涉嫌互推(互相宣传) err(21000), //涉嫌其他
        $status = $request['status'];
        //计划发送的总粉丝数。group_id下粉丝数；或者openid_list中的粉丝数
        $totalCount = $request['totalcount'];
        //过滤（过滤是指特定地区、性别的过滤、用户设置拒收的过滤，用户接收已超4条的过滤）后，准备发送的粉丝数，原则上，FilterCount = SentCount + ErrorCount
        $filterCount = $request['filtercount'];
        //发送成功的粉丝数
        $sentCount = $request['sentcount'];
        //发送失败的粉丝数
        $errorCount = $request['errorcount'];
        $content = '发送完成，状态是'.$status.'。计划发送总粉丝数为'.$totalCount.'。发送成功'.$sentCount.'人，发送失败'.$errorCount.'人。';
        return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
    }

    /**
     * 群发接口完成后推送的结果
     *
     * 本消息有公众号群发助手的微信号“mphelper”推送的消息
     * @param $request
     */
    public static function eventTemplateSendJobFinish(&$request){
        //发送状态，成功success，用户拒收failed:user block，其他原因发送失败failed: system failed
        $status = $request['status'];
        if($status == 'success'){
            //发送成功
        }else if($status == 'failed:user block'){
            //因为用户拒收而发送失败
        }else if($status == 'failed: system failed'){
            //其他原因发送失败
        }
        return true;
    }


    public static function test(){
        // 第三方发送消息给公众平台
        $encodingAesKey = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFG";
        $token = "pamtest";
        $timeStamp = "1409304348";
        $nonce = "xxxxxx";
        $appId = "wxb11529c136998cb6";
        $text = "<xml><ToUserName><![CDATA[oia2Tj我是中文jewbmiOUlr6X-1crbLOvLw]]></ToUserName><FromUserName><![CDATA[gh_7f083739789a]]></FromUserName><CreateTime>1407743423</CreateTime><MsgType><![CDATA[video]]></MsgType><Video><MediaId><![CDATA[eYJ1MbwPRJtOvIEabaxHs7TX2D-HV71s79GUxqdUkjm6Gs2Ed1KF3ulAOA9H1xG0]]></MediaId><Title><![CDATA[testCallBackReplyVideo]]></Title><Description><![CDATA[testCallBackReplyVideo]]></Description></Video></xml>";


        $pc = new Aes\WXBizMsgCrypt($token, $encodingAesKey, $appId);
        $encryptMsg = '';
        $errCode = $pc->encryptMsg($text, $timeStamp, $nonce, $encryptMsg);
        if ($errCode == 0) {
            print("加密后: " . $encryptMsg . "\n");
        } else {
            print($errCode . "\n");
        }

        $xml_tree = new \DOMDocument();
        $xml_tree->loadXML($encryptMsg);
        $array_e = $xml_tree->getElementsByTagName('Encrypt');
        $array_s = $xml_tree->getElementsByTagName('MsgSignature');
        $encrypt = $array_e->item(0)->nodeValue;
        $msg_sign = $array_s->item(0)->nodeValue;

        $format = "<xml><ToUserName><![CDATA[toUser]]></ToUserName><Encrypt><![CDATA[%s]]></Encrypt></xml>";
        $from_xml = sprintf($format, $encrypt);

// 第三方收到公众号平台发送的消息
        $msg = '';
        $errCode = $pc->decryptMsg($msg_sign, $timeStamp, $nonce, $from_xml, $msg);
        if ($errCode == 0) {
            print("解密后: " . $msg . "\n");
        } else {
            print($errCode . "\n");
        }
    }


    /*
    *  保存用户信息
    */
    public static function handleNewUser( &$user_info ) {
        $obj = Db('member');
        $unionid =$obj->where(array("unionid"=>$user_info['unionid']))->find();
        //保存用户新
        if(empty($unionid)){
            $obj->insert( ['unionid'=>$user_info['unionid'],'city'=>$user_info['city'],'nickname'=>$user_info['nickname'],'sex'=>$user_info['sex'],'headimgurl'=>$user_info['headimgurl'], 'create_time'=>Date("Y-m-d H:i:s", time()),'is_bu_id'=>$user_info['bu_id']] );
        }
    }

    /*
    *  保存用户关联的openid 和 unionid
    */
    public static function handleMapOpenidAndUninid( &$user_info ) {
        $obj = Db('member_openid_unionid');
        $unionid =$obj->where(array("unionid"=>$user_info['unionid'],'bu_id'=>$user_info['bu_id']))->find();
        //保存用户新
        if(empty($unionid)){
            $obj->insert( ['unionid'=>$user_info['unionid'], 'openid'=>$user_info['openid'], 'bu_id'=>$user_info['bu_id'],'create_time'=>Date("Y-m-d H:i:s", time())] );
        }
    }

}