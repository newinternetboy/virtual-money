<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2018/1/8
 * Time: 下午3:42
 */

namespace app\common\service;

use app\common\model\CertificationModel;

class CertificationService extends CommonService
{
    public function __construct()
    {
        $this->dbModel = new CertificationModel();
    }

}