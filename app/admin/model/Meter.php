<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/9/12
 * Time: 下午5:22
 */

namespace app\admin\model;


class Meter extends Admin
{

    public function setMTypeAttr($value){
        return intval($value);
    }

    public function getMeterByCode($M_Code){
        return $this->where('M_Code',$M_Code)->where('meter_status','neq',METER_STATUS_CHANGED)->find();
    }

    public function updateMeter($data,$scene){
        return $this->validate($scene)->isUpdate(true)->save($data);
    }
}