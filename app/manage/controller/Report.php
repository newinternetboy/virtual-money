<?php
/**
 * Created by PhpStorm.
 * User: ducongshu
 * Date: 2017/10/25
 * Time: 下午15:30
 */

namespace app\manage\controller;

use app\manage\service\CompanyService;
use app\manage\service\MeterdataService;
use app\manage\service\MeterService;
use think\Controller;


class Report
{
    //清分月报
    public function monthReport(){
        $year       = input('year',date('Y'));
        $company_name       = input('company_name');

        $report = [];
        if($company_name){
            $companyService = new CompanyService();
            $where['company_name'] = $company_name;
            $company = $companyService->findInfo($where,'company_name');
            $condition['company_id'] = $company['id'];
            $report =getMonthReport($year,$condition);
        }
        $this->assign('report',$report);
        $this->assign('year',$year);
        $this->assign('company_name',$company_name);
        return view();
    }
    //清分年报
    public function yearReport(){
        $startYear = input('startYear') ? input('startYear/d') : date('Y',strtotime('-5 years'));
        $endYear = input('endYear') ? input('endYear/d') : date('Y');
        $companyService = new CompanyService();
        $companys = $companyService->selectInfo([],'id,company_name');
        $company_id = input('company_id') ? input('company_id') : $companys[0]['id'];
        $company = $companyService->findInfo(['id'=>$company_id],'company_name');
        $where['company_id'] = $company_id;
        $res = getYearReport($startYear,$endYear,$where);
        $this->assign('companys',$companys);
        $this->assign('company_id',$company_id);
        $this->assign('company_name',$company['company_name']);
        $this->assign('startYear',$startYear);
        $this->assign('endYear',$endYear);
        $this->assign('report',$res['report']);
        $this->assign('years',$res['years']);
        return view();
    }

    //表具余额；
    public function meterBalance(){
        $M_Code = input('M_Code') ? input('M_Code') : '';
        $companyService = new CompanyService();
        $companys   = $companyService->selectInfo([],'id,company_name');
        $company_id = input('company_id') ? input('company_id') : $companys[0]['id'];
        $where['company_id'] = $company_id;
        $where['M_Code'] = empty($M_Code) ? ['neq',''] : $M_Code ;
        $where['meter_status'] = METER_STATUS_BIND;
        $where['meter_life'] = METER_LIFE_ACTIVE;
        $param['M_Code'] = $M_Code;
        $param['company_id'] = $company_id;
        $meterService = new MeterService();
        $meter = $meterService->getInfoPaginate($where,$param,'detail_address,M_Address,U_ID,M_Code,totalCube,balance');
        $totalbalance = array_sum(array_column($meter->toArray()['data'],'balance'));
        $number = count($meter);
        if($number){
            $savbalance = round($totalbalance/$number,2);
        }else{
            $savbalance = 0 ;
        }
        $this->assign('totalbalance',$totalbalance);
        $this->assign('number',$number);
        $this->assign('savbalance',$savbalance);
        $this->assign('meter',$meter);
        $this->assign('M_Code',$M_Code);
        $this->assign('companys',$companys);
        $this->assign('company_id',$company_id);
        return $this->fetch();
    }

    //余额统计
    public function balanceStatistics()
    {
        $companyService = new CompanyService();
        $company = $companyService->selectInfo([], 'id,company_name,OPT_ID');
        $meterService = new MeterService();
        $where['meter_status'] = METER_STATUS_BIND;
        $where['meter_life'] = METER_LIFE_ACTIVE;
        foreach ($company as & $value) {
            $where['company_id'] = $value['id'];
            $value['count'] = $meterService->counts($where);
            $value['meterbalance'] = $meterService->sums($where,'balance');
            $value = $value->toArray();
        }
        $this->assign('company',$company);
        return $this->fetch();
    }

    /**
     * 表具月报
     * @return \think\response\View
     */
    public function meterMonthReport(){
        $searchDate = input('searchDate',date('Y-m'));
        $M_Code = input('M_Code');
        if($M_Code){
            $meterService = new MeterService();
            $meterInfo = $meterService->findInfo(['M_Code' => $M_Code,'meter_life' => METER_LIFE_ACTIVE]);
            if( $meterInfo ){
                $searchDate .= '-01';
                $startDate = $searchDate.' 00:00:00';
                $endDate = date('Y-m-d H:i:s',strtotime('+1 month',strtotime($searchDate))-1);
                $reportLogs = $meterService->ReportLogs($meterInfo['id'],$M_Code,$startDate,$endDate,'diffCube,diffCost,create_time');
            }
        }
        $this->assign('searchDate',date('Y-m',strtotime($searchDate)));
        $this->assign('M_Code',$M_Code);
        $this->assign('reportLogs',isset($reportLogs) ? $reportLogs : []);
        return view();
    }

