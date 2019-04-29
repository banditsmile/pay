<?php
namespace com\jdjr\pay\demo\action;
use com\jdjr\pay\demo\common\ConfigUtil;
use com\jdjr\pay\demo\common\HttpUtils;
use com\jdjr\pay\demo\common\XMLUtil;
include '../common/ConfigUtil.php';
include '../common/HttpUtils.php';
include '../common/XMLUtil.php';
class QueryBtStrategyAction{
	public function execute(){
		$param["version"]=$_POST["version"];
		$param["merchant"]=$_POST["merchant"];
		$param["amount"]=$_POST["amount"];
		$param["tradeNum"]=$_POST["tradeNum"];
// 		$param["version"]="V2.0";
// 		$param["merchant"]="22294531";
// 		$param["amount"]="1001";
// 		$param["tradeNum"]="12121234";

		$url=ConfigUtil::get_val_by_key("queryBaiTiaoFQUrl");

		$reqXmlStr = XMLUtil::encryptReqXml($param);
		
		echo "请求地址：".$url;
		echo "----------------------------------------------------------------------------------------------";
		echo $reqXmlStr."\n";
		$httputil = new HttpUtils();
		list ( $return_code, $return_content )  = $httputil->http_post_data($url, $reqXmlStr);
		//echo $return_content."\n";
		$resData;
		$flag=XMLUtil::decryptResXml($return_content,$resData);
		
		$htmlStr="";


		foreach($resData['billsInfoList'] as $k => $v){
			$b[$k]=$v;
			foreach($b[$k] as $h => $l){
				$c[$h]=$l;
				$htmlStr=$htmlStr."<br /><lable>期数:</lable>";
				$htmlStr=$htmlStr."<lable>".$c[$h]['plan']."</lable>";
				$htmlStr=$htmlStr."<br /><lable>费率:</lable>";
				$htmlStr=$htmlStr."<lable>".$c[$h]['rate']."</lable>";
				$htmlStr=$htmlStr."<br /><lable>总手续费:</lable>";
				$htmlStr=$htmlStr."<lable>".$c[$h]['fee']."</lable>";
				$htmlStr=$htmlStr."<br /><lable>每期费率:</lable>";
				$htmlStr=$htmlStr."<lable>".$c[$h]['planFee']."</lable>";
				$htmlStr=$htmlStr."<br /><lable>首期付费:</lable>";
				$htmlStr=$htmlStr."<lable>".$c[$h]['firstPay']."</lable>";
				$htmlStr=$htmlStr."<br /><lable>非首期付款:</lable>";
				$htmlStr=$htmlStr."<lable>".$c[$h]['laterPay']."</lable>";
				$htmlStr=$htmlStr."<br /><lable>付款总金额:</lable>";
				$htmlStr=$htmlStr."<lable>".$c[$h]['total']."</lable>";
			}
		}
		
		if($flag){
			
			$_SESSION["BtStrategy"]=$resData;
			echo $htmlStr;
			$_SESSION['subhtml']=$htmlStr;
			//echo var_dump($resData);
			header("location:../page/queryBtStrategyResult.php");	
		}else{
			echo "验签失败";
		}
	}
	
	
}
error_reporting(0);
$m = new QueryBtStrategyAction();
$m->execute();
?>