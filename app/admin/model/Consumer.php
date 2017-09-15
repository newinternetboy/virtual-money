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

    public function setConsumerOld($id){
        $data = [
            'id' => $id,
            'consumer_state' => CONSUMER_STATE_OLD
        ];
        return $this->update($data);
    }

    public function updateConsumer($data,$scene){
        return $this->validate($scene)->isUpdate(true)->save($data);
    }
}