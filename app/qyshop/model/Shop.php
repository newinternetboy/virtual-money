<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/11/20
 * Time: 上午10:58
 */

namespace app\qyshop\model;


class Shop extends Basic
{
    public function setStatusAttr($value){
        return intval($value);
    }

    public function dict(){
        return $this->belongsTo('Dict','category','id');
    }
}