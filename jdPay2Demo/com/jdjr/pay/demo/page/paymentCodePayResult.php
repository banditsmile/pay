<?php
error_reporting(0);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8?>">
<meta http-equiv="expires" content="0" />
<meta http-equiv="pragma" content="no-cache" />
<meta http-equiv="cache-control" content="no-cache" />
<link rel="stylesheet" type="text/css"
	href="../../../../../css/main.css">
<title>"京东支付--付款码支付</title>

</head>
<body>
		<div class="content?>">
			<div class="content_0?>">
					<label>tradeNum:</label>
					<input type="text" name="tradeNum" value="<?php echo$_SESSION['resultData']['tradeNum']?>"><br />
					<label>tradeTime:</label>
					<input type="text" name="tradeTime" value="<?php echo$_SESSION['resultData']['tradeTime']?>"><br />
					<label>amount:</label>
					<input type="text" name="amount" value="<?php echo$_SESSION['resultData']['amount']?>"><br />
					<label>currency:</label>
					<input type="text" name="currency" value="<?php echo$_SESSION['resultData']['currency']?>"><br />
					<label>note:</label>
					<input type="text" name="note" value="<?php echo$_SESSION['resultData']['note']?>"><br />
					<label>sign:</label>
					<input type="text" name="sign" value="<?php echo$_SESSION['resultData']['sign']?>"><br />
					<label>version:</label>
					<input type="text" name="version" value="<?php echo$_SESSION['resultData']['version']?>"><br />
					<label>merchant:</label>
					<input type="text" name="merchant" value="<?php echo$_SESSION['resultData']['merchant']?>"><br />
					<label>status:</label>
					<input type="text" name="status" value="<?php echo$_SESSION['resultData']['status']?>"><br />
					<label>result.code:</label>
					<input type="text" name="code" value="<?php echo$_SESSION['resultData']['result']['code']?>"><br />
					<label>result.desc:</label>
					<input type="text" name="desc" value="<?php echo$_SESSION['resultData']['result']['desc']?>"><br />
				</div>
			</div>

</body>
</html>