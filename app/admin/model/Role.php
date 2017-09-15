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

    //根据uid返回角色 rule_val
    public function getRoleInfo( $uid )
    {

    }

    public function getKvData($company_id)
    {
        return $this->where('status',1)->where('company_id',$company_id)->field('name,id')->select();
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
        $result = $this->update( $data );
        if( false === $result) {
            $info = info(lang('Edit failed'), 0);
        } else {
            $info = info(lang('Edit succeed'), 1);
        }
        return $info;
    }

    public function add( $data )
    {
        $this->save($data);
        if( !$this->id) {
            $info = info(lang('Add failed'), 0);
        } else {
            $info = info(lang('Add succeed'), 1, '', $this->id);
        }

        return $info;
    }

    public function deleteById($id)
    {
        $result = Role::destroy($id);
        if ($result > 0) {
            return info(lang('Delete succeed'), 1);
        }
    }

    public function getTotalRoleNumber($where){
        return $this->where($where)->count();
    }
}
