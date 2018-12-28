<?php
// +------------------------------------------------+
// |   2214601330@qq.com   网易直播组装工具
// +------------------------------------------------+
// |   modfid by xiakai    2016、11、21
// +------------------------------------------------+
namespace app\index\controller;
use think\Controller;
/**
 * 网易开发者API SDK
 */
class Wylive{
    private $AppKey;                //开发者平台分配的AppKey
    private $AppSecret;             //开发者平台分配的AppSecret,可刷新
    private $Nonce;                 //随机数（最大长度128个字符）
    private $CurTime;               //当前UTC时间戳，从1970年1月1日0点0 分0 秒开始到现在的秒数(String)
    private $CheckSum;              //SHA1(AppSecret + Nonce + CurTime),三个参数拼接的字符串，进行SHA1哈希计算，转化成16进制字符(String，小写)
    const   HEX_DIGITS = "0123456789abcdef";
    public function __construct(){
        $this->AppKey    = '5b08064b5c0b440586c77d990f25ede2';
        $this->AppSecret = 'cb922869a02f4c3b832e7df3102eac88';
    }
    /**生成验证码**/
    public function checkSumBuilder(){
        //此部分生成随机字符串
        $hex_digits = self::HEX_DIGITS;
        $this->Nonce;
        for($i=0;$i<128;$i++){           //随机字符串最大128个字符，也可以小于该数
            $this->Nonce.= $hex_digits[rand(0,15)];
        }
        $this->CurTime = (string)(time());   //当前时间戳，以秒为单位

        $join_string = $this->AppSecret.$this->Nonce.$this->CurTime;
        $this->CheckSum = sha1($join_string);

    }

    /*****post请求******/
    public function postDataCurl($url,$data=array()){
        $this->checkSumBuilder();        //发送请求前需先生成checkSum
        if(!empty($data)){
            $json=json_encode($data);
        }else{
            $json="";
        }
        $timeout = 5000;
        $http_header = array(
            'AppKey:'.$this->AppKey,
            'Nonce:'.$this->Nonce,
            'CurTime:'.$this->CurTime,
            'CheckSum:'.$this->CheckSum,
            'Content-Type: application/json;charset=utf-8;',
            'Content-Length: ' . strlen($json)
        );
        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_URL, $url);
        curl_setopt ($ch, CURLOPT_POST, 1);
        curl_setopt ($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt ($ch, CURLOPT_HEADER, false);
        curl_setopt ($ch, CURLOPT_HTTPHEADER,$http_header);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($ch);
        if (false === $result) {
            $result =  curl_errno($ch);
        }
        curl_close($ch);
        return json_decode($result,true) ;
    }


    /*
    *直播服务sdk
    */
    /**
     *直播完毕回调地址
     */
    public function live_end($recordClk){
        $url="https://vcloud.163.com/app/record/setcallback";
        return $data=$this->postDataCurl($url,array("recordClk"=>$recordClk));
    }

    /***频道添加***/
    public function channel_add($name,$type=0){
        $url="https://vcloud.163.com/app/channel/create";
        return $data=$this->postDataCurl($url,array("name"=>$name,"type"=>$type));
    }
    /****频道更新*****/
    public function channel_update($name,$cid,$type=0){
        $url="https://vcloud.163.com/app/channel/update";
        return $data=$this->postDataCurl($url,array("name"=>$name,"cid"=>$cid,"type"=>$type));
    }
    /****频道删除******/
    public function channel_delete($cid){
        $url="https://vcloud.163.com/app/channel/delete";
        return $data=$this->postDataCurl($url,array("cid"=>$cid));
    }
    /****获取频道信息******/
    public function channel_get($cid){
        $url="https://vcloud.163.com/app/channelstats";
        return $data=$this->postDataCurl($url,array("cid"=>$cid));
    }
    /***
    获取频道列表
    records int 单页记录数，默认值为10    否
    pnum    int 要取第几页，默认值为1 否
    ofield  String  排序的域，支持的排序域为：ctime（默认）  否
    sort    int 升序还是降序，1升序，0降序，默认为desc  否
     **/
    public function channel_list($option=array("records"=>20,"pnum"=>1,"ofield"=>"ctime","sort"=>1)){
        $url="https://vcloud.163.com/app/channellist";
        return $data=$this->postDataCurl($url,$option);
    }
    /**重新获取推流地址***/
    public function channel_reset($cid){
        $url="https://vcloud.163.com/app/address";
        return $data=$this->postDataCurl($url,array("cid"=>$cid));
    }
    /*****
    设置频道为录制状态
    cid String  频道ID    是
    needRecord  int 1-开启录制； 0-关闭录制  是
    format  int 1-flv； 0-mp4    是
    duration    int 录制切片时长(分钟)，默认120分钟  否
    filename    String  录制后文件名，格式为filename_YYYYMMDD-HHmmssYYYYMMDD-HHmmss,
    文件名录制起始时间（年月日时分秒) -录制结束时间（年月日时分秒)   否
     ****/

