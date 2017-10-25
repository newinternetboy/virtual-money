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

    public function upsert($data,$scene = true){
        return $this->dbModel->upsert($data,$scene);
    }

    public function getError(){
        return $this->dbModel->getError();
    }
}