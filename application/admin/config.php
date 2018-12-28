<?php

return [
    //模板参数替换
    'view_replace_str'       => array(
        '__CSS__'    => '/glf/public/static/css',
        '__JS__'     => '/glf/public/static/js',
        '__IMG__' => '/glf/public/static/img',
    '__STATIC__'    =>  '/glf/public/static',
     '__UPLOAD__'    =>  '/glf/public/uploads'
    ),

    //管理员状态
    'user_status' => [
        '1' => '正常',
        '2' => '禁止登录'
    ],
    //角色状态
    'role_status' => [
        '1' => '启用',
        '2' => '禁用'
    ]
];
