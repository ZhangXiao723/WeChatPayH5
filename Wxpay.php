<?php
ini_set('date.timezone', 'Asia/Shanghai');

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "lib/WxPay.Api.php");
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "lib/WxPay.Notify.php");
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "unit/WxPay.CommonFun.php");
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "unit/WxPay.Config.php");
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'unit/log.php');

$wxPayCommonFuc = new WxPayCommonFun();
$WxPayConfig = new WxPayConfig();
$WxPayApi = new WxPayApi();

/*=====================================*/

/**请在此书写您的业务逻辑

/*=====================================*/



$total_fee = '1'; //支付的金额
$out_trade_no = "1234"; //网站的订单号

$url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';  //下单地址
$appid = $WxPayConfig->GetAppId();//公众号appid
$appsecret = $WxPayConfig->GetAppSecret();
$mch_id = $WxPayConfig->GetMerchantId();//商户平台id
$nonce_str = $WxPayApi->getNonceStr();//随机数


$trade_type = 'MWEB';
$attach = '测试';
$body = '测试';
$notify_url = $WxPayConfig->GetNotifyUrl();
$notify_url .= 'notify.php'; //支付回调的地址
$ip = $wxPayCommonFuc->getClientIp();

$arr = array(
    'appid' => $appid,
    'mch_id' => $mch_id,
    'nonce_str' => $nonce_str,
    'out_trade_no' => $out_trade_no,
    'spbill_create_ip' => getIp(),
    'total_fee' => $total_fee,
    'trade_type' => "MWEB",
    'attach' => $attach,
    'body' => $body,
    'notify_url' => $notify_url
);

$sign = $wxPayCommonFuc->getSign($arr, $WxPayConfig);

$data = '<xml>
 <appid>' . $appid . '</appid>
 <attach>' . $attach . '</attach>
 <body>' . $body . '</body>
 <mch_id>' . $mch_id . '</mch_id>
 <nonce_str>' . $nonce_str . '</nonce_str>
 <notify_url>' . $notify_url . '</notify_url>
 <out_trade_no>' . $out_trade_no . '</out_trade_no>
 <spbill_create_ip>'.getIp().'</spbill_create_ip>
 <total_fee>' . $total_fee . '</total_fee>
 <trade_type>' . $trade_type . '</trade_type>
 <sign>' . $sign . '</sign>
 </xml>';
$result = $wxPayCommonFuc->https_request($WxPayConfig, $data, $url, $useCert = false, $second = 30);

//禁止引用外部xml实体
libxml_disable_entity_loader(true);
$result_info = json_decode(json_encode(simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
$result_url = $result_info['mweb_url'];

$arr = array(
    'deposit_id' => $deposit_id,
    'url' => $result_url
);

if($result_url){
    json_return(ERROR_CODE_SUCCESS, $arr, '');
}else{
    json_return(ERROR_CODE_FAIL, '', '发起支付请求失败，请重试');
}