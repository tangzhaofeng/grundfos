<?php

namespace app\admin\model;

use think\Model;

class MemberModel extends Model
{

    protected $table = "xk_member";

    public function getBuBywhere($where, $size,$arrmember)
    {

        $data= '';
        foreach ($arrmember as $k=>$v){
            $data .= ' or M.id='.$v['member_id'];
        }
        if (isset($where['tel'])){
            $whereor['M.tel'] = $where['tel'];
        }else{
            $whereor = '';
        }

        if (isset($where['date'])){
            return $this->alias('M')
                ->join('xk_company_type t','t.id = M.company_id','LEFT')
                ->join('xk_company_work_type w','w.id = M.company_work_type_id','LEFT')
                ->where(substr($data,4))
                ->where('M.create_time','>',$where['date'])
                ->where('M.create_time','<',$where['enddate'])
                ->where($whereor)
                ->field(['M.*','t.type_name','w.work_type_name'])
                ->order('M.create_time desc')
                ->Distinct(true)
                ->paginate($size);
        }
        return $this->alias('M')
            ->join('xk_company_type t','t.id = M.company_id','LEFT')
            ->join('xk_company_work_type w','w.id = M.company_work_type_id','LEFT')
            ->where(substr($data,4))
            ->where($whereor)
            ->field(['M.*','t.type_name','w.work_type_name'])
            ->order('M.create_time desc')
            ->Distinct(true)
            ->paginate($size);

    }

    public function getAllUsers($where)
    {
        return $this->where($where)->count();
    }

