<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/11/20
 * Time: 上午10:58
 */

namespace app\qyshop\model;


use think\Model;

class Basic extends Model
{
    public function findInfo($where = [],$field = ''){
        if($field){
            return $this->where($where)->field($field)->find();
        }
        return $this->where($where)->find();
    }

    public function selectInfo($where = [],$field = ''){
        if($field){
            return $this->where($where)->field($field)->order('create_time','desc')->select();
        }
        return $this->where($where)->order('create_time','desc')->select();
    }

    public function paginateInfo($where = [],$param = [],$field = '')
    {
        if( $field ){
            return $this->where($where)->field($field)->order('create_time','desc')->paginate()->appends($param);
        }
        return $this->where($where)->order('create_time','desc')->paginate()->appends($param);
    }

    public function upsert($data,$scene = true){
        if( isset($data['id']) && !empty($data['id']) ){
            $result =  $this->validate($scene)->isUpdate(true)->save($data);
            if($result === false){
                return false;
            }
            return true;
        }else{
            unset($data['id']);
            $result = $this->validate($scene)->isUpdate(false)->save($data);
            if($result === false){
                return false;
            }
            return $this->getLastInsID();
        }
    }

    public function sums($where,$field){
        return $this->where($where)->sum($field);
    }
}