<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/11/17
 * Time: 下午2:34
 */

namespace app\manage\service;

use app\manage\model\ShopAdminModel;


class ShopAdminService extends BasicService
{
    public function __construct(){
        $this->dbModel = new ShopAdminModel();
    }
}