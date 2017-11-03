<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/10/25
 * Time: 下午3:46
 */

namespace app\manage\model;


class UserModel extends BasicModel
{
    // 当前模型名称
    protected $name = 'user';

    //设置主键名
    protected $pk  = 'id';

    public function setPasswordAttr($value){
        return mduser($value);
    }

    public function setStatusAttr($value){
        return intval($value);
    }

    public function setAdministratorAttr($value){
        return intval($value);
    }

    public function role(){
        return $this->belongsTo('RoleModel','role_id','id');
    }
}