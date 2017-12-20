<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/9/11
 * Time: 下午5:30
 */

namespace app\admin\model;

use traits\model\SoftDelete;

class Area extends Admin
{

    use SoftDelete;
    protected $deleteTime = 'delete_time';

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
        return Area::destroy($id);
    }

    public function getTotalAreaNumber($where){
        return $this->where($where)->count();
    }

    public function getAreaById($id){
        return $this->where('id',$id)->find();
    }

    public function getAreaInfo($where,$method,$field = ''){
        if( !$field ){
            return $this->where($where)->$method();
        }
        return $this->field($field)->where($where)->$method();
    }

    public function getAreasById($id, $company_id)
    {
        $ids = explode(',', $id);
        return $this->where('id', 'in', $ids)->where('company_id', $company_id)->select();
    }

    public function selectInfo($where=[],$field=''){
        if($field==''){
            return $this->where($where)->select();
        }
        return $this->field($field)->where($where)->select();
    }

}