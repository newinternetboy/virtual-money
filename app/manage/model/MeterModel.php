<?php
/**
 * Created by PhpStorm.
 * User: ducongshu
 * Date: 2017/10/26
 * Time: 下午14:15
 */

namespace app\manage\model;


class MeterModel extends BasicModel
{
    // 当前模型名称
    protected $name = 'Meter';
    //关联consumer表；
    public function consumer(){
        return $this->belongsTo('consumerModel','U_ID');
    }

    //关联area表；
    public function area(){
        return $this->belongsTo('AreaModel','M_Address');
    }

}