    public function channel_setRecord($option=array()){
        $url="https://vcloud.163.com/app/channel/setAlwaysRecord";
        return $data=$this->postDataCurl($url,$option);
    }
    /****暂停频道*****/
    public function channel_pause($cid){
        $url="https://vcloud.163.com/app/channel/pause";
        return $data=$this->postDataCurl($url,array("cid"=>$cid));
    }
    /****批量暂停频道****/
    public function channel_pauselist($cidList){
        $url="https://vcloud.163.com/app/channellist/pause";
        return $data=$this->postDataCurl($url,array("cidList"=>$cidList));
    }
    /****恢复频道*****/
    public function channel_resume($cid){
        $url="https://vcloud.163.com/app/channel/resume";
        return $data=$this->postDataCurl($url,array("cid"=>$cid));
    }
    /****批量恢复频道****/
    public function channel_resumelist($cidList){
        $url="https://vcloud.163.com/app/channellist/resume";
        return $data=$this->postDataCurl($url,array("cidList"=>$cidList));
    }
    /****获取频道的视频地址*****/
    public function channel_videolist($cid){
        $url="https://vcloud.163.com/app/videolist";
        return $data=$this->postDataCurl($url,array("cid"=>$cid));
    }
    /****设置视频录制回调地址*****/
    public function record_setcallback($recordClk){
        $url="https://vcloud.163.com/app/record/setcallback";
        return $data=$this->postDataCurl($url,array("recordClk"=>$recordClk));
    }

    /*
	*点播管理服务sdk
	*/
    /****创建视频分类*****/
    public function create_videoType($typeName,$description){
        $url="https://vcloud.163.com/app/vod/type/create";
        return $data=$this->postDataCurl($url,array("typeName"=>$typeName,'description'=>$description));
    }

    /****获取单个视频分类信息*****/
    public function get_videoType($typeId){
        $url="https://vcloud.163.com/app/vod/type/get";
        return $data=$this->postDataCurl($url,array("typeId"=>$typeId));
    }

    /****修改视频分类*****/
    public function edit_videoType($typeId){
        $url="https://vcloud.163.com/app/vod/type/update";
        return $data=$this->postDataCurl($url,array("typeId"=>$typeId,'typeName'=>$typeName,'description'=>$description));
    }

    /****删除视频分类*****/
    public function delete_videoType($typeId){
        $url="https://vcloud.163.com/app/vod/type/typeDelete";
        return $data=$this->postDataCurl($url,array("typeId"=>$typeId));
    }

    /****获取视频分类列表*****/
    public function get_typeList($currentPage,$pageSize){
        $url="https://vcloud.163.com/app/vod/type/list";
        return $data=$this->postDataCurl($url,array("currentPage"=>$currentPage,'pageSize'=>$pageSize));
    }

    /****设置视频的分类*****/
    public function set_videoType($vid,$typeId){
        $url="https://vcloud.163.com/app/vod/type/set";
        return $data=$this->postDataCurl($url,array("vid"=>$vid,'typeId'=>$typeId));
    }

    /****获取视频文件信息*****/
    public function get_videoInfo($vid){
        $url="https://vcloud.163.com/app/vod/video/get";
        return $data=$this->postDataCurl($url,array("vid"=>$vid));
    }

    /****获取视频文件信息列表*****/
    public function get_videoList($currentPage,$pageSize,$status,$type){
        $url="https://vcloud.163.com/app/vod/video/list";
        return $data=$this->postDataCurl($url,array("currentPage"=>$currentPage,"pageSize"=>$pageSize,"status"=>$status,"type"=>$type));
    }

    /****删除视频文件*****/
    public function delete_video($vid){
        $url="https://vcloud.163.com/app/vod/video/videoDelete";
        return $data=$this->postDataCurl($url,array("vid"=>$vid));
    }

    /**合并视频 */
    public function merge_video($name, $vids) {
        if (!$name || empty($vids)) {
            return ['code' => -1];
        }
        if (count($vids) == 1) {
            return ['code' => 200];
        }
        $url = 'https://vcloud.163.com/app/video/merge';
        return $data=$this->postDataCurl($url,array("vidList"=>$vids, 'outputName'=>$name));
    }
}