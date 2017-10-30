<?php
/**
 * Created by PhpStorm.
 * User: ducongshu
 * Date: 2017/10/26
 * Time: 下午12:37
 */

namespace app\manage\model;


class MeterdataModel extends BasicModel
{
    // 当前模型名称
    protected $name;

    /**
     * 查询单条记录
     * @param array $where
     * @param string $field
     * @return array|false|\PDOStatement|string|Model
     */
    public function findInfo($where = [], $field = '',$M_Code){
        $this->name = getMeterdataTablename($M_Code);
        if( $field ){
            return $this->where($where)->field($field)->find();
        }
        return $this->where($where)->find();
    }

    /**
     * 查询多条记录
     * @param array $where
     * @param string $field
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function selectInfo($where = [], $field = '',$M_Code){
        $this->name = getMeterdataTablename($M_Code);
        if( $field ){
            return $this->where($where)->field($field)->select();
        }
        return $this->where($where)->select();
    }

    /**
     * 翻页查询
     * @param array $where
     * @param array $param
     * @param string $field
     * @return $this
     */
    public function getInfoPaginate($where = [], $param = [], $field = '',$M_Code){

        if( $field ){
            return $this->where($where)->field($field)->paginate()->appends($param);
        }
        return $this->where($where)->paginate()->appends($param);
    }

    /**
     * 插入/更新
     * @param $data
     * @param bool|true $scene
     * @return bool|string
     */
    public function upsert($data, $scene = true,$M_Code){
        if( isset($data['id']) && !empty($data['id']) ){
            $result =  $this->validate($scene)->isUpdate(true)->save($data);
            if($result === false){
                return false;
            }
            return true;
        }else{
            unset($data['id']);
            $result = $this->validate($scene)->isUpdate(false)->save($data);
            if($result === false){
                return false;
            }
            return $this->getLastInsID();
        }
    }

}
