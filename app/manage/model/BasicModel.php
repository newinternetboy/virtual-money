<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/10/24
 * Time: 上午11:57
 */

namespace app\manage\model;


use think\Model;

class BasicModel extends Model
{

    public function findInfo($where = [],  $field = ''){
        if( $field ){
            return $this->where($where)->field($field)->find();
        }
        return $this->where($where)->find();
    }

    public function selectInfo($where = [],  $field = ''){
        if( $field ){
            return $this->where($where)->field($field)->select();
        }
        return $this->where($where)->select();
    }

    public function getInfoPaginate($where = [], $param = [], $field = ''){
        if( $field ){
            return $this->where($where)->field($field)->paginate()->appends($param);
        }
        return $this->where($where)->paginate()->appends($param);
    }

    public function upsert($data,$scene = true){
        if( isset($data['id']) ){
            $result =  $this->validate($scene)->isUpdate(true)->save($data);
            if($result === false){
                return false;
            }
            return true;
        }else{
            $result = $this->validate($scene)->isUpdate(false)->save($data);
            if($result === false){
                return false;
            }
            return $this->getLastInsID();
        }
    }
}