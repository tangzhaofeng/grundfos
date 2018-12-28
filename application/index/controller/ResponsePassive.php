<?php
namespace app\index\controller;
/**
 * 发送被动响应
 * Created by Lane.
 * User: lane
 * Date: 13-12-19
 * Time: 下午3:01
 * Mail: lixuan868686@163.com
 * Website: http://www.lanecn.com
 */
use app\admin\model\ActivityModel;
use app\admin\model\AppsecretModel;
use app\admin\model\GoodsModel;
use app\admin\model\InfoModel;
use app\admin\model\MembergroupModel;
use app\admin\model\MemberModel;
use app\admin\model\MemberopenunionModel;
use app\admin\model\AutoreplyModel;
use think\Controller;
use think\Cookie;
use think\Db;

class ResponsePassive extends Controller
{
    /**a
     * @descrpition 文本
     * @param $fromusername
     * @param $tousername
     * @param $content 回复的消息内容（换行：在content中能够换行，微信客户端就支持换行显示）
     * @param $funcFlag 默认为0，设为1时星标刚才收到的消息
     * @return string
     */
    public static function text($fromusername, $tousername, $content, $funcFlag = 0)
    {
        $template = <<<XML
<xml>
    <ToUserName><![CDATA[%s]]></ToUserName>
    <FromUserName><![CDATA[%s]]></FromUserName>
    <CreateTime>%s</CreateTime>
    <MsgType><![CDATA[text]]></MsgType>
    <Content><![CDATA[%s]]></Content>
    <FuncFlag>%s</FuncFlag>
</xml>
XML;
        return sprintf($template, $fromusername, $tousername, time(), $content, $funcFlag);
    }

    /**
     * @descrpition 图片
     * @param $fromusername
     * @param $tousername
     * @param $mediaId 通过上传多媒体文件，得到的id。
     * @param $funcFlag 默认为0，设为1时星标刚才收到的消息
     * @return string
     */
    public static function image($fromusername, $tousername, $mediaId, $funcFlag = 0)
    {
        $template = <<<XML
<xml>
    <ToUserName><![CDATA[%s]]></ToUserName>
    <FromUserName><![CDATA[%s]]></FromUserName>
    <CreateTime>%s</CreateTime>
    <MsgType><![CDATA[image]]></MsgType>
    <Image>
    <MediaId><![CDATA[%s]]></MediaId>
    </Image>
    <FuncFlag>%s</FuncFlag>
</xml>
XML;
        return sprintf($template, $fromusername, $tousername, time(), $mediaId, $funcFlag);
    }

    /**
     * @descrpition 语音
     * @param $fromusername
     * @param $tousername
     * @param $mediaId 通过上传多媒体文件，得到的id
     * @param $funcFlag 默认为0，设为1时星标刚才收到的消息
     * @return string
     */
    public static function voice($fromusername, $tousername, $mediaId, $funcFlag = 0)
    {
        $template = <<<XML
<xml>
    <ToUserName><![CDATA[%s]]></ToUserName>
    <FromUserName><![CDATA[%s]]></FromUserName>
    <CreateTime>%s</CreateTime>
    <MsgType><![CDATA[voice]]></MsgType>
    <Voice>
    <MediaId><![CDATA[%s]]></MediaId>
    </Voice>
    <FuncFlag>%s</FuncFlag>
</xml>
XML;
        return sprintf($template, $fromusername, $tousername, time(), $mediaId, $funcFlag);
    }

    /**
     * @descrpition 视频
     * @param $fromusername
     * @param $tousername
     * @param $mediaId 通过上传多媒体文件，得到的id
     * @param $title 标题
     * @param $description 描述
     * @param $funcFlag 默认为0，设为1时星标刚才收到的消息
     * @return string
     */
    public static function video($fromusername, $tousername, $mediaId, $title, $description, $funcFlag = 0)
    {
        $template = <<<XML
<xml>
    <ToUserName><![CDATA[%s]]></ToUserName>
    <FromUserName><![CDATA[%s]]></FromUserName>
    <CreateTime>%s</CreateTime>
    <MsgType><![CDATA[video]]></MsgType>
    <Video>
    <MediaId><![CDATA[%s]]></MediaId>
    <Title><![CDATA[%s]]></Title>
    <Description><![CDATA[%s]]></Description>
    </Video>
    <FuncFlag>%s</FuncFlag>
</xml>
XML;
        return sprintf($template, $fromusername, $tousername, time(), $mediaId, $title, $description, $funcFlag);
    }

