<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/10/24
 * Time: 上午11:57
 */

namespace app\manage\model;


use think\Model;

class BasicModel extends Model
{

    //设置主键名
    protected $pk  = 'id';

    /**
     * 查询单条记录
     * @param array $where
     * @param string $field
     * @param $M_Code 分表预留字段
     * @return array|false|\PDOStatement|string|Model
     */
    public function findInfo($where = [], $field = '',$M_Code = ''){
        if( $field ){
            return $this->where($where)->field($field)->order('create_time','desc')->find();
        }
        return $this->where($where)->order('create_time','desc')->find();
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
            return $this->where($where)->field($field)->order('create_time','desc')->select();
        }
        return $this->where($where)->order('create_time','desc')->select();
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
        if( $field ){
            return $this->where($where)->field($field)->order('create_time','desc')->paginate()->appends($param);
        }
        return $this->where($where)->order('create_time','desc')->paginate()->appends($param);
    }

    /**
     * 插入/更新
     * @param $data
     * @param bool|true $scene
     * @param $M_Code 分表预留字段
     * @return bool|string
     */
    public function upsert($data, $scene = true,$M_Code = ''){
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

    //查总个数；
    public function counts($where){
        return $this->where($where)->count();
    }
    //获取总和；
    public function sums($where,$field){
        return $this->where($where)->sum($field);
    }

    public function del($id){
        return $this->where(['id' => $id])->delete();
    }

}