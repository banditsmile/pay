<?php 
use com\jdjr\pay\demo\common\ConfigUtil;
include '../common/ConfigUtil.php';
error_reporting(0);
?>
<!DOCTYPE html>
<html>

<head>
<meta charset="UTF-8">
<link rel="stylesheet" type="text/css"
	href="../../../../../css/main.css">
<title>交易查询</title>
</head>
<body>
	<div class="content">
		<div class="content_0">
			<div class="content_1">
				<form method="post" action="../action/QueryRefund.php"
					id="queryTradeForm">

					<ul class="form-wrap" id="J-form-wrap">
						<li class="form-item form-item-border clearfix"><label>接口版本:</label>
							<input type="text" class="" name="version" value="V2.0"
							data-callback="input.status" /></li>
						<li class="form-item form-item-border clearfix"><label>商户号:
						</label> <input type="text" class="" name="merchantNum"
							value="" placeholder="请输入商户号" /></li>

						<li class="form-item form-item-border clearfix"><label>查询类型:
						</label> <input type="text" class="" readonly  name="tradeType" value="1"
							 /></li>

						<li class="form-item form-item-border clearfix"><label>原交易号:
						</label> <input type="text" class="" name="oTradeNum" value=""
							placeholder="请输入原交易号" /></li>
					     <li> <input type="submit" value="查询"
							class="btn"></li>
					</ul>
				</form>
			</div>
		</div>
	</div>
</body>
</html>