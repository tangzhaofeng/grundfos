<?php
// +----------------------------------------------------------------------
// | validate vieo 
// +----------------------------------------------------------------------
namespace app\admin\validate;

use think\Validate;

class VideoValidate extends Validate
{
    protected $rule = [
        'title'      => 'require|max:250',
        'file_url'   => 'require',  
    ];

    protected $msg = [
        'title.require'      => 'title value is required ',
        'title.max'          => 'title can not exceed 250 characters at most',
        'file_url.require'   => 'video is required',
    ];



}