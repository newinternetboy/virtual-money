<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/11/17
 * Time: 下午2:35
 */

namespace app\manage\model;


class ShopAdminModel extends BasicModel
{
    public $name = 'shop_admin';

    public function setStatusAttr($value){
        return intval($value);
    }
}