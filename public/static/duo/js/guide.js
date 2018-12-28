$(function(){
	
	$('.start-wrapper').addClass('animated fadeInDownBig')
	
	/*指导层首次的弹出窗，点击start*/
	$('.starat-btn button').click(function(){
		$('.start-wrapper').fadeOut(function(){
			$('.start-wrapper').removeClass('animated fadeInDownBig')
		});		
		$('.guide-box').fadeIn();
	})
	
	/*从页面通过vue方式获取的内容*/
	var vueDate={
		next:$('.vue-data .nextStep').html(),
		study:$('.vue-data .study').html(),
	}
	/*记录点击到哪一个了*/
	var index=0;	
	setTimeout(function(){
		$(document).on('mousewheel', function(event) { return false; });
		$('.img-wrapper .vedio-step').addClass('activeStep-1');	
		var clientHeight=$(window).outerHeight();
		var bodyHeight=$('.scroll-data-0').offset().top;
/*		console.log(clientHeight);
		console.log(bodyHeight);
		console.log(bodyHeight - (clientHeight/2) + 15 + 'px');*/
/*		document.body.scrollTop = document.documentElement.scrollTop = bodyHeight - (clientHeight/2) + 15;*/
		$(window).scrollTop(bodyHeight - (clientHeight/2) + 15);
	},500)
	
	/*Scrollbar();
	function Scrollbar(){
		w1 = $(window).width();
		$('html').addClass('fancybox-lock-test');
		w2 = $(window).width();
		$('html').removeClass('fancybox-lock-test');
		$("<style type='text/css'>.fancybox-margin{margin-right:" + (w2 - w1) + "px;}</style>").appendTo("head");
	}*/
	
	function scrollPostion(index){
		var clientHeight=$(window).outerHeight();
		var bodyHeight=$('.scroll-data-'+index+'').offset().top;
/*		console.log(clientHeight);
		console.log(bodyHeight);
		console.log(bodyHeight - (clientHeight/2) + 15 + 'px');*/
		if(index>=0 && index<4){
			$('html,body').animate({
				scrollTop: bodyHeight - (clientHeight/2) + 15 + 'px'
			}, 500);
			
		}
	}
	
	
	$('.click-wrapper .next').click(function(){				
		if(index==-1){
			index=0;
		}
		
		if(index<5 && index>=0){
			$('.click-wrapper .btn').attr('disabled','disabled');
		}		
		var that=this;
		$('.click-wrapper .prev').addClass('active').removeClass('bgActive');
		
		if(index>=1 && index<4){
			scrollPostion(index);
		}
		
		if(index==0){
/*			console.log(index)*/
			$('.step-container .slot-wrapper ul li').eq(index).removeClass('active').next().addClass('active');
			$('.guide-wrapper .step').eq(index+1).addClass('activeStep-1');
			index++;
			setTimeout(function(){
				$('.click-wrapper .btn').removeAttr('disabled');
			},600)
		}else if(index==1){
/*			console.log(index)*/
			$('.step-container .slot-wrapper ul li').eq(index).removeClass('active').next().addClass('active');
			$('.guide-wrapper .step').eq(0).removeClass('activeStep-1').addClass('activeStep-2');
			$('.guide-wrapper .step').eq(1).removeClass('activeStep-1').addClass('activeStep-2');
			$('.guide-wrapper .step').eq(2).addClass('activeStep-1');
			setTimeout(function(){
				$('.guide-wrapper .step').eq(0).removeClass('activeStep-2');
				$('.guide-wrapper .step').eq(1).removeClass('activeStep-2');
				$('.click-wrapper .btn').removeAttr('disabled');
				index++;
			},600)
		}else if(index>1 && index<4){
/*			console.log(index)*/
			$('.step-container .slot-wrapper ul li').eq(index).removeClass('active').next().addClass('active');
			$('.guide-wrapper .step').eq(index).removeClass('activeStep-1').addClass('activeStep-2').next().addClass('activeStep-1');
			setTimeout(function(){
				$('.guide-wrapper .step').eq(index).removeClass('activeStep-2');				
				$('.click-wrapper .btn').removeAttr('disabled');
				index++;				
			},600)						
		}else if(index==4){
/*			console.log(index)*/
			$('.step-container .slot-wrapper ul li').eq(index).removeClass('active').next().addClass('active');			
			$('.guide-wrapper .step').eq(index).removeClass('activeStep-1').addClass('activeStep-2');
			$('.feedback-step').addClass('activeStep-1');
			setTimeout(function(){
				$('.guide-wrapper .step').eq(index).removeClass('activeStep-2');				
				$(that).html(vueDate.study);
				$('.click-wrapper .btn').removeAttr('disabled');
				index++;				
			},600)	
		}else if(index==5){
/*			console.log(index)*/
			$('#guide-mask').fadeOut();
			document.getElementsByTagName("body")[0].style.overflowY="auto";
			/*$(document).on('mousewheel', function(event) { return true; });*/
			$(document).off('mousewheel');
			document.body.scrollTop = document.documentElement.scrollTop = 0;
			window.topSwiper();
		}
		
	})
	
	$('.click-wrapper .prev').click(function(){
/*		console.log(index);*/
		if(index==5){
			index=4;
		}
		
		if(index>=0 && index<=4){			
			$('.click-wrapper .btn').attr('disabled','disabled');
		}		
		var that=this;
/*		$('.click-wrapper .next').addClass('active');
		*/
		if(index>0 && index<4){
			scrollPostion(index-1);
		}
		$('.click-wrapper .next').html(vueDate.next).addClass('active');
		
		if(index==0){
			$('.step-container .slot-wrapper ul li').eq(index+1).removeClass('active').prev().addClass('active');
			$('.guide-wrapper .step').eq(index+1).removeClass('activeStep-1').addClass('activeStep-2');
			setTimeout(function(){
				$('.guide-wrapper .step').eq(index+1).removeClass('activeStep-2');								
				$('.click-wrapper .btn').removeAttr('disabled');
				$(that).removeClass('active').addClass('bgActive');
				index--;
			},600)	
		}else if(index==1){
			$('.step-container .slot-wrapper ul li').eq(index+1).removeClass('active').prev().addClass('active');
			$('.guide-wrapper .step').eq(index+1).removeClass('activeStep-1').addClass('activeStep-2');
			$('.guide-wrapper .step').eq(index).addClass('activeStep-1');
			$('.guide-wrapper .step').eq(index-1).addClass('activeStep-1');
			setTimeout(function(){
				$('.guide-wrapper .step').eq(index+1).removeClass('activeStep-2');								
				$('.click-wrapper .btn').removeAttr('disabled');
				index--;
			},600)	
		}else if(index>=2 && index<4){
			$('.step-container .slot-wrapper ul li').eq(index+1).removeClass('active').prev().addClass('active');
			$('.guide-wrapper .step').eq(index+1).removeClass('activeStep-1').addClass('activeStep-2').prev().addClass('activeStep-1');
			setTimeout(function(){
				$('.guide-wrapper .step').eq(index+1).removeClass('activeStep-2');								
				$('.click-wrapper .btn').removeAttr('disabled');
				index--;
			},600)	
		}else if(index==4){
			$('.step-container .slot-wrapper ul li').eq(index+1).removeClass('active').prev().addClass('active');	
			$('.feedback-step').removeClass('activeStep-1').addClass('activeStep-2');
			$('.guide-wrapper .step').eq(index).addClass('activeStep-1');				
			setTimeout(function(){
				$('.feedback-step').removeClass('activeStep-2');							
				$('.click-wrapper .btn').removeAttr('disabled');
				index--;
			},600)	
		}
		
	})
})