    /**
     * @param $param
     * 获取指定bu下的全部用户
     */
    public function getArrMemberId($bu_id,$where=[])
    {
        if (isset($where['date'])){
            return collection(
                $this->alias('M')
                    ->join('xk_member_openid_unionid ou','ou.unionid = M.unionid')
                    ->where('ou.bu_id',$bu_id)
                    ->where('M.create_time','>',$where['date'])
                    ->where('M.create_time','<',$where['enddate'])
                    ->order('M.create_time desc')
                    ->field(['M.id as member_id','M.name','M.unionid'])->Distinct(true)->select()
            )->toArray();
        }else{
            return collection(
                $this->alias('M')
                    ->join('xk_member_openid_unionid ou','ou.unionid = M.unionid')
                    ->where('ou.bu_id',$bu_id)
                    ->order('M.create_time desc')
                    ->field(['M.id as member_id','M.name','M.unionid'])->Distinct(true)->select()
            )->toArray();
        }
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

                return ['code' => 1, 'data' => '', 'msg' => '添加成功', 'faq_id'=>$this->id ];
            }
        }catch( PDOException $e){

            return ['code' => -2, 'data' => '', 'msg' => $e->getMessage()];
        }
    }
    /**
     * 基础注册
     */
    public function saveOn($param)
    {
        try{
            //等待确认在加验证
            $result = $this->save($param);
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


    /**
     *后端修改用户信息
     */
    public function adminedit($param)
    {
        try{
            $result = $this->save($param, ['id' => $param['id']]);

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

    /**
     *微信注册更新匹配信息
     */
    public function edit($param,$view,$type,$appid,$is_sign = 0,$is_invitation=0)
    {
        try{
//            $isregister = $this->where('name',$param['name'])
//                ->find();
//            if ($isregister){
//                return returninfo('2','用户已注册');
//            }else{
            $result = $this->save($param, ['unionid' => $param['unionid']]);
            // dump($result);exit();
           
            if(false === $result){
                // 验证失败 输出错误信息
                return returninfos('0',$this->getError());
            }else{
                //获取session放入数据库中的用户id
                $id = $this->where('unionid',$param['unionid'])->value('id');
                $sess = session($appid."user_oauth");
                $sess['id'] = $id;
                session($appid."user_oauth",$sess);
                if ($is_sign == 1){
                    return returninfos('5','注册成功','type='.$type.'&view='.$view);
                }
                if($is_sign == 3){
                    return returninfos('3','注册成功','type='.$type.'&view='.$view);
                }
                if ($is_invitation == 1){
                    return returninfos('2','注册成功','type='.$type.'&view='.$view);
                }
                return returninfos('1','注册成功','type='.$type.'&view='.$view);
            }
//            }

        }catch( PDOException $e){
            return returninfos('0',$this->getError());
        }
    }

    /**
     * 小程序注册
     */
    public function programEdit($param)
    {
        $result = $this->save($param, ['id' => $param['id']]);
        if(false === $result){
            // 验证失败 输出错误信息
            return ['code' => -1, 'data' => '', 'msg' => ''];
        }else{

            return ['code' => 1, 'data' => '', 'msg' => '' ];
        }
    }

    /**
     *微信用户个人中心修改用户
     */
    public function memverEdit($param)
    {
        try{

            $result = $this->save($param, ['unionid' => $param['unionid']]);

            if(false === $result){
                // 验证失败 输出错误信息
                return returninfos('0',$this->getError());
            }else{
                return returninfos('1','注册成功');
            }
        }catch( PDOException $e){
            return returninfos('0',$this->getError());
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
     *获取用户个人中心当前bu的活动
     */
    public function getActivity($id,$todo,$bu_id)
    {
        try{
            // tod0分别过期与未过期
            if ($todo == 1){
                $b = '>';
            }else{
                $b = '<';
            }
            $info = collection($this->alias('m')
                ->join('xk_activity_member am','am.member_id = m.id')
                ->join('xk_activity a','a.id = am.activity_id')
                ->where(['m.id'=>$id])
                ->where('a.time_end',$b,date('Y-m-d H:i:s',time()))
                ->field(['a.id activity_id','a.title','a.time','a.img','a.city','a.bu_id','a.is_open'])
                ->select())->toArray();
            //筛选出属于当前bu的活动
            $list = array();
            foreach ($info as $k=>$v){
                // if (strstr($v['bu_id'],strval($bu_id))){
                    $list[$k]['activity_id'] = $v['activity_id'];
                    $list[$k]['title'] = $v['title'];
                    $list[$k]['time'] = $v['time'];
                    $list[$k]['img'] = $v['img'];
                    $list[$k]['city'] = $v['city'];
                    $list[$k]['is_open'] = $v['is_open'];
                // }
            }
            return returninfos('1','查询成功',$list);
        }catch( PDOException $e){
            return returninfos('0',$this->getError());
        }
    }

    /**
     * 根据id获取用户详细信息
     */
    public function detailed($id)
    {
        try{
            $info = collection($this
                ->alias('M')
//                ->join('xk_bu b','b.id = M.is_bu_id')
                ->join('xk_company_type t','t.id = M.company_id','LEFT')
                ->join('xk_company_work_type w','w.id = M.company_work_type_id','LEFT')
                ->field(['M.*','t.type_name','w.work_type_name'])
                ->where('M.id',$id)
                ->select())->toArray();
            if ($info['0']['name']){
                $code = 1;
            }else{
                $code = 2;
            }
            return returninfos($code,'查询成功',$info);
        }catch( PDOException $e){
            return returninfos('0',$this->getError());
        }

    }

    /**
     * 根据unionid获取用户信息
     */
    public function unionidInfo($unionid ='',$id = '')
    {
        $info = $this->whereOr('unionid',$unionid)->whereOr('id',$id)
                    ->find();
        $live = Db('live')->where('member_id',$info['id'])->find();
        if ($live){
            $info['cid'] = $live['cid'];
            $info['status'] = $live['status'];
            $info['start_time'] = $live['start_time'];
        }
        return $info;
    }

    /**
     * 更新用户修改信息
     */
    public function interEdit($param)
    {

        try{
            $result =  $this->save($param, ['id' => $param['id']]);
            if(false === $result){
                // 验证失败 输出错误信息
                return returninfos('1','更新失败');
            }else{

                return returninfos('1','更新成功');
            }
        }catch( PDOException $e){
            return returninfos('0',$this->getError());
        }

    }

    /**
     * 根据用户unionid获取到对应的openid
     */
    public function getOpenid($param)
    {
        try{
            return $info =$this
                ->alias('M')
                ->join('xk_member_openid_unionid o','o.unionid = M.unionid')
                ->where('M.id',$param['id'])
                ->where('o.bu_id',$param['bu_id'])
                ->field(['o.openid'])
                ->find();
        }catch( PDOException $e){
            return returninfos('0',$this->getError());
        }
    }

    /**
     * 根据openid来判断用户是否已经注册
     */
    public function isOpenLogin($openid)
    {
        return $this->alias('m')
                    ->join('xk_member_openid_unionid u','u.unionid = m.unionid','LEFT')
                    ->where('u.openid',$openid)
                    ->find();

    }

    /**
     * 根据tel电话号码来判断用户是否已经注册
     */
    public function isTel($tel)
    {
        return $this
            ->where('tel',$tel)
            ->find();

    }

    /**
     * 获取属于这个分组与bu的用户
     */
    public function getGroupBu($group_id,$bu_id,$date='')
    {
        $where = ['u.bu_id'=>$bu_id, 'M.is_bu_id'=>$bu_id];
        $param = array();
        if ($group_id){
            $where = ['g.group_id'=>$group_id,'u.bu_id'=>$bu_id,'g.bu_id'=>$bu_id, 'M.is_bu_id'=>$bu_id];
        }
        if ($date != ''){
            $param['enddate'] = date("Y-m-d",strtotime($date." +1 month"));
            $param['date'] = $date;
        }

        return collection(
            $this->alias('M')
                ->join('xk_bu b','b.id = M.is_bu_id','LEFT')
                ->join('xk_company_type t','t.id = M.company_id','LEFT')
//            ->join('xk_grouping g','M.grouping_id = g.id','LEFT')
                ->join('xk_company_work_type w','w.id = M.company_work_type_id','LEFT')
                ->join('xk_member_group g','g.member_id=M.id','LEFT')
                ->join('xk_member_openid_unionid u','u.unionid = M.unionid')
                ->field(['M.name','M.tel','M.email','M.company_name','t.type_name','w.work_type_name',
                    'M.integral','M.create_time',
                    ])
                ->where('M.tel', '<>', '')
                ->where('M.is_bu_id', $bu_id)
                ->where($where)
                ->order('M.create_time desc')
                ->where(function ($query) use($param){
                    if (!empty($param)){
                        $query->where('M.create_time','>',$param['date'])->where('M.create_time','<',$param['enddate']);
                    }
                })
//                ->where('M.create_time','>',$where['date'])
//                ->where('M.create_time','<',$where['enddate'])
//            ->order('M.id desc')
                ->Distinct(true)
                     ->select()
        )->toArray();
    }

    /**
     * 更新用户是否能发言
     */
    public function isProhibit($param)
    {
        try{
            $result =  $this->save(['is_speak'=>$param['is_speak']], ['id' => $param['id']]);
            if(false === $result){
                // 验证失败 输出错误信息
                return returninfos('1','更新失败');
            }else{

                return returninfos('1','更新成功');
            }
        }catch( PDOException $e){
            return returninfos('0',$this->getError());
        }

    }
}