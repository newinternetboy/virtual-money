<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/10/16
 * Time: 上午10:59
 */

namespace app\admin\controller;

use think\Db;
use app\manage\service\MeterService;
use app\manage\service\MeterDataService;

/**
 * 报表
 * Class Report
 * @package app\admin\controller
 */
class Report extends Admin
{

    /**
     * 月报表
     * @return \think\response\View
     */
    public function monthReport(){
        $year = input('year') ? input('year') : date('Y');
        $where['company_id'] = $this->company_id;
        $report =getMonthReport($year,$where);
        $this->assign('report',$report);
        $this->assign('year',$year);
        return view();
    }

    /**
     * 年报表
     * @return \think\response\View
     */
    public function yearReport(){
        $startYear = input('startYear') ? input('startYear/d') : date('Y',strtotime('-5 years'));
        $endYear = input('endYear') ? input('endYear/d') : date('Y');
        $this->assign('startYear',$startYear);
        $this->assign('endYear',$endYear);
        $where['company_id'] = $this->company_id;
        $res = getYearReport($startYear,$endYear,$where);
        $this->assign('report',$res['report']);
        $this->assign('years',$res['years']);
        return view();
    }

    /**
     * 表具安装统计
     * @return \think\response\View
     */
    public function setupReport(){
        $year = input('year') ? input('year') : date('Y');
        $where['company_id'] = $this->company_id;
        for($month = 1;$month <= 12;$month++){
            $where['company_id'] = $this->company_id;
            $startTime = strtotime($year.'-'.$month.'-1 00:00:00');
            $endTime = strtotime('+1 month',$startTime) - 1;
            $where['setup_time'] = ['between',[$startTime,$endTime]];
            $report[$month] = model('Meter')->getAllMeterInfo($where,'count');
        }
        $this->assign('report',$report);
        $this->assign('year',$year);
        return view();
    }

    /**
     *表具用量
     */
    public function meterUsage(){
        $M_Code = input('M_Code');
        $area = input('area');
        $startDate = input('startDate',date('Y-m-d',strtotime('-1 day')));
        $endDate = input('endDate',date('Y-m-d'));
        $where = [
            'meter_status' => ['neq',METER_STATUS_NEW],
            'company_id'    => $this->company_id
        ];
        if($M_Code){
            $where['M_Code'] = $M_Code;
        }
        if($area){
            $where['M_Address'] = $area;
        }
        $usage = [];
        $meters = (new MeterService())->getInfoPaginate($where,['M_Code' => $M_Code,'startDate' => $startDate,'endDate' => $endDate],'id,M_Code,U_ID,detail_address,setup_time,change_time');
        $condition['create_time'] = ['$gte' => strtotime($startDate.' 00:00:00'),'$lte' => strtotime($endDate.' 23:59:59')];
        $condition['meter_id'] = ['$in' => array_map(function($x){return $x['id'];},$meters->items())];
        $condition['source_type'] = METER;
        $table = 'meter_data';
        $result  = (new MeterDataService())->getAllMeterUsageData($table,$condition);
        foreach( $meters as $meter){
            $tmp = [
                'M_Code' => $meter['M_Code'],
                'consumer_name' => $meter->consumer->username,
                'detail_address' => $meter['detail_address'],
                'diffUsage' => 0,
                'setup_time' => isset($meter['setup_time']) ? $meter['setup_time'] : $meter['change_time'],
            ];
            foreach($result[0]->result as $index => $re){
                if($meter['id'] == $re->_id->meter_id){
                    $tmp['diffUsage'] = $re->max - $re->min;
                    unset($result[0]->result[$index]);
                    break;
                }
            }
            $usage[] = $tmp;
        }
        $area_where['company_id'] = $this->company_id;
        $areas = model('Area')->getList( $area_where );
        $this->assign('usage',$usage);
        $this->assign('M_Code',$M_Code);
        $this->assign('startDate',$startDate);
        $this->assign('endDate',$endDate);
        $this->assign('meters',$meters);
        $this->assign('areas',$areas);
        $this->assign('area',$area);
        return view();
    }

    /**
     *下载表具用量excel
     */
    public function downloadMeterUsage(){
        $M_Code = input('M_Code');
        $area = input('area');
        $startDate = input('startDate',date('Y-m-d',strtotime('-1 day')));
        $endDate = input('endDate',date('Y-m-d'));
        $where = [
            'meter_status' => ['neq',METER_STATUS_NEW],
            'company_id'    => $this->company_id
        ];
        if($M_Code){
            $where['M_Code'] = $M_Code;
        }
        if($area){
            $where['M_Address'] = $area;
        }
        $usage = [];
        $meters = (new MeterService())->selectInfo($where,'id,M_Code,U_ID,detail_address,setup_time,change_time');
        $condition['create_time'] = ['$gte' => strtotime($startDate.' 00:00:00'),'$lte' => strtotime($endDate.' 23:59:59')];
        $condition['meter_id'] = ['$in' => array_map(function($x){return $x['id'];},$meters)];
        $condition['source_type'] = METER;
        $table = 'meter_data';
        $result  = (new MeterDataService())->getAllMeterUsageData($table,$condition);
        foreach( $meters as $meter){
            $tmp = [
                'M_Code' => $meter['M_Code'],
                'consumer_name' => $meter->consumer->username,
                'detail_address' => $meter['detail_address'],
                'diffUsage' => 0,
                'setup_time' => isset($meter['setup_time']) ? $meter['setup_time'] : $meter['change_time'],
            ];
            foreach($result[0]->result as $index => $re){
                if($meter['id'] == $re->_id->meter_id){
                    $tmp['diffUsage'] = $re->max - $re->min;
                    unset($result[0]->result[$index]);
                    break;
                }
            }
            $usage[] = $tmp;
        }

        (new MeterDataService())->downloadMeterUsageExcel($usage,$this->company['company_name'].$M_Code.'表具用量',$this->company['company_name'].$M_Code.'表具用量',$startDate,$endDate,PLATFORM_ADMIN);
    }
}