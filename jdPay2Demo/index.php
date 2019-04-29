<!DOCTYPE html>
<html>

<head>

  <meta charset="UTF-8">

  <title>京东支付</title>

    <link rel="stylesheet" href="css/style.css" media="screen" type="text/css" />

</head>
	<body>
		
		
		<div class="container">
			<div class="main clearfix">
				<div class="column">
					<p>京东支付Demo--SHOW<br>
				                  （1）在线支付接口<br>
				                  （2）退款申请接口<br>
				                  （3）交易查询接口<br>
				                  （4）统一下单接口<br>
				                  （5）交易号查退款<br>
				                  （6）商户二维码支付接口<br>
					       （7）撤销申请接口<br>
					       （8）付款码支付接口<br>
					       （9）用户关系查询接口<br>
					       （10）用户关系解绑接口<br>
					       （11）白条策略查询接口&#12288;
                    </p>
				</div>
				<div class="column">
					<button class="md-trigger" id="modal-1">在线支付接口</button>
					<button class="md-trigger" id="modal-2">退款申请接口</button>
					<button class="md-trigger" id="modal-3">交易查询接口</button>
					<br>
	                <button class="md-trigger" id="modal-4">统一下单接口</button>
	                <button class="md-trigger" id="modal-5">交易号查退款</button>
	                <button class="md-trigger" id="modal-6">商户二维码支付接口</button>
                	<button class="md-trigger" id="modal-7">撤销申请接口</button>
                	<button class="md-trigger" id="modal-8">付款码支付接口</button>
                	<button class="md-trigger" id="modal-9">用户关系查询接口</button>
                	<button class="md-trigger" id="modal-10">用户关系解绑接口</button>
                	<button class="md-trigger" id="modal-11">白条策略查询接口</button>
				</div>
			</div>
		</div><!-- /container -->
<script>
	document.getElementById("modal-1").onclick= function(){
	    window.open("com/jdjr/pay/demo/page/payStart.php");  
	}
	document.getElementById("modal-2").onclick= function(){
	    window.open("com/jdjr/pay/demo/page/refundIndex.php");  
	}
	document.getElementById("modal-3").onclick= function(){
	    window.open("com/jdjr/pay/demo/page/queryIndex.php");  
	}
	document.getElementById("modal-4").onclick= function(){
	    window.open("com/jdjr/pay/demo/page/showCreateOrder.php");  
	}
	document.getElementById("modal-5").onclick= function(){
	    window.open("com/jdjr/pay/demo/page/queryRefundIndex.php");  
	}
	document.getElementById("modal-6").onclick= function(){
	    window.open("com/jdjr/pay/demo/page/customerPayPage.php");  
	}
	document.getElementById("modal-7").onclick= function(){
	    window.open("com/jdjr/pay/demo/page/revokeIndex.php");  
	}
	document.getElementById("modal-8").onclick= function(){
	    window.open("com/jdjr/pay/demo/page/paymentCodePay.php");  
	}
	document.getElementById("modal-9").onclick= function(){
	    window.open("com/jdjr/pay/demo/page/showGetUserRelation.php");  
	}
	document.getElementById("modal-10").onclick= function(){
	    window.open("com/jdjr/pay/demo/page/showCancelUserRelation.php");  
	}
	document.getElementById("modal-11").onclick= function(){
	    window.open("com/jdjr/pay/demo/page/queryBtStrategyIndex.php");  
	}
 </script>
		

	

</body>

</html>