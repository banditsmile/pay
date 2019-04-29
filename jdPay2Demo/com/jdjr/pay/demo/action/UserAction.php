<?php
namespace com\jdjr\pay\demo\action;
use com\jdjr\pay\demo\common\ConfigUtil;
use com\jdjr\pay\demo\common\HttpUtils;
use com\jdjr\pay\demo\common\XMLUtil;
include '../common/ConfigUtil.php';
include '../common/HttpUtils.php';
include '../common/XMLUtil.php';
class UserAction{
	public function execute(){
		$param["version"]=$_POST["version"];
		$param["merchant"]=$_POST["merchant"];
		$param["userId"]=$_POST["userId"];
		$userType=$_POST["userType"];
// 		$param["version"]="V2.0";
// 		$param["merchant"]="22294531";
// 		$param["userId"]="1234";
// 		$tradeType="1";
		if ($userType =="0" ){
			$url=ConfigUtil::get_val_by_key("getUserRelationUrl");
		}elseif ($userType =="1" ){
			$url=ConfigUtil::get_val_by_key("cancelUserRelationUrl");
		}	
		$reqXmlStr = XMLUtil::encryptReqXml($param);
		
		echo "请求地址：".$url;
		echo "----------------------------------------------------------------------------------------------";
		echo $reqXmlStr."\n";
		$httputil = new HttpUtils();
		list ( $return_code, $return_content )  = $httputil->http_post_data($url, $reqXmlStr);
		//echo $return_content."\n";
		$resData;
		$flag=XMLUtil::decryptResXml($return_content,$resData);
		//echo var_dump($resData);
		
		if($flag){
			
			$_SESSION["user"]=$resData;
			//echo var_dump($resData);
			if ($userType =="0" ){
				header("location:../page/showGetUserRelationResult.php");
			}elseif ($userType =="1" ){
				header("location:../page/showCancelUserRelationResult.php");
			}
		}else{
			echo "验签失败";
		}
	}
	
	
}
error_reporting(0);
$m = new UserAction();
$m->execute();
?>