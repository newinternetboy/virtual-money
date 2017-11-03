<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/10/31
 * Time: 下午6:49
 */

namespace app\manage\service;

use app\manage\model\MoneyLogModel;

class MoneyLogService extends BasicService
{
    public function __construct(){
        $this->dbModel = new MoneyLogModel();
    }
}