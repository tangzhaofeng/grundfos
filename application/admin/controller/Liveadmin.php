<?php
/**
 * Created by PhpStorm.
 * User: juplus-06
 * Date: 2018/4/2
 * Time: 16:12
 */

namespace app\admin\controller;



use app\admin\model\BuModel;
use app\admin\model\LiveModel;
use app\admin\model\VideoModel;
use app\admin\model\MemberModel;
use app\api\controller\Live;
use app\index\controller\Wylive;
use think\Request;
use think\Db;
use app\admin\model\AppsecretModel;
use app\index\controller\Media;

class Liveadmin extends Base
{
    private $wylive;

    public function __construct()
    {
        parent::__construct();
        $this->wylive = new Wylive();
    }

    public function index()
    {
        $param = input('param.');

        $size = 10;
        $where = [];

        $obj = new LiveModel();

        $selectResult = $obj->getBuBywhere($where, $size);

        $return['total'] = $obj->getAllUsers($where);  //总数据

        $return['rows'] = $selectResult;
        $return['page'] = $selectResult->render();

        $this->assign('return',$return);
        return $this->fetch();
    }

    public function edit()
    {
        $obj = new LiveModel();
        $id = input('param.id');
        if(request()->isPost()){
            $param = input('post.');
            $param['describe'] = $_POST['describe'];
            return json($obj->edit($param));
        }else{
            $id = input('param.id');
            $this->assign('live',$obj->where('id',$id)->find());
            return $this->fetch();
        }
    }

    public function add()
    {
        $obj = new LiveModel();
        if(request()->isPost()){
            $param = input('post.');
            $is_tel = $this->isMember($param['tel']);
            $param['describe'] = $_POST['describe'];
            if ($is_tel){
                $param['member_id'] = $is_tel['id'];
            }else{
                return json(['code' => 0, 'data' => '', 'msg' => '没有此用户']);
            }
//            $param['pass'] = md5($param['pass']);//目前不需要加密
            $info = $obj->inster($param);
            if ($info['code'] == 1){//插入数据库成功再进行添加频道
                $live_info =  $this->wylive->channel_add($param['title']);
                if ($live_info['code'] == 200){
                    $parama['pushUrl'] = $live_info['ret']['pushUrl'];
                    $parama['hlsPullUrl'] = $live_info['ret']['hlsPullUrl'];
                    $parama['httpPullUrl'] = $live_info['ret']['httpPullUrl'];
                    $parama['rtmpPullUrl'] = $live_info['ret']['rtmpPullUrl'];
                    $parama['cid'] = $live_info['ret']['cid'];
                    $parama['id'] = $info['faq_id'];



                        $this->wylive->channel_setRecord([
                            'cid' => $parama['cid'],
                            'needRecord' => 1,
                            'format' => 0,
                            'duration' => 120,
                        ]);//开始录制
                        $this->wylive->record_setcallback('https://www.juplus.cn/glf/api/Live/saveVidos');//设置直播回调地址
                    $obj->edit($parama);
                }else{
                    return json(['code' => 0, 'data' => '', 'msg' => '创建频道失败'.json_encode($live_info)]);
                }
            }
            return json($info);
        }else{

            return $this->fetch();
        }
    }
    /**
     * 直播删除并关闭直播间
     */
    public function sampdelete()
    {
        $id = input('param.id');
        $obj = new LiveModel();
        $ret = $obj->del($id);
        if ($ret['code'] == 1){//并关闭直播间
            $this->wylive->channel_delete($ret['data']['cid']);
        }
        return json($ret);
    }
    /**
     * 检查本号码是否是注册了的
     */
    public function isMember($tel)
    {
        $member = new MemberModel();
        return $member->isTel($tel);
    }

    /**
     * 获取直播组
     */
    public function group() 
    {  
        
        $params = input('param.');
        if (isset($params['live_id'])) {
            if ($params['live_id'] == 0) {
                $params['live_id'] = collection(Db::table('xk_live')->select())->toArray()[0]['id'];
            }
            $live_id = $params['live_id'];
            $livegroup = Db::table('xk_live_group')->alias('a')
            ->join('xk_member b', 'a.member_id = b.id')
            ->where('live_id', $live_id)
            ->paginate(10,false,[
                'query' => [
                    'live_id' => $live_id
                ]
            ]);
            $this->assign('total', Db::table('xk_live_group')->alias('a')
            ->join('xk_member b', 'a.member_id = b.id')
            ->where('live_id', $live_id)->count());
            $this->assign('forward', Db::table('xk_live')->where('id', $live_id)->value('forward'));
            $this->assign('live_id', $live_id);
            $this->assign('livegroup', $livegroup);
            $this->assign('page', $livegroup->render());
            return $this->fetch('grouplist');
        } else {
            $livedata = collection(Db::table('xk_live')->select())->toArray();
            $this->assign('livedata', $livedata);
            $this->assign("url", url('group'));
            return $this->fetch();
        }
    }

