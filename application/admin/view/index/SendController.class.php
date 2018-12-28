<?php
namespace Home\Controller;
use Think\Controller;
class SendController extends CommonController {
	
	public function form()
	{
		$db = M( 'user' );
		$gameover = $this->userinfo[ 'gameover' ];
		$is_send = $this->userinfo[ 'is_send' ];
		$openid = $this->userinfo[ 'openid' ];
		
		
		if( !$gameover && !$is_send ){
			
			$data = $_POST;
			
			//性别
			$sex = (int)$data[ 'sex' ];
			if( $sex === 1 || $sex === 0 )
			{
				
			}else{
				$this->result( '请选择性别' );
			}
			
			//姓
			$name = $this->filter( $data[ 'name' ], '请填写姓' );
			
			//名
			$name2 = $this->filter( $data[ 'name2' ], '请填写姓' );
			
			//手机
			$phone = $data[ 'phone' ];
			if( !preg_match( '/^0?1[3|4|5|6|7|8][0-9]\d{8}$/', $phone ) )
			{
				$this->result( '请输入正确的电话' );
			}
			
			//公司
			$company = $this->filter( $data[ 'company' ], '请填写公司' );
			
			//职位
			$position = $this->filter( $data[ 'position' ], '请填写职位' );
			
			//从事行业年限
			$age = (int)$data[ 'age' ];
			if( $age === 1 || $age === 2 )
			{
				
			}else{
				$this->result( '请选择从事行业年限' );
			}
			
			
			if( is_array( $data[ 'industry' ] ) && !empty( $data[ 'industry' ] ) )
			{
				$industry = array_unique( $data[ 'industry' ] );
			}else{
				$this->result( '请选择专业领域' );
			}
			
			$data2[ 'sex' ] = $sex;
			$data2[ 'name' ] = $name;
			$data2[ 'name2' ] = $name2;
			$data2[ 'phone' ] = $phone;
			$data2[ 'company' ] = $company;
			$data2[ 'position' ] = $position;
			$data2[ 'age' ] = $age;
			$data2[ 'industry' ] = serialize( $industry );
			$data2[ 'send_time' ] = time();
			$where[ 'openid' ] = $openid;
			$result = $db->where( $where )->save( $data2 );
			
			//抽奖ID
			$cz_id = $db->order('cz_id desc')->find();
			$cz_id = $cz_id[ 'cz_id' ];
			if( !$cz_id )
			{
				$cz_id = 1;
			}else{
				$cz_id = $cz_id + 1;
			}
			
			if( $cz_id > 3000 )
			{
				$czid = 0;
			}else{
				$czid = $cz_id;
			}
			
			$db->data( array( 'cz_id' => $czid ) )->where( array( 'openid' => $openid ) )->save();
			
			//更新
			if( $result )
			{
				$user = M( 'userData' )->where( $where )->find();
				
				//保存状态
				$db->data( array( 'gameover' => 1 ) )->where( array( 'openid' => $openid ) )->save();
				session( 'ytg_userinfo.gameover', 1 );
				
				//开始发送红包
				if( $cz_id < 2000 ){
					//证书
					$ssl = array();
					$ssl[ 'cert' ] = getcwd() . '/pay_ssl/apiclient_cert.pem';
					$ssl[ 'key' ]  = getcwd() . '/pay_ssl/apiclient_key.pem';
					$ssl[ 'ca' ]  = getcwd() . '/pay_ssl/rootca.pem';
					
					//随机字符串 32
					$nonce_str = $this->rands( 32 );
					
					//订单号
					$mch_billno = time() . 'AID' . $user[ 'id' ];
					
					//商户号
					$mch_id = C( 'mch_id' );
					
					//公众账号appid
					$wxappid = C( 'app_id' );
					
					//商户名称
					$send_name = 'Entegris应特格微电子';
					
					//用户openid
					$re_openid = $openid;
					
					//付款金额
					$total_amount = 100;
					
					//红包发放总人数
					$total_num = 1;
					
					//红包祝福语
					$wishing = 'Entegris!';
					
					//Ip地址
					$client_ip = '121.40.218.106';
					
					//活动名称
					$act_name = 'Entrgris互动';
					
					//备注
					$remark = '快来抢！';
					
					$data3 = array();
					$stringSignTemp = '';
					
					$data3[ 'sign' ] = '';
					
					$data3[ 'act_name' ] = $act_name;
					$data3[ 'client_ip' ] = $client_ip;
					$data3[ 'mch_billno' ] = $mch_billno;
					$data3[ 'mch_id' ] = $mch_id;
					$data3[ 'nonce_str' ] = $nonce_str;
					$data3[ 're_openid' ] = $re_openid;
					$data3[ 'remark' ] = $remark;
					$data3[ 'send_name' ] = $send_name;
					$data3[ 'total_amount' ] = $total_amount;
					$data3[ 'total_num' ] = $total_num;
					$data3[ 'wishing' ] = $wishing;
					$data3[ 'wxappid' ] = $wxappid;
					
					
					foreach( $data3 as $key => $value )
					{
						if( $key != 'sign' )
						{
							$stringSignTemp .= $key . '=' . $value . '&';
						}
					}
					
					$stringSignTemp .= 'key=' . C( 'pay_api' );
					//签名
					$sign = strtoupper( md5( $stringSignTemp ) );
					
					
					//$sign = strtoupper( hash_hmac("sha256", stringSignTemp, C( 'pay_api' ) ) );
					$data3[ 'sign' ] = $sign;
					
					$send = '<xml>';
					
					foreach( $data3 as $key => $value )
					{
						$send .= '<' . $key . '><![CDATA[' . $value . ']]></' . $key . '>';
					}
					
					$send .= '</xml>';
					
					
					$respond = httpPost( 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack', $send, $ssl );
					
					$dom = new \DOMDocument();
					$dom->loadXML($respond);
					$status = $dom->getElementsByTagName('result_code');
					$r = $status->item(0)->nodeValue;
					
					if( $r == 'SUCCESS' )
					{
						//保存状态
						$db->data( array( 'is_send' => 1 ) )->where( array( 'openid' => $openid ) )->save();
						session( 'ytg_userinfo.is_send', 1 );
					}
				}
				
				$this->result( $cz_id , 1 );
			}else{
				$this->result( '提交失败,请稍后再试！' );
			}
			
			
		}else{
			$this->result( '请勿重复提交！' );
		}
		
	}
	
	protected function result( $msg, $code = 0 )
	{
		$result[ 'code' ] = $code;
		$result[ 'msg' ] = $msg;
		//清除缓冲
		ob_clean();
		exit( json_encode( $result ) );
	}
	
	protected function filter( $value, $msg )
	{
		$value = htmlspecialchars( $value );
		if( empty( $value ) )
		{
			$this->result( $msg );
		}
		
		return $value;
	}
	
	protected function rands( $count )
	{
		$return = '';
		$str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		
		$str = str_split( $str, 1 );
		$length = count( $str );
		
		for( $i = 0; $i < $count; $i ++ )
		{
			$key = rand( 0, $length );
			$return .= $str[ $key ];
		}
		return $return;
	}
}