<?php
/**
 * Created by PhpStorm.
 * User: juplus-06
 * Date: 2018/4/9
 * Time: 16:05
 */
namespace app\api\controller;

use app\admin\model\ActivitymemberModel;
use app\admin\model\ActivityModel;
use app\admin\model\ActivityshareModel;
use app\admin\model\GoodsModel;
use app\admin\model\GroupingModel;
use app\admin\model\MembergroupModel;
use app\admin\model\MemberModel;
use app\admin\model\TemplateModel;
use app\api\model\AccessTokenModel;
use app\api\model\AppsecretModel;
use app\api\model\BuModel;
use app\index\controller\Group;
use app\index\controller\Index;
use app\index\controller\SendOut;
use think\Db;
use think\Exception;
use think\Request;
use think\Session;

class Activity extends Base
{
    private $member_info;
    private $obj;
    private $activity_member;
    private $app_secret;
    private $config;

    public function __construct()
    {
        // parent::__construct();

        $this->obj = new ActivityModel();
        $this->activity_member = new ActivitymemberModel();
        $type = session('type');
        $this->config = session('wxconfig_'.$type);
        $this->member_info = session($this->config['appid']."user_oauth");
        $this->app_secret = new AppsecretModel();
    }

    /**
     * 线下会展列表
     */
    function activityList()
    {
        try{
            // 获取当前bu全部有效的展会
            // dump($this->config);exit();
            $bu_id = $this->config['bu_id'];

            $line = 1;
            $info = $this->obj->getBuActivity($bu_id,$line);

            return $info;
        }catch (Exception $e){
            return returninfos('0','系统错误');
        }
    }

    /**
     * 获取线上活动
     */
    public function activityLine()
    {
        try{
            // 获取当前bu全部有效的展会
            $bu_id = $this->config['bu_id'];
            $line = 2;
            $info = $this->obj->getBuActivity($bu_id,$line);
            return $info;
        }catch (Exception $e){
            return returninfos('0','系统错误');
        }
    }

    /**
     * 会展详情
     */
    public function activityInfo()
    {
        try{
            //根据展会id获取详情
            $id = input('param.id');
            $info = $this->obj->where('id',$id)->find();
            return returninfos('1','获取成功',$info);
        }catch (Exception $e){
            return returninfos('0','系统错误');
        }
    }

    /**
     * 报名
     */
    public function activitySignUp()
    {
        try{
//             获取活动id与用户id
            if(request()->isPost()){

                //验证是否已经完成注册
                if($this->is_register() == 3){
                    return returninfos('3','请先注册');
                }

                $member_id = $this->member_info['id'];
                $member_work = Db('member')->where('id',$member_id)->value('company_work_type_id');
                $actitvity_id = input('post.id');

                //todo 目前因为无需注册也可报名关闭此功能 验证用户部门是否符合报名资格
                // $this->obj->isOpen($member_work,$actitvity_id);

                $info = $this->activity_member->signUp($member_id,$actitvity_id,$member_work);

                //报名成功就行下列操作
                if ($info['code'] == 1){
                    //获取用户当前公众号的openid
                    $member = new MemberModel();
                    $param['id'] = $member_id;
                    $param['bu_id'] = $this->config['bu_id'];
                    $openid = $member->getOpenid($param);

                    //在微信中对用户就行标签
                    $config = $this->config;
                    $config['type'] = session('type');

                    $grouping = new GroupingModel();//获取标签id
                    $grouping_info = $grouping->where('activity_id',$actitvity_id)->find();

                    $gruop = new Group();//对用户进行分组
                    $gruop->editUserGroup($config,$openid['openid'],$grouping_info['wx_id']);
                    $this->memberGroup($member_id,$grouping_info['id'],$param['bu_id']);//添加分组id到用户

                    $register = new Register();//获取活动详情
                    $register->sendtemplate($member_id,$this->config['bu_id'],$param,$actitvity_id,$config,session('type'),2);

                    //记录用户操作
                    $record = Db('record');
                    $record->insert(['category'=>5,'content'=>'报名活动','creater_time'=>date('Y-m-d H:i:s',time()),
                        'member_id'=>$member_id,'category_id'=>$actitvity_id
                    ]);
                }
                return returninfos($info['code'],$info['msg']);
            }
        }catch (Exception $e){
            return returninfos('0','系统错误');
        }
    }

