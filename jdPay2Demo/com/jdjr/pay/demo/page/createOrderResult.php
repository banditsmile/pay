<?php 
error_reporting(0);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<title>京东支付</title>
<link rel="stylesheet" type="text/css"
	href="../../../../../css/main.css">
</head>
<body>

	<div class="content" align="center">

		<?php echo  $_SESSION['resultData']['result']['code']?>   <br />
		<?php echo  $_SESSION['resultData']['result']['desc']?><br/>
		<lable>订单号:</lable>
		<lable><?php echo  $_SESSION['resultData']['orderId']?></lable>
		<br />
		<lable>商户号:</lable>
		<lable><?php echo  $_SESSION['resultData']['merchant']?></lable>
		<br />
		<lable>商户名:</lable>
		<lable><?php echo  $_SESSION['resultData']['merchantName']?></lable>
		<br />
		<lable> 金额:</lable>
		<lable><?php echo  $_SESSION['resultData']['amount']?></lable>
		<br />
		<lable> 交易号：</lable>
	    <lable><?php echo  $_SESSION['resultData']['tradeNum']?></lable>
		<br />
		<lable> 二维码：</lable>
	    <lable><?php echo  $_SESSION['resultData']['qrCode']?></lable>
	    <br />
	    <lable>  有效期：</lable>
	    <lable><?php echo  $_SESSION['resultData']['expireTime']?></lable>
	    <br />

	</div>

</body>
</html>