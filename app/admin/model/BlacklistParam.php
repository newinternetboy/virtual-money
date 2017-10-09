<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/9/11
 * Time: 下午5:30
 */

namespace app\admin\model;


class BlacklistParam extends Admin
{
    public function setParamTypeAttr($value){
        return intval($value);
    }

    public function setOptIdAttr($value){
        return intval($value);
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
            return $this->validate(true)->isUpdate(true)->save($data);
        } else {
            $data['create_time'] = time();
            return $this->validate(true)->save($data);
        }
    }

    public function getTotalBlacklistParamNumber($where){
        return $this->where($where)->count();
    }

    public function getBlacklistParamById($id){
        return $this->where('id',$id)->find();
    }

    public function deleteById($id)
    {
        return BlacklistParam::destroy($id);
    }

    public function getBlacklistParamsById($id,$company_id){
        $ids = explode(',',$id);
        return $this->where('id','in',$ids)->where('company_id',$company_id)->select();
    }
}