<?php

namespace app\admin\controller;
use app\admin\model\SystemUserModel;
use app\admin\model\UserType;

class Systemuser extends Base
{
    //用户列表
    public function index() {
        $param = input('param.');
        $size = 10;
        $where = [];
        if (isset($param['searchText']) && !empty($param['searchText'])) {
            $where['username'] = ['like', '%' . trim($param['searchText']) . '%'];	
        }
        $obj = new SystemUserModel();
        $selectResult = $obj->getUsersByWhere($where, $size);
        
        $return['searchText'] = !empty($param['searchText'])?trim($param['searchText']):null;
        $return['total'] = $obj->getAllUsers($where);  //总数据
        $return['rows'] = $selectResult;
        $return['page'] = $selectResult->render();
        $this->assign('return',$return);
        
        return $this->fetch();
    }

    //添加用户 
    public function add()
    {
        if(request()->isPost()){
            $param = input('param.');
            $param['password'] = md5($param['password']);
            $user = new SystemUserModel();
            return json( $user->insertUser($param) );
 
        }

        $role = new UserType();
        $this->assign([
            'role' => $role->getRole(),
            'status' => config('user_status')
        ]);

        return $this->fetch();
    }

    //编辑角色
    public function edit()
    {
        $user = new SystemUserModel();

        if(request()->isPost()){

            $param = input('post.');
            
            if(empty($param['password'])){
                unset($param['password']);
            }else{
                $param['password'] = md5($param['password']);
            }

             return json( $user->editUser($param) );
        }

        $id = input('param.id');
        $role = new UserType();
        $this->assign([
            'user' => $user->getOneUser($id),
            'status' => config('user_status'),
            'role' => $role->getRole()
        ]);
        return $this->fetch();
    }

    //删除角色
    public function delete()
    {
        $id = input('param.id');

        $role = new SystemUserModel();
        $flag = $role->delUser($id);
        return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
    }
}
