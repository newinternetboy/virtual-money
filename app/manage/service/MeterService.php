<?php
/**
 * Created by PhpStorm.
 * User: ducongshu
 * Date: 2017/10/26
 * Time: 下午14:12
 */

namespace app\manage\service;

use app\manage\model\MeterModel;

class MeterService extends BasicService
{

    public function __construct(){
        $this->dbModel = new MeterModel();
    }


    /**
     * 获取表具上报数据
     * @param $meter_id
     * @param $M_Code
     * @param $startDate
     * @param $endDate
     * @param string $field
     * @return mixed
     */
    public function reportLogs($meter_id, $M_Code, $startDate, $endDate, $field = ''){
        $where['meter_id'] = $meter_id;
        $where['source_type'] = METER;
        $where['action_type'] = METER_REPORT;
        $where['create_time'] = ['between',[strtotime($startDate),strtotime($endDate)]];
        $meterDataService = new MeterDataService();
        $reportLogs = $meterDataService->selectInfo($where,$field,$M_Code);
        return $reportLogs;
    }

    /**
     *获取表具消费信息
     * @param $meter_id
     * @param $startDate
     * @param $endDate
     * @param $type     类型
     * @return mixed
     */
    public function moneyLogs($meter_id, $startDate, $endDate, $type){
        $where['from'] = $meter_id;
        $where['type'] = ['in',$type];
        $where['create_time'] = ['between',[strtotime($startDate),strtotime($endDate)]];
        $moneyLogService = new MoneyLogService();
        return $moneyLogService->selectInfo($where);
    }
}