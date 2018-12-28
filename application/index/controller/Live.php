<?php
namespace app\index\controller;
use think\Controller;
/**
* 直播测试
*	appid: 1256441419	 bizid : 22875
*/
class Live extends Controller
{
	
	public function controller() {
		parent::controller();
		vendor("Qcloudapi.QcloudApi");
		$config = array('SecretId'       => '',  //你的secretId
						'SecretKey'      => '',  //你的secretKey
						'RequestMethod'  => 'GET',
						'DefaultRegion'  => 'gz');

		$cvm = \QcloudApi::load(QcloudApi::MODULE_CVM, $config);
		$package = array('offset' => 0, 'limit' => 3, 'SignatureMethod' =>'HmacSHA256');
		$a = $cvm->DescribeInstances($package);
		// $a = $cvm->generateUrl('DescribeInstances', $package);
		if ($a === false) {
			$error = $cvm->getError();
			echo "Error code:" . $error->getCode() . ".\n";
			echo "message:" . $error->getMessage() . ".\n";
			echo "ext:" . var_export($error->getExt(), true) . ".\n";
		} else {
			var_dump($a);
		}

		echo "\nRequest :" . $cvm->getLastRequest();
		echo "\nResponse :" . $cvm->getLastResponse();
		echo "\n";
		exit;
	}
	
    public function index() {
		echo $this->getPushUrl("22875","123456","861f505deb3292c1bf3685326b15c08f","2018-4-20 12:00:00");
		print_r($this->getPlayUrl("22875","123456"));
	}
	
	/**
	* 获取推流地址
	* 如果不传key和过期时间，将返回不含防盗链的url
	* @param bizId 您在腾讯云分配到的bizid
	* @param streamId 您用来区别不同推流地址的唯一id
	* @param key 安全密钥
	* @param time 过期时间 sample 2018-4-12 12:00:00
	* @return String url */
	private function getPushUrl($bizId, $streamId, $key = null, $time = null){
		if($key && $time){
			$txTime = strtoupper(base_convert(strtotime($time),10,16));
			//txSecret = MD5( KEY + livecode + txTime )
			//livecode = bizid+"_"+stream_id  如 8888_test123456
			$livecode = $bizId."_".$streamId; //直播码
			$txSecret = md5($key.$livecode.$txTime);
			$ext_str = "?".http_build_query(array(
					"bizid"=> $bizId,
					"txSecret"=> $txSecret,
					"txTime"=> $txTime
			));
		}
		return "rtmp://".$bizId.".livepush.myqcloud.com/live/".$livecode.(isset($ext_str) ? $ext_str : "");
	}
	
	/**
	* 获取播放地址
	* @param bizId 您在腾讯云分配到的bizid
	*        streamId 您用来区别不同推流地址的唯一id
	* @return String url 
	*/
	private function getPlayUrl($bizId, $streamId){
		$livecode = $bizId."_".$streamId; //直播码
		return array(
			"rtmp://".$bizId.".liveplay.myqcloud.com/live/".$livecode,
			"http://".$bizId.".liveplay.myqcloud.com/live/".$livecode.".flv",
			"http://".$bizId.".liveplay.myqcloud.com/live/".$livecode.".m3u8"
		);
	}
	
		


}
