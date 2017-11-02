<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/11/1
 * Time: 下午5:36
 */

namespace app\manage\service;

use app\manage\model\RoleModel;

class RoleService extends BasicService
{
    public function __construct(){
        $this->dbModel = new RoleModel();
    }

}