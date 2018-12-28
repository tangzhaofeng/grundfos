<?php
// +----------------------------------------------------------------------
// | validate vieo 
// +----------------------------------------------------------------------
namespace app\admin\validate;

use think\Validate;

class IntegralgoodsValidate extends Validate
{
    protected $rule = [
        'name'      => 'require|max:250',
        //'file_url'   => 'require',  
    ];

    protected $msg = [
        'name.require'      => 'title value is required ',
        'name.max'          => 'title can not exceed 250 characters at most',
        //'file_url.require'   => 'video is required',
    ];



}