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
<title>白条分期策略查询</title>
</head>
<body>
<form method="post" action="../action/QueryBtStrategyAction.php"  id="queryBtStrategyForm">
	<div class="content">
		<div class="content_0">
			<div class="content_1">
					<ul class="form-wrap" id="J-form-wrap">
						<li class="form-item form-item-border clearfix"><label>接口版本:</label>
							<input type="text" class="" name="version" value="V2.0"
						maxlength="18" /></li>
						
						<li class="form-item form-item-border clearfix"><label>商户号:
						</label> <input type="text" class="" name="merchant" value="<?php echo ConfigUtil::get_val_by_key('merchantNum');?>"
						placeholder="请输入商户号" maxlength="50" /></li>
						
						<li class="form-item form-item-border clearfix"><label>交易金额:
						</label> <input type="text" class="" name="amount" value="1001" 
							placeholder="商户订单的资金总额。单位：分，大于10元才能显示分期策略" />单位：分，金额大于10元才能显示分期策略
							</li>	
							
							<li class="form-item form-item-border clearfix"><label>交易号流水:
						</label> <input type="text" class="" name="tradeNum" value=""
							placeholder="请输入交易号" /></li>					
					     <li> <input type="submit" value="查询"
							class="btn"></li>
					</ul>
			</div>
		</div>
	</div>
</form>
</body>
</html>