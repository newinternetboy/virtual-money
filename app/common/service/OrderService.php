<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/26 0026
 * Time: 22:33
 */

namespace app\common\service;

use app\common\model\OrderModel;
class OrderService extends CommonService
{
    public function __construct()
    {
        $this->dbModel = new OrderModel();
    }
}