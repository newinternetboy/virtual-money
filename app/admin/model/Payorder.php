<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/27 0027
 * Time: 22:19
 */

namespace app\admin\model;

use think\Model;
class Payorder extends Model
{
    protected $table ='payorder';
/*    public function getUserInfo(){
        return $this->hasOne('User','id','u_id');
    }*/
}