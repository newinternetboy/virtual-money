<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/9/11
 * Time: 下午5:30
 */

namespace app\admin\model;


class Price extends Admin
{
    //存储前转为整数
    public function setTypeAttr($value){
        return intval($value);
    }

    //存储前转为整数
    public function setPeriodAttr($value){
        return intval($value);
    }

    //存储前转为浮点数
    public function setFirstPriceAttr($value){
        return floatval($value);
    }

    //存储前转为浮点数
    public function setBasicPriceAttr($value){
        return floatval($value);
    }

    //存储前转为整数
    public function setFirstValAttr($value){
        return intval($value);
    }

    //存储前转为浮点数
    public function setSecondPriceAttr($value){
        return floatval($value);
    }

    //存储前转为整数
    public function setSecondValAttr($value){
        return intval($value);
    }

    //存储前转为浮点数
    public function setThirdPriceAttr($value){
        return floatval($value);
    }

    //存储前转为整数
    public function setThirdValAttr($value){
        return intval($value);
    }

    //存储前转为浮点数
    public function setFourthPriceAttr($value){
        return floatval($value);
    }

    //存储前转为浮点数
    public function setEndTimeAttr($value){
        return strtotime($value);
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
            $info = $this->edit( $data );
        } else {
            $data['create_time'] = time();
            $info = $this->add( $data );
        }

        return $info;
    }

    public function edit( $data )
    {
        $result = $this->validate(true)->isUpdate(true)->save( $data );
        if( false === $result) {
            $info = info($this->getError(), 0);
        } else {
            $info = info(lang('Edit succeed'), 1);
        }
        return $info;
    }

    public function add( $data )
    {
        $result = $this->validate(true)->save($data);
        if( $result === false ) {
            $info = info($this->getError(), 0);
        } else {
            $info = info(lang('Add succeed'), 1, '', $this->id);
        }

        return $info;
    }

    public function deleteById($id)
    {
        $result = Price::destroy($id);
        if ($result > 0) {
            return info(lang('Delete succeed'), 1);
        }
    }

    public function getTotalPriceNumber($where){
        return $this->where($where)->count();
    }
}