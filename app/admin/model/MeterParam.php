<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/9/19
 * Time: 下午1:56
 */

namespace app\admin\model;


class MeterParam extends Admin
{
    //存储前转为整数
    public function setpulseRatioAttr($value){
        return intval($value);
    }

    //存储前转为整数
    public function setlowLimitAttr($value){
        return intval($value);
    }

    //存储前转为整数
    public function setoverdraftLimitAttr($value){
        return intval($value);
    }

    //存储前转为整数
    public function settransformerRatioAttr($value){
        return intval($value);
    }

    public function setUploadTimeAttr($value){
        return intval($value);
    }

    public function setOverdraftTimeAttr($value){
        return intval($value);
    }

    public function setFreezeTimeAttr($value){
        return intval($value);
    }

    public function setOverFlimitAttr($value){
        return intval($value);
    }

    public function getList( $request )
    {
        $request = $this->fmtRequest( $request );
        if( $request['offset'] == 0 && $request['limit'] == 0 ){
            return $this->order('create_time desc')->where( $request['map'] )->select();
        }
        return $this->order('create_time desc')->where( $request['map'] )->limit($request['offset'], $request['limit'])->select();
    }

    public function saveData( $data )
    {
        if( isset( $data['id']) && !empty($data['id'])) {
            $data['update_time'] = time();
            return $this->validate(true)->isUpdate(true)->save( $data );
        } else {
            $data['create_time'] = time();
            return $this->validate(true)->save($data);
        }
    }

    public function deleteById($id)
    {
        $result = MeterParam::destroy($id);
        if ($result > 0) {
            return info(lang('Delete succeed'), 1);
        }
    }

    public function getTotalMeterParamNumber($where){
        return $this->where($where)->count();
    }

    public function getMeterParamsById($id, $company_id)
    {
        $ids = explode(',', $id);
        return $this->where('id', 'in', $ids)->where('company_id', $company_id)->select();
    }
    //根据company_id查询出所有的运行参数数据
    public function getAllMeterParams($where){
        return $this->where($where)->paginate();
    }

    public function findInfo($where=[],$field=''){
        if($field==""){
            return $this->where($where)->find();
        }
        return $this->field($field)->where($where)->find();
    }

    public function selectInfo($where=[],$field=''){
        if($field==''){
            return $this->where($where)->select();
        }
        return $this->field($field)->where($where)->select();
    }
}