    /**
     * 表具年报
     * @return \think\response\View
     */
    public function meterYearReport(){
        $year = input('year',date('Y'));
        $M_Code = input('M_Code');
        if($M_Code){
            $meterService = new MeterService();
            $meterInfo = $meterService->findInfo(['M_Code' => $M_Code,'meter_life' => METER_LIFE_ACTIVE]);
            if( $meterInfo ){
                $reportLogs = getMonthReport($year,['meter_id' => $meterInfo['id']]);
            }
        }
        $this->assign('year',$year);
        $this->assign('M_Code',$M_Code);
        $this->assign('reportLogs',isset($reportLogs) ? $reportLogs : []);
        return view();
    }

    /**
     * 根据运营商名称进行模糊查找,返回匹配数组
     * @return \think\response\Json
     */
    public function getCompanyByName(){
        $company_name = input('company_name');
        if($company_name){
            $where['company_name'] = ['like',$company_name];
        }else{
            $where = [];
        }
        $companys = (new CompanyService())->selectInfo($where,'company_name');
        return json($companys);
    }

    /**
     * 表具用量
     * @return \think\response\View
     */
    public function meterUsage(){
        $company_name = input('company_name');
        $M_Code = input('M_Code');
        $startDate = input('startDate',date('Y-m-d',strtotime('-1 day')));
        $endDate = input('endDate',date('Y-m-d'));
        $where = [
            'meter_status' => ['neq',METER_STATUS_NEW]
        ];
        if($company_name){
            $where['company_id'] = '';
            $companyService = new CompanyService();
            if( $company = $companyService->findInfo(['company_name' => $company_name]) ){
                $where['company_id'] = $company['id'];
            }
        }
        if($M_Code){
            $where['M_Code'] = $M_Code;
        }
        $usage = [];
        $meters = (new MeterService())->getInfoPaginate($where,['company_name' => $company_name,'M_Code' => $M_Code,'startDate' => $startDate,'endDate' => $endDate]);
        foreach( $meters as $meter ){
            $meterDataUsage = new MeterDataService();
            $maxUsage = $meterDataUsage->findInfo(['meter_id' => $meter['id'],'source_type' => METER,'create_time' => ['between',[strtotime($startDate.' 00:00:00'),strtotime($endDate.' 23:59:59')]]],'',$meter['M_Code']);
            $minUsage = $meterDataUsage->findInfoAsc(['meter_id' => $meter['id'],'source_type' => METER,'create_time' => ['between',[strtotime($startDate.' 00:00:00'),strtotime($endDate.' 23:59:59')]]],'',$meter['M_Code']);
            $diffUsage = ($maxUsage ? $maxUsage['totalCube'] : 0) - ($minUsage ? $minUsage['totalCube'] : 0);
            $usage[] = [
                'M_Code' => $meter['M_Code'],
                'consumer_name' => $meter->consumer->username,
                'consumer_tel' => $meter->consumer->tel,
                'detail_address' => $meter['detail_address'],
                'diffUsage' => $diffUsage,
                'setup_time' => isset($meter['setup_time']) ? $meter['setup_time'] : $meter['change_time'],
            ];
        }
        $this->assign('usage',$usage);
        $this->assign('company_name',$company_name);
        $this->assign('M_Code',$M_Code);
        $this->assign('startDate',$startDate);
        $this->assign('endDate',$endDate);
        $this->assign('meters',$meters);
        return view();
    }

    public function downloadMeterUsage(){
        $company_name = input('company_name');
        $M_Code = input('M_Code');
        $startDate = input('startDate',date('Y-m-d',strtotime('-1 day')));
        $endDate = input('endDate',date('Y-m-d'));
        $where['source_type'] = METER;
        $where['create_time'] = ['$gte' => strtotime($startDate.' 00:00:00'),'$lte' => strtotime($endDate.' 23:59:59')];
        if($company_name){
            $where['company_id'] = '';
            $meter_where['company_id'] = '';
            $companyService = new CompanyService();
            if( $company = $companyService->findInfo(['company_name' => $company_name]) ){
                $where['company_id'] = $company['id'];
                $meter_where['company_id'] = $company['id'];
            }
        }
        if($M_Code){
            $meter_where['M_Code'] = $M_Code;
            $meters = (new MeterService())->selectInfo($meter_where,'id');
            $where['meter_id'] = ['in',array_map(function($item){return $item['id'];},$meters)];
        }
        $usages = (new MeterDataService())->getAllMeterUsageData('meter_data',$where);
        return json($usages);
    }
}