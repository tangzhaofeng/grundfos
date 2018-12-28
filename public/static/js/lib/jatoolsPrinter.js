function checkJatoolsPrinterInstalled(){
	var support=false,err=null,bs=["MSIE","Firefox","Chrome"];
	for(var i=0;i<bs.length;i++){
		if(navigator.userAgent.indexOf(bs[i])>-1){
			support=true;
			break;
		}
	}
	if(!support){
		err="杰表打印控件不支持本浏览器!";
	}else if(navigator.userAgent.indexOf('Chrome')>-1){
		var a=navigator.plugins,installed=false;
		for(var f=0;f<a.length;f++){
			if(a[f].name.indexOf('jatoolsPrinter')==0){
				installed=true;
				break;
			}
		}
		if(!installed)
			err="杰表打印控件未安装，请点击<a href='jatoolsPrinter.crx'>此处</a>安装.";
	}
	if(err){
		showError(err);
	}
}
function showError(err){
	var buttons=document.getElementsByTagName("input");
	for(var i=0;i<buttons.length;i++){
		buttons[i].disabled=true;
	}
	var _errs=document.getElementById("errs");
	_errs.innerHTML=err;
	_errs.style.display='block';
}
function getJatoolsPrinter(){
	return navigator.userAgent.match(/(msie\s|trident.*rv:)([\w.]+)/i)?document.getElementById('ojatoolsPrinter'): document.getElementById('ejatoolsPrinter');
}