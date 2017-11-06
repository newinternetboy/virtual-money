<?php
/**
 * Created by PhpStorm.
 * User: ducongshu
 * Date: 2017/10/26
 * Time: 下午12:28
 */

namespace app\manage\service;

use app\manage\model\MeterdataModel;

class MeterDataService extends BasicService
{

    public function __construct(){
        $this->dbModel = new MeterDataModel();
    }

    /**
     * 获取信息 单条记录 降序
     * @param $where
     * @param $method
     * @param $field
     * @return mixed
     */
    public function findInfo($where,  $field = '',$M_Code = ''){
        return $this->dbModel->findInfo($where, $field,$M_Code);
    }

    /**
     * 获取信息 单条记录 升序
     * @param $where
     * @param $method
     * @param $field
     * @return mixed
     */
    public function findInfoAsc($where,  $field = '',$M_Code = ''){
        return $this->dbModel->findInfoAsc($where, $field,$M_Code);
    }

    /**
     * 获取信息 多条记录
     * @param $where
     * @param $method
     * @param $field
     * @return mixed
     */
    public function selectInfo($where = [],  $field = '',$M_Code = ''){
        return $this->dbModel->selectInfo($where, $field,$M_Code);
    }

    /**
     * 获取翻页信息
     * @param $where
     * @param $method
     * @param $field
     * @return mixed
     */
    public function getInfoPaginate($where = [], $param = [], $field = '',$M_Code = ''){
        return $this->dbModel->getInfoPaginate($where, $param, $field,$M_Code);
    }

    /**
     * 插入/更新
     * @param $data
     * @param bool|true $scene
     * @return mixed
     */
    public function upsert($data, $scene = true,$M_Code = ''){
        return $this->dbModel->upsert($data,$scene,$M_Code);
    }

    public function getAllMeterUsageData($table,$where){
        $connectString = 'mongodb://';
        if(config('database.username') && config('database.password')){
            $connectString .= config('database.username') . ':' .config('database.password') . '@';
        }
        $connectString .= config('database.hostname') . ':' . config('database.hostport') . '/' . config('database.database');
        $mongodb = new \MongoDB\Driver\Manager($connectString);
        $database = config('database.database');
        $command = new \MongoDB\Driver\Command([
            'aggregate' => $table,
            'pipeline' => [
                ['$match' => $where],
                ['$group' => ['_id' => ['company_id' => '$company_id','meter_id' => '$meter_id','M_Code' => '$M_Code'],'sum' => ['$sum' => '$diffCube']]],

            ],
        ]);
        $result = $mongodb->executeCommand($database,$command);
        return $result->toArray();
    }

}