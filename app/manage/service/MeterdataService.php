<?php
/**
 * Created by PhpStorm.
 * User: ducongshu
 * Date: 2017/10/26
 * Time: 下午12:28
 */

namespace app\manage\service;

use app\manage\model\MeterdataModel;

class MeterdataService extends BasicService
{

    public function __construct(){
        $this->dbModel = new MeterdataModel();
    }

    /**
     * 获取信息 单条记录
     * @param $where
     * @param $method
     * @param $field
     * @return mixed
     */
    public function findInfo($where,  $field = '',$M_Code){
        return $this->dbModel->findInfo($where, $field,$M_Code);
    }

    /**
     * 获取信息 多条记录
     * @param $where
     * @param $method
     * @param $field
     * @return mixed
     */
    public function selectInfo($where = [],  $field = '',$M_Code){
        return $this->dbModel->selectInfo($where, $field,$M_Code);
    }

    /**
     * 获取翻页信息
     * @param $where
     * @param $method
     * @param $field
     * @return mixed
     */
    public function getInfoPaginate($where = [], $param = [], $field = '',$M_Code){
        return $this->dbModel->getInfoPaginate($where, $param, $field,$M_Code);
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
    public function upsert($data, $scene = true,$M_Code){
        return $this->dbModel->upsert($data,$scene,$M_Code);
    }

    /**
     * 获取错误信息
     * @return mixed
     */
    public function getError(){
        return $this->dbModel->getError();
    }
}