    /**
     * 内部报名
     */
    public function scanActivitySignUp($actitvity_id)
    {
        try{
//             获取活动id与用户id
            if(request()->isPost()){

                //验证是否已经完成注册
                if($this->is_register() == 3){
                    return returninfos('3','请先注册');
                }

                $member_id = $this->member_info['id'];
                $member_work = Db('member')->where('id',$member_id)->value('company_work_type_id');

                //todo 目前因为无需注册也可报名关闭此功能 验证用户部门是否符合报名资格
                // $this->obj->isOpen($member_work,$actitvity_id);

                $info = $this->activity_member->signUp($member_id,$actitvity_id,$member_work);

                //报名成功就行下列操作
                if ($info['code'] == 1){
                    //获取用户当前公众号的openid
                    $member = new MemberModel();
                    $param['id'] = $member_id;
                    $param['bu_id'] = $this->config['bu_id'];
                    $openid = $member->getOpenid($param);

                    //在微信中对用户就行标签
                    $config = $this->config;
                    $config['type'] = session('type');

                    $grouping = new GroupingModel();//获取标签id
                    $grouping_info = $grouping->where('activity_id',$actitvity_id)->find();

                    $gruop = new Group();//对用户进行分组
                    $gruop->editUserGroup($config,$openid['openid'],$grouping_info['wx_id']);
                    $this->memberGroup($member_id,$grouping_info['id'],$param['bu_id']);//添加分组id到用户

                    $register = new Register();//获取活动详情
                    $register->sendtemplate($member_id,$this->config['bu_id'],$param,$actitvity_id,$config,session('type'),2);

                    //记录用户操作
                    $record = Db('record');
                    $record->insert(['category'=>5,'content'=>'报名活动','creater_time'=>date('Y-m-d H:i:s',time()),
                        'member_id'=>$member_id,'category_id'=>$actitvity_id
                    ]);
                }
                return returninfos($info['code'],$info['msg']);
            }
        }catch (Exception $e){
            return returninfos('0','系统错误');
        }
    }


    /**
     * 添加分组到用户
     */
    public function memberGroup($member_id,$group_id,$bu_id)
    {
        //将分组添加到数据库
        $member_group = new MembergroupModel();
        $member_group->save(['creater_time'=>date('Y-m-d h:i:s',time()),
            'bu_id'=>$bu_id,'member_id'=>$member_id,'group_id'=>$group_id
        ]);
    }

    /**
     * 查询线上是否已经分享与图片类型
     */
    public function getIsShare()
    {
        try{
            //查询出是否已经有上传分享图片
            $id = input('param.id');
            $member_id = $this->member_info['id'];

            $as = new ActivityshareModel();
            $info = $as->where(['activity_id'=>$id,'member_id'=>$member_id])->find();

            if ($info == null){
                return returninfos('2','无上传');
            }else{
                return returninfos('1','已上传');

            }

        }catch (Exception $e){
            return returninfos('0','系统错误');
        }
    }



    /**
     * 保存用户分享的图片
     */
    public function saveShare(Request $request)
    {
        if (request()->isPost()){
            //获取所有上传数据
            $activity_member = new ActivityshareModel();
            $member = $this->member_info;
            $post = input('post.');

            $param = array();
            $param['activity_id'] = $post['activeId'];
            $param['GPS'] = $post['latitude'].'X'.$post['longitude'];
            $param['status'] = 1;

            $img_list = array();
            foreach ($post['media'] as $k=>$V){
                $file = $this->doWechatPic($V);
                $img_list[$k] = $file;
            }

            $param['img'] = implode(',',$img_list);

            $param['member_id'] = $member['id'];

            $param['create_time'] = date('Y-m-d H:i:s',time());

            //记录用户操作
            $record = Db('record');
            $record->insert(['category'=>5,'content'=>'线上活动参加','creater_time'=>date('Y-m-d H:i:s',time()),
                'member_id'=>$member['id'],'category_id'=>$post['activeId']
            ]);

            return $activity_member->saveLine($param);

        }
    }

