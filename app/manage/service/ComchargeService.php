<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/12/25
 * Time: 下午3:35
 */

namespace app\manage\service;

use app\manage\model\ComchargeModel;

class ComchargeService extends BasicService
{
    public function __construct(){
        $this->dbModel = new ComchargeModel();
    }

}