<div class="card-inner">
	<p class="card-heading">充值</p>
    您的余额:{$user->money}
    <div class="form-group form-group-label">
		<label class="floating-label" for="amount">金额</label>
		<input class="form-control" id="amount" type="text" value="25">
	</div>
    <div class="card-action">
        <div class="card-action-btn pull-left">
            <a class="btn btn-brand" id="submit" style="background-color: #337ab7;padding-right:16px;margin-left:8px"><span style="margin-left:8px;margin-right:8px" class="icon">local_gas_station</span>充值</a>
        </div>
    </div>
</div>

<script>
    window.onload = function(){
        $('body').append("<script src=\" \/assets\/public\/js\/jquery.qrcode.min.js \"><\/script>");
        $("#submit").click(function(){
            var price = parseFloat($("#amount").val());
		    console.log("将要充值"+price+"元")
            $("#result").modal();
		    $("#msg").html("正在尝试调用支付宝...");
            if(isNaN(price)){
			    $("#result").modal();
			    $("#msg").html("非法的金额!");
		    }
            $.ajax({
                'url':"/user/epay",
                'data':{ 
                    'price':price
                },
                'dataType':'json',
                'type':"POST",
                success:function(data){
                    console.log(data);
                    if (data.errcode != 0) {
                         $("#result").modal();
                         $("#msg").html(data.code);
                    }
                    else {
                        $("#result").modal();
                        $("#msg").html("正在跳转到支付宝..."+data.code);
                    }
                }
            });
        });
    }
</script>