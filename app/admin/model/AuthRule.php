<?php
namespace app\admin\model;

use \think\Config;
use \think\Model;
use \think\Session;


/**
 * 权限规则
 *
 * @author chengbin
 */
class AuthRule extends Admin
{

    protected $insert = ['type'];

    //是否菜单显示的状态,存储前转为整数
    public function setDisplayAttr($value){
        return intval($value);
    }

    //排序的状态,存储前转为整数
    public function setSortnumAttr($value){
        return intval($value);
    }

    public function setTypeAttr(){
        return PLATFORM_ADMIN;
    }

    public function getList($request = [])
    {
        $request = $this->fmtRequest( $request );
        if( empty($request) ){
            return $this->where('type',PLATFORM_ADMIN)->order('sortnum','asc')->select();
        }
        if( $request['offset'] == 0 && $request['limit'] == 0 ){
            return $this->where('type',PLATFORM_ADMIN)->where($request['map'])->order('sortnum','asc')->select();
        }
        return $this->where('type',PLATFORM_ADMIN)->where($request['map'])->limit($request['offset'], $request['limit'])->order('sortnum','asc')->select();
    }

    public function saveData($data)
    {
        if(isset($data['rule_val'])) {
            $data['rule_val'] = strtolower($data['rule_val']);
        }
        if(isset($data['id']) && !empty($data['id'])) {
            return $this->isUpdate(true)->save($data);
        } else {
            return $this->save($data);
        }
    }

    //是否需要检查节点，如果不存在权限节点数据，则不需要检查
    public function isCheck( $rule_val )
    {
        $rule_val = strtolower($rule_val);
        $map = ['rule_val'=>$rule_val];
        if($this->where('type',PLATFORM_ADMIN)->where($map)->count()){
            return true;
        }
        return false;
    }

    public function deleteById($id)
    {
        return AuthRule::destroy($id);
    }

    /**
     * 整理权限列表,找出子菜单,只支持二级菜单的整理
     * @return array|false|\PDOStatement|string|\think\Collection
     */
    public function getLevelData($where = [])
    {
        $data = $this->where('type',PLATFORM_ADMIN)->where($where)->order('pid asc')->order('sortnum','asc')->select();
        if( empty($data) ) {
            return $data;
        }

        $ret = [];
        foreach($data as $val) {
            if( $val->pid == 0 ) {
                $ret[$val->id] = array_merge(isset($ret[$val->id]) ? $ret[$val->id] : [],['id'=>$val->id,'title'=>$val->title,'pid'=>$val->pid, 'rule_val'=>$val->rule_val]);
            } else {
                $ret[$val->pid]['children'][] = ['id'=>$val->id,'title'=>$val->title,'pid'=>$val->pid, 'rule_val'=>$val->rule_val];
            }
        }
        return $ret;
    }
}
