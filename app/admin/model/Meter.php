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
        return $this->where('M_Code',$M_Code)->where('meter_status','in',[null,METER_STATUS_BIND])->find();
    }

    public function updateMeter($data,$scene){
        return $this->validate($scene)->isUpdate(true)->save($data);
    }

    public function getMeterInfo($where,$method,$field = ''){
        if( !$field ){
            return $this->where($where)->$method();
        }
        return $this->where($field)->$method();
    }
}