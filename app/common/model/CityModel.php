<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2018/1/8
 * Time: 下午2:24
 */

namespace app\common\model;


class CityModel extends Common
{
    public $table = 'cities';

    public function selectCityInfo($where = [], $field = ''){
        if( $field ){
            return $this->where($where)->field($field)->select();
        }
        return $this->where($where)->select();
    }
}