<?php

namespace app\admin\validate;

use think\Validate;

class SystemUserValidate extends Validate
{
    protected $rule = [
        ['username', 'unique:user', 'The administrator has already existed']
    ];

}