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

    public function setSdlpreferenceAttr($value){
        return intval($value);
    }

    public function setStatusAttr($value){
        return intval($value);
    }

    public function setHealthauthAttr($value){
        return intval($value);
    }

    public function setSdlauthAttr($value){
        return intval($value);
    }

    public function columnInfo($where,$field){
        return $this->where($where)->column($field);
    }

}