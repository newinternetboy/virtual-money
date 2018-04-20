<?php
namespace app\admin\model;

use think\Config;
use think\Db;
use think\Loader;
use think\Model;

class Role extends Admin
{
   

    //启用的状态,存储前转为整数
    public function setStatusAttr($value){
        return intval($value);
    }

    public function getKvData()
    {
        return $this->where('status',1)->field('name,id')->select();
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
            return $this->isUpdate(true)->save( $data );
        } else {
            $data['create_time'] = time();
            return $this->save( $data );
        }
    }

    public function deleteById($id)
    {
        return Role::destroy($id);
    }

    public function getTotalRoleNumber(){
        return $this->count();
    }

    public function getRolesById($id){
        $ids = explode(',',$id);
        return $this->where('id','in',$ids)->select();
    }
}
