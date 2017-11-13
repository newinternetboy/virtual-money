<?php
/**
 * Created by PhpStorm.
 * User: ducongshu
 * Date: 2017/10/26
 * Time: 下午15:35
 */

namespace app\manage\service;

use app\manage\model\Money_logModel;

class Money_logService extends BasicService
{

    public function __construct(){
        $this->dbModel = new Money_logModel();
    }
}