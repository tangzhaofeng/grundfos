/**
 * 主方法
 */
var Main = function() {

	var defaultGridSettings = {
		lang : 'zh-cn',
	    tools : '',
	    pageSize : 10,
	    check : true,
	    pageSizeLimit : [10, 20, 50,100]
	};
	
	return {
		/**
		 * 创建数据表格
		 */
		createTable : function(settings) {
			var grid = null;
			if(settings) {
				grid = $.fn.DtGrid.init($.extend(defaultGridSettings,settings));
			} else {
				grid = $.fn.DtGrid.init(defaultGridSettings);
			}
			return grid;
		},
		/**
		 * 禁用左侧菜单
		 */
		disableLeft : function() {
			window.parent.frames['leftFrame'].disable();
		},
		/**
		 * 禁用页头菜单
		 */
		disableTop : function() {
			window.parent.frames['topFrame'].disable();
		},
		/**
		 * 启用左侧菜单
		 */
		enableLeft : function() {
			window.parent.frames['leftFrame'].enable();
		},
		/**
		 * 启用页头菜单
		 */
		enableTop : function() {
			window.parent.frames['topFrame'].enable();
		}
	};
}();

$(function() {
	//全选
	$(".chkall").on("change",function() {
		$("input[type='checkbox'][name='"+$(this).attr("target")+"']").removeAttr("checked");
		if($(this).is(':checked')) {
			$("input[type='checkbox'][name='"+$(this).attr("target")+"']").trigger("click");
		} else {
			$("input[type='checkbox'][name='"+$(this).attr("target")+"']").trigger("click");
			$("input[type='checkbox'][name='"+$(this).attr("target")+"']").trigger("click");
		}
	});
});