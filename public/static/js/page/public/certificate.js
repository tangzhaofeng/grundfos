var layerObj;//弹出窗体
var posts = new Array();

$(function() {
	initButton();
	//去除查询出来的岗位id和名称，然后将组成一个数组post 并将post加入数组posts
	$("table[postid]").each(function(index) {
		var post = new Array();
		var name = $(this).find($("[zs='zs']")).attr("name");
		var id = $(this).find($("[zs='zs']")).attr("id");
		post.push(id);
		post.push(name);
		posts.push(post);
	});
	
	$("table[wokerNum]").each(function(index) {
		var post = new Array();
		var name = $(this).attr("postName");
		var id = $(this).attr("wokerNum");
		post.push(id);
		post.push(name);
		posts.push(post);
	});
	
	$("table[specialNum]").each(function(index) {
		var post = new Array();
		var name = $(this).attr("postName");
		var id = $(this).attr("specialNum");
		post.push(id);
		post.push(name);
		posts.push(post);
	});
	
	
	
	//读取出posts数组的记录，添加li
	for(var i=0;i<posts.length;i++){
		$("<li postid="+posts[i][0]+" onclick=show('"+posts[i][0]+"')>"+posts[i][1]+"</li>").appendTo($("#tabul"));		
	}
	//将第一个li 添加样式 cur
	$("#tabul li:first").addClass("cur");
	//获取第一个li的属性postId,然后将对应的岗位证书显示出来
	var postId = $("#tabul li:first").attr("postid");
	$("#"+postId).show();
	
	continueEdu();
});

function initButton(){
	
	/*返回按钮*/
	$("#backBtn").on("click",function(){
		window.location.href = Util.getRootPath();
	});
	
	/*申请补办按钮*/
	$(".recertificate").on("click",function() {
		
		var certID = $(this).attr("certID");
		var dataInfo = new Array();
		layer.prompt({
		    formType: 2,
		    value: '',
		    title: '请输入补办原因'
		}, function(value, index, elem){
			var now = new Date();
			var mon = now.getMonth()+1;
			if(mon < 10){
				mon = "0"+mon;
			}
			var day = now.getDate();
			if(day < 10){
				day = "0"+day;
			}
			var time = now.getFullYear()+"年"+mon+"月"+day+"日";
			var companyName = $("#profess_company_"+certID).text();
			var html = "<tr><td>"+time+"</td><td>已申请</td><td>"+companyName+"</td><td>"+value+"</td><td></td></tr>";
			dataInfo.push({
				"certID" : certID,
				"reason" : encodeURIComponent(value).replace(/%[0-1][0-F]/g,"")
			});
		     
		    Util.ajaxJson(Util.getRootPath()+"/personApply",dataInfo, function(data) {    	
		    	var length = data.message.length;
		    	if(length!=0){
		    		//message的code为1，则是申请成功，layer的icon设置为6
		    		if(data.message[0].code == "1"){
		    			layer.msg(data.message[0].content,{time: 3000, icon:6});
		    			$("#certRecord_"+certID).append(html);
		    		} else{
		    			layer.msg(data.message[0].content,{time: 3000, icon:5});
		    		}
		    	}
		    }); 
		    layer.close(index);
		});
	});
}

function continueEdu(){
	$("table").find("tbody[id^='continueEdu1_']").each(function(){
		var spanId = $(this).attr("id");
		var spanIds = spanId.split("_");
		var dataId = spanIds[1];
		var sendDate = $("#sendDate_"+dataId).text();
		
		var year = 0;
		var sendM = 0;
		var sendD = 0;
		var dt = "";
		if(sendDate){
			var sendYear = sendDate.split("年")[0];
			dt = sendDate.split("年")[1];
			sendM = parseInt(dt.split("月")[0],10);
			sendD = parseInt(dt.split("月")[1].split("日")[0],10);
			year = parseInt(sendYear,10);
			if(year < 2014 || (year == 2014 && sendM < 3)) {
				year = year + 2;
				if(year > 2014 || (year == 2014 && sendM >= 3)) {
					year = year + 1;
				}
			} else {
				year = year + 3;
			}
		}
		Util.ajaxJson(Util.getRootPath()+"/search/certificate/edu/"+dataId,{
			
		},function(data) {
			
			var tbody = "#continueEdu1_"+dataId;
			var tfoot = "#continueEdu2_"+dataId;
			var ret = JSON.parse(data);
			if(ret && ret.length > 0){
				for(var i=0;i<ret.length;i++){
					var start = year+"年"+dt;
					var continueYear = parseInt(ret[i].continue_year,10);
					if(continueYear != 2 && continueYear != 3) {
						continueYear = 2;
					}
					
					year = year + continueYear;
					
					if(continueYear == 2 && (year > 2014 || (year == 2014 && sendM >= 3))) {
						year = year + 1;
					}
					
					var end = year+"年"+dt;
					var operDate = "";
					if(ret[i].operate_date){
						operDate = Util.dateFormat(new Date(ret[i].operate_date), "yyyy年MM月dd");
					}
					var str = '<tr>';
					str += '<td>'+operDate+'</td>';
					str += '<td>'+ret[i].company_name+'</td>';
					str += '<td>'+start+'&nbsp;&nbsp;至 &nbsp;&nbsp;'+end+'</td>';
					str += '</tr>';
					$(tbody).append(str);
				}
			}
			
			var now = new Date();
			var nowY = now.getFullYear();
			var nowM = now.getMonth()+1;
			var nowD = now.getDate();
			var isEdu = false;
			if(year < nowY){
				isEdu = true;
			} else if(year == nowY){
				if(sendM < nowM){
					isEdu = true;
				} else if(sendM == nowM){
					if(sendD < nowD){
						isEdu = true;
					}
				}
			}
			
			var tf = '<tr><td colspan="3" style="text-align:center;">';
			if(isEdu){
				tf += '<b>应参加继教日期：'+year+'年'+dt+'。</b>';
			} else {
				tf += '<b>下次继教日期：'+year+'年'+dt+',须提前1-12个月参加继续教育，逾期证书作废。</b>';
			}
			
			tf += '</td></tr>';
			$(tfoot).append(tf);
			
		});
	});
}
/**
 * 显示考生证书
 * @param id
 */
function show(id){
	//将当前显示的li的样式设置为cur并且将它的兄弟节点li的样式cur移除
	$("li[postid="+id+"]").addClass("cur").siblings().removeClass("cur");
	$("div.certificate").hide();
	$("#"+id).show();
}


/**
 * 获取弹出窗体
 * @returns
 */
function getLayerIndex() {
	return layerObj;
}

