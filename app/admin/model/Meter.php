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

    /**
     * 获取所有表具信息
     * @param $where
     * @param $method
     * @param string $field
     * @return mixed
     */
    public function getAllMeterInfo($where, $method, $field = ''){
        if( !$field ){
            return $this->where($where)->$method();
        }
        return $this->where($where)->field($field)->$method();
    }

    /**
     * 获取用户所在公司表具信息,带分页
     * @param $where
     * @param $param
     * @return mixed
     */
    public function getMyMetersUsePaginate($where, $param){
        $where['meter_status'] = METER_STATUS_BIND;
        $where['meter_life'] = METER_LIFE_ACTIVE;
        $userRow = session('userinfo','', 'admin');
        $where['company_id'] = $userRow['company_id'];
        return $this->getAllMetersUsePaginate($where,$param);
    }

    /**
     * 获取所有表具信息,带分页
     * @param $where
     * @param $param  分页标签附带参数
     * @return $this
     */
    public function getAllMetersUsePaginate($where, $param){
        return $this->where($where)->paginate()->appends($param);
    }
}