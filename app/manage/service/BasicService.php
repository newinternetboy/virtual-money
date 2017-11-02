<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/10/24
 * Time: 上午11:46
 */

namespace app\manage\service;


/**
 * 基础服务类
 * Class Basic
 * @package app\manage\service
 */
class BasicService
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
     * @param $M_Code 分表预留字段
     * @return mixed
     */
    public function findInfo($where,  $field = '',$M_Code = ''){
        return $this->dbModel->findInfo($where, $field);
    }

    /**
     * 获取信息 多条记录
     * @param $where
     * @param $method
     * @param $field
     * @param $M_Code 分表预留字段
     * @return mixed
     */
    public function selectInfo($where = [],  $field = '',$M_Code = ''){
        return $this->dbModel->selectInfo($where, $field);
    }

    /**
     * 获取翻页信息
     * @param $where
     * @param $method
     * @param $field
     * @param $M_Code 分表预留字段
     * @return mixed
     */
    public function getInfoPaginate($where = [], $param = [], $field = '',$M_Code = ''){
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
      * @param $M_Code 分表预留字段
     * @return mixed
     */
    public function upsert($data, $scene = true,$M_Code = ''){
        return $this->dbModel->upsert($data,$scene);
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

    public function del($id){
        return $this->dbModel->del($id);
    }

}