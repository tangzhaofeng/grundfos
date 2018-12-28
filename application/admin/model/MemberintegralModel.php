<?php

namespace app\admin\model;

use think\Model;

class MemberintegralModel extends Model
{

    protected $table = "xk_member_integral";

    /**条件查询列表
     * @param array $where
     * @param array $pageData
     * @param string $order
     * @param string $field
     * @return mixed
     */
    public function listByWhere($where = [], $pageData = [], $order = 'create_time Desc', $field = '*')
    {

        $res['count'] = $this->conditionCascade($where, $order, $field)->count();
        if ($pageData)
            $res['list'] = $this->conditionCascade($where, $order, $field)
                ->page($pageData['pageNow'], $pageData['pageSize'])
                ->select();
        else
            $res['list'] = $this->conditionCascade($where, $order, $field)
                ->select();
        return $res;
    }

    /**
     * 构造级联条件
     * @param $where
     * @param $order
     * @param $field
     * @return $this
     */
    public function conditionCascade($where, $order, $field)
    {
        return $this->alias('a')
            ->where($where)
            ->order($order)
            ->field($field);
    }

}