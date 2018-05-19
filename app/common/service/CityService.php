<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/26 0026
 * Time: 22:33
 */

namespace app\common\service;

use app\common\model\CityModel;
class CityService extends CommonService
{
    public function __construct()
    {
        $this->dbModel = new CityModel();
    }

    public function selectCityInfo($where = [],  $field = ''){
        return $this->dbModel->selectCityInfo($where, $field);
    }
}