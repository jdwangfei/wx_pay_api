<?php 
/**
*微信小程序支付后台交易程序
**/
require_once "../lib/WxPay.Api.php";
require_once "WxPay.Config.php";
require_once 'log.php';
header('Access-Control-Allow-Origin:*');//注意！跨域要加这个头
//初始化日志
$logHandler= new CLogFileHandler("../logs/".date('Y-m-d').'.log');
$log = Log::Init($logHandler);

if($_SERVER['REQUEST_METHOD'] != 'POST'){
	return_err('error request method');
}

try{
	$openid = $_POST['openid'];//openid 微信唯一识别码
	$body = $_POST['body'];//设置商品或支付单简要描述
	$order_sn = $_POST['order_sn'];//订单号
	$total_fee = $_POST['total_fee'];//付款金额

	//统一下单
	$input = new WxPayUnifiedOrder();
	$input->SetBody($body);//设置商品或支付单简要描述
	$input->SetAttach($body);//设置附加数据，在查询API和支付通知中原样返回，该字段主要用于商户携带订单的自定义数据
	$input->SetOut_trade_no($order_sn);//设置商户系统内部的订单号,32个字符内、可包含字母, 其他说明见商户订单号
	$input->SetTotal_fee($total_fee);//设置订单总金额，只能为整数，详见支付金额
	$input->SetTime_start(date("YmdHis"));
	$input->SetTime_expire(date("YmdHis", time() + 600));
	//$input->SetGoods_tag("test");//设置商品标记，代金券或立减优惠功能的参数，说明详见代金券或立减优惠
	$input->SetNotify_url( 'https://'.$_SERVER['HTTP_HOST'].'/wxpay/notify.php');
	$input->SetTrade_type("JSAPI");
	$input->SetOpenid($openId);
	$config = new WxPayConfig();
	$order = WxPayApi::unifiedOrder($config, $input);
	return_data($order);
} catch(Exception $e) {
	Log::ERROR(json_encode($e));
}
/**
 * 错误返回提示
 * @param string $errMsg 错误信息
 * @param string $status 错误码
 * @return  json的数据
 */
function return_err($errMsg='error',$status=0){
	$ret_str = json_encode(array('status'=>$status,'result'=>'fail','errmsg'=>$errMsg));
    Log::ERROR("error request method");
	exit($ret_str);

}
/**
 * 正确返回
 * @param 	array $data 要返回的数组
 * @return  json的数据
 */
function return_data($data=array()){
	$ret_str = json_encode(array('status'=>1,'result'=>'success','data'=>$data));
    Log::INFO($ret_str);
	exit($ret_str);
}
?>