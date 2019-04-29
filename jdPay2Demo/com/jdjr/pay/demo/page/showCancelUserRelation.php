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
<title>用户关系解绑</title>
</head>
<body>
<form method="post" action="../action/UserAction.php"  id="queryUserForm">
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
						
						<li class="form-item form-item-border clearfix"><label>用户ID:
						</label> <input type="text" class="" name="userId" value="" 
							placeholder="请输入用户ID"  />
							<input type="hidden" class="" name="userType" value="1" 
							/></li>
						
					     <li> <input type="submit" value="解绑"
							class="btn"></li>
					</ul>
			</div>
		</div>
	</div>
</form>
</body>
</html>