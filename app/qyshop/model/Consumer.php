<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/11/22
 * Time: 下午12:49
 */

namespace app\qyshop\model;


class Consumer extends Basic
{
    public function findInfo($where){
        return $this->where($where)->find();
    }
}