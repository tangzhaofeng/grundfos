$(function() {
	$(".register").on("click",function() {
		window.location.href = Util.getRootPath() + "/login";
	});
	$("form").on("submit",function() {
		$(".error").html("");
		var name = $("#name").val().replace("姓名","");
		var idCardNo = $("#idCard").val().replace("身份证号","");
		var certNo = $("#certNo").val().replace("证书号","");
		var safeCode = $("#safeCode").val().replace("验证码","");
		var enterAmount = 0;
		if($.trim(name).length == 0) {
			enterAmount += 1;
		}
		if($.trim(idCardNo).length == 0) {
			enterAmount += 1;
		}
		if($.trim(certNo).length == 0) {
			enterAmount += 1;
		}
		if(enterAmount > 1){
			$(".error").html("至少输入两项查询条件");
			return false;
		}
		if($.trim(safeCode).length == 0) {
			$(".error").html("请输入验证码");
			return false;
		}
		
	});
	
	$("#verifycode_img").on("click",function() {
		$(this).attr("src",Util.getRootPath() + "/captcha?t="+new Date());
	});
	
	// ie8及以下兼容html标签属性placeholder
	placeholderView();
	
});