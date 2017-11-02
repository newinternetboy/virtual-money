<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/10/24
 * Time: 下午12:03
 */

namespace app\manage\model;


class AuthRuleModel extends BasicModel
{
    // 当前模型名称
    protected $name = 'authRule';

    //设置主键名
    protected $insert  = ['type'];

    //是否菜单显示的状态,存储前转为整数
    public function setDisplayAttr($value){
        return intval($value);
    }

    //排序的状态,存储前转为整数
    public function setSortnumAttr($value){
        return intval($value);
    }

    public function setTypeAttr(){
        return PLATFORM_MANAGE;
    }

    public function setRuleValAttr($value){
        return strtolower($value);
    }

    /**
     * 查询单条记录
     * @param array $where
     * @param string $field
     * @param $M_Code 分表预留字段
     * @return array|false|\PDOStatement|string|Model
     */
    public function findInfo($where = [], $field = '',$M_Code = ''){
        if( $field ){
            return $this->where($where)->field($field)->order('sortnum','asc')->find();
        }
        return $this->where($where)->order('sortnum','asc')->find();
    }

    /**
     * 查询多条记录
     * @param array $where
     * @param string $field
     * @param $M_Code 分表预留字段
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function selectInfo($where = [], $field = '',$M_Code = ''){
        if( $field ){
            return $this->where($where)->field($field)->order('sortnum','asc')->select();
        }
        return $this->where($where)->order('sortnum','asc')->select();
    }

}