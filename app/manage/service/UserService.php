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

    /**
     * 禁用用户
     * @param $where
     * @param $data
     * @return $this
     */
    public function disableUser($where){
        $data['status'] = 0;
        return $this->dbModel->where($where)->update($data);
    }
}