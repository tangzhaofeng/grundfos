<?php

namespace app\admin\controller;

use app\admin\model\Node;
use think\Controller;
use think\Cache;

class Base extends Controller
{
    public function _initialize()
    {	
		$username = session('username');
        if(empty($username)){
            $this->redirect(url('admin/login/index'));
        }

        //检测权限
        $control = lcfirst( request()->controller() );
        $action = lcfirst( request()->action() );

        //跳过登录系列的检测以及主页权限
//        if(!in_array($control, ['login', 'index'])){
//
//            if(!in_array($control . '/' . $action, session('action'))){
//                $this->error('Sorry! Access is denied');
//            }
//        }
//
        //获取权限菜单
        $node = new Node();
        session('rule',null);
//        print_r($node->getMenu(session('rule')));exit();
        $this->assign([
            'username' => session('username'),
            'menu' => $node->getMenu(session('rule')),
            'rolename' => session('role')
        ]);

    }
}