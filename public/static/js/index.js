$(function() {
	/*定义四个模块距离顶部的距离*/
	var Destance = [];
	//定义当前语言类型，默认为英语 1
	var _language = 1;
	/*定义视频数组1 原始数据*/
	var originVideo=[];
	/*定义视频数组2 处理过的数据*/
	var _video=[];
	/*定义document数组*/
	var _document=[];
	/*定义webinars数组*/
	var _webinars=[];	
	/*定义FAQs数组*/
	var _FAQs=[];
	
	//假数据
	var WebinarsList = [{
			title: 'Become a Windows System Administrator (Server 2012 R2)',
			date: '2018/1/20',
			address: 'shanghai',
			imgUrl: ''+PINGimgURL+'images/Webinars-1.png'
		},
		{
			title: 'Become a Windows System Administrator (Server 2012 R1)',
			date: '2018/1/21',
			address: 'shanghai',
			imgUrl: ''+PINGimgURL+'images/Webinars-2.png'
		},
		{
			title: 'Become a Windows System Administrator (Server 2012 R3)',
			date: '2018/1/22',
			address: 'shanghai',
			imgUrl: ''+PINGimgURL+'images/Webinars-2.png'
		},
		{
			title: 'Become a Windows System Administrator (Server 2012 R4)',
			date: '2018/1/23',
			address: 'shanghai',
			imgUrl: ''+PINGimgURL+'images/Webinars-1.png'
		},
		{
			title: 'Become a Windows System Administrator (Server 2012 R5)',
			date: '2018/1/24',
			address: 'shanghai',
			imgUrl: ''+PINGimgURL+'images/Webinars-2.png'
		},
	];

	var FAQSList = [{
			question: 'QUESTION TEXT1',
			anwer: 'Design wording Design wording Design wording Design wording Des1'
		},
		{
			question: 'QUESTION TEXT2',
			anwer: 'Design wording Design wording Design wording Design wording Des2'
		},
		{
			question: 'QUESTION TEXT3',
			anwer: 'Design wording Design wording Design wording Design wording Des3'
		},
		{
			question: 'QUESTION TEXT4',
			anwer: 'Design wording Design wording Design wording Design wording Des4'
		},
		{
			question: 'QUESTION TEXT5',
			anwer: 'Design wording Design wording Design wording Design wording Des5'
		},
		{
			question: 'QUESTION TEXT6',
			anwer: 'Design wording Design wording Design wording Design wording Des6'
		}
	]
	
	
	/*页面加载请求数据*/	
	function ajaxindex(language,type,fn){
		$.post(ajaxUrl,{'type':type,'language':language},function(response){
			fn(response);
			DistanceFn();
			topFixed();
		})
	}
	
	/*调取视屏ajax*/
	ajaxindex(_language,'video',function(response){
		if(response.type==='video'){
			originVideo=response.data;
			var res=response.data;		
			if(res.length>0){
				var j=0;
				for(var i=0;i<res.length;i+=4){
					_video[j]=res.slice(i,i+4);
					j++;
				}
				console.log(_video);
				viedoHtml(_video);
			}
		}
	});
	/*视屏ajaxHTML拼接*/
	function viedoHtml(data){
		var html="";		
		if(data!=null && data.length>0){
			data.forEach(function(firstItem, firstIndex) {
				html+=`
					<div class="swiper-slide swiper-no-swiping">
						${secondFor(firstItem,firstIndex)}
					</div>
				`;							
			});			
			function secondFor(firstItem,firstIndex){
				var secondHtml="";
				firstItem.forEach(function(secondItem,secondIndex){
					secondHtml+=`
						<li>
			      			<p><img src="${secondItem.poster}" alt="" /></p>
			      			<div class="text fontFamily">${secondItem.title}</div>
			      			<div class="mask"></div>
			      			<div class="gredient-mask"></div>
			      			<div class="play-img" data-id="${secondItem.id}" vediohref="${secondItem.file_url}"><i class="iconfont icon-bofang"></i></div>
						</li>
					`;
				});	
				return secondHtml;
			}
						
			$('.videos-wrapper .swiper-wrapper').html(html);
			/*判断视频数量,将滑动按钮隐藏*/
			if(data.length<5){
				$('#vedio-button').hide();
			}else{
				$('#vedio-button').show();
			}
			var swiper2 = new Swiper('.swiper-container-2', {
				navigation: {
					nextEl: '.swiper-button-next-1',
					prevEl: '.swiper-button-prev-2',
				},
			});			
		}
	}
	
	/*documentAjax请求*/
	ajaxindex(_language,'document',function(response){
		if(response.type==='document'){
			_document=response.data;
			console.log(_document);
			documentHtml(_document)
		}
	});
	/*documentHTML拼接*/
	function documentHtml(data){
		var html="";
		if(data!=null && data.length>0){
			data.forEach(function(item, index) {
				html+=`
					<div class="swiper-slide">
				      	<div class="top-img">
				      		<div class="wrapper">
					      		<p class="img"><img src="${item.poster}" alt="" /></p>
					      		<div class="mask"></div>
					      		<div class="search-img" data-id="${item.id}" hrefSrc="${item.file_url}">
					      			<b><i class="iconfont icon-shiliangzhinengduixiang"></i></b>
					      		</div>
				      		</div>
				      	</div>	
				      	<div class="bottom-text">
				      		<span class="fontFamily">${item.title}</span>
				      	</div>
				    </div>
				`;
			});
			$('.documents-wrapper .swiper-wrapper').html(html);
			/*判断document数量,将滑动按钮隐藏*/
			if(data.length<5){
				$('#document-button').hide();
			}else{
				$('#document-button').show();
			}
			
			var swiper3 = new Swiper('.swiper-container-3', {
				slidesPerView: 4,
				spaceBetween: 26.6,
				slidesPerGroup: 4,
				loopFillGroupWithBlank: true,
				navigation: {
					nextEl: '.swiper-button-next-3',
					prevEl: '.swiper-button-prev-4',
				},
			});		
		}
	}
	
	/*WebinarsAjax请求*/
	ajaxindex(_language,'webinars',function(response){
		if(response.type==='webinars'){
			_webinars=response.data;
			console.log(_webinars);
		}
	});
	

	/*Webinars部分html的拼接*/
	WebinarsHtml(WebinarsList.slice(0, 3));
	function WebinarsHtml(data) {
		var html = "";
		data.forEach(function(item, index) {
			html += `<li class="clearfix">
						<div class="left-img">
							<img src="${item.imgUrl}" alt="" />
						</div>
						<div class="right-content fontFamily">
							<p class="title"><a href="javascript:;">${item.title}</a></p>
							<p class="time">
								<b><i class="iconfont icon-shijian"></i></b>
								<span>${item.date}</span>
							</p>
							<p class="location">
								<b><i class="iconfont icon-didian"></i></b>
								<span>${item.address}</span>
							</p>
							<a href="javascript:;" class="btn">Sign Up</a>
						</div>
					</li>`;
		});
		$('.Webinars-list ul').html(html);
	}

	/*FAQsAjax请求*/
	ajaxindex(_language,'faq',function(response){
		if(response.type==='faq'){
			_FAQs=response.data;
/*			console.log(_FAQs);*/
			if(_FAQs.length>3){
				$('.FAQS-wrapper .more-wrapper').show();
			}
			FAQSHtml(_FAQs.slice(0, 3));			
		}		
	});
	/*FAQS部分html的拼接*/
	function FAQSHtml(data) {
		var html = "";
		if(data.length>0){
			data.forEach(function(item, index) {
				html += `<li class="fontFamily">
							<div class="title-wrapper">
								<a href="javascript:;">
									<span class="line"></span>
									<span class="title">${item.title}</span>
								</a>									
							</div>
							<div class="content" style="display:none">
								<div>${item.desc}</div>
							</div>
						</li>`;
			});
			$('.FAQS-list ul').html(html);
			$('.FAQS-list ul li').eq(0).addClass('shadow animate').find('.content').slideDown();
		}		
	}
	
	/*点击FAQS中的more*/
	$('.FAQS-list .more-wrapper span').click(function() {
		var list = $('.FAQS-list ul li');
		if(list.length <= 3) {
			FAQSHtml(_FAQs);
			$(this).addClass('up').removeClass('down');
		} else {
			FAQSHtml(_FAQs.slice(0, 3));
			$(this).addClass('down').removeClass('up');
		}
	});

	/*点击Webinars中的more*/
	$('.Webinars-list .more-wrapper span').click(function() {
		var list = $('.Webinars-list ul li');
		if(list.length <= 3) {
			WebinarsHtml(WebinarsList);
			$(this).addClass('up').removeClass('down');
		} else {
			WebinarsHtml(WebinarsList.slice(0, 3));
			$(this).addClass('down').removeClass('up');
		}
	});

	/*FAQS部分对于鼠标的操作效果*/
	$('.FAQS-list ul').on('mouseover', '.title-wrapper a', function() {
		$(this).parents('li').addClass('active');
	});

	$('.FAQS-list ul').on('mouseout', '.title-wrapper a', function() {
		$(this).parents('li').removeClass('active');
	});
	$('.FAQS-list ul').on('click', '.title-wrapper a', function() {
		$(this).parents('li').addClass('shadow animate').siblings().removeClass('shadow animate').find('.content').hide();
		$(this).parent().siblings('.content').slideDown('slow');
	});

	//顶部轮播
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
	
	

	//顶部轮播语言选择
	$('.select-language .title').click(function() {
		document.getElementsByClassName('dropdown')[0].classList.toggle('down');
		/*		  document.getElementsByClassName('arrow')[0].classList.toggle('gone');*/
		if(document.getElementsByClassName('dropdown')[0].classList.contains('down')) {
			setTimeout(function() {
				document.getElementsByClassName('dropdown')[0].style.overflow = 'visible'
			}, 500)
		} else {
			document.getElementsByClassName('dropdown')[0].style.overflow = 'hidden'
		}
	})

	$('.dropdown p').click(function() {
		var value = $(this).text();
		$('.select-language .title .text').text(value);
		document.getElementsByClassName('dropdown')[0].classList.toggle('down');
		/*			document.getElementsByClassName('arrow')[0].classList.toggle('gone');*/
		document.getElementsByClassName('dropdown')[0].style.overflow = 'hidden'
	})

	/*	var video = document.querySelector('video');
		function videoMethod(video) {
			if (video.paused) {//->当前是暂停的,我让其播放
				setTimeout(function(){
				    video.play();		
				},1000)           
	            return;
		    }	
		}*/

	//点击视屏按钮弹出现视频
	$('.videos-wrapper .swiper-wrapper').on("click",'li .play-img',function(){
		$('#vedio-mask').fadeIn();
		$('#vedio-mask').show().find('.vedio-wrapper').addClass('active');
		var selectId=$(this).attr('data-id');
		videoContent(selectId);
		var hrefSrc = $(this).attr('vediohref');	
		var myPlayer = videojs('my-video');
		videojs("my-video", {}, function() {
			window.myPlayer = this;
			$("#video source").attr("src", hrefSrc);
			myPlayer.src(hrefSrc);
			myPlayer.load(hrefSrc);
			myPlayer.play();
		});		
	})
	/*通过以获取数据的id查找当前点击的是哪个视屏,并将数据赋予内容*/
	function videoContent(selectId){
		var html="";
		if(originVideo.length>0){
			originVideo.forEach(function(item,index){
				if(item.id==selectId){
					html=`
						<span class="content">${item.title}</span>
						<div class="text">${item.desc}</div>
					`;
					$("#vedio-mask .right-text .title").html(html);
/*					$("#vedio-mask .right-text .title .text").children()..css({'fontSize':'20px','lineHeight':'28px','margin-top':'20px','color':'#a9a9a9','fontStyle':'normal','fontWeight':'normal','font-family':'Arial, Verdana,sans-serif, Tahoma, Helvetica'});
*/				}
			})
		}				
	}
	
	//以下代码暂时没有用	
	function jindutiao(myPlayer){
		myPlayer.ready(function(){
		    var width= $('.vjs-progress-control').position().left;
			var height= $('.vjs-progress-control').outerHeight();
			console.log(width);
			console.log(height);
			$('.jindutiao').css({'width':'4rem','height':'36px'});
		})		
	}
	
	/*点击视屏弹出层关闭按钮*/
	$('.vedio-wrapper .closes-btn').click(function() {
		$(this).parents('.box-layer').hide().find('.vedio-wrapper').removeClass('active');
		console.log(myPlayer)
		myPlayer.pause();
	})
	
	//点击文档弹出文档悬浮窗
	$('.documents-wrapper').on('click','.top-img .search-img',function(){
		console.log(666666);
		var src=$(this).siblings('.img').children('img').attr('src');
		$('#document-mask .left-img img').attr('src',src);
		var selectId=$(this).attr('data-id');
		documentContent(selectId);
		$('#document-mask').show().find('.doumentRead').addClass('active');
	})	
	/*通过获取数据的id查找当前点击的是哪个document,并将数据赋予内容*/
	function documentContent(selectId){
		var html="";
		if(_document.length>0){
			_document.forEach(function(item,index){
				if(item.id==selectId){
					html=`
						<span class="content">${item.title}</span>
						<div class="text">${item.desc}</div>
					`;
					var href=`${PINGimgURL}generic/web/viewer.html?file=${item.file_url}`;
					$("#document-mask .right-text .info").html(html);
					$('#document-mask .right-text .doument-btn').html('<a class="view" href="'+href+'" target="_blank">View</a>');					
/*					$("#document-mask .right-text .info .text").children().css({'fontSize':'20px','lineHeight':'32px','margin-top':'20px','color':'#a9a9a9','fontStyle':'normal','fontWeight':'normal','font-family':'Arial, Verdana,sans-serif, Tahoma, Helvetica'});
*/					/*$(".doument-btn .view").click(function(){
						window.location.href=
					})*/
				}
			})			
		}				
	}
		
	//点击弹出框按钮关闭
	$('.doumentRead .closes-btn').click(function() {
		$(this).parents('.box-layer').hide().find('.doumentRead').removeClass('active');
	})

	/*意见反馈按钮*/
	var timer = null;

	$(".submit-container,.submit-btn-wrapper .submit-bg").mouseover(function() {
		clearTimeout(timer);
		$('.submit-container').addClass('active');
	})
	$(".submit-container,.submit-btn-wrapper .submit-bg").mouseleave(function() {
		timer = setTimeout(function() {
			$('.submit-container').removeClass('active');
		}, 100);
	});

	/*点击右侧进行导航*/
	$('.navigation ul li a').click(function() {
		var href = $(this).attr('href');
		var clientDestance = $(href).offset().top;
/*		$(this).parent().addClass('active').siblings().removeClass('active');*/
		$('html,body').stop().animate({
			scrollTop: clientDestance - 500 + 'px'
		}, 500);
	})

	$('.submit-container .middle-img a').click(function() {
		var index=$(this).index();
		
		if(index==0){
			$('.submit-container .middle-img a').eq(0).find('use').attr('xlink:href','#icon-xiaolianjuse');
			$('.submit-container .middle-img a').eq(1).find('use').attr('xlink:href','#icon-yibanlanse');
			$('.submit-container .middle-img a').eq(2).find('use').attr('xlink:href','#icon-kulianlanse');
			$(this).addClass('active').siblings().removeClass('active');
		}else if(index==1){
			$('.submit-container .middle-img a').eq(0).find('use').attr('xlink:href','#icon-xiaolianlanse');
			$('.submit-container .middle-img a').eq(1).find('use').attr('xlink:href','#icon-yibanjuse');
			$('.submit-container .middle-img a').eq(2).find('use').attr('xlink:href','#icon-kulianlanse');
			$(this).addClass('active').siblings().removeClass('active');
		}else if(index==2){
			$('.submit-container .middle-img a').eq(0).find('use').attr('xlink:href','#icon-xiaolianlanse');
			$('.submit-container .middle-img a').eq(1).find('use').attr('xlink:href','#icon-yibanlanse');
			$('.submit-container .middle-img a').eq(2).find('use').attr('xlink:href','#icon-kulianjuse');
			$(this).addClass('active').siblings().removeClass('active');
		}else{
			$('.submit-container .middle-img a').eq(0).find('use').attr('xlink:href','#icon-xiaolianlanse');
			$('.submit-container .middle-img a').eq(1).find('use').attr('xlink:href','#icon-yibanlanse');
			$('.submit-container .middle-img a').eq(2).find('use').attr('xlink:href','#icon-kulianlanse');
			$('.submit-container .middle-img a').removeClass('active');
		}	
	})
	
	



	function DistanceFn() {
		Destance = [];
		var list = $('.section-info');
		list.each(function(index, item) {
			Destance.push($(item).offset().top);
		});
		RihgtScroll();
	}
	function RihgtScroll(){
		$(window).scroll(function() {
		/*		console.log(Destance);
				console.log($(this).scrollTop()>Destance[0]-100)*/
			if($(this).scrollTop() > Destance[0] - 200 && $(this).scrollTop() <= Destance[1] - 800) {
				$('.right-navigation').fadeIn();
				$('.navigation ul li').eq(0).addClass('active').siblings().removeClass('active');
				$('.navigation ul li').eq(0).find('use').attr('xlink:href', '#icon-Videosxuanzhong');
				$('.navigation ul li').eq(1).find('use').attr('xlink:href', '#icon-Documentsweixuanzhong');
				$('.navigation ul li').eq(2).find('use').attr('xlink:href', '#icon-Webinarsweixuanzhong');
				$('.navigation ul li').eq(3).find('use').attr('xlink:href', '#icon-FAQsweixuanzhong');
			} else if($(this).scrollTop() > Destance[1] - 800 && $(this).scrollTop() <= Destance[2] - 800) {
				$('.right-navigation').fadeIn();
				$('.navigation ul li').eq(1).addClass('active').siblings().removeClass('active');
				$('.navigation ul li').eq(0).find('use').attr('xlink:href', '#icon-Videosweixuanzhong');
				$('.navigation ul li').eq(1).find('use').attr('xlink:href', '#icon-Documentsxuanzhong');
				$('.navigation ul li').eq(2).find('use').attr('xlink:href', '#icon-Webinarsweixuanzhong');
				$('.navigation ul li').eq(3).find('use').attr('xlink:href', '#icon-FAQsweixuanzhong');
			} else if($(this).scrollTop() > Destance[2] -800 && $(this).scrollTop() <= Destance[3] - 800) {
				$('.right-navigation').fadeIn();
				$('.navigation ul li').eq(2).addClass('active').siblings().removeClass('active');
				$('.navigation ul li').eq(0).find('use').attr('xlink:href', '#icon-Videosweixuanzhong');
				$('.navigation ul li').eq(1).find('use').attr('xlink:href', '#icon-Documentsweixuanzhong');
				$('.navigation ul li').eq(2).find('use').attr('xlink:href', '#icon-Webinarsxuanzhong');
				$('.navigation ul li').eq(3).find('use').attr('xlink:href', '#icon-FAQsweixuanzhong');
			} else if($(this).scrollTop() > Destance[3] - 800) {
				$('.right-navigation').fadeIn();
				$('.navigation ul li').eq(3).addClass('active').siblings().removeClass('active');
				$('.navigation ul li').eq(0).find('use').attr('xlink:href', '#icon-Videosweixuanzhong');
				$('.navigation ul li').eq(1).find('use').attr('xlink:href', '#icon-Documentsweixuanzhong');
				$('.navigation ul li').eq(2).find('use').attr('xlink:href', '#icon-Webinarsweixuanzhong');
				$('.navigation ul li').eq(3).find('use').attr('xlink:href', '#icon-FAQsxuanzhong');
			} else {
				$('.right-navigation').fadeOut();
				$('.navigation ul li').removeClass('active');
				$('.navigation ul li').eq(0).find('use').attr('xlink:href', '#icon-Videosweixuanzhong');
				$('.navigation ul li').eq(1).find('use').attr('xlink:href', '#icon-Documentsweixuanzhong');
				$('.navigation ul li').eq(2).find('use').attr('xlink:href', '#icon-Webinarsweixuanzhong');
				$('.navigation ul li').eq(3).find('use').attr('xlink:href', '#icon-FAQsweixuanzhong');
			}
	
		})
	}	
	/*点击top返回顶部*/
	$('.submit-btn-wrapper .return-bg').click(function() {
		$('html,body').stop().animate({
			scrollTop: '0px'
		}, 500);
	})

	/*对顶部固定导航的展示的处理*/
	function topFixed() {
		var topHeaderHeight = $('.top-header').outerHeight();
		scrollTop();

		function scrollTop() {
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
		$(window).scroll(function() {
			scrollTop();
		})
	}


	/*function vedioDrag(myPlayer){
		var isMousedown = false;
		myPlayer.on('pause', function() {
			if(isMousedown == false) {　
				oldTime = myPlayer.currentTime();
			}
		});
		myPlayer.on('play', function() {
			isMousedown = false;
			newTime = myPlayer.currentTime();
			if(newTime < maxTime) {
				myPlayer.currentTime(newTime);
			} else {
				myPlayer.currentTime(oldTime);
			};
		});
		$(".vjs-progress-holder").mousedown(function() {
			isMousedown = true;
			oldTime = myPlayer.getCache().currentTime;
		});
		myPlayer.on('timeupdate', function() {
			if(myPlayer.currentTime() > maxTime && !isMousedown) {
				maxTime = myPlayer.currentTime();
			}
		});
	}*/
	
})