<?php
namespace app\index\controller;
use think\Controller;
use app\index\controller\Index;
   header('Content-Type:text/html; charset=utf-8');
/**
 * 发送主动响应
 */
class SendOut extends Controller {
    protected static $queryUrl = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=';

    protected static $action = 'POST';

    private $config;
    private $member_info;

    public function __construct()
    {

        //自动载入函数载入相关操作类
        vendor("LaneWeChat.config");
        vendor("LaneWeChat.wechat");

    }


      /**
     * 向用户推送模板消息
     * @param $data = array(
     *                  'first'=>array('value'=>'您好，您已成功消费。', 'color'=>'#0A0A0A')
     *                  'keynote1'=>array('value'=>'巧克力', 'color'=>'#CCCCCC')
     *                  'keynote2'=>array('value'=>'39.8元', 'color'=>'#CCCCCC')
     *                  'keynote3'=>array('value'=>'2014年9月16日', 'color'=>'#CCCCCC')
     *                  'keynote3'=>array('value'=>'欢迎再次购买。', 'color'=>'#173177')
     * );
     * @param $touser 接收方的OpenId。
     * @param $templateId 模板Id。在公众平台线上模板库中选用模板获得ID
     * @param $url URL
     * @param string $topcolor 顶部颜色， 可以为空。默认是红色
     * @return array("errcode"=>0, "errmsg"=>"ok", "msgid"=>200228332} "errcode"是0则表示没有出错
     *
     * 注意：推送后用户到底是否成功接受，微信会向公众号推送一个消息。
     */
    public static function sendTemplateMessage($data, $touser, $templateId, $url, $topcolor='#FF0000',$config,$type){
       //获取ACCESS_TOKEN
        $index = new Index();

        $accessToken = $index->getAccessToken($config['appid'],$config['appsecret'],$type);

        $queryUrl = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token='.$accessToken;
        $queryAction = 'POST';
        $template = array();
        $template['touser'] = $touser;
        $template['template_id'] = $templateId;
        $template['url'] = $url;
        $template['topcolor'] = $topcolor;
        $template['data'] = $data;
        $template = json_encode($template);
        return \LaneWeChat\Core\Curl::callWebServer($queryUrl, $template, $queryAction);
    }


    /**
     * @descrpition 文本
     * @param $tousername
     * @param $content 回复的消息内容（换行：在content中能够换行，微信客户端就支持换行显示）
     * @return string
     */
    public static function text($tousername, $content,$config,$type){
        //获取ACCESS_TOKEN
        $index = new Index();
        header('Content-Type:text/html; charset=utf-8');
        $accessToken = $index->getAccessToken($config['appid'],$config['appsecret'],$type);
        //开始
        $template = array(
            'touser'=>$tousername,
            'msgtype'=>'text',
            'text'=>array(
                'content'=>$content,
            ),
        );
        $template = urldecode(json_encode($template, JSON_UNESCAPED_UNICODE));
        $url = 'https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token=';
//        dump($tousername);exit();
      return Curl::callWebServer(self::$queryUrl.$accessToken, $template, self::$action);
    }

 

    /**
     * @descrpition 图片
     * @param $tousername
     * @param $mediaId 通过上传多媒体文件，得到的id。
     * @return string
     */
    public static function image($tousername, $mediaId,$config,$type){
        $index = new Index();
        //获取ACCESS_TOKEN
        $accessToken = $index->getAccessToken($config['appid'],$config['appsecret'],$type);

        //开始
        $template = array(
            'touser'=>$tousername,
            'msgtype'=>'image',
            'image'=>array(
                'media_id'=>$mediaId,
            ),
        );
        $template = json_encode($template);
        return Curl::callWebServer(self::$queryUrl.$accessToken, $template, self::$action);
    }

    /**
     * @descrpition 语音
     * @param $tousername
     * @param $mediaId 通过上传多媒体文件，得到的id
     * @return string
     */
    public static function voice($tousername, $mediaId){
        //获取ACCESS_TOKEN
        $accessToken = AccessToken::getAccessToken();

        //开始
        $template = array(
            'touser'=>$tousername,
            'msgtype'=>'voice',
            'voice'=>array(
                'media_id'=>$mediaId,
            ),
        );
        $template = json_encode($template);
        return Curl::callWebServer(self::$queryUrl.$accessToken, $template, self::$action);
    }

    /**
     * @descrpition 视频
     * @param $tousername
     * @param $mediaId 通过上传多媒体文件，得到的id
     * @param $title 标题
     * @param $description 描述
     * @return string
     */
    public static function video($tousername, $mediaId, $title, $description){
        //获取ACCESS_TOKEN
        $accessToken = AccessToken::getAccessToken();

        //开始
        $template = array(
            'touser'=>$tousername,
            'msgtype'=>'video',
            'video'=>array(
                'media_id'=>$mediaId,
                'title'=>$title,
                'description'=>$description,
            ),
        );
        $template = json_encode($template);
        return Curl::callWebServer(self::$queryUrl.$accessToken, $template, self::$action);
    }

    /**
     * @descrpition 音乐
     * @param $tousername
     * @param $title 标题
     * @param $description 描述
     * @param $musicUrl 音乐链接
     * @param $hqMusicUrl 高质量音乐链接，WIFI环境优先使用该链接播放音乐
     * @param $thumbMediaId 缩略图的媒体id，通过上传多媒体文件，得到的id
     * @return string
     */
    public static function music($tousername, $title, $description, $musicUrl, $hqMusicUrl, $thumbMediaId){
        //获取ACCESS_TOKEN
        $accessToken = AccessToken::getAccessToken();

        //开始
        $template = array(
            'touser'=>$tousername,
            'msgtype'=>'music',
            'music'=>array(
                'title'=>$title,
                'description'=>$description,
                'musicurl'=>$musicUrl,
                'hqmusicurl'=>$hqMusicUrl,
                'thumb_media_id'=>$thumbMediaId,
            ),
        );
        $template = json_encode($template);
        return Curl::callWebServer(self::$queryUrl.$accessToken, $template, self::$action);
    }

    /**
     * @descrpition 图文消息 - 单个项目的准备工作，用于内嵌到self::news()中。现调用本方法，再调用self::news()
     *              多条图文消息信息，默认第一个item为大图,注意，如果调用本方法得到的数组总项数超过10，则将会无响应
     * @param $title 标题
     * @param $description 描述
     * @param $picUrl 图片链接，支持JPG、PNG格式，较好的效果为大图360*200，小图200*200
     * @param $url 点击图文消息跳转链接
     * @return string
     */
    public static function newsItem($title, $description, $picUrl, $url){
        return $template = array(
            'title'=>$title,
            'description'=>$description,
            'url'=>$url,
            'picurl'=>$picUrl,
        );
    }

    /**
     * @descrpition 图文 - 先调用self::newsItem()再调用本方法
     * @param $tousername
     * @param $item 数组，每个项由self::newsItem()返回
     * @return string
     */
    public static function news($tousername, $item){
        //获取ACCESS_TOKEN
        $accessToken = AccessToken::getAccessToken();

        //开始
        $template = array(
            'touser'=>$tousername,
            'msgtype'=>'news',
            'news'=>array(
                'articles'=>$item
            ),
        );
        $template = json_encode($template);
        return Curl::callWebServer(self::$queryUrl.$accessToken, $template, self::$action);
    }


}