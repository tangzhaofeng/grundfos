<?php

namespace app\admin\controller;
use app\admin\model\CategoryModel;
use think\Db;
use \think\Loader;
class Category extends Base
{
	public function __construct(){
		parent::__construct();
		vendor("Tree");
	}
    //banner list
    public function index() {
		//部门列表
		$department = Db('bu')->order('id desc')->select();
		$this->assign("department",$department);
		
		$department_id = input('param.department_id')? input('param.department_id') : $department[0]['id'];	 
		
		//原始数据, 从数据库读出
		$data = $this->getCategory($department_id);
		//tree struct
		
		if(!empty($data)){
			$data = $this->makeTree($data);
//			return json($data);
			$this->assign("data",$this->treeList($data,$department_id));
		} else {
			$this->assign("data","");
		}
		$type = input("param.type");
		if(isset($type))
			return $this->fetch('data');
		else
			return $this->fetch();
    }
	
	
	//tree struct
	function makeTree(&$data){
		return \Tree::makeTree($data);
	}
	
	function getCategory(&$group_id){
		$ret = Db('category')->where(['group_id'=>$group_id, 'parent_id'=>0])->find();
		if(!empty($ret))
			return Db('category')->where("group_id",$group_id)->order('id desc')->select();
		else 
			return null;
	}


	function treeList(&$data, &$department_id){
		$i = 0;
		$li = '';
		foreach($data as $k=>$v){
			$space		 = "│&emsp;&emsp;";  //空格
			$icon_center = "├──&nbsp;&nbsp;";  //前缀线
			$icon_end 	 = "└──&nbsp;&nbsp;";  //数组中最后
			$array_count = count($data);//当前层的个数
			$i++;
            if ($v['order_num'] == 99999999){
                $v['order_num'] = '';
            }
			$icon = $i===$array_count ? $icon_end : $icon_center;  //计算当前该使用八个icon  'department_id'=>$department_id
			$li.= "<li>".str_repeat($space,$v['level']).$icon.$v['name']."&nbsp;&nbsp;排序:".$v['order_num']."<a href='".url('add',['parent_id'=>$v['id'],'department_id'=>$department_id])."'> <i class='icon icon-36'></i>添加子分类</a> <a href='".url('edit',['id'=>$v['id'],'parent_id'=>$v['parent_id'],'department_id'=>$department_id  ])."'><i class='icon icon-115'></i>编辑</a> <a href='".url('delete',['id'=>$v['id']])."'><i class='icon icon-23'></i>删除</a></li>";
			if(!empty($v['children'])){
				$li.= '<div class="level'.$v['level'].'">'.$this->treeList($v['children'],$department_id)."</div>";
			}
		}
		return $li;
	}
	
	function treeOptionList(&$data, $selected_id){
		$i = 0;
		$option = '';
		foreach($data as $k=>$v){
			$space		 = "│&emsp;&emsp;";  //空格
			$icon_center = "├──&nbsp;&nbsp;";  //前缀线
			$icon_end 	 = "└──&nbsp;&nbsp;";  //数组中最后
			$array_count = count($data) ;//当前层的个数
			$i++;
			$icon = $i===$array_count ? $icon_end : $icon_center;  //计算当前该使用那个icon
			
			$selected = ($selected_id==$v['id'])?'selected="selected"': '';
			$option.= "<option value='".$v['id']."'  $selected >".str_repeat($space,$v['level']).$icon.$v['name']."</option>";
			if(!empty($v['children'])){
				$option.= $this->treeOptionList($v['children'],$selected_id);
			}
		}
		return $option;
	}

   //Video add
    public function add()
    {
        if(request()->isPost()){
            $param = input('post.');
            $param['create_time'] = Date('Y-m-d H:i:s',time());
            $obj = new CategoryModel(); 
            return json($obj->insert($param));
        } else {
			$department_id = input('param.department_id');
			$data = $this->getCategory( $department_id );
			if(!empty($data)){
				$data = $this->makeTree($data);
				$parent_id = input('param.parent_id');
				$this->assign("option",$this->treeOptionList($data,$parent_id));
			} else {
				$this->assign("option","");
			}
            return $this->fetch();
        }
    }

    //编辑视频 
    public function edit()
    { 
        $obj = new CategoryModel();
        if(request()->isPost()){
            $param = input('param.');
            return json( $obj->edit($param) );
        } else {
            $id = input('param.id');
            $this->assign('data',$obj->getOneData($id));
			$department_id = input('param.department_id');
			
			$data = $this->getCategory($department_id);
			$data = \Tree::makeTree($data);
			$this->assign("option",$this->treeOptionList($data,input('param.parent_id')));
            return $this->fetch();
        }
    }

    //删除视频 
    public function delete()
    {
        $id = input('param.id');
        $obj = new CategoryModel();
		$ret = $obj->del($id);
		
		$this->hand_delete($ret);
        return json($ret);
    }

	//处理该条删除
	function hand_delete(&$ret){
		$child_category = Db('category')->where('parent_id',$ret['id'])->select();
		if(!empty($child_category)){
			foreach($child_category as $k=>$v){
				$this->hand_delete($v['id']);
			}
		}
	}


}
