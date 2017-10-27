<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/10/20
 * Time: 下午4:18
 */

namespace app\common\model;


use think\Model;

class MoneyLog extends Model
{
    protected $pk = 'id';

    public function setMoneyTypeAttr($value){
        return intval($value);
    }

    public function setTypeAttr($value){
        return intval($value);
    }

    public function setMoneyAttr($value){
        return floatval($value);
    }

    public function add($data){
        if( $this->isUpdate(false)->save($data) ){
            return $this->id;
        }
        return false;
    }

    public function getMoneyLog($where,$method,$field = ''){
        if($field){
            return $this->where($where)->field($field)->$method();
        }
        return $this->where($where)->$method();
    }
}