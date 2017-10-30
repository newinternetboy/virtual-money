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

    public function counts($where){
        return $this->dbModel->counts($where);
    }

    //获取总和；
    public function sums($where,$field){
        return $this->dbModel->sums($where,$field);
    }

    public function ReportLogs($meter_id,$M_Code,$startDate,$endDate){
        $where['meter_id'] = $meter_id;
        $where['source_type'] = METER;
        $where['action_type'] = METER_REPORT;
        $where['create_time'] = ['between',[strtotime($startDate),strtotime($endDate)]];
        $meterDataService = new MeterDataService();
        $reportLogs = $meterDataService->selectInfo($where,'',$M_Code);
        return $reportLogs;
    }
}