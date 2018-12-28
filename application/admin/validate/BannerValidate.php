<?php
// +----------------------------------------------------------------------
// | validate vieo 
// +----------------------------------------------------------------------
namespace app\admin\validate;

use think\Validate;

class BannerValidate extends Validate
{
    protected $rule = [
        'poster'   => 'require',  
    ];

    protected $msg = [
        'poster.require'      => 'title value is required ',
    ];



}