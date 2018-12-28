



$.ajax({
    url:languageApi,
    async:false,
    success:function (res) {
        langres(res)
    }
})

function langres(res){
//语言版本								
if(res && res.length>0){
    var langHtml="";
    if(res.length>1){
        res.forEach(function(item,index){
            if(item==1){
                langHtml+=`<p data-language="en" lang-num="1">English<span class="fa fa-inbox"></span></p>`;
            }else if(item==2){
                langHtml+=`<p data-language="zh" lang-num="2">中文<span class="fa fa-inbox"></span></p>`;
            }else if(item==3){
                langHtml+=`<p data-language="ja" lang-num="3">Japan<span class="fa fa-inbox"></span></p>`;
            }else if(item==4){
                langHtml+=`<p data-language="sp" lang-num="4">Spaish<span class="fa fa-inbox"></span></p>`;
            }else if(item==5){
                langHtml+=`<p data-language="in" lang-num="5">Indo<span class="fa fa-inbox"></span></p>`;
            }else if(item==6){
                langHtml+=`<p data-language="ru" lang-num="6">Russian<span class="fa fa-inbox"></span></p>`;
            }else if(item==7){
                langHtml+=`<p data-language="ar" lang-num="7">Arabic<span class="fa fa-inbox"></span></p>`;
            }else if(item==8){
                langHtml+=`<p data-language="be" lang-num="8">Bengali<span class="fa fa-inbox"></span></p>`;
            }else if(item==9){
                langHtml+=`<p data-language="br" lang-num="9">Brazilian<span class="fa fa-inbox"></span></p>`;
            }														
        })
        
        //多种语言默认是英语
        languageObj = {
            num: 1,
            type: 'English',
            Change: 'en'
        };
    }else if(res.length==1){
        var item=res[0];				
        if(item==1){
            langHtml=`<p data-language="en" lang-num="1">English<span class="fa fa-inbox"></span></p>`;
            languageObj = {
                num: 1,
                type: 'English',
                Change: 'en'
            };
        }else if(item==2){
            langHtml=`<p data-language="zh" lang-num="2">中文<span class="fa fa-inbox"></span></p>`;
            languageObj = {
                num: 2,
                type: '中文',
                Change: 'zh'
            };
        }else if(item==3){
            langHtml=`<p data-language="ja" lang-num="3">Japan<span class="fa fa-inbox"></span></p>`;
            languageObj = {
                num: 3,
                type: 'Japan',
                Change: 'ja'
            };
        }else if(item==4){
            langHtml=`<p data-language="sp" lang-num="4">Spaish<span class="fa fa-inbox"></span></p>`;
            languageObj = {
                num: 4,
                type: 'Spaish',
                Change: 'sp'
            };
        }else if(item==5){
            langHtml=`<p data-language="in" lang-num="5">Indo<span class="fa fa-inbox"></span></p>`;
            languageObj = {
                num: 5,
                type: 'Indo',
                Change: 'in'
            };
        }else if(item==6){
            langHtml=`<p data-language="ru" lang-num="6">Russian<span class="fa fa-inbox"></span></p>`;
            languageObj = {
                num: 6,
                type: 'Russian',
                Change: 'ru'
            };
        }else if(item==7){
            langHtml=`<p data-language="ar" lang-num="7">Arabic<span class="fa fa-inbox"></span></p>`;
            languageObj = {
                num: 7,
                type: 'Arabic',
                Change: 'ar'
            };
        }else if(item==8){
            langHtml=`<p data-language="be" lang-num="8">Bengali<span class="fa fa-inbox"></span></p>`;
            languageObj = {
                num: 8,
                type: 'Bengali',
                Change: 'be'
            };
        }else if(item==9){
            langHtml=`<p data-language="br" lang-num="9">Brazilian<span class="fa fa-inbox"></span></p>`;	
            languageObj = {
                num: 9,
                type: 'Brazilian',
                Change: 'br'
            };
        }								
    }			
	
	
	var sessionData = JSON.parse(sessionStorage.getItem('key'));
	
	if(sessionData != null) {
		console.log(sessionData)
		languageObj = {
			num: sessionData.num,
			type: sessionData.type,
			Change: sessionData.Change
		};
		if(languageObj.num == 2) {
			$('head').prepend('<link rel="stylesheet" href="'+PINGimgURL+'css/fontFamilyZH.css" />');
		} else if(languageObj.num) {
			$('head').prepend('<link rel="stylesheet" href="'+PINGimgURL+'css/fontFamilyEn.css" />');
		}
	}else{
		if(languageObj.num == 2) {
	        $('head').prepend('<link rel="stylesheet" href="'+PINGimgURL+'css/fontFamilyZH.css" />');
	    } else if(languageObj) {
	        $('head').prepend('<link rel="stylesheet" href="'+PINGimgURL+'css/fontFamilyEn.css" />');
	    }	
	}

    $('.js-language').html(langHtml)		
   
   
   
    
              
    // setTimeout(function(){
    //      var internationScriptHtml='<script src="'+PINGimgURL+'/js/international.js" type="text/javascript" charset="utf-8">'+'<\/script>';
    //     $(document.body).append(internationScriptHtml);
        
        
    //     var indexScriptHtml='<script src="'+PINGimgURL+'/js/index12.js" type="text/javascript" charset="utf-8">'+'<\/script>';
    //     $(document.body).append(indexScriptHtml);
    // },500)
   

    $('html').removeAttr('style');
    
}else{
    var langHtml=`<p data-language="en" lang-num="1">English<span class="fa fa-inbox"></span></p>`;
    languageObj = {
        num: 1,
        type: 'English',
        Change: 'en'
    };
    var _language = languageObj.num;
    //在调取页面数据
}
}
