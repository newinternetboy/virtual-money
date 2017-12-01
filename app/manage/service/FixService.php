<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/12/1
 * Time: 下午1:47
 */

namespace app\manage\service;

use app\manage\model\FixModel;

class FixService extends BasicService
{
    public function __construct(){
        $this->dbModel = new FixModel();
    }
}