
var weui_icon_warn="<i class='weui_icon_warn'></i>";
Parsley.addMessages('zh-cn', {
  defaultMessage: weui_icon_warn+"不正确的值",
  type: {
    email:        weui_icon_warn+"请输入一个有效的电子邮箱地址",
    url:          weui_icon_warn+"请输入一个有效的链接",
    number:       weui_icon_warn+"请输入正确的数字",
    integer:      weui_icon_warn+"请输入正确的整数",
    digits:       weui_icon_warn+"请输入正确的号码",
    alphanum:     weui_icon_warn+"请输入字母或数字"
  },
  notblank:       weui_icon_warn+"请输入值",
  required:       weui_icon_warn+"必填项",
  pattern:        weui_icon_warn+"格式不正确",
  min:            weui_icon_warn+"输入值请大于或等于 %s",
  max:            weui_icon_warn+"输入值请小于或等于 %s",
  range:          weui_icon_warn+"输入值应该在 %s 到 %s 之间",
  minlength:      weui_icon_warn+"请输入至少 %s 个字符",
  maxlength:      weui_icon_warn+"请输入至多 %s 个字符",
  length:         weui_icon_warn+"字符长度应该在 %s 到 %s 之间",
  mincheck:       weui_icon_warn+"请至少选择 %s 个选项",
  maxcheck:       weui_icon_warn+"请选择不超过 %s 个选项",
  check:          weui_icon_warn+"请选择 %s 到 %s 个选项",
  equalto:        weui_icon_warn+"输入值不同"
});

Parsley.setLocale('zh-cn');
