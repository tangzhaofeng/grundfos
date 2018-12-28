$(function(){
	/*从页面通过vue方式获取的内容*/
	var vueDate = {
		sign: $(".vue-data .signUp").html(),
		submitSuccess: $(".vue-data .submitSuccess").html(),
	};
	
	//定义当前语言类型，默认为英语 1
	var _language = languageObj.num;
	var _webinars = {};
	
	
	/*发送ajax请求*/	
	function ajaxindexContent(language,fn){
		$.post(ajaxUrl,{'language':language},function(response){
			fn(response);			
		})
	}
	
	/*调取Webinars 和  顶部banner*/
	ajaxindexContent(_language,function(response){
		/*换logo*/
		if(response.logo){
			$(".logo-img-2 .nei").css('background-image','url('+response.logo.poster+')')
		}
		
		/*顶部banner*/
		if(response.banner.length>0){
			topBanner(response.banner)
		}else{
			$('.top-header').css('height','300px')
		}
		
		/*顶部欢迎标语*/
		if(response.welcome){
			$('.js-top-text p').html(response.welcome.tagLine)
		}
		
		/*活动详情*/
		webinarsId(response.bookCourse.webinars);
		
	});
	
	/*顶部轮播Dom的添加*/
	function topBanner(data){
		var html="";
		if(data!=null && data.length>0){
			data.forEach(function(item, index) {
				if(item){
					html+=`
						<div class="swiper-slide">
							<img src="${item.poster}" alt="" />
							<div class="gredient-mask"></div>
						</div>
					`;
				}
				
			});
			$('.js-top-swiper').html(html);
									
			//然后再调取swpier
			topSwiper();			
		}
	};
	
	/*对于url地址的解析*/
	function GetQueryString(name){
	    var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
	    var r = window.location.search.substr(1).match(reg);
	    if(r!=null)return  unescape(r[2]); return null;
	}
	
	var WebinarsInfoId=GetQueryString('id');
	console.log(WebinarsInfoId);
	
	function webinarsId(data){
		if(WebinarsInfoId){
			var htmlData="";			
			if(data.length>0){
				data.forEach(function(item,index){
					if(!item){
						return;
					}
					
					if(item.id==WebinarsInfoId){
						_webinars=item;
					}
				})
				
				if(Object.keys(_webinars).length>0){
					htmlData=`
						<div class="left-img">
							<img src="${_webinars.poster || ""}" alt="" />
						</div>
						<div class="right-content fontFamily">
							<p class="title"><span href="javascript:;">${_webinars.title || ''}</span></p>
							<p class="time">
								<b><i class="iconfont icon-shijian"></i></b>
								<span>${(_webinars.start_time).split(' ')[0]} - ${(_webinars.end_time).split(' ')[0]}</span>
							</p>
							<p class="location">
								<b><i class="iconfont icon-didian"></i></b>
								<span>${_webinars.venue || ""}</span>
							</p>
							<a data-customer="${_webinars.customerCode}" href="javascript:;" class="btn">${vueDate.sign || ""}</a>
						</div>		
					`;
					$('.Webinars-list').html(htmlData);
					$('.Introduction .text').html(`<div>${_webinars.desc || ""}</div>`);
				}		
			}								
		}
	}
	
	
	//顶部轮播
	function topSwiper(){
		var swiper1 = new Swiper('.swiper-container-1', {
			effect: 'coverflow',
			speed: 800,
			grabCursor: true,
			centeredSlides: true,
			slidesPerView: 'auto',
			coverflowEffect: {
				rotate: 100,
				stretch: 0,
				depth: 100,
				modifier: 1,
				slideShadows: true,
			},
			autoplay: {
				delay: 3000,
				stopOnLastSlide: false,
				disableOnInteraction: false,
			},
			pagination: {
				el: '.swiper-pagination-1',
				clickable: true,
				renderBullet: function(index, className) {
					return '<span class="' + className + '"></span>';
				},
			},
		});
	}
	
	
	
	//顶部轮播语言选择
// 	$('.select-language .title').click(function() {
// 		document.getElementsByClassName('dropdown')[0].classList.toggle('down');
// 		/*		  document.getElementsByClassName('arrow')[0].classList.toggle('gone');*/
// 		if(document.getElementsByClassName('dropdown')[0].classList.contains('down')) {
// 			setTimeout(function() {
// 				document.getElementsByClassName('dropdown')[0].style.overflow = 'visible'
// 			}, 500)
// 		} else {
// 			document.getElementsByClassName('dropdown')[0].style.overflow = 'hidden'
// 		}
// 	})

// 	$('.dropdown p').click(function() {
// 		var value = $(this).text();
// 		$('.select-language .title .text').text(value);
// 		var dataLanguage=$(this).attr('data-language');
// 		var langNum=$(this).attr('lang-num');
// 		var locationAddress = location;
// 		var canshuInfo = {
// 	        langNum:langNum,
// 	        dataLanguage:dataLanguage,
// 	        value: value
//     	};
// 	    sessionStorage.setItem('key', JSON.stringify(canshuInfo));
// /*	    localStorage.setItem('key', JSON.stringify(info))*/
// 		setTimeout(function(){
// 			location.reload();
// 			/*location.href=indexUrl;*/
// 		},500)
// 		document.getElementsByClassName('dropdown')[0].classList.toggle('down');
// 		/*			document.getElementsByClassName('arrow')[0].classList.toggle('gone');*/
// 		document.getElementsByClassName('dropdown')[0].style.overflow = 'hidden'
// 	})
	
	/*对顶部固定导航的展示的处理*/
	function topFixed() {
		var topHeaderHeight = $('.top-header').outerHeight();
		if($(window).scrollTop() > topHeaderHeight) {
			$('.fixed-header').addClass('bg');
			$('.logo-img-wrapper .nei').removeClass('active');
			$('.select-language .title').css('color', '#108cee');
			$('.logo-img .line').css('background-color','rgba(0,0,0,.2)');
		} else {;
			$('.fixed-header').removeClass('bg');
			$('.logo-img-wrapper .nei').addClass('active');
			$('.select-language .title').css('color', '#ffffff');
			$('.logo-img .line').css('background-color','rgba(255,255,255,.3)');
		}
	}	
	
	setTimeout(function(){
		$(window).scroll(function() {
			topFixed();
		})
	},500)	
	
	/*点击报名按钮出现表单*/
	$('.Webinars-list').on('click','.btn',function(){
		$('#form').fadeIn();
	})
	
	//点击弹出框按钮关闭
	$('.closes-btn').click(function(){
		$(this).parents('.box-layer').fadeOut(function(){
			$('.form-biaodan').show();
			$(".success-content").hide();
		});
		
	})	
	
	/*提交表单*/
	$('.js-submit-btn').click(function(){
		
		var dataCustomer=$(".Webinars-list .btn").attr('data-customer');
		
		console.log(dataCustomer);
		
		var NameValue=$.trim($('#name').val());
		if(!NameValue){			
			$("#nameTip").fadeIn();
			return false;
		}
		
		var companyValue=$.trim($('#company').val());
		if(!companyValue){
			$("#companyTip").fadeIn();
			return false;			
		}
		
		var positionValue=$.trim($('#position').val());
		if(!positionValue){			
			$("#positionTip").fadeIn();
			return false;
		}
		
		/*var eMailreg = /^[a-z0-9]+([._\\-]*[a-z0-9])*@([a-z0-9]+[-a-z0-9]*[a-z0-9]+.){1,63}[a-z0-9]+$/*/
		var eMailreg = /^\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/
		var eMailValue=$.trim($('#eMail').val());
		if(!eMailreg.test(eMailValue)){			
			$("#eMailTip").fadeIn();
			return false;
		}
		
		// var telephonereg = /^1[34578]{1}\d{9}$/;
		var telephoneValue=$.trim($('#telephone').val());
		if(!telephoneValue){
			$("#telephoneTip").fadeIn();
			return false;			
		}
		
		
		$.post(formAjaxUrl,{
			name:NameValue,
			company:companyValue,
			position:positionValue,
			email:eMailValue,
			phone:telephoneValue,
			webinarId:WebinarsInfoId,
			customerCode:dataCustomer
		},function(data){
//			console.log(data);
			if(data.code==1){
				$('.form-biaodan').hide();
				$(".success-content").fadeIn().children('span').html(submitSuccess);
				$("#form").parents('.box-layer').fadeOut(); 
			}else{
				$(".success-content").fadeIn().children('span').html(data.msg);
				$('.form-biaodan').hide();
			}
		})
		
	})
	
	/*鼠标离开input框错误提示消失*/
	var inputList=$('.form-control input');
	inputList.each(function(index,item){
		$(item).blur(function(){
			$(this).next().fadeOut();
		})
	})
})
