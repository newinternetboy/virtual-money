<?php
/**
 * Created by PhpStorm.
 * User: ducongshu
 * Date: 2017/10/26
 * Time: 下午12:37
 */

namespace app\manage\model;


class MeterDataModel extends BasicModel
{
    // 当前模型名称
    protected $name;

    /**
     * 查询单条记录 降序
     * @param array $where
     * @param string $field
     * @return array|false|\PDOStatement|string|Model
     */
    public function findInfo($where = [], $field = '',$M_Code = ''){
        $this->name = getMeterdataTablename($M_Code);
        if( $field ){
            return db($this->name)->where($where)->field($field)->order('create_time','desc')->find();
        }
        return db($this->name)->where($where)->order('create_time','desc')->find();
    }

    /**
     * 查询单条记录 升序
     * @param array $where
     * @param string $field
     * @return array|false|\PDOStatement|string|Model
     */
    public function findInfoAsc($where = [], $field = '',$M_Code = ''){
        $this->name = getMeterdataTablename($M_Code);
        if( $field ){
            return db($this->name)->where($where)->field($field)->order('create_time','asc')->find();
        }
        return db($this->name)->where($where)->order('create_time','asc')->find();
    }

    /**
     * 查询多条记录
     * @param array $where
     * @param string $field
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function selectInfo($where = [], $field = '',$M_Code = ''){
        $this->name = getMeterdataTablename($M_Code);
        if( $field ){
            return db($this->name)->where($where)->field($field)->order('create_time','desc')->select();
        }
        return db($this->name)->where($where)->order('create_time','desc')->select();
    }

    /**
     * 翻页查询
     * @param array $where
     * @param array $param
     * @param string $field
     * @return $this
     */

    public function getInfoPaginate($where = [], $param = [], $field = '',$M_Code = ''){
        $this->name = getMeterdataTablename($M_Code);
        if( $field ){
            return db($this->name)->where($where)->field($field)->order('create_time','desc')->paginate()->appends($param);
        }
        return db($this->name)->where($where)->order('create_time','desc')->paginate()->appends($param);
    }

    /**
     * 插入/更新
     * @param $data
     * @param bool|true $scene
     * @return bool|string
     */

    public function upsert($data, $scene = true,$M_Code = ''){
        $this->name = getMeterdataTablename($M_Code);
        if( isset($data['id']) && !empty($data['id']) ){
            $result =  db($this->name)->validate($scene)->isUpdate(true)->save($data);
            if($result === false){
                return false;
            }
            return true;
        }else{
            unset($data['id']);
            $result = db($this->name)->validate($scene)->isUpdate(false)->save($data);
            if($result === false){
                return false;
            }
            return db($this->name)->getLastInsID();
        }
    }

}
