<?php
/**
 * Created by PhpStorm.
 * User: ducongshu
 * Date: 2017/11/02
 * Time: 下午15:35
 */

namespace app\manage\service;

use app\manage\model\ShopModel;

class ShopService extends BasicService
{

    public function __construct(){
        $this->dbModel = new ShopModel();
    }
}