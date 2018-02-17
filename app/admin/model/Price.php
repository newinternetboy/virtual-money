<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/9/11
 * Time: 下午5:30
 */

namespace app\admin\model;
use traits\model\SoftDelete;


class Price extends Admin
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';

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
    public function setLowCostAttr($value){
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

    //存储前转为整数
    public function setFourthValAttr($value){
        return intval($value);
    }

    //存储前转为浮点数
    public function setFifthPriceAttr($value){
        return floatval($value);
    }

    //存储前转为整数
    public function setFifthValAttr($value){
        return intval($value);
    }

    //存储前转为浮点数
    public function setSixthPriceAttr($value){
        return floatval($value);
    }

    //存储前转为整数
    public function setSixthValAttr($value){
        return intval($value);
    }

    //存储前转为浮点数
    public function setEndTimeAttr($value){
        return strtotime($value);
    }

    public function getList( $request )
    {
        $request = $this->fmtRequest( $request );
        if( $request['offset'] == 0 && $request['limit'] == 0 ){
            return $this->order('create_time desc')->where( $request['map'] )->where(['delete_time'=> null])->select();
        }
        return $this->order('create_time desc')->where( $request['map'] )->where(['delete_time'=> null])->limit($request['offset'], $request['limit'])->select();
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

    /**
     * 获取用户所属公司的价格列表,带分页
     * @param array $where
     * @return \think\Paginator
     */
    public function getMyPricesUsePaginate($where = [] )
    {
        $userRow = session('userinfo','','admin');
        $where['company_id'] = $userRow['company_id'];
        return $this->getAllPricesUsePaginate($where);
    }

    /**
     * 获取所有价格列表,带分页
     * @param array $where
     * @return \think\Paginator
     */
    public function getAllPricesUsePaginate($where = []){
        return $this->where($where)->order('create_time desc')->paginate();
    }

    public function findInfo($where=[],$field=''){
        return $this->field($field)->where($where)->find();
    }

    public function selectInfo($where=[],$field=''){
        if($field==''){
            return $this->where($where)->select();
        }
        return $this->field($field)->where($where)->select();
    }
}