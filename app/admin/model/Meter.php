<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/9/12
 * Time: 下午5:22
 */

namespace app\admin\model;


/**
 * Class Meter
 * @package app\admin\model
 */
class Meter extends Admin
{

    /**关联模型
     * @return \think\model\relation\BelongsTo
     */
    public function area(){
        return $this->belongsTo('area','M_Address');
    }

    /**关联模型
     * @return \think\model\relation\BelongsTo
     */
    public function price(){
        return $this->belongsTo('price','P_ID');
    }

    /**
     * @param $value
     * @return int
     */
    public function setMTypeAttr($value){
        return intval($value);
    }

//    public function getMeterByCode($M_Code){
//        return $this->where('M_Code',$M_Code)->where('meter_status','in',[null,METER_STATUS_BIND])->where('meter_life','in',[null,METER_LIFE_INIT,METER_LIFE_START])->find();
//    }

    /**
     * 更新表具状态
     * @param $data
     * @param $scene
     * @return false|int
     */
    public function updateMeter($data, $scene){
        return $this->validate($scene)->isUpdate(true)->save($data); 
    }

    /**
     * 获取表具信息
     * @param $where
     * @param $method
     * @param string $field
     * @return mixed
     */
    public function getMeterInfo($where, $method, $field = ''){
        $where['meter_life'] = METER_LIFE_ACTIVE;
        return $this->getAllMeterInfo($where, $method, $field);
    }

    public function getAllMeterInfo($where, $method, $field = ''){
        if( !$field ){
            return $this->where($where)->$method();
        }
        return $this->where($where)->field($field)->$method();
    }

    /**
     * @param $where
     * @return \think\Paginator
     */
    public function getMeterByCodeandarea($where){
        return $this->where($where)->paginate(10);
    }

    /**
     * @return \think\Paginator
     */
    public function getallMeter(){
        return $this->paginate(10);
    }
}