<?php

return [
    //模板参数替换
    'view_replace_str'       => array(
        '__CSS__'    => '/public/static/css',
        '__JS__'     => '/public/static/js',
        '__IMG__' => '/public/static/img',
        '__STATIC__'    => '/public/static',//静态文件路径   
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
