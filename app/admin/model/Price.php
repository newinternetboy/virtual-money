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
            return $this->validate(true)->isUpdate(true)->save( $data );
        } else {
            $data['create_time'] = time();
            return $this->validate(true)->save($data);
        }
    }

    public function deleteById($id)
    {
        return  Price::destroy($id);
    }

    public function getTotalPriceNumber($where){
        return $this->where($where)->count();
    }

    public function getPricesById($id, $company_id)
    {
        $ids = explode(',', $id);
        return $this->where('id', 'in', $ids)->where('company_id', $company_id)->select();
    }
    public function getLists( $data )
    {
        return $this->order('create_time desc')->where($data)->paginate();
    }
}