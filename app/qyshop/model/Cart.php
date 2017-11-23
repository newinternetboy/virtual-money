<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/11/22
 * Time: 下午12:37
 */

namespace app\qyshop\model;


class cart extends Basic
{
    public function consumer(){
        return $this->belongsTo('Consumer','uid');
    }
}