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

    public function columnInfo($where,$field){
        return $this->dbModel->columnInfo($where,$field);
    }

    public function insertQYShop($data,$scene = true){
        $data['type'] = COMPANY_ELE_BUSINESS;
        $data['productsCount'] = 0;
        return $this->upsert($data,$scene);
    }
}