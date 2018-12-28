<?php

namespace app\admin\model;

use think\Db;
use think\Model;

class ActivitymemberModel extends Model
{

    protected $table = "xk_activity_member";

    public function getBuBywhere($where, $size)
    {
        return $this
            ->alias('c')
            ->join('xk_member b','b.id = c.member_id')
            ->join('xk_company_work_type w','w.id = b.company_work_type_id')
            ->join('xk_company_type t','t.id = b.company_id')
            ->join('xk_bu u','u.id = b.is_bu_id')
            ->field(['c.*','b.name','w.work_type_name','t.type_name','u.bu_name','b.name',
                'b.nickname',
                'b.sex',
                'b.headimgurl',
                'b.tel',
                'b.email',
                'b.id as member_id'
            ])
            ->where($where)
//            ->order('c.id desc')
            ->paginate($size);
    }

    public function getAllUsers($where)
    {
        return $this->alias('c')
        ->where($where)
        ->join('xk_member b', 'b.id = c.member_id')
        // ->where('b.id','not null')
        ->count();
    }


    /**
     * 线下报名
     */
    public function signUp($member_id,$activity_id,$member_work)
    {
        try{
//            Db::startTrans();
            $todo = $this->where(['member_id'=>$member_id,'activity_id'=>$activity_id])->find();
            if (!$todo){
                
                $result = $this->save(['activity_id' => $activity_id,
                    'member_id' => $member_id,
                    'status' => 1,
                    'time' => date('Y-m-d H:i:s'),
                    'is_open' => 1
                ]);

                if(false === $result){
                    return ['code' => 0,'msg' => '报名失败'];
                }else{

                    //报名成功如果是线下活动进行积分添加
                    $info = Db('activity')->where('id',$activity_id)->find();
                    if ($info['line'] == 1) {
                        $this->addIntegral($member_id, 2, $info['integral'], $activity_id);
                    }
//                    Db::commit();
                    return ['code' => 1,'msg' => '报名成功'];
                }
            }
            return ['code' => 2,'msg' => '您已报名'];
        }catch(PDOException $e){
//            Db::rollback();
            return returninfos( '0',$e->getMessage());
        }
    }

    /**
     * 添加用户积分
     */
    public function addIntegral($member_id,$title,$activity_integral,$title_id)
    {
        $old_member_integral = Db('member')->where('id',$member_id)->value('integral');
        $integral = $old_member_integral+$activity_integral;

        //对用户添加积分
        Db('member')->update(['integral'=>$integral,'id'=>$member_id]);

        //添加积分操作记录
            Db('member_integral')->insert([
                'member_id'=>$member_id,'title'=>$title,
                'action'=>1,'now'=>$integral,'integral'=>intval($activity_integral),
                'title_id'=>$title_id,'create_time'=>date('Y-m-d H:i:s',time())
            ]);

    }

    /**
     * 签到
     */
    public function scanUp($activity_id,$member_id,$qrcode = '')
    {
        try{

                $info = $this->where('activity_id',$activity_id)->where('member_id',$member_id)->find();
                if(NULL === $info){
                    return returninfos( '2','您未报名此活动');
                }
                $result = $this->where('activity_id',$activity_id)->where('member_id',$member_id)->update([
                    'status' => 2,
                    'sign_time' => date('Y-m-d H:i:s',time())
                ]);

                if(false === $result){
                    return returninfos( '0','签到失败');
                }else{
                    //签到成功添加积分
                    $info = Db('activity')->where('id',$activity_id)->find();
                    if ($info['line'] == 1) {
                        $this->addIntegral($member_id, 3, 5, $activity_id);
                    }
                    $activity = Db('activity')->where('id',$activity_id)->find();
                    $member_info = Db('member')->where('id',$member_id)->find();
                    $member_info['title'] = $activity['title'];
                    $member_info['isfollow'] = 0;
                    if ($qrcode){
                        $member_info['isfollow'] = 1;
                        $member_info['qrcode'] = $qrcode;
                    }
                    return returninfos( '1','签到成功',$member_info);
                }
        }catch(PDOException $e){
            return returninfos( '0',$e->getMessage());
        }
    }

       /**
     * 查询参加此活动的所以用户
     */
    public function getAcMember($id,$bu_id)
    {
        try{
           return collection($this->alias('t')
                       ->join('xk_member m','m.id = t.member_id')
                       ->join('xk_company_work_type w','w.id = m.company_work_type_id')
                       ->join('xk_company_type ct','ct.id = m.company_id')
                       ->join('xk_member_openid_unionid o','o.unionid = m.unionid')
                       ->where('o.bu_id',$bu_id)
                       ->where('t.activity_id',$id)
                       ->field(['m.name','m.tel','m.email','ct.type_name','m.company_name','w.work_type_name','o.openid','t.time','t.sign_time'])
                       ->select())->toArray();
        }catch(PDOException $e){
            return returninfos( '0',$e->getMessage());
        }
    }

    /**
     * 获取所有签到的数量
     */
    public function onSign($id,$on)
    {
        return count(collection(
                $this->alias('c')
                ->join('xk_member b', 'b.id = c.member_id')
                ->where('activity_id',$id)->where('c.status',$on)->field('c.*')->select()
        )->toArray());
    }
}