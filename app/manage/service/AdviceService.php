<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/12/4
 * Time: 上午10:46
 */

namespace app\manage\service;

use app\manage\model\AdviceModel;

class AdviceService extends BasicService
{
    public function __construct(){
        $this->dbModel = new AdviceModel();
    }
}