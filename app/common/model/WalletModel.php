<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2018/1/8
 * Time: 下午2:24
 */

namespace app\common\model;


class WalletModel extends Common
{
    public $table = 'wallet';

    public function doSetInc($where,$data){
        return $this->where($where)->setInc($data[0],$data[1]);
    }

}