<?php
/**
 * Created by PhpStorm.
 * User: ducongshu
 * Date: 2017/10/26
 * Time: 下午17:54
 */

namespace app\manage\service;

use app\manage\model\AreaModel;

class AreaService extends BasicService
{
    public function __construct(){
        $this->dbModel = new AreaModel();
    }

    public function columnInfo($where,$field){
        return $this->dbModel->columnInfo($where,$field);
    }
}