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
	<?php echo $_SESSION['subhtml']?>
		<!-- <lable>版本号：</lable>
		<lable>${rresp.version}</lable>
		<br />
		<lable>商户号：</lable>
		<lable>${rresp.merchant}</lable>
		<br />
		<lable> 交易返回码： </lable>
		<lable> ${rresp.result.code}</lable>
		<br />
		<lable> 交易返回描述： </lable>
		<lable> ${rresp.result.desc} </lable>
		<br />
		<c:forEach items="${rresp.refList}" var="refund">
			<lable> 原流水号： </lable>
		<lable> ${refund.tradeNum}</lable>
		<br />
		<lable> 流水号： </lable>
		<lable> ${refund.oTradeNum}</lable>
		<br />
		<lable> 金额： </lable>
		<lable> ${refund.amount}</lable>
		<br />
		<lable> 交易时间： </lable>
		<lable> ${refund.tradeTime}</lable>
		<br />
		<lable> 状态： </lable>
		<lable> ${refund.status}</lable>
		<br />
		
		</c:forEach> -->
	</div>

</body>
</html>
