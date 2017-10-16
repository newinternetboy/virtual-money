<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/9/27
 * Time: 下午3:54
 */

namespace app\admin\model;

use think\Db;

class MeterData extends Admin
{
    // 数据表名称
    protected $table;

    //根据表号获取分表名
    //暂时未分表,只有一个表meterData
    private function setTableName($M_Code){
        $this->table = 'MeterData';
    }

    public function upsert($M_Code,$data,$scene = true){
        $this->setTableName($M_Code);
        $validate = validate('MeterData');
        if( !$validate->scene($scene)->check($data) ){
            exception($validate->getError(),ERROR_CODE_DATA_ILLEGAL);
        }
        if( isset($data['id']) ){
            $data['update_time'] = time();
            return Db::name($this->table)->update($data);
        }
        $data['create_time'] = time();
        return Db::name($this->table)->insert($data);
    }

    public function getMeterDataInfo($M_Code, $where, $method = 'select', $field = '', $orderfield = 'create_time',$orderRule = 'desc'){
        $this->setTableName($M_Code);
        if( !$field ){
            return Db::name($this->table)->where($where)->order($orderfield,$orderRule)->field($field)->$method();
        }
        return Db::name($this->table)->where($where)->order($orderfield,$orderRule)->$method();
    }
}