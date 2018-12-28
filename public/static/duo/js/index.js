'use strict';

$(function () {
	/*	console.log(messages);*/
	/*从页面通过vue方式获取的内容*/
	var vueDate = {
		sign: $(".vue-data .signUp").html(),
		more: $('#webinars .down').html(),
		less: $('#webinars .less').html()
		/*	console.log(vueDate);*/
		/*定义四个模块距离顶部的距离*/
	};var Destance = [];

	/*定义视频数组1 原始数据*/
	var originVideo = [];
	/*定义视频数组2 处理过的数据*/
	var _video = [];
	/*定义document数组*/
	var _document = [];
	/*定义webinars数组*/
	var _webinars = [];
	/*定义FAQs数组*/
	var _FAQs = [];
	/*定义反馈变量*/
	var _feedBack = 1;
	//全局的视屏Id;
	var $selectId = 0;
	var $customerCode = '';

	/*解析url地址*/
	function GetQueryString(name) {
		var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
		var r = window.location.search.substr(1).match(reg);
		if (r != null) return unescape(r[2]);return null;
	}

	//customerCode:GetQueryString('customerCode'),
	var UrlLocal = {
		userId: GetQueryString('userId'),
		courseCode: GetQueryString('courseCode'),
		vedio: [{
			id: '',
			progress: 0
		}]

		//	console.log(UrlLocal);

	};var urlLocalArray = [UrlLocal];
	//首次进入页面判断storage是否存入userid相关的值
	!function () {
		if (!localStorage) {
			return false;
		}
		var result = localStorage.getItem('vedioStorage');
		if (result) {
			var resultLocal = JSON.parse(result);
			var someResult = resultLocal.some(function (item) {
				return item.userId == UrlLocal.userId && item.courseCode == UrlLocal.courseCode;
			});

			if (!someResult) {
				resultLocal.push(UrlLocal);
				localStorage.setItem('vedioStorage', JSON.stringify(resultLocal)); //存入缓存值 
			}
		} else {
			localStorage.setItem('vedioStorage', JSON.stringify(urlLocalArray)); //存入缓存值 
		}
	}();

	/*缓存的处理*/
	var MyLocalStorage = {
		put: function put(key, stringVal, time) {
			if (!localStorage) {
				return false;
			}
			if (!time || isNaN(time)) {
				time = 60;
			}
			var cacheExpireDate = new Date() - 1 + time * 1000;
			var cacheVal = { val: stringVal, exp: cacheExpireDate };
			localStorage.setItem(key, JSON.stringify(cacheVal)); //存入缓存值  1          						
		},

		/**获取缓存*/
		get: function get(key) {
			try {
				if (!localStorage) {
					return false;
				}
				var cacheVal = localStorage.getItem(key);
				var result = JSON.parse(cacheVal);
				var now = new Date() - 1;
				if (!result) {
					return null;
				} //缓存不存在  
				//              console.log(now);
				//              console.log(result.exp);
				if (now > result.exp) {
					//缓存过期  
					this.remove(key);
					return "";
				}
				return result.val;
			} catch (e) {
				this.remove(key);
				return null;
			}
		},

		/**移除缓存，一般情况不手动调用，缓存过期自动调用*/
		remove: function remove(key) {
			if (!localStorage) {
				return false;
			}
			localStorage.removeItem(key);
		},
		/**清空所有缓存*/
		clear: function clear() {
			if (!localStorage) {
				return false;
			}
			localStorage.clear();
		}

		/*页面加载请求数据*/
	};function ajaxindexContent(language, fn) {
		$.post(ajaxUrl, { 'language': language }, function (response) {
			fn(response);
		});
	}

	/*调取首页所有ajax*/
	//当获取语言版本是在进行请求
	ajaxindexContent(languageObj.num, function (response) {
		// console.log(response);					
		/*换logo*/
		if (response.logo) {
			$(".logo-img-2 .nei").css('background-image', 'url(' + response.logo.poster + ')');
		}

		/*顶部banner*/
		if (response.banner && response.banner.length > 0) {
			topBanner(response.banner);
		} else {
			$('.top-header').css('height', '380px');
		}

		/*顶部banner标语*/
		if (response.welcome) {
			$('.js-top-text p').html(response.welcome.tagLine);
		} else {
			$('.js-top-text p').html("敬请期待！");
		}

		/*刚开始进入时的欢迎信*/
		if (response.welcome) {
			$('.js-start-text p').html(response.welcome.title);
		}

		/*视频*/
		originVideo = response.bookCourse.video;
		if (originVideo && originVideo.length > 0) {
			var j = 0;
			for (var i = 0; i < originVideo.length; i += 4) {
				if (originVideo[i]) {
					_video[j] = originVideo.slice(i, i + 4);
					j++;
				}
			}
			/*			console.log(_video);*/
			viedoHtml(_video);
		} else {
			$('#vedio .teshuclass').show();
		}

		/*获取证书的内容*/
		if (response.cert) {
			$('.js-getCert-text').html(response.cert.title);
		}

		// 如果看完了内容，弹出证书框

		if (response.userCert) {
			setTimeout(function () {
				$('#getCert-box').show().find('.getCert').addClass('active');
				is_Certicate = 1;
			}, 1000);
		}

		/*document调用*/
		_document = response.bookCourse.document;

		if (_document != null && _document.length > 0) {
			documentHtml(_document);
		} else {
			$('#documents .teshuclass').show();
		}

		/*webinars调用*/
		_webinars = response.bookCourse.webinars;

		if (_webinars != null && _webinars.length > 0) {
			WebinarsHtml(_webinars);
		} else {
			$('#webinars .teshuclass').show();
		}

		/*faq调用*/
		_FAQs = response.bookCourse.faq;

		if (_FAQs != null && _FAQs.length > 0) {
			FAQSHtml(_FAQs);
		} else {
			console.log(55555);
			$('#FAQS .teshuclass').show();
		}

		/*数据加载完成之后延迟调用DistanceFn*/

		setTimeout(function () {
			DistanceFn();
			topFixed();
		}, 500);
	});

	/*视屏ajaxHTML拼接*/
	function viedoHtml(data) {
		var html = "";
		if (data != null && data.length > 0) {
			var secondFor = function secondFor(firstItem, firstIndex) {
				var secondHtml = "";
				firstItem.forEach(function (secondItem, secondIndex) {

					function isWatch(data) {
						if (data) {
							return 'block';
						} else {
							return 'none';
						}
					}

					secondHtml += '\n\t\t\t\t\t\t\t<li class="wow fadeInDown">\n\t\t\t\t\t\t\t\t<span class="is_watched" style="display:' + isWatch(secondItem.is_watched) + '"><img src="' + PINGimgURL + '/images/hasView.png" /></span>\n\t\t\t\t\t\t\t\t  <p><img src="' + secondItem.poster + '" alt="" /></p>\n\t\t\t\t\t\t\t\t  <div class="text fontFamily">' + secondItem.title + '</div>\n\t\t\t\t\t\t\t\t  <div class="mask"></div>\n\t\t\t\t\t\t\t\t  <div class="gredient-mask"></div>\n\t\t\t\t\t\t\t\t  <div class="play-img" data-id="' + secondItem.id + '" data-customer="' + secondItem.customerCode + '" vediohref="' + secondItem.file_url + '"><i class="iconfont icon-bofang"></i></div>\n\t\t\t\t\t\t\t</li>\n\t\t\t\t\t\t';
				});
				return secondHtml;
			};

			data.forEach(function (firstItem, firstIndex) {
				html += '\n\t\t\t\t\t\t<div class="swiper-slide swiper-no-swiping">\n\t\t\t\t\t\t\t' + secondFor(firstItem, firstIndex) + '\n\t\t\t\t\t\t</div>\n\t\t\t\t\t';
			});


			$('.videos-wrapper .swiper-wrapper').html(html);

			/*判断视频数量,将滑动按钮隐藏*/
			if (originVideo.length < 5) {
				$('#vedio-button').hide();
			} else {
				$('#vedio-button').show();
			}
			var swiper2 = new Swiper('.swiper-container-2', {
				navigation: {
					nextEl: '.swiper-button-next-1',
					prevEl: '.swiper-button-prev-2'
				}
			});
		}
	}

	/*documentHTML拼接*/
	function documentHtml(data) {
		var html = "";
		if (data != null && data.length > 0) {
			data.forEach(function (item, index) {
				if (item) {
					var isWatch = function isWatch(data) {
						if (data) {
							return 'block';
						} else {
							return 'none';
						}
					};

					html += '\n\t\t\t\t\t\t\t<div class="swiper-slide swiper-no-swiping wow fadeInDown">\n\t\t\t\t\t\t\t\t  <div class="top-img">\n\t\t\t\t\t\t\t\t\t  <div class="wrapper">\n\t\t\t\t\t\t\t\t\t\t  <span class="is_watched" style="display:' + isWatch(item.is_watched) + '"><img src="' + PINGimgURL + '/images/hasView.png" /></span>\n\t\t\t\t\t\t\t\t\t\t  <p class="img"><img src="' + item.poster + '" alt="" /></p>\n\t\t\t\t\t\t\t\t\t\t  <div class="mask"></div>\n\t\t\t\t\t\t\t\t\t\t  <div class="search-img" data-id="' + item.id + '" data-customer="' + item.customerCode + '" hrefSrc="' + item.file_url + '">\n\t\t\t\t\t\t\t\t\t\t\t  <b><i class="iconfont icon-shiliangzhinengduixiang"></i></b>\n\t\t\t\t\t\t\t\t\t\t  </div>\n\t\t\t\t\t\t\t\t\t  </div>\n\t\t\t\t\t\t\t\t  </div>\t\n\t\t\t\t\t\t\t\t  <div class="bottom-text">\n\t\t\t\t\t\t\t\t\t  <span class="fontFamily">' + item.title + '</span>\n\t\t\t\t\t\t\t\t  </div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t';
				}
			});
			$('.documents-wrapper .swiper-wrapper').html(html);
			/*判断document数量,将滑动按钮隐藏*/
			if (data.length < 5) {
				$('#document-button').hide();
			} else {
				$('#document-button').show();
			}

			var swiper3 = new Swiper('.swiper-container-3', {
				slidesPerView: 4,
				spaceBetween: 26.6,
				slidesPerGroup: 4,
				loopFillGroupWithBlank: true,
				navigation: {
					nextEl: '.swiper-button-next-3',
					prevEl: '.swiper-button-prev-4'
				}
			});
		}
	}

	/*Webinars部分html的拼接*/
	function WebinarsHtml(data) {
		var html = "";
		var indexNum = 0;
		if (!(data && data.length > 0)) {
			return false;
		}

		var nowTime = new Date().getTime();

		data.forEach(function (item, index) {
			if (!item) {
				return;
			}
			indexNum++;
			var hide = indexNum > 3 ? "hide" : '';

			//转时间戳
			var stringTime = item.end_time;
			var timestamp2 = Date.parse(new Date(stringTime));

			if (nowTime > timestamp2) {
				var active = 'active';
				var hrefSrc = "javascript:;";
			} else {
				var active = "";
				var hrefSrc = getWebinarsInfo + '?id=' + item.id;
			}

			html += '<li class="clearfix wow fadeInDown ' + hide + '">\n\t\t\t\t\t\t\t<div class="left-img">\n\t\t\t\t\t\t\t\t<img src="' + item.poster + '" alt="" />\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<div class="right-content fontFamily">\n\t\t\t\t\t\t\t\t<p class="title ' + active + '"><a onclick="liulanWebinars(' + item.id + ',\'' + item.customerCode + '\')" href="' + hrefSrc + '">' + item.title + '</a></p>\n\t\t\t\t\t\t\t\t<p class="time">\n\t\t\t\t\t\t\t\t\t<b><i class="iconfont icon-shijian"></i></b>\n\t\t\t\t\t\t\t\t\t<span>' + item.start_time.split(' ')[0] + ' - ' + item.end_time.split(' ')[0] + '</span>\n\t\t\t\t\t\t\t\t</p>\n\t\t\t\t\t\t\t\t<p class="location">\n\t\t\t\t\t\t\t\t\t<b><i class="iconfont icon-didian"></i></b>\n\t\t\t\t\t\t\t\t\t<span>' + item.venue + '</span>\n\t\t\t\t\t\t\t\t</p>\n\t\t\t\t\t\t\t\t<a onclick="liulanWebinars(' + item.id + ',\'' + item.customerCode + '\')" href="' + hrefSrc + '" class="btn ' + active + '">' + vueDate.sign + '</a>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</li>';
		});
		$('.Webinars-list ul').html(html);
		if (indexNum > 3) {
			$('.Webinars-list .more-wrapper').show();
		}
	}

	/*点击Webinars中的more*/
	$('.Webinars-list .more-wrapper span').click(function () {
		var list = $('.Webinars-list ul li');
		if ($(this).hasClass('up')) {
			$('.Webinars-list ul li:gt(2)').addClass('hide');
			$(this).addClass('down').removeClass('up');
			$(this).html(vueDate.more);
		} else {
			list.removeClass('hide');
			$(this).addClass('up').removeClass('down');
			$(this).html(vueDate.less);
		}
	});

	/*FAQS部分html的拼接*/
	function FAQSHtml(data) {
		var html = "";
		var indexNum = 0;

		if (!(data && data.length > 0)) {
			return false;
		}

		data.forEach(function (item, index) {
			if (!item) {
				return;
			}
			indexNum++;
			var hide = indexNum > 3 ? "hide" : '';
			html += '<li class="fontFamily ' + hide + '">\n\t\t\t\t\t\t\t<div class="title-wrapper">\n\t\t\t\t\t\t\t\t<a href="javascript:;"  onclick="liulanFAQ(' + item.id + ',this,\'' + item.customerCode + '\')">\n\t\t\t\t\t\t\t\t\t<span class="line"></span>\n\t\t\t\t\t\t\t\t\t<span class="title">' + item.title + '</span>\n\t\t\t\t\t\t\t\t</a>\t\t\t\t\t\t\t\t\t\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<div class="content" style="display:none">\n\t\t\t\t\t\t\t\t<div>' + item.desc + '</div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</li>';
		});
		$('.FAQS-list ul').html(html);
		$('.FAQS-list ul li').eq(0).addClass('shadow animate').find('.content').slideDown();
		if (indexNum > 3) {
			$('.FAQS-list .more-wrapper').show();
		}
	}

	/*点击FAQS中的more*/
	$('.FAQS-list .more-wrapper span').click(function () {
		var list = $('.FAQS-list ul li');
		if ($(this).hasClass('up')) {
			$('.FAQS-list ul li:gt(2)').addClass('hide');
			$(this).addClass('down').removeClass('up');
			$(this).html(vueDate.more);
		} else {
			list.removeClass('hide');
			$(this).addClass('up').removeClass('down');
			$(this).html(vueDate.less);
		}
	});

	/*FAQS部分对于鼠标的操作效果*/
	$('.FAQS-list ul').on('mouseover', '.title-wrapper a', function () {
		$(this).parents('li').addClass('active');
	});

	$('.FAQS-list ul').on('mouseout', '.title-wrapper a', function () {
		$(this).parents('li').removeClass('active');
	});
	$('.FAQS-list ul').on('click', '.title-wrapper a', function () {
		$(this).parents('li').addClass('shadow animate').siblings().removeClass('shadow animate').find('.content').hide();
		$(this).parent().siblings('.content').slideDown('slow');
	});

	/*顶部轮播Dom的添加*/
	function topBanner(data) {
		var html = "";
		if (data != null && data.length > 0) {
			data.forEach(function (item, index) {
				if (item) {
					html += '\n\t\t\t\t\t\t\t<div class="swiper-slide">\n\t\t\t\t\t\t\t\t<img src="' + item.poster + '" alt="" />\n\t\t\t\t\t\t\t\t<div class="gredient-mask"></div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t';
				}
			});
			$('.js-top-swiper').html(html);

			/*判断是不是第一次进入首页，并存入session*/
			var firstGuideData = MyLocalStorage.get("isFisrt");
			if (firstGuideData != null) {
				window.topSwiper();
			} else {
				/*sessionStorage.setItem('isFisrt','yes');*/
				/*MyLocalStorage.put("isFisrt",'yes',1*24*60*60);//存储一天  */
				MyLocalStorage.put("isFisrt", 'yes', 60 * 60 * 24 * 10); //存储10分钟
				$('#guide-mask').fadeIn();
				var scriptHtml = '<script src="' + PINGimgURL + '/js/guide.js" type="text/javascript" charset="utf-8">' + '<\/script>';
				$('body').append(scriptHtml);
			}
		}
	};

	//顶部轮播	
	window.topSwiper = function () {
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
				slideShadows: true
			},
			autoplay: {
				delay: 3000,
				stopOnLastSlide: false,
				disableOnInteraction: false
			},
			pagination: {
				el: '.swiper-pagination-1',
				clickable: true,
				renderBullet: function renderBullet(index, className) {
					return '<span class="' + className + '"></span>';
				}
			}
		});
	};

	//顶部轮播语言选择
	$('.select-language .title').click(function () {
		document.getElementsByClassName('dropdown')[0].classList.toggle('down');
		/*		  document.getElementsByClassName('arrow')[0].classList.toggle('gone');*/
		if (document.getElementsByClassName('dropdown')[0].classList.contains('down')) {
			setTimeout(function () {
				document.getElementsByClassName('dropdown')[0].style.overflow = 'visible';
			}, 500);
		} else {
			document.getElementsByClassName('dropdown')[0].style.overflow = 'hidden';
		}
	});

	$('.dropdown').on('click', 'p', function () {
		var value = $(this).text();
		$('.select-language .title .text').text(value);
		var dataLanguage = $(this).attr('data-language');
		var langNum = $(this).attr('lang-num');
		var locationAddress = location;
		var canshuInfo = {
			num: langNum,
			Change: dataLanguage,
			type: value
		};
		sessionStorage.setItem('key', JSON.stringify(canshuInfo));
		/*	    localStorage.setItem('key', JSON.stringify(info))*/
		setTimeout(function () {
			location.reload();
			/*location.href=indexUrl;*/
		}, 500);
		document.getElementsByClassName('dropdown')[0].classList.toggle('down');
		/*			document.getElementsByClassName('arrow')[0].classList.toggle('gone');*/
		document.getElementsByClassName('dropdown')[0].style.overflow = 'hidden';
	});

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
	$('.videos-wrapper .swiper-wrapper').on("click", 'li .play-img', function () {

		//		console.log(myPlayer);
		var progressTime = 0;
		var selectId = $(this).attr('data-id');
		$selectId = selectId;
		var customerCode = $(this).attr('data-customer');
		$customerCode = customerCode;
		videoContent(selectId);

		//当卡开视屏窗口时，获取local的当前视屏的播放进度
		var result = localStorage.getItem('vedioStorage');
		if (result) {
			var resultLocal = JSON.parse(result);
			//			console.log(resultLocal);
			//			console.log("selectId是:"+selectId);
			resultLocal.forEach(function (item, index) {
				if (item.userId == UrlLocal.userId && item.courseCode == UrlLocal.courseCode) {
					item.vedio.forEach(function (item2, index2) {
						if (item2.id == $selectId) {
							progressTime = item2.progress;
						}
					});
				}
			});
		}

		//		console.log(progressTime);

		var CUSTOMVEDIO = document.getElementById('my-video_html5_api');
		var myPlayer = null;
		$('#vedio-mask').fadeIn();
		$('#vedio-mask').show().find('.vedio-wrapper').addClass('active');

		var hrefSrc = $(this).attr('vediohref');
		// var myPlayer = videojs('my-video');
		/*初始化视屏*/
		myPlayer = videojs("my-video", {}, function () {
			window.myPlayer = this;
			$("#my-video_html5_api .source").attr("src", hrefSrc);
			myPlayer.src(hrefSrc);
			myPlayer.load(hrefSrc);
			myPlayer.play();
		});
		/*第一次播放*/
		console.log(myPlayer);

		myPlayer.one("firstplay", function () {
			myPlayer.currentTime(progressTime);
			//			console.log("测试："+progressTime)
			//			CUSTOMVEDIO.currentTime=progressTime;

			$('.vjs-volume-panel-horizontal').hide();
			$('.vjs-volume-panel-horizontal').siblings('.custom-volume-btn').remove();
			$('.vjs-volume-panel-horizontal').siblings('.progress-mask').remove();
			$(".vjs-control-bar").append('<div class="progress-mask"></div>');
			$(".vjs-control-bar>.vjs-volume-horizontal").before('<div class="custom-volume-btn" id="danmu_send_opt"><span><i class="iconfont icon-yinliang"></i></span></div>');
			progressMask();

			// 记录播放的次数
			$.post(liulanUrl, { type: "video", is_watched: 0, id: selectId, customerCode: customerCode }, function (res) {
				console.log("vedio点击播放时is_Certicate:" + is_Certicate);
			});
		});

		// 暂时使用h5原生事件处理播放完成事件

		CUSTOMVEDIO.onended = function () {
			console.log("播放完毕");
			$.post(liulanUrl, { type: "video", is_watched: 1, id: selectId, customerCode: customerCode }, function (res) {

				if (res.code == 1 || res.code == 0) {
					$('.play-img[data-id=' + selectId + ']').siblings(".is_watched").show();
				}
				if (res.code == 1) {
					//					console.log("vedio播放完毕时is_Certicate:"+is_Certicate)
					if (is_Certicate == 0) {
						setTimeout(function () {
							$('#getCert-box').show().find('.getCert').addClass('active');
							is_Certicate = 1;
						}, 1000);
					}
				}
			});
		};

		/*获取播放进度判断是否有播放完毕*/
		// myPlayer.one('ended',function(){
		// 	/*console.log(myPlayer.currentTime());*/
		// 	if(myPlayer.duration() !=0 && myPlayer.currentTime() === myPlayer.duration()){
		// 		console.log("selectId的值是："+selectId);
		// 		console.log("播放结束")
		// 		// $.post(liulanUrl,{type: "video",is_watched:true, id:selectId },function(res){
		// 		//  	if(res.code==1){
		// 		// 		 setTimeout(function(){
		// 		// 			$('#getCert-box').show().find('.getCert').addClass('active');
		// 		// 		},1000)
		// 		// 	}
		// 		// })

		// 	}
		// })

		/*进度条的宽度和高度*/
		function progressMask() {
			var progressWidth = $('.vjs-progress-control').outerWidth();
			var progressLeft = $('.vjs-progress-control').position().left;
			$('.progress-mask').css({ 'width': progressWidth - 2 + 'px', 'height': "36px", 'left': progressLeft - 2 + 'px' });
		}
		/*点击音量键*/
		$(".vjs-control-bar").on('click', '.custom-volume-btn', function () {
			if ($(this).hasClass('active')) {
				$(this).removeClass('active');
				$(this).find('i');
				$(this).find('i').removeClass('icon-jingyin').addClass('icon-yinliang');
				myPlayer.volume(0.5);
			} else {
				$(this).addClass('active');
				$(this).find('i').removeClass('icon-yinliang').addClass('icon-jingyin');
				myPlayer.volume(0);
			}
		});
		/*全屏事件*/
		myPlayer.on("fullscreenchange", function () {
			/*console.log("fullscreenchange")*/
			progressMask();
		});
		$(window).resize(function () {
			progressMask();
		});
	});
	/*通过以获取数据的id查找当前点击的是哪个视屏,并将数据赋予内容*/
	function videoContent(selectId) {
		var html = "";
		if (originVideo.length > 0) {
			originVideo.forEach(function (item, index) {
				if (item.id == selectId) {
					html = '\n\t\t\t\t\t\t\t<span class="content">' + item.title + '</span>\t\t\t\t\t\t\n\t\t\t\t\t\t\t<span class="time" style="display:block; font-size:16px;color:#cecaca;margin-top:16px">' + item.create_time.substr(0, 10) + '</span>\n\t\t\t\t\t\t\t<div class="text">' + item.desc + '</div>\n\t\t\t\t\t\t';
					$("#vedio-mask .right-text .title").html(html);
					/*					$("#vedio-mask .right-text .title .text").children()..css({'fontSize':'20px','lineHeight':'28px','margin-top':'20px','color':'#a9a9a9','fontStyle':'normal','fontWeight':'normal','font-family':'Arial, Verdana,sans-serif, Tahoma, Helvetica'});
     */
				}
			});
		}
	}

	//以下代码暂时没有用	
	/*	function jindutiao(myPlayer){
 		myPlayer.ready(function(){
 			var width= $('.vjs-progress-control').position().left;
 			var height= $('.vjs-progress-control').outerHeight();
 			console.log(width);
 			console.log(height);
 			$('.jindutiao').css({'width':'4rem','height':'36px'});
 		})		
 	}*/

	/*点击视屏弹出层关闭按钮*/
	$('.vedio-wrapper .closes-btn').click(function () {
		var progressTime = 0;
		$(this).parents('.box-layer').hide().find('.vedio-wrapper').removeClass('active');
		var CUSTOMVEDIO = document.getElementById('my-video_html5_api');
		CUSTOMVEDIO.pause();
		CUSTOMVEDIO.onpause = function () {
			console.log("视屏暂停了");
			//			console.log("duration是"+CUSTOMVEDIO.duration);
			progressTime = CUSTOMVEDIO.currentTime;
			//			progressTime=window.myPlayer.currentTime();	

			if (progressTime == CUSTOMVEDIO.duration) {
				progressTime = 0;
			}
			window.myPlayer = null;
			storageTime();
		};

		//当关闭视屏按钮时存储当前视屏的播放进度
		function storageTime() {
			var result = localStorage.getItem('vedioStorage');
			console.log("progressTime是：" + progressTime);
			if (result) {
				var resultLocal = JSON.parse(result);
				resultLocal.forEach(function (item, index) {
					if (item.userId == UrlLocal.userId && item.courseCode == UrlLocal.courseCode) {
						item.vedio.forEach(function (item2, index2) {
							if (item2.id == $selectId) {
								item2.progress = progressTime;
								return;
							}
						});

						var someResult = item.vedio.some(function (item3) {
							return item3.id == $selectId;
						});

						if (!someResult) {
							item.vedio.push({ id: $selectId, progress: progressTime });
						}
					}

					return resultLocal;
				});

				console.log(resultLocal);
				localStorage.setItem('vedioStorage', JSON.stringify(resultLocal)); //存入缓存值 			
			}
		}
	});

	/*观看完视屏后弹出获取证书部分*/
	$(".js-getCert-box .closes-btn").click(function () {
		$(this).parents('.box-layer').hide().find('.getCert').removeClass('active');
	});

	//点击文档弹出文档悬浮窗
	$('.documents-wrapper').on('click', '.top-img .search-img', function () {
		var src = $(this).siblings('.img').children('img').attr('src');
		$('#document-mask .left-img img').attr('src', src);
		var selectId = $(this).attr('data-id');
		documentContent(selectId);
		$('#document-mask').show().find('.doumentRead').addClass('active');
	});
	/*通过获取数据的id查找当前点击的是哪个document,并将数据赋予内容*/
	function documentContent(selectId) {
		var html = "";
		if (_document.length > 0) {
			_document.forEach(function (item, index) {
				if (item) {
					if (item.id == selectId) {
						html = '\n\t\t\t\t\t\t\t\t<span class="content">' + item.title + '</span>\n\t\t\t\t\t\t\t\t<span class="time" style="display:block; font-size:16px;color:#cecaca;margin-top:16px">' + item.create_time.substr(0, 10) + '</span>\n\t\t\t\t\t\t\t\t<div class="text">' + item.desc + '</div>\n\t\t\t\t\t\t\t';
						var href = PINGimgURL + 'generic/web/viewer.html?file=' + item.file_url;
						$("#document-mask .right-text .info").html(html);
						var aHtmlFn = "liulanWeb(" + item.id + ", '" + item.customerCode + "')";
						$('#document-mask .right-text .doument-btn').html('<a onclick="' + aHtmlFn + '" class="view" href="' + href + '" target="_blank">View</a>');
					}
				}
			});
		}
	}

	//点击弹出框按钮关闭
	$('.doumentRead .closes-btn').click(function () {
		$(this).parents('.box-layer').hide().find('.doumentRead').removeClass('active');
	});

	//意见反馈按钮
	$(".submit-btn-wrapper .submit-bg").click(function () {
		if ($('.submit-container').hasClass('active')) {
			$(this).find('use').attr('xlink:href', '#icon-yijianfankui');
			$('.submit-container').removeClass('active');
		} else {
			$('.submit-container').addClass('active');
			$(this).find('use').attr('xlink:href', '#icon-yijianfankuiguanbi');
		}
	});

	/*点击提交按钮的动画效果*/
	Ladda.bind('.progress-demo button', {
		callback: function callback(instance) {
			/*console.log(instance);*/
			$('.progress-demo button b.text').addClass('active');
			var progress = 0;
			$.post(feedBackUrl, { feedback: _feedBack }, function (data) {
				/*				console.log(data)*/
				if (data.code == 1) {
					progressFn(1);
				} else {
					progressFn(2);
				}
			});
			function progressFn(num) {
				var interval = setInterval(function () {
					progress = Math.min(progress + Math.random() * 0.1, 1);
					instance.setProgress(progress);
					if (progress === 1) {
						instance.stop();
						clearInterval(interval);
						$('.progress-demo button').addClass('active-1').attr('disabled', 'disabled');
						if (num == 1) {
							$('.ladda-button').css('background', '#53b5e6');
							$('.progress-demo button i.icon1').addClass('duigou active');
						} else if (num == 2) {
							$('.ladda-button').css('background', '#fb797e');
							$('.progress-demo button i.icon1').addClass('cuowu active');
						}

						setTimeout(function () {
							if (num == 1) {
								$('.progress-demo button i.icon1').removeClass('duigou active').siblings('b.text').removeClass('active');
								$(".submit-btn-wrapper .submit-bg").find('use').attr('xlink:href', '#icon-yijianfankui');
								$('.submit-container').removeClass('active');
							} else if (num == 2) {
								$('.progress-demo button i.icon1').removeClass('cuowu active').siblings('b.text').removeClass('active');
							}
							$('.progress-demo button').removeAttr('disabled').removeClass('active-1');
							$('.ladda-button').css('background', '#53b5e6');
						}, 2000);
					}
				}, 100);
			}
		}
	});

	/*点击右侧进行导航*/
	$('.navigation ul li a').click(function () {
		var href = $(this).attr('href');
		var clientDestance = $(href).offset().top;
		/*		$(this).parent().addClass('active').siblings().removeClass('active');*/
		$('html,body').stop().animate({
			scrollTop: clientDestance - 500 + 'px'
		}, 500);
	});

	/*提交评价*/
	$('.submit-container .middle-img a').click(function () {
		var index = $(this).index();

		if (index == 0) {
			$('.submit-container .middle-img a').eq(0).find('use').attr('xlink:href', '#icon-xiaolianjuse');
			$('.submit-container .middle-img a').eq(1).find('use').attr('xlink:href', '#icon-yibanlanse');
			$('.submit-container .middle-img a').eq(2).find('use').attr('xlink:href', '#icon-kulianlanse');
			$(this).addClass('active').siblings().removeClass('active');
			_feedBack = 1;
		} else if (index == 1) {
			$('.submit-container .middle-img a').eq(0).find('use').attr('xlink:href', '#icon-xiaolianlanse');
			$('.submit-container .middle-img a').eq(1).find('use').attr('xlink:href', '#icon-yibanjuse');
			$('.submit-container .middle-img a').eq(2).find('use').attr('xlink:href', '#icon-kulianlanse');
			$(this).addClass('active').siblings().removeClass('active');
			_feedBack = 2;
		} else if (index == 2) {
			$('.submit-container .middle-img a').eq(0).find('use').attr('xlink:href', '#icon-xiaolianlanse');
			$('.submit-container .middle-img a').eq(1).find('use').attr('xlink:href', '#icon-yibanlanse');
			$('.submit-container .middle-img a').eq(2).find('use').attr('xlink:href', '#icon-kulianjuse');
			$(this).addClass('active').siblings().removeClass('active');
			_feedBack = 3;
		} else {
			$('.submit-container .middle-img a').eq(0).find('use').attr('xlink:href', '#icon-xiaolianlanse');
			$('.submit-container .middle-img a').eq(1).find('use').attr('xlink:href', '#icon-yibanlanse');
			$('.submit-container .middle-img a').eq(2).find('use').attr('xlink:href', '#icon-kulianlanse');
			$('.submit-container .middle-img a').removeClass('active');
			_feedBack = 1;
		}
	});

	function DistanceFn() {
		Destance = [];
		var list = $('.section-info');
		list.each(function (index, item) {
			Destance.push($(item).offset().top);
		});
		/*console.log("首次加载："+Destance);*/
		RihgtScroll();
	}
	function RihgtScroll() {
		$(window).scroll(function () {
			/*console.log(Destance);
   console.log("滚动距离："+$(this).scrollTop());*/
			/*	console.log($(this).scrollTop()>Destance[0]-100)*/
			if ($(this).scrollTop() > Destance[0] - 200 && $(this).scrollTop() <= Destance[1] - 800) {
				$('.right-navigation').fadeIn();
				$('.navigation ul li').eq(0).addClass('active').siblings().removeClass('active');
				$('.navigation ul li').eq(0).find('use').attr('xlink:href', '#icon-Videosxuanzhong-');
				$('.navigation ul li').eq(1).find('use').attr('xlink:href', '#icon-Documentsweixuanzhong');
				$('.navigation ul li').eq(2).find('use').attr('xlink:href', '#icon-Webinarsweixuanzhong-');
				$('.navigation ul li').eq(3).find('use').attr('xlink:href', '#icon-FAQsweixuanzhong-');
			} else if ($(this).scrollTop() > Destance[1] - 800 && $(this).scrollTop() <= Destance[2] - 800) {
				$('.right-navigation').fadeIn();
				$('.navigation ul li').eq(1).addClass('active').siblings().removeClass('active');
				$('.navigation ul li').eq(0).find('use').attr('xlink:href', '#icon-Videosweixuanzhong');
				$('.navigation ul li').eq(1).find('use').attr('xlink:href', '#icon-Documentsxuanzhong');
				$('.navigation ul li').eq(2).find('use').attr('xlink:href', '#icon-Webinarsweixuanzhong-');
				$('.navigation ul li').eq(3).find('use').attr('xlink:href', '#icon-FAQsweixuanzhong-');
			} else if ($(this).scrollTop() > Destance[2] - 800 && $(this).scrollTop() <= Destance[3] - 800) {
				$('.right-navigation').fadeIn();
				$('.navigation ul li').eq(2).addClass('active').siblings().removeClass('active');
				$('.navigation ul li').eq(0).find('use').attr('xlink:href', '#icon-Videosweixuanzhong');
				$('.navigation ul li').eq(1).find('use').attr('xlink:href', '#icon-Documentsweixuanzhong');
				$('.navigation ul li').eq(2).find('use').attr('xlink:href', '#icon-Webinarsxuanzhong--');
				$('.navigation ul li').eq(3).find('use').attr('xlink:href', '#icon-FAQsweixuanzhong-');
			} else if ($(this).scrollTop() > Destance[3] - 800) {
				$('.right-navigation').fadeIn();
				$('.navigation ul li').eq(3).addClass('active').siblings().removeClass('active');
				$('.navigation ul li').eq(0).find('use').attr('xlink:href', '#icon-Videosweixuanzhong');
				$('.navigation ul li').eq(1).find('use').attr('xlink:href', '#icon-Documentsweixuanzhong');
				$('.navigation ul li').eq(2).find('use').attr('xlink:href', '#icon-Webinarsweixuanzhong-');
				$('.navigation ul li').eq(3).find('use').attr('xlink:href', '#icon-FAQsxuanzhong-');
			} else {
				$('.right-navigation').fadeOut();
				$('.navigation ul li').removeClass('active');
				$('.navigation ul li').eq(0).find('use').attr('xlink:href', '#icon-Videosweixuanzhong');
				$('.navigation ul li').eq(1).find('use').attr('xlink:href', '#icon-Documentsweixuanzhong');
				$('.navigation ul li').eq(2).find('use').attr('xlink:href', '#icon-Webinarsweixuanzhong-');
				$('.navigation ul li').eq(3).find('use').attr('xlink:href', '#icon-FAQsweixuanzhong-');
			}
		});
	}
	/*点击top返回顶部*/
	$('.submit-btn-wrapper .return-bg').click(function () {
		$('html,body').stop().animate({
			scrollTop: '0px'
		}, 500);
	});

	/*对顶部固定导航的展示的处理*/
	function topFixed() {
		var topHeaderHeight = $('.top-header').outerHeight();
		scrollTop();

		function scrollTop() {
			if ($(window).scrollTop() > topHeaderHeight) {
				$('.fixed-header').addClass('bg');
				$('.logo-img-wrapper .nei').removeClass('active');
				$('.select-language .title').css('color', '#108cee');
				$('.logo-img .line').css('background-color', 'rgba(0,0,0,.2)');
			} else {
				;
				$('.fixed-header').removeClass('bg');
				$('.logo-img-wrapper .nei').addClass('active');
				$('.select-language .title').css('color', '#ffffff');
				$('.logo-img .line').css('background-color', 'rgba(255,255,255,.3)');
			}
		}
		$(window).scroll(function () {
			scrollTop();
		});
	}
});

