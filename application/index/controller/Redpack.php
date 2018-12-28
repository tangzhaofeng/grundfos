<?php
namespace app\index\controller;
use think\Controller;

class Redpack extends Controller
{
	
    public function index($member_openid,$momey) {
		$openid = $member_openid;
		$ret = $this->sendRedpack($momey, $openid);  //普通测试ok
		//$ret = $this->sendGroupRedpack(3,3.5,$openid);  //裂变红包测试ok
		return $ret;
	}
	
	
	/**
     * 普通红包发送
     * @param money    money 单位元  
     * @param openid   需要领取红包的用户
     * @return array
     */
	private function sendRedpack($money,$openid)
    {
        //调用请求接口基类
		Vendor('WeChatPay.WxPayPubHelper');
        $Redpack = new \Redpack_pub();
        //商户订单号
        $Redpack->setParameter('mch_billno', uniqid());
        //提供方名称
        $Redpack->setParameter('nick_name', "格兰富");
        //商户名称
        $Redpack->setParameter('send_name', "格兰富");
        //用户openid
        $Redpack->setParameter('re_openid', $openid);
        //付款金额  每个红包的金额必须在1-200元之间
        $Redpack->setParameter('total_amount', $money*100);
        //最小红包金额
        $Redpack->setParameter('min_value', $money*100);
        //最大红包金额
        $Redpack->setParameter('max_value', $money*100);
        //红包发放总人数
        $Redpack->setParameter('total_num', 1);
		//场景id发放红包使用场景，红包金额大于200时必传PRODUCT_1:商品促销PRODUCT_2:抽奖PRODUCT_3:虚拟物品兑奖 PRODUCT_4:企业内部福利PRODUCT_5:渠道分润PRODUCT_6:保险回馈PRODUCT_7:彩票派奖PRODUCT_8:税务刮奖
		//$Redpack->setParameter('scene_id', "PRODUCT_3");
        //红包祝福语
        $Redpack->setParameter('wishing', "恭喜你获得".number_format($money,2)."元现金红包。");
        //活动名称
        $Redpack->setParameter('act_name', "格兰富活动");
        //备注
        $Redpack->setParameter('remark', "test");
        //以下是非必填项目
        //子商户号  
//       $Redpack->setParameter('sub_mch_id', $parameterValue);
//      //商户logo的url
//      $Redpack->setParameter('logo_imgurl', $parameterValue);
//      //分享文案
//      $Redpack->setParameter('share_content', $parameterValue);
//      //分享链接
//      $Redpack->setParameter('share_url', $parameterValue);
//      //分享的图片
//      $Redpack->setParameter('share_imgurl', $parameterValue);
		return $Redpack->sendRedpack();
    }
	
	
	
	/**
     * 裂变红包发送
	 * @param total_num 裂变红包发放总人数
     * @param money     总金额（单位元  每个红包的平均金额必须在1.00元到200.00元之间. $total_num<$money）
     * @param openid    需要领取红包的用户
     * @return array
     */	
    public function sendGroupRedpack($total_num, $money,$openid)
    {
        //调用请求接口基类
		Vendor('WeChatPay.WxPayPubHelper');
        $Redpack = new \Groupredpack_pub();
        //商户订单号
        $Redpack->setParameter('mch_billno',uniqid());
        //商户名称
        $Redpack->setParameter('send_name', "格兰富");
        //用户openid
		$Redpack->setParameter('re_openid', $openid);
        //付款金额
        $Redpack->setParameter('total_amount', $money*100);
        //红包发放总人数
        $Redpack->setParameter('total_num', $total_num);
        $Redpack->setParameter('amt_type','ALL_RAND');
        //红包祝福语
        $Redpack->setParameter('wishing', "恭喜你获得格兰富红包。");
        //活动名称
        $Redpack->setParameter('act_name', "格兰富活动");
        //备注
        $Redpack->setParameter('remark', "备注:xxx");
        //以下是非必填项目
        //子商户号  
		//$Redpack->setParameter('sub_mch_id', $openid);
		//商户logo的url
		//$Redpack->setParameter('amt_list', '200|100|100');
        return $Redpack->sendRedpack();
    }
	
	
	
	
	
	
	

}
