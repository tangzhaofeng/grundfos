<?php

namespace app\api\controller;

use think\Db;

class Buvideo extends Base
{
    public function selectByType() {
        $param = input("param.");
        $data = Db('bu_video')->where('bu_id', $param['bu_id'])->where('video_type', $param['type'])->order('id desc')->select();
        return json(['code' => 0, 'data' => $data]);
    }

    public function selectBySearchWord() {
        $param = input("param.");
        $data = Db('bu_video')->where('bu_id', $param['bu_id'])->where('tag', 'like', ';'.$param['searchWord'].';')->whereOr('tag', 'like', $param['searchWord'].';')->whereOr('tag', 'like', $param['searchWord'])->whereOr('tag', 'like', ';'.$param['searchWord'])->order('id desc')->select();
        return json(['code' => 0, 'data' => $data]);
    }

    public function addClick() {
        $param = input("param.");
        Db('bu_video')->where('id', $param['id'])->setInc('click_num');
    }
}