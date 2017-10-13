<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/9/27
 * Time: 下午3:54
 */

namespace app\admin\model;


class MeterData extends Admin
{

    public function upsert($data,$scene = true){
        if( isset($data['id']) ){
            return $this->validate($scene)->isUpdate(true)->save($data);
        }
        return $this->validate($scene)->save($data);
    }

    public function getMeterDataInfo($where, $method = 'select', $field = '', $orderfield = 'create_time',$orderRule = 'desc'){
        if( !$field ){
            return $this->where($where)->order($orderfield,$orderRule)->field($field)->$method();
        }
        return $this->where($where)->order($orderfield,$orderRule)->$method();
    }
}