// 点击文档浏览并记录次数
function liulanWeb(itemId, customer) {
	$.post(liulanUrl, { type: "document", is_watched: true, id: itemId, customerCode: customer }, function (res) {
		if (res.code == 0 || res.code == 1) {
			$('.search-img[data-id=' + itemId + ']').siblings(".is_watched").show();
		}
		//			console.log("document点击时is_Certicate:"+is_Certicate)

		if (res.code == 1) {
			//				console.log("document点击时code为1时is_Certicate:"+is_Certicate)
			if (is_Certicate == 0) {
				setTimeout(function () {
					$('#getCert-box').show().find('.getCert').addClass('active');
					is_Certicate = 1;
				}, 1000);
			}
		}
	});
}
// 点击活动浏览并记录次数
function liulanWebinars(itemId, customer) {

	// 当点击详情时将语言类型存入session
	if (languageObj) {
		sessionStorage.setItem('key', JSON.stringify(languageObj));
	}

	$.post(liulanUrl, { type: "webinars", is_watched: true, id: itemId, customerCode: customer }, function (res) {});
}

// 点击FAQ浏览并记录次数
function liulanFAQ(itemId, that, customer) {
	if ($(that).hasClass("judege")) {
		return false;
	}

	$.post(liulanUrl, { type: "faq", is_watched: true, id: itemId, customerCode: customer }, function (res) {
		$(that).addClass("judege");
		//			console.log("faq点击时is_Certicate:"+is_Certicate)
		if (res.code == 1) {
			//				console.log("faq点击时code为1时is_Certicate:"+is_Certicate)
			if (is_Certicate == 0) {
				setTimeout(function () {
					$('#getCert-box').show().find('.getCert').addClass('active');
					is_Certicate = 1;
				}, 1000);
			}
		}
	});
}
