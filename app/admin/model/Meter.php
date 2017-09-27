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
        return $this->where('M_Code',$M_Code)->where('meter_status','in',[null,METER_STATUS_BIND])->where('meter_life','in',[null,METER_LIFE_INIT,METER_LIFE_START])->find();
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
    public function getallMeter($where,$data){
            return $this->where($where)->paginate()->appends($data);
    }
    public function getCount($data){
        return $this->order('create_time desc')->where($data)->count();
    }
    public function area(){
        return $this->belongsTO('area','M_Address');
    }
     public function price(){
        return $this->belongsTO('price','P_ID');
    }
}