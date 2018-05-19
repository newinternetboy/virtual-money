<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2018/1/8
 * Time: 下午2:24
 */

namespace app\common\model;


class ProvinceModel extends Common
{
    public $table = 'provinces';

    public function selectProvinceInfo($where = [], $field = ''){
        if( $field ){
            return $this->where($where)->field($field)->select();
        }
        return $this->where($where)->select();
    }
}