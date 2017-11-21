<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/11/21
 * Time: 下午1:36
 */

namespace app\qyshop\model;


class User extends Basic
{
    public function setPasswordAttr($value){
        return mduser($value);
    }
}