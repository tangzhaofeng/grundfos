<?php

namespace app\admin\model;

use think\Model;

class MessageModel extends Model
{

    protected $table = "xk_message";


    public function getBuBywhere($where, $size)
    {
        $data = [];
        if (isset($where['bu_id'])){
            $data['c.bu_id'] = $where['bu_id'];
        }
        return $this->alias('c')
            ->join('xk_bu b','b.id = c.bu_id')
            ->join('xk_grouping g','c.group_id=g.id','LEFT')
            ->field(['c.*','b.bu_name','g.grouping_name'])
            ->where($data)
            ->order('c.id desc')
            ->paginate($size);
    }

    public function getAllUsers($where)
    {
        return $this->where($where)->count();
    }

    public function inster($param)
    {
        try{
            //等待确认在加验证
            $result = $this->save(['content'=>$param['content'],'bu_id'=>$param['bu_id'],'creater_time'=>date('Y-m-d H:i:s',time()),'group_id'=>$param['group_id'],'template_content'=>$param['template_content'],
                'template_id'=>$param['template_id'],'type'=>$param['type'],'media'=>$param['media']
                ]);
            if(false === $result){
                // 验证失败 输出错误信息
                return ['code' => -1, 'data' => '', 'msg' => $this->getError()];
            }else{

                return ['code' => 1, 'data' => '', 'msg' => '添加成功', 'faq_id'=>$this->id ];
            }
        }catch( PDOException $e){

            return ['code' => -2, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

    public function edit($param)
    {
        try{

            $result =  $this->validate('RoleValidate')->save($param, ['id' => $param['id']]);

            if(false === $result){
                // 验证失败 输出错误信息
                return ['code' => 0, 'data' => '', 'msg' => $this->getError()];
            }else{

                return ['code' => 1, 'data' => '', 'msg' => '编辑成功'];
            }
        }catch( PDOException $e){
            return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

    public function del($id) {
        try{
            $data = $this->where('id', $id)->find();
            $this->where('id', $id)->delete();
            return ['code' => 1, 'data' => '', 'msg' => '删除成功','id'=>$id, 'data'=>$data];
        }catch( PDOException $e){
            return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

}