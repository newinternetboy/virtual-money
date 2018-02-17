<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2018/2/2
 * Time: 下午2:07
 */

namespace app\admin\model;


class Alarm extends Admin
{
    public function setReasonAttr($value){
        return intval($value);
    }
}