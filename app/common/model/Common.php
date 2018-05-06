<?php
namespace app\common\model;


use think\Config;
use think\Controller;
use think\Lang;
use think\Model;

/**
* 公用的控制器，pc、app、微信各端不需要控制权限的控制器，必须继承该控制器
 *
* @author aierui github  https://github.com/Aierui
* @version 1.0 
*/
class Common extends Model
{
    //设置主键名
    protected $pk  = 'id';

    protected $insert = ['create_time'];

    public function SetCreateTimeAttr(){
        return time();
    }

    /**
     * 查询单条记录
     * @param array $where
     * @param string $field
     * @return array|false|\PDOStatement|string|Model
     */
    public function findInfo($where = [], $field = ''){
        if( $field ){
            return $this->where($where)->field($field)->order('create_time','desc')->find();
        }
        return $this->where($where)->order('create_time','desc')->find();
    }

    /**
     * 查询多条记录
     * @param array $where
     * @param string $field
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function selectInfo($where = [], $field = ''){
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
     * @return $this
     */
    public function getInfoPaginate($where = [], $param = [], $field = ''){
        if( $field ){
            return $this->where($where)->field($field)->order('create_time','desc')->paginate()->appends($param);
        }
        return $this->where($where)->order('create_time','desc')->paginate()->appends($param);
    }

    /**
     * 插入/更新
     * @param $data
     * @param bool|true $scene
     * @return bool|string
     */
    public function upsert($data, $scene = true){
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

    /**
     * 批量插入
     * @param $data
     * @return bool|string
     */
    public function insertBatch($data){
        return $this->insertAll($data);
    }


    //查总个数；
    public function counts($where){
        return $this->where($where)->count();
    }
    //获取总和；
    public function sums($where,$field){
        return $this->where($where)->sum($field);
    }

    //删除数据
    public function del($id){
        return $this->where(['id' => $id])->delete();
    }

    public function getColumn($where,$col){
        return $this->where($where)->column($col);
    }

//    public function selectLimitInfo($where,$field,$skip,$limit,$order){
//        if( $field ){
//            return $this->where($where)->limit($skip,$limit)->field($field)->order($order)->select();
//        }
//        return $this->where($where)->limit($skip,$limit)->order($order)->select();
//    }

    public function delMany($where){
        return $this->where($where)->delete();
    }

    public function updateData($where,$data){
        return $this->where($where)->update($data);
    }

}