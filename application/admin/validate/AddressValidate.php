<?php
// +----------------------------------------------------------------------
// | validate vieo 
// +----------------------------------------------------------------------
namespace app\admin\validate;

use think\Validate;

class AddressValidate extends Validate
{
    protected $rule = [
        'username'      => 'require|max:250',
        'phone'      => 'require',
        'city'      => 'require',
        'address'      => 'require',
        //'file_url'   => 'require',  
    ];

    protected $msg = [
        'username.require'      => 'username value is required ',
        'phone.require'      => 'phone value is required ',
        'city.require'      => 'city value is required ',
        'address.require'      => 'address value is required ',
        'username.max'          => 'username can not exceed 250 characters at most',
        //'file_url.require'   => 'video is required',
    ];



}