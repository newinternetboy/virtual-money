<?php
/**
 * Created by PhpStorm.
 * User: ducongshu
 * Date: 2017/11/09
 * Time: 下午09:35
 */

namespace app\manage\service;

use app\manage\model\DictModel;

class DictService extends BasicService
{

    public function __construct(){
        $this->dbModel = new DictModel();
    }

}