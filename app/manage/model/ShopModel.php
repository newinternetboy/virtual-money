<?php
/**
 * Created by PhpStorm.
 * User: ducongshu
 * Date: 2017/11/02
 * Time: 下午14:15
 */

namespace app\manage\model;


class ShopModel extends BasicModel
{
    // 当前模型名称
    protected $name = 'Shop';

    public function consumer(){
        return $this->belongsTo('ConsumerModel','uid');
    }

    public function dict(){
        return $this->belongsTo('DictModel','category');
    }

    public function columnInfo($where,$field){
        return $this->where($where)->column($field);
    }

    public function setStatusAttr($value){
        return intval($value);
    }

    public function setTypeAttr($value){
        return intval($value);
    }

    public function setSdlPreferenceAttr($value){
        return intval($value);
    }

    public function setHealthAuthAttr($value){
        return intval($value);
    }

    public function setSdlAuthAttr($value){
        return intval($value);
    }

}