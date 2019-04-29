<?php
use com\jdjr\pay\demo\common\ConfigUtil;
include '../common/ConfigUtil.php';

error_reporting(0);

date_default_timezone_set("PRC");
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta http-equiv="expires" content="0" />
<meta http-equiv="pragma" content="no-cache" />
<meta http-equiv="cache-control" content="no-cache" />
<link rel="stylesheet" type="text/css"
	href="../../../../../css/main.css">
<title>"京东支付--客户支付</title>

</head>
<body>
	<form action="../action/customerClientOrder.php" method="post" target="_blank">
		<div class="content">
			<div class="content_0">
				<label>version:</label>
				<input type="text" name="version" value="V2.0"> <br />
				<label>merchant:</label>
				<input type="text" name="merchant" value="<?php echo ConfigUtil::get_val_by_key('merchantNum');?>"> <br />
				<label>payMerchant:</label>
				<input type="text" name="payMerchant" value=""> <br />
				<label>device:</label>
				<input type="text" name="device" value="111"> <br />
				<label>tradeNum:</label>
				<input type="text" name="tradeNum" value="<?php echo time()?>"> <br />
				<label>tradeName:</label>
				<input type="text" name="tradeName" value="商品1111"> <br />
				<label>tradeDesc:</label>
				<input type="text" name="tradeDesc" value="交易描述"> <br />
				<label>tradeTime:</label>
				<input type="text" name="tradeTime" value="<?php echo  date('YmdHis')?>"> <br />
				<label>amount:</label>
				<input type="text" name="amount" > <br />
				<label>currency:</label>
				<input type="text" name="currency" value="CNY"> <br />
				<label>note:</label>
				<input type="text" name="note" value="备注"> <br />
				<label>callbackUrl:</label>
				<input type="text" name="callbackUrl" value="<?php echo ConfigUtil::get_val_by_key('callbackUrl');?>"> <br />
				<label>notifyUrl:</label>
				<input type="text" name="notifyUrl" value="<?php echo ConfigUtil::get_val_by_key('notifyUrl');?>"> <br />
				<label>ip:</label>
				<input type="text" name="ip" value="10.45.251.153"> <br />
				<label>expireTime:</label>
				<input type="text" name="expireTime" value=""> <br />
				<label>industryCategoryCode:</label>
				<input type="text" name="industryCategoryCode" value=""> <br />
				<label>orderType:</label>
				<input type="text" name="orderType" value="1"> <br />
				<label>tradeType:</label>
				<input type="text" name="tradeType" value="CUST"> <br />
				<label>riskInfo:</label>
				<input type="text" name="riskInfo" value=""> <br />
				<label>saveUrl:</label>
				<input type="text" name="saveUrl" value="http://h5pay.jd.com/jdpay/customerPay"> <br /> 
				
				<input type="submit" value="京东支付" id="showlayerButton" class="btn">

			</div>
		</div>
	</form>

</body>
</html>