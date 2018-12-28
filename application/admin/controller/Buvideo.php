<?php

namespace app\admin\controller;

class Buvideo extends Base {
    public function index () {
        //部门列表
		$department = Db('bu')->order('id desc')->select();
        $this->assign("department",$department);
        
        $department_id = input('param.department_id')? input('param.department_id') : $department[0]['id'];
        $type = input("param.type");
		if(isset($type)){
            $return['rows'] = Db('bu_video')->where('bu_id', $department_id)->paginate(10,false,[
                'query' => [
                    'department_id' => $department_id
                ]
            ]);
            $return['total'] = Db('bu_video')->where('bu_id', $department_id)->count();
            $this->assign('department_id', $department_id);
            $return['page'] = $return['rows']->render();
            $this->assign('return', $return);
            return $this->fetch('data');
        }
		else{
            return $this->fetch('index');
        }
    }
    public function add() {
        if (request()->isPost()){
            $param = input('post.');
            $result = Db('bu_video')->insert([
                'video_name' => $param['title'],
                'video_dir' => $param['img'][0],
                'get_img' => $param['get_img'],
                'create_time' => time(),
                'tag' => $param['tag'],
                'view_tag' => $param['view_tag'],
                'bu_id' => $param['bu_id'],
                'video_type' => $param['video_type'],
            ]);
            return json(['code' => 1]);
        } else {
            return $this->fetch();
        }
    }

    //$_SERVER["DOCUMENT_ROOT"]
    public function delete() {
        $param = input('param.');
        $res = Db('bu_video')->where('id', $param['id'])->find();
        Db('bu_video')->where('id', $param['id'])->delete();
        //删除视频和文件
        if ($res['get_img']) {
            unlink($_SERVER["DOCUMENT_ROOT"].$res['get_img']);
        }
        if ($res['video_dir']) {
            unlink($_SERVER["DOCUMENT_ROOT"].$res['video_dir']);
        }
        $this->success('删除成功', 'Buvideo/index/department_id/'.$param['department_id']);
    }

    public function edit() {
        if (request()->isPost()){
            $param = input('post.');
            $data = Db('bu_video')->where('id', $param['id'])->find();
            //删除旧图片
            if ($data['get_img'] != $param['get_img'] && $param['get_img'] && $data['get_img']) {
                unlink($_SERVER["DOCUMENT_ROOT"].$data['get_img']);
            }
            $where = [];
            if ($param['title']) {
                $where['video_name'] = $param['title'];
            }
            if ($param['get_img']) {
                $where['get_img'] = $param['get_img'];
            }
            if ($param['tag']) {
                $where['tag'] = $param['tag'];
            }
            if ($param['view_tag']) {
                $where['view_tag'] = $param['view_tag'];
            }
            Db('bu_video')->where('id', $param['id'])->update($where);
            $this->success('编辑成功', 'Buvideo/index');
        }
        else {
            $param = input('param.');
            $data = Db('bu_video')->where('id', $param['id'])->find();
            $this->assign('data', $data);
            return $this->fetch();
        }
    }
}