    /**
     * @descrpition 音乐
     * @param $fromusername
     * @param $tousername
     * @param $title 标题
     * @param $description 描述
     * @param $musicUrl 音乐链接
     * @param $hqMusicUrl 高质量音乐链接，WIFI环境优先使用该链接播放音乐
     * @param $thumbMediaId 缩略图的媒体id，通过上传多媒体文件，得到的id
     * @param $funcFlag 默认为0，设为1时星标刚才收到的消息
     * @return string
     */
    public static function music($fromusername, $tousername, $title, $description, $musicUrl, $hqMusicUrl, $thumbMediaId, $funcFlag = 0)
    {
        $template = <<<XML
<xml>
    <ToUserName><![CDATA[%s]]></ToUserName>
    <FromUserName><![CDATA[%s]]></FromUserName>
    <CreateTime>%s</CreateTime>
    <MsgType><![CDATA[music]]></MsgType>
    <Music>
    <Title><![CDATA[%s]]></Title>
    <Description><![CDATA[%s]]></Description>
    <MusicUrl><![CDATA[%s]]></MusicUrl>
    <HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
    <ThumbMediaId><![CDATA[%s]]></ThumbMediaId>
    </Music>
    <FuncFlag>%s</FuncFlag>
</xml>
XML;
        return sprintf($template, $fromusername, $tousername, time(), $title, $description, $musicUrl, $hqMusicUrl, $thumbMediaId, $funcFlag);
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
    public static function newsItem($title, $description, $picUrl, $url)
    {
        $template = <<<XML
<item>
  <Title><![CDATA[%s]]></Title>
  <Description><![CDATA[%s]]></Description>
  <PicUrl><![CDATA[%s]]></PicUrl>
  <Url><![CDATA[%s]]></Url>
</item>
XML;
        return sprintf($template, $title, $description, $picUrl, $url);
    }

    /**
     * @descrpition 图文 - 先调用self::newsItem()再调用本方法
     * @param $fromusername
     * @param $tousername
     * @param $item 数组，每个项由self::newsItem()返回
     * @param $funcFlag 默认为0，设为1时星标刚才收到的消息
     * @return string
     */
    public static function news($fromusername, $tousername, $item, $funcFlag = 0)
    {
        //多条图文消息信息，默认第一个item为大图,注意，如果图文数超过10，则将会无响应
        if (count($item) >= 10) {
            $request = array('fromusername' => $fromusername, 'tousername' => $tousername);
            return Msg::returnErrMsg(MsgConstant::ERROR_NEWS_ITEM_COUNT_MORE_TEN, '图文消息的项数不能超过10条', $request);

        }
        $template = <<<XML
<xml>
    <ToUserName><![CDATA[%s]]></ToUserName>
    <FromUserName><![CDATA[%s]]></FromUserName>
    <CreateTime>%s</CreateTime>
    <MsgType><![CDATA[news]]></MsgType>
    <ArticleCount>%s</ArticleCount>
    <Articles>
    %s
    </Articles>
    <FuncFlag>%s</FuncFlag>
</xml>
XML;
        return sprintf($template, $fromusername, $tousername, time(), count($item), implode($item), $funcFlag);
    }

    /**
     * 将消息转发到多客服
     * 如果公众号处于开发模式，需要在接收到用户发送的消息时，返回一个MsgType为transfer_customer_service的消息，微信服务器在收到这条消息时，会把这次发送的消息转到多客服系统。用户被客服接入以后，客服关闭会话以前，处于会话过程中，用户发送的消息均会被直接转发至客服系统。
     * @param $fromusername
     * @param $tousername
     * @return string
     */
    public static function forwardToCustomService($fromusername, $tousername)
    {
        $template = <<<XML
<xml>
    <ToUserName><![CDATA[%s]]></ToUserName>
    <FromUserName><![CDATA[%s]]></FromUserName>
    <CreateTime>%s</CreateTime>
    <MsgType><![CDATA[transfer_customer_service]]></MsgType>
</xml>
XML;
        return sprintf($template, $fromusername, $tousername, time());
    }

    /**
     * 带参数二维码选择活动
     */
    public static function view($fromusername, $tousername, $content, $funcFlag = 0, $wechat,$scan)//scan表示是否关注
    {
        $template = <<<XML
<xml>
    <ToUserName><![CDATA[%s]]></ToUserName>
    <FromUserName><![CDATA[%s]]></FromUserName>
    <CreateTime>%s</CreateTime>
    <MsgType><![CDATA[text]]></MsgType>
    <Content><![CDATA[%s]]></Content>
    <FuncFlag>%s</FuncFlag>
</xml>
XML;

        //url前缀与参数二维码类型
        $url = '<a href="https://www.juplus.cn/glf/index/index?type=';
        $arr = explode('_',$content);

        //先对用户进行基本信息注册
        $index = new Index();
        $token = $index->getAccessToken($wechat['appid'],$wechat['appsecret'],$wechat['type']);
        $member_info = curl_get('https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$token.'&openid='.$fromusername);
        $user_info = json_decode($member_info,true);//获取到用户基本信息
        $user_info['bu_id'] = $wechat['bu_id'];

        switch ($arr[0]){
            case '1'://活动
                //查询活动详情
                $activity = new ActivityModel();
                $line = $activity->where('id', $arr[1])->find();
                //查询得到type标识
                $appsecret = new AppsecretModel();
                $type = $appsecret->where('bu_id',$line['bu_id'])->value('type');

                //发送的链接与设置参数
                if ($line['line'] == 1) {
                    $content = $url.$type.'&view=/activity/activityDetail_xia.html&activity_id='.$line['id'].'&is_open='.$line['is_open'].'">点击进入分享活动</a>';
                } else {
                    $content = $url.$type.'&view=/activity/activityDetail_shang.html&activity_id='.$line['id'].'&is_open='.$line['is_open'].'">点击进入分享活动</a>';
                }
                break;
            case '2'://普通二维码,对用户进行分组与打标签
                //获取标签
                $dataqrcode = Db('data_qrcode');
                $grouping = Db('grouping');
                $gruop = new Group();

                $qrcode_info = $dataqrcode->where('id',$arr[1])->find();
                $grouping_info = $grouping->where('id',$qrcode_info['grouping_id'])->find();


                $user_info['grouping_id'] = $grouping_info['id'];
                $user_info['data_qrcode_id'] = $qrcode_info['id'];
                //基本信息注册并打上标签
                $member_id = self::handleNewUser($user_info);
//                self::handleMapOpenidAndUninid($user_info);



                //在微信中对用户就行标签
                $gruop->editUserGroup($wechat,$fromusername,$grouping_info['wx_id']);

                //记录用户操作
                if ($scan == 0){
                    $record = Db('record');
                    $record->insert(['category'=>2,'content'=>'带参数二维码关注','creater_time'=>date('Y-m-d H:i:s',time()),
                        'member_id'=>$member_id,'category_id'=>$arr[1]
                    ]);
                }

                $auto = new AutoreplyModel();
                $text = $auto->where('key','关注')->where('bu_id','like', '%' .  $wechat['bu_id'] . '%')->value('content');

                //如果有添加参数二维码回复的值,则回复二维码的
                if($qrcode_info['push']){
                    //if($scan == 0){
                    // $content = "欢迎关注".$text.",".$qrcode_info['push'];
                    //}else{
                    $content = $qrcode_info['push'];
                    //}
                }else{
                    //回复的信息
                    $auto = new AutoreplyModel();
                    $text = $auto->where('key','关注')->where('bu_id','like', '%' .  $wechat['bu_id'] . '%')->value('content');
                    //如果已经注册会员的则不提示
                    if (self::isUpLogin($fromusername)){
                        $content = "欢迎关注".$text;
                    }else{
                        $content = "欢迎您关注".$text."，<a href='https://www.juplus.cn/glf/index/index/index?type=".$wechat['type']."&view=/register/index.html'>欢迎您注册成为会员</a>";
                    }
                }

                break;
            case '3'://获取关注二维码
                $from_member_id = $arr[1];//推荐者id
                $member_id = self::handleNewUser($user_info);
                $content = '<a href="https://www.juplus.cn/glf/index/index?type='.$wechat['type'].'&view=/personal/invites.html&from_member_id='.$arr[1].'">请点击继续完成注册,赢取积分</a>';

                break;
        }


        return sprintf($template, $fromusername, $tousername, time(), $content , $funcFlag);
    }

    /**
     * 是否已经注册
     */
    public static function isUpLogin($fromusername)
    {
        $obj = new MemberModel();

        $info = $obj->isOpenLogin($fromusername);
        if ($info['name']){
            return true;
        }else{
            return false;
        }
    }

    /**
     *  保存用户信息
     */
    public static function handleNewUser( $user_info ) {
        $obj = new MemberModel();
        $unionid = $obj->where("unionid",$user_info['unionid'])->find();
        //保存用户新

        if(empty($unionid)){
     $unionid['id'] = $obj->insert(['unionid'=>$user_info['unionid'],'city'=>$user_info['city'],'nickname'=>$user_info['nickname'],'sex'=>$user_info['sex'],
            'headimgurl'=>$user_info['headimgurl'], 'create_time'=>Date("Y-m-d H:i:s", time()),
            'is_bu_id'=>$user_info['bu_id']],false,true);
        
        }

        //保存用户新
        $ob = Db('member_openid_unionid');
        $unioni =$ob->where(array("unionid"=>$user_info['unionid'],'bu_id'=>$user_info['bu_id']))->find();
        //保存用户新
        if(empty($unioni)){
            $ob->insert( ['unionid'=>$user_info['unionid'], 'openid'=>$user_info['openid'], 'bu_id'=>$user_info['bu_id'],'create_time'=>Date("Y-m-d H:i:s", time()),'is_cancel'=>1] );
        }else{
            $new = new MemberopenunionModel();
            $new->save(['is_cancel'=>1],['unionid'=>$user_info['unionid']]);
        }

        //如果分组改变重新打上分组
        $member_group = new MembergroupModel();
        $result = $member_group->where('bu_id',$user_info['bu_id'])->where('member_id',$unionid['id'])->find();
        if ($result['id']){
            $member_group->edit([
                'group_id'=>$user_info['grouping_id'],
                'data_qrcode_id'=>$user_info['data_qrcode_id'],
                'id'=>$result['id']
            ]);
            return $unionid['id'];
        }
        $member_group->insert([
            'bu_id'=>$user_info['bu_id'],
            'member_id'=>$unionid['id'],
            'group_id'=>$user_info['grouping_id'],
            'data_qrcode_id'=>$user_info['data_qrcode_id']
        ]);


        return $unionid['id'];
    }

    /*
    *  保存用户关联的openid 和 unionid
    */
    public static function handleMapOpenidAndUninid( $user_info ) {
        $obj = Db('member_openid_unionid');
        $unionid =$obj->where(array("unionid"=>$user_info['unionid'],'bu_id'=>$user_info['bu_id']))->find();
        //保存用户新
        if(empty($unionid)){
            $obj->insert( ['unionid'=>$user_info['unionid'], 'openid'=>$user_info['openid'], 'bu_id'=>$user_info['bu_id'],'create_time'=>Date("Y-m-d H:i:s", time()),'is_cancel'=>1] );
        }else{
            $new = new MemberopenunionModel();
            $new->save(['is_cancel'=>1],['unionid'=>$user_info['unionid']]);
        }
    }


    /*
      *  保存用户信息
      */
    public static function newUser( $user_info ) {
        $obj = Db('member');
        $unionid =$obj->where(array("unionid"=>$user_info['unionid']))->find();
        //保存用户新
        if(empty($unionid)){
            $obj->insert( ['unionid'=>$user_info['unionid'],'city'=>$user_info['city'],'nickname'=>$user_info['nickname'],'sex'=>$user_info['sex'],
                'headimgurl'=>$user_info['headimgurl'], 'create_time'=>Date("Y-m-d H:i:s", time()),
                'is_bu_id'=>$user_info['bu_id']] );
            return $obj->id;
        }
        return $unionid['id'];
    }

    /*
    *  保存用户关联的openid 和 unionid
    */
    public static function openidAndUninid( $user_info ) {
        $obj = Db('member_openid_unionid');
        $unionid =$obj->where(array("unionid"=>$user_info['unionid'],'bu_id'=>$user_info['bu_id']))->find();
        //保存用户新
        if(empty($unionid)){
            $obj->insert( ['unionid'=>$user_info['unionid'], 'openid'=>$user_info['openid'], 'bu_id'=>$user_info['bu_id'],'create_time'=>Date("Y-m-d H:i:s", time())] );
        }
    }

    /**
     * click菜单配置返回信息
     **/
    public static function clickInfo($fromusername, $tousername, $eventKey, $funcFlag = 0)
    {
        $obj = new InfoModel();
        $content = $obj->where('key',$eventKey)->value('content');

        $template = <<<XML
<xml>
    <ToUserName><![CDATA[%s]]></ToUserName>
    <FromUserName><![CDATA[%s]]></FromUserName>
    <CreateTime>%s</CreateTime>
    <MsgType><![CDATA[text]]></MsgType>
    <Content><![CDATA[%s]]></Content>
    <FuncFlag>%s</FuncFlag>
</xml>
XML;
        return sprintf($template, $fromusername, $tousername, time(), $content , $funcFlag);
    }
}

