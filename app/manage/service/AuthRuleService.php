<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/10/24
 * Time: 下午12:02
 */

namespace app\manage\service;

use app\manage\model\AuthRuleModel;

class AuthRuleService extends BasicService
{

    public function __construct(){
        $this->dbModel = new AuthRuleModel();
    }

    public function getSortAuthRule(){
        $authRules = $this->selectInfo(['type' => PLATFORM_MANAGE]);
        $authRules = sortAuthRules($authRules);
        $authRules = $this->listAuthRules($authRules);
        foreach( $authRules as & $item ){
            $item['title'] = $item['prefix'].'|--'.$item['title'];
        }
        return $authRules;
    }


    /**
     * 将权限数组按照层级关系,转换成用于显示的list形式,通过"prefix"字段识别层级关系
     * @param array $authRules
     * @param int $level
     * @return array
     */
    private function listAuthRules($authRules, $level = 1){
        static $tmp = [];
        foreach($authRules as $authRule){
            $authRule['prefix'] = str_repeat('&emsp;',$level);
            $tmp[] = $authRule;
            if( isset($authRule['children']) ){
                $this->listAuthRules($authRule['children'],$level+1);
            }
        }
        return $tmp;
    }

    /**
     * 整理权限列表,找出子菜单,只支持二级菜单的整理
     * @return array|false|\PDOStatement|string|\think\Collection
     */
    public function getLevelData()
    {
        $data = $this->dbModel->where('type',PLATFORM_MANAGE)->order('pid asc')->order('sortnum','asc')->select();
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