<?php
/**
 * Created by PhpStorm.
 * User: ducongshu
 * Date: 2017/11/02
 * Time: 下午14:15
 */

namespace app\manage\model;


class CartModel extends BasicModel
{
    // 当前模型名称
    protected $name = 'Cart';

    public function setFreezeAttr($value){
        return intval($value);
    }

    public function shop(){
        return $this->belongsTo('ShopModel','sid');
    }

    public function consumer(){
        return $this->belongsTo('ConsumerModel','uid');
    }

    public function updateCart($where,$change){
        return $this->where($where)->update($change);
    }

}