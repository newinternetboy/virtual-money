<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/9/12
 * Time: 下午5:33
 */

namespace app\admin\model;


class Company extends Admin
{
    public function getCompany($where, $field){
        if( empty($field) ){
            return $this->where($where)->find();
        }
        return $this->where($where)->field($field)->find();
    }
}