    /**
     *从微信服务器获取图片
     **/
    function doWechatPic($serverId)
    {
        $type = session('type');
        $wxconfig = session('wxconfig_'.$type);
        $url = input('param.url');

        $dow = ROOT_PATH . 'public' . DS . 'uploads/';
        $name = uniqid().'.jpg';
        $file = $dow.$name;
        //获取access_token
        $index = new Index();
        $access_token = $index->getAccessToken($wxconfig['appid'],$wxconfig['appsecret'],\session('type'));
        //通过接口获取图片流
        $pic_url = "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token={$access_token}&media_id={$serverId}";
        $ch = curl_init($pic_url);

        $fp = fopen($file,'wb');
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);

        return  '/public' . DS . 'uploads/'.$name;
    }

    /**
     * 获取最新accsee_token
     */
    public function getAccessToken($appid,$appsecret)
    {
        $token = new AccessTokenModel();
        $app_secret_id = session('app_secret_id');
        $old_accsee = $token->where('app_secret_id',$app_secret_id)->order('id desc')->group('id')->find();
        // dump($old_accsee);exit();
        if ($old_accsee){
            //如果有运算时间超过2小时重新获取
            if ((time()-$old_accsee['time']) > 7160){
                return $this->saveAccessToken($app_secret_id,$appid,$appsecret);
            }else{
                return $old_accsee['token'];
            }
        }else{
            return $this->saveAccessToken($app_secret_id,$appid,$appsecret);
        }
    }

    /**
     * 增加产品收藏数量
     */
    public function total()
    {
        $obj = new GoodsModel();
        $activity_id = input('param.activity_id');
        //获取到当前的收藏数
        $old_sum = $obj->where('id',$activity_id)->value('total');
        $new_sum = $old_sum + 1;
        //更新
        $obj->save(['total'=>$new_sum],['id'=>$activity_id]);
        //添加用户积分
        if (!Db::table('xk_member_integral')->where('title',5)->where('member_id', $this->member_info['id'])->where('title_id', $activity_id)->value('id')) {
            $regi = new Register();
            $regi->memberAddIntegral('5',2,$this->member_info['id'],$activity_id);
        }
        return json(['code'=>1,'msg'=>'添加成功']);
    }


    /**
     * 获取accsee_token并存储
     */
    function saveAccessToken($app_secret_id,$appid,$appsecret)
    {
        $token = new AccessTokenModel();
        $access = curl_get('https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appid.'&secret='.$appsecret);
        $info = json_decode($access);

        $token->save([
            'app_secret_id' => $app_secret_id,
            'token' => $info->access_token,
            'time' => time()
        ]);
        return $info->access_token;
    }


    /*
    *  获取参与抽奖的用户
    */
    function getActiveUser(){
        return json( Db("table")->where(['status'=>0])->select() );
    }

    /*
    * 随机获取抽中奖的用户
    */
    function getRoundAwardUser(){
        $ret = $this->getDataFromTable("table",0,1);
        //处理抽中的用户
        if(empty($ret)){
            $obj = Db();
            foreach($ret as $k=>$v){
                $obj->query("update table set status=1 where id=`".$vo['id']."` ");
            }
        }
        return json($ret);
    }

    /*
    *高效随机查询（sql优化-order by rand）
    *====返回数据库表中随机n条数据  15万行数据 花费时间 0.014970 秒 === 【测试ok 记：返回存在的数据】
    *param $table 表示要查询的表（需表的全名）
    *param $status 条件  status 0表示没有抽奖 1表示已经抽奖
    *param $returnNum 随机返回的列数
    *
    */
    private function getDataFromTable($table,$status=0,$returnNum=1){
        return Db()->query("SELECT * FROM
							`$table` AS t1
							JOIN (
								SELECT
									ROUND(
										RAND() * (
											(SELECT MAX(id) FROM `$table`) - (SELECT MIN(id) FROM `$table`)
										) + (SELECT MIN(id) FROM `$table`)
									) AS id
							) AS t2
							WHERE
								t1.id >= t2.id and status=`$status`
							ORDER BY
								t1.id
							LIMIT $returnNum");
    }
}