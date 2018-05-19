<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/26 0026
 * Time: 22:33
 */

namespace app\common\service;

use app\common\model\ProvinceModel;
class ProvinceService extends CommonService
{
    public function __construct()
    {
        $this->dbModel = new ProvinceModel();
    }

    public function selectProvinceInfo($where = [],  $field = ''){
        return $this->dbModel->selectProvinceInfo($where, $field);
    }
}