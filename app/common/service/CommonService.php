<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2018/1/8
 * Time: 下午3:41
 */

namespace app\common\service;


class CommonService
{
    /**
     * 数据库model类
     * @var
     */
    protected $dbModel ;

    /**
     * 获取信息 单条记录
     * @param $where
     * @param $method
     * @param $field
     * @return mixed
     */
    public function findInfo($where,  $field = ''){
        return $this->dbModel->findInfo($where, $field);
    }

    /**
     * 获取信息 多条记录
     * @param $where
     * @param $method
     * @param $field
     * @return mixed
     */
    public function selectInfo($where = [],  $field = ''){
        return $this->dbModel->selectInfo($where, $field);
    }

    /**
     * 获取翻页信息
     * @param $where
     * @param $method
     * @param $field
     * @return mixed
     */
    public function getInfoPaginate($where = [], $param = [], $field = ''){
        return $this->dbModel->getInfoPaginate($where, $param, $field);
    }

    /**
     * 获取上次执行的sql
     * @return mixed
     */
    public function getLastSql(){
        return $this->dbModel->getLastSql();
    }

    /**
     * 插入/更新
     * @param $data
     * @param bool|true $scene
     * @return mixed
     */
    public function upsert($data, $scene = true){
        return $this->dbModel->upsert($data,$scene);
    }

    /**
     * 批量插入
     * @param $data
     * @param bool|true $scene
     * @return mixed
     */
    public function insertAll($data){
        return $this->dbModel->insertBatch($data);
    }

    /**
     * 获取错误信息
     * @return mixed
     */
    public function getError(){
        return $this->dbModel->getError();
    }

    /**
     * 获取数组总个数；
     * @return mixed
     */
    public function counts($where){
        return $this->dbModel->counts($where);
    }

    /**
     * 获取总和；
     * @return mixed
     */
    public function sums($where,$field){
        return $this->dbModel->sums($where,$field);
    }

    //删除
    public function del($id){
        return $this->dbModel->del($id);
    }

    public function getColumn($where,$col){
        return $this->dbModel->getColumn($where,$col);
    }

    public function selectLimitInfo($where,$field,$skip,$limit){
        return $this->dbModel->selectLimitInfo($where,$field,$skip,$limit);
    }

    public function delMany($where){
        return $this->dbModel->delMany($where);
    }
}