    public function sendmessage() {
       
        if (request()->isPost()){
            $params = input('post.');
            //获取bu_id
            $live_id = $params['live_id'];
            $bu_id = Db::table('xk_live')->where('id', $live_id)->find()['bu_id'];
            //获取微信配置
            $appse = new AppsecretModel();
            $config = $appse->where('bu_id',$bu_id)->find();
            $member_id = collection(Db::table('xk_live_group')->where('live_id', $live_id)->select())->toArray();
            $type = $params['type'];
            switch ($type) {
                case 1: 
                    $member = new MemberModel();
                    foreach ($member_id as $key=>$val){
                        $info['bu_id'] = $bu_id;
                        $info['id'] = $val['member_id'];
                        $openid = $member->getOpenid($info);
                        $relute = \app\index\controller\SendOut::text($openid['openid'],$params['content'],$config,$config['type']);
                    }
                    echo json_encode(['code' => 1]);
                    break;
                case 3:
                    $send = new \app\index\controller\SendOut();
                    $med = new Media();
                    //获取到微信配置
                    $config = Db('bu_app_secret')->where('id',$bu_id)->find();
            
                    //先进行新增临时素材
                    $result = $med->temporary($config,'image',$params['media']);//截取到mediaId
                    //根据mediaId发送临时素材
                    if ($result['code'] == 1){
                        $member = new MemberModel();
            
                        foreach ($member_id as $key=>$val){
                            $info['bu_id'] = $bu_id;
                            $info['id'] = $val['member_id'];
                            $openid = $member->getOpenid($info);
                            $info = $send->image($openid['openid'],$result['media_id'],$config,$config['type']);
                        }
                        echo json_encode(['code' => 1]);
                    }else{
                        echo json_encode(['code' => 0, 'msg' => '推送失败']);
                    }
                    break;
            }
            exit;
        } else {
            $params = input('param.');
            $live_id = $params['live_id'];
            $this->assign('live_id', $live_id);
            return $this->fetch();
        }
    }
    //导出视频聊天记录
    public function getLivetalk() {
        $params = input('param.');
        $data = collection(Db::table('xk_live_comment')->alias('a')
        ->join('xk_member b', 'a.member_id = b.id')
        ->where('live_id', $params['live_id'])
        ->where('type', 1)
        ->field('`name`, `content`, `creater_time`')
        ->select())->toArray();
        // dump($data);
        $title =[ 'A'=>'用户姓名','B'=>'内容','C'=>'时间'];
        $this->export_xls($data,$title);
        exit();
    }

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

        $fileName = '直播聊天记录'.date('Y-m-d H:i:s',time());

        $objWrite = \PHPExcel_IOFactory::createWriter($obj, 'Excel2007');
        $obj->getActiveSheet()->getColumnDimension('A')->setWidth(30);
        $obj->getActiveSheet()->getColumnDimension('B')->setWidth(30);
        $obj->getActiveSheet()->getColumnDimension('C')->setWidth(30);
        $obj->getActiveSheet()->getColumnDimension('D')->setWidth(30);
        $obj->getActiveSheet()->getColumnDimension('E')->setWidth(30);
        $obj->getActiveSheet()->getColumnDimension('F')->setWidth(30);
        $obj->getActiveSheet()->getColumnDimension('G')->setWidth(30);
        $obj->getActiveSheet()->getColumnDimension('H')->setWidth(30);

        header('pragma:public');
        header("Content-Disposition:attachment;filename=$fileName.xls");
        $objWrite->save('php://output');exit;
    }

    //合并直播录制视频
    public function merge_video() {
        $params = input('param.');
        $live_cid = $params['cid'];
        $vidoemodel = new VideoModel;
        $livemodel = new LiveModel;
        $vids = $vidoemodel->where('cid', $live_cid)->order('id')->column('vid');
        if (count($vids) <= 1){
            echo '<h2>视频切片小于等于1，无需合成！</h2>';
            exit;
        }
        $livemsg = $livemodel->where('cid', $live_cid)->find();
        $name = $livemsg['title'];
        $res = $this->wylive->merge_video($name, $vids);
        if ($res['code'] == 200){
            //删除合并的视频
            Db::table('xk_video')->where('cid', $params['cid'])->delete();
            echo '<h2>已成功推送合并请求，请耐心等待合并！</h2>';
        } else {
            echo '合并失败！因为：'.json_encode($res);
        }
        exit;
    }

    //结束直播
    public function endLive() {
        $params = input('param.');
        Db::table('xk_live')->where('id', $params['id'])->update(['status' => 3]);
        echo "直播已结束";
    }
}