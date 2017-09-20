<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/9/20
 * Time: 下午6:03
 */

namespace app\admin\model;


class Valve extends Admin
{

    public function setValveTypeAttr($value){
        return intval($value);
    }

    public function setOptionAttr($value){
        return intval($value);
    }

    public function setExectimeAttr($value){
        return strtotime($value);
    }

    public function add($data,$scene = true){
        return $this->validate($scene)->save($data);
    }
}