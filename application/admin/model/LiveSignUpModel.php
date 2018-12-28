<?php

namespace app\admin\model;

use think\exception\PDOException;
use think\Model;

class LiveSignUpModel extends Model
{

    protected $table = "xk_live_signup";

    public function getBuBywhere($where, $size)
    {
        return $this->alias('c')
//            ->join('xk_bu b','b.id = c.bu_id')
//            ->field(['c.*','b.bu_name'])
            ->where($where)
//            ->order('c.id desc')
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
            $result = $this->save($param);
            if(false === $result){
                // 验证失败 输出错误信息
                return ['code' => -1, 'data' => '', 'msg' => $this->getError()];
            }else{

                return ['code' => 1, 'data' => '', 'msg' => '报名成功', 'faq_id'=>$this->id ];
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


    /**
     * 个人中心获取详情
     */
    public function getInfo($id)
    {
        $info = $this->where('id',$id)->find();
        return returninfos('1',session('type'),$info);
    }

    /**
     * 获取当前buid线上或线下全部有效的展会
     */
    public function getBuActivity($bu_id,$line)
    {
        try{
            //查询当前线下活动与当前bu的
            $info = collection($this->where('status','1')
                ->where(['line'=>$line,'bu_id'=>$bu_id])
                ->field(['id','img','city','title','time','bu_id','time_end','is_open'])
                ->select())
                ->toArray();
            $list = array();
            foreach($info as $k=>$v){
                //  return returninfos('0',$v['bu_id']);
                if ($v['bu_id'] == $bu_id) {
                    $list[$k]['id'] = $v['id'];
                    $list[$k]['img'] = $v['img'];
                    $list[$k]['city'] = $v['city'];
                    $list[$k]['title'] = $v['title'];
                    $list[$k]['time'] = $v['time'];
                    $list[$k]['time_end'] = $v['time_end'];
                     $info[$k] = $v;
                }
            }

            return returninfos('1','查询成功',$info);
        }catch(PDOException $e){
            return returninfos( '0',$e->getMessage());
        }
    }

    /**
     * 验证用户部门是否符合报名资格
     */
    public function isOpen($member_work,$actitvity_id)
    {
        $work_id = $this->where('id',$actitvity_id)->value('work_id');

        if (!strstr($work_id,strval($member_work))) {
            return returninfos('2','账户不符合报名资格');
        }
    }

    /**
     * 线上活动分享照片
     */
    public function saveLine($param)
    {
        try{
            //等待确认在加验证
            $result = $this->save($param);
            if(false === $result){
                // 验证失败 输出错误信息
                return returninfos('2','提交失败');
            }else{
                return returninfos('1','提交成功');
            }
        }catch( PDOException $e){
            return returninfos('2',$e->getMessage());
        }
    }

    /**
     * 分组bu查询全部有效与活动时间未过期的活动
     */
    public function getAll()
    {
        try{
            return collection($this->alias('a')
                ->join('xk_bu b','b.id = a.bu_id')
                ->where('a.status','=','1')
                ->where('a.time_end','>',date('Y-m-d H:i'))
                ->field(['a.title','a.id activity_id','b.bu_name','b.id bu_id','a.is_open'])
                ->select())->toArray();
        }catch (PDOException $e){
            return ['code' => -2, 'data' => '', 'msg' => $e->getMessage()];
        }
    }


}