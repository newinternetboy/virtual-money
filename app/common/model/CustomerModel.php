<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2018/1/8
 * Time: 下午2:24
 */

namespace app\common\model;


class CustomerModel extends Common
{
    public $table = 'customer';

    public function wallet(){
        return $this->hasOne('WalletModel','u_id');
    }


}