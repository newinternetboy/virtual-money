<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/10/24
 * Time: 下午12:02
 */

namespace app\manage\service;

use app\manage\model\CompanyModel;

class CompanyService extends BasicService
{

    public function __construct(){
        $this->dbModel = new CompanyModel();
    }
}