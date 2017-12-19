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

    /**关联模型
     * @return \think\model\relation\BelongsTo
     */
    public function user(){
        return $this->belongsTo('user','operator');
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
    public function updateMeter($data, $scene, $where = []){
        return $this->validate($scene)->isUpdate(true)->save($data,$where);
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
     * @param $param  分页标签附带参数
     * @return mixed
     */
    public function getMyMetersUsePaginate($where, $param){
        return $this->where($where)->paginate()->appends($param);
    }

    public function InitMeter($data, $scene){
        if( $this->validate($scene)->isUpdate(false)->save($data) ){
            return $this->data['id'];
        }
        return false;
    }

    /**
     * 更新金额
     * @param $meter_id
     * @param $method inc/dec
     * @param $money
     * @return false|int
     */
    public function updateMoney($meter_id, $method, $field, $money){
        return $this->where('id', $meter_id)->$method($field, $money)->update([]);
    }

    public function columnInfo($where=[],$field){
        return $this->where($where)->column($field);
    }

    public function findInfo($where=[],$field=''){
        return $this->field($field)->where($where)->find();
    }

    public function selectInfo($where=[],$field=''){
        if($field==''){
            return $this->where($where)->select();
        }
        return $this->field($field)->where($where)->select();
    }
}