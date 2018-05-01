<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/1 0001
 * Time: 21:00
 */

namespace app\timetask\model;

use think\Model;
use app\front\model\Customer;
class Wallet extends Model
{
    protected  $table = 'wallet';

    public function customer(){
        return $this->hasOne('app\front\model\Customer','id','u_id');
    }
}