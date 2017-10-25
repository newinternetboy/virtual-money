<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/10/25
 * Time: 下午3:46
 */

namespace app\manage\service;

use app\manage\model\UserModel;

class UserService extends BasicService
{
    public function __construct(){
        $this->dbModel = new UserModel();
    }
}