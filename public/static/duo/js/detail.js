$(function(){
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
	setTimeout(function(){
		$('.dropdown p').click(function() {
			var value = $(this).text();
			$('.select-language .title .text').text(value);
			document.getElementsByClassName('dropdown')[0].classList.toggle('down');
			/*			document.getElementsByClassName('arrow')[0].classList.toggle('gone');*/
			document.getElementsByClassName('dropdown')[0].style.overflow = 'hidden'
		})
	},500)
	
	

	
	//点击弹出框按钮关闭
	$('.closes-btn').click(function(){
		$(this).parents('.box-layer').fadeOut(); 
	})
	
	$('.content .btn').click(function(){
		$('#form').fadeIn(); 
	})
})
