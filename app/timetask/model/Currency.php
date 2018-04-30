<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/30 0030
 * Time: 14:35
 */

namespace app\timetask\model;

use think\Model;
class Currency extends Model
{
    protected $table = 'currency';

    //获取所有需要发的币
    public function getAllWaitSendCoin(){
        return $this->where('send',1);
    }
}