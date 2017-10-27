<?php
/**
 * Created by PhpStorm.
 * User: ducongshu
 * Date: 2017/10/26
 * Time: 下午15:35
 */

namespace app\manage\service;

use app\manage\model\ConsumerModel;

class ConsumerService extends BasicService
{

    public function __construct(){
        $this->dbModel = new ConsumerModel();
    }
}