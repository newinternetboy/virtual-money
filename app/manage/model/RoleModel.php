<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/11/1
 * Time: 下午5:35
 */

namespace app\manage\model;


class RoleModel extends BasicModel
{
    protected $name = 'role';

    //设置主键名
    protected $insert  = ['type'];

    public function setStatusAttr($value){
        return intval($value);
    }

    public function setTypeAttr(){
        return PLATFORM_MANAGE;
    }

    /**
     * 查询单条记录
     * @param array $where
     * @param string $field
     * @param $M_Code 分表预留字段
     * @return array|false|\PDOStatement|string|Model
     */
    public function findInfo($where = [], $field = '',$M_Code = ''){
        $where['type'] = PLATFORM_MANAGE;
        return parent::findInfo($where,$field,$M_Code);
    }

    /**
     * 翻页查询
     * @param array $where
     * @param array $param
     * @param string $field
     * @param $M_Code 分表预留字段
     * @return $this
     */
    public function getInfoPaginate($where = [], $param = [], $field = '',$M_Code = ''){
        $where['type'] = PLATFORM_MANAGE;
        return parent::getInfoPaginate($where,$param,$field,$M_Code);
    }

}