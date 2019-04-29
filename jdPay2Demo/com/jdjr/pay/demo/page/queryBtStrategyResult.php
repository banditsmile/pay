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
	
		<br /> 
		<lable> 版本号:</lable> 
		<lable><?php echo $_SESSION['BtStrategy']['version']?></lable>
		<br /> 
		<lable> 商户号:</lable> 
		<lable><?php echo $_SESSION['BtStrategy']['merchant']?></lable> 
		<br /> 
		<lable> 交易流水: </lable> 
		 <lable><?php echo $_SESSION['BtStrategy']['tradeNum']?></lable> 
		<br /> <lable> 交易返回码：</lable> 
		<lable><?php echo $_SESSION['BtStrategy']['result']['code']?></lable>
		<br />	<lable> 交易返回描述：</lable>
		 <lable><?php echo $_SESSION['BtStrategy']['result']['desc']?></lable>
		<?php echo $_SESSION['subhtml']?>
	</div>

</body>
</html>