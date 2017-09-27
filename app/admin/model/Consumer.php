<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/9/13
 * Time: 下午2:20
 */

namespace app\admin\model;


/**
 * 表具客户
 * Class Consumer
 * @package app\admin\model
 */
class Consumer extends Admin
{

    public function setFamilyNumAttr($value){
        return floatval($value);
    }

    public function setBuildingAreaAttr($value){
        return floatval($value);
    }

    public function setIncomePeryearAttr($value){
        return floatval($value);
    }

    public function getConsumerById($id,$field = ''){
        if( !empty($field) ){
            return $this->where('id',$id)->field($field)->find();
        }
        return $this->where('id',$id)->find();
    }

    public function insertConsumer($data){
        if( $this->validate(true)->save($data) ){
            return $this->id;
        }
    }

    /**
     * 插入/更新 用户信息
     * @param $data
     * @param $scene
     * @return false|int
     */
    public function upsertConsumer($data, $scene){
        if( isset($data['id']) ){
            return $this->validate($scene)->isUpdate(true)->save($data);
        }
        if( $this->validate($scene)->save($data) ){
            return $this->data['id'];
        }
        return false;

    }
}