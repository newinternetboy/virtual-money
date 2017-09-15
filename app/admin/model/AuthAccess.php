<?php
namespace app\admin\model;

use \think\Config;
use think\Db;
use \think\Model;
use \think\Session;


/**
 * 角色权限
 *
 * @author chengbin
 */
class AuthAccess extends Admin
{
    public function getRuleVals( $uid )
    {
        $role_id = model('User')->where(['id'=>$uid])->value('role_id');
        $rule_ids = array_values(model('AuthAccess')->where(['role_id'=>$role_id])->column('rule_id'))[0];
        $ret =  model('AuthRule')->where('id', 'in', $rule_ids)->field('id,title,rule_val,pid,display,glyphicon')->order('sortnum','asc')->select();
        foreach( $ret as & $item ){
            $item = $item->toArray();
        }
        return $ret;
    }

    public function getIds( $role_id )
    {
        $ret = model('AuthAccess')->where(['role_id'=>$role_id])->column('rule_id');
        if($ret){
            $ret = array_values($ret)[0];
        }
        return $ret;
    }

    public function saveData( $role_id, $data )
    {
        if(empty($data)) {
            return info(lang('Save success'), 1);
        }
        try{
            $rule_id = array_values($data);
            if( $this->where(['role_id' => $role_id])->find() ){
                $this->where(['role_id' => $role_id])->update([ 'rule_id' => $rule_id ]);
            }else{
                $this->save(['role_id' => $role_id,'rule_id' => $rule_id]);
            }
        }catch (\Exception $e) {
            info(lang('Save fail'), 0);
        }
        return info(lang('Save success'), 1);
    }
}
