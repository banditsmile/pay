<?php
use com\jdjr\pay\demo\common\ConfigUtil;
include '../common/ConfigUtil.php';
error_reporting(0)
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<title>模拟商户--订单撤销页面</title>
<link rel="stylesheet" type="text/css"
	href="../../../../../css/main.css">
</head>
</head>
<body>

	<form method="post" action="../action/RevokeOrder.php" id="paySignForm">
		<div class="content">
			<div class="content_1">
				<ul class="form-wrap" id="J-form-wrap">

					<li class="form-item form-item-border clearfix"><label>接口版本</label>
						<input type="text" class="" name="version" value="V2.0"
						maxlength="18" /></li>

					<li class="form-item form-item-border clearfix"><label>商户号</label>
						<input type="text" class="" name="merchant" value="<?php echo ConfigUtil::get_val_by_key('merchantNum');?>"
						placeholder="请输入商户号" maxlength="50" /></li>

					<li class="form-item form-item-border clearfix"><label>交易号</label>
						<input type="text" class="" name="tradeNum" value=""
						placeholder="请输入交易号" maxlength="50" /></li>

					<li class="form-item form-item-border clearfix"><label>原交易号</label>
						<input type="text" class="" name="oTradeNum" value=""
						placeholder="请输入原交易号" maxlength="50" /></li>

					<li class="form-item form-item-border clearfix"><label>交易时间</label>
						<input type="text" class="" name="tradeTime" value=""
						 placeholder="请输入交易时间" maxlength="50" />
					</li>

					<li class="form-item form-item-border clearfix"><label>交易金额</label>
						<input type="text" class="" name="amount" value="1"
						autocomplete="off" placeholder="请输入交易金额" maxlength="50"
						data-callback="input.status" /></li>

					<li class="form-item form-item-border clearfix"><label>货币种类</label>
						<input type="text" class="" name="currency" value="CNY"
						autocomplete="off" placeholder="请输入交易币种" maxlength="50"
						data-callback="input.status" /></li>


					<li class="form-item form-item-border clearfix"><label>交易备注</label>
						<input type="text" class="" name="note" value=""
						autocomplete="off" placeholder="交易备注" maxlength="200"
						data-callback="input.status" /></li>
					<li class="form-item form-item-border clearfix"><input
						type="submit" value="申请撤销" class="btn1"></li>
				</ul>
			</div>
		</div>
	</form>
</body>
</html>