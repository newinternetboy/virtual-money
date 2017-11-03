<?php
/**
 * Created by PhpStorm.
 * User: ducongshu
 * Date: 2017/11/02
 * Time: 下午14:15
 */

namespace app\manage\model;


class ProductionModel extends BasicModel
{
    // 当前模型名称
    protected $name = 'Production';

    public function shop(){
        return $this->belongsTo('ShopModel','sid');
    }

}