
/*
 =====【滚动到顶部】===
*/
window.onscroll = function(){ 
     var t = document.documentElement.scrollTop || document.body.scrollTop;  //获取距离页面顶部的距离
     if( t >= 100 ) { //当距离顶部超过300px时
        $( ".bottom_tools" ).show();
     } else { //如果距离顶部小于300px 
        $( ".bottom_tools" ).hide();
     } 
} 
var timer=null;
$('.bottom_tools').onclick = function(){ //点击图片时触发点击事件
        timer=setInterval(function(){ //设置一个计时器
        var ct = document.documentElement.scrollTop || document.body.scrollTop; //获取距离顶部的距离
        ct-=40;
        if(ct>0){
            //如果与顶部的距离大于零
             window.scrollTo(0,ct);//向上移动10px
        }else{
            //如果距离小于等于零
             window.scrollTo(0,0);//移动到顶部
             clearInterval(timer);//清除计时器
        }
    },10);//隔10ms执行一次前面的function，展现一种平滑滑动效果
}