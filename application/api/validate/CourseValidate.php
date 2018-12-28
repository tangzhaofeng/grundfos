<?php
// +----------------------------------------------------------------------
// | validate vieo 
// +----------------------------------------------------------------------
namespace app\admin\validate;

use think\Validate;

class CourseValidate extends Validate
{
    protected $rule = [
        'bookingCourse'   => 'require',  
    ];

    protected $msg = [
        'bookingCourse.require'      => 'bookingCourse is required ',
    ];



}