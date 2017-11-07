<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/11/6
 * Time: 下午3:18
 */

namespace app\manage\controller;


use app\manage\service\CompanyService;
use app\manage\service\MoneyLogService;

/**
 * 对账
 * Class Reconcile
 * @package app\manage\controller
 */
class Reconcile extends Admin
{
    /**
     * 充值日报
     * @return \think\response\View
     */
    public function clearDayReport(){
        $date = input('date',date('Y-m-d'));
        $startTime = strtotime($date.' 00:00:00');
        $endTime = strtotime('+1 day',$startTime)-1;
        $this->clearReportCommon($date,$startTime,$endTime);
        return view();
    }

    /**
     *下载充值日报
     */
    public function downloadDayReport(){
        $date = input('date',date('Y-m-d'));
        $startTime = strtotime($date.' 00:00:00');
        $endTime = strtotime('+1 day',$startTime)-1;
        $result = $this->downloadReportCommon($startTime,$endTime);
        (new moneyLogService())->downloadClearReport($result['companys'],'充值日报'.$date,'充值日报',$date,$result['totalChargeTimes_rmb'],$result['totalChargeMoney_rmb'],$result['totalChargeTimes_deli'],$result['totalChargeMoney_deli']);
    }

    /**
     * 充值月报
     * @return \think\response\View
     */
    public function clearMonthReport(){
        $date = input('date',date('Y-m'));
        $startTime = strtotime($date.'-01 00:00:00');
        $endTime = strtotime('+1 month',$startTime)-1;
        $this->clearReportCommon($date,$startTime,$endTime);
        return view();
    }

    /**
     *下载充值月报
     */
    public function downloadMonthReport(){
        $date = input('date',date('Y-m'));
        $startTime = strtotime($date.'-01 00:00:00');
        $endTime = strtotime('+1 month',$startTime)-1;
        $result = $this->downloadReportCommon($startTime,$endTime);
        (new moneyLogService())->downloadClearReport($result['companys'],'充值月报'.$date,'充值月报',$date,$result['totalChargeTimes_rmb'],$result['totalChargeMoney_rmb'],$result['totalChargeTimes_deli'],$result['totalChargeMoney_deli']);
    }

    /**
     * 充值年报
     * @return \think\response\View
     */
    public function clearYearReport(){
        $date = input('date',date('Y'));
        $startTime = strtotime($date.'-01-01 00:00:00');
        $endTime = strtotime('+1 year',$startTime)-1;
        $this->clearReportCommon($date,$startTime,$endTime);
        return view();
    }

    /**
     *下载充值年报
     */
    public function downloadYearReport(){
        $date = input('date',date('Y'));
        $startTime = strtotime($date.'-01-01 00:00:00');
        $endTime = strtotime('+1 year',$startTime)-1;
        $result = $this->downloadReportCommon($startTime,$endTime);
        (new moneyLogService())->downloadClearReport($result['companys'],'充值年报'.$date,'充值年报',$date,$result['totalChargeTimes_rmb'],$result['totalChargeMoney_rmb'],$result['totalChargeTimes_deli'],$result['totalChargeMoney_deli']);
    }

    /**
     * 清分报表 获取运营商充值数据公共方法
     * @param $date
     * @param $startTime
     * @param $endTime
     */
    private function clearReportCommon($date, $startTime, $endTime){
        $companyService = new CompanyService();
        $companys = $companyService->getInfoPaginate(['status' => COMPANY_STATUS_NORMAL],['date' => $date],'company_name,desc');
        $moneyLogService = new MoneyLogService();
        foreach($companys as & $company){
            $rmb_where = [
                'company_id' => $company['id'],
                'to'    => null, //to字段不存在是充值记录
                'type' => MONEY_PAY,
                'money_type' => MONEY_TYPE_RMB,
                'channel'  => ['in',[MONEY_CHANNEL_WEIXIN]],
                'create_time' => ['between',[$startTime,$endTime]]
            ];
            $deli_where = [
                'company_id' => $company['id'],
                'to'    => null, //to字段不存在是充值记录
                'type' => MONEY_PAY,
                'money_type' => MONEY_TYPE_DELI,
                'channel'  => ['in',[MONEY_CHANNEL_WEIXIN]],
                'create_time' => ['between',[$startTime,$endTime]]
            ];
            $company['chargeTimes_rmb'] = $moneyLogService->counts($rmb_where);
            $company['chargeTimes_deli'] = $moneyLogService->counts($deli_where);
            $company['chargeMoney_rmb'] = $moneyLogService->sums($rmb_where,'money');
            $company['chargeMoney_deli'] = $moneyLogService->sums($deli_where,'money');
        }
        $this->assign('date',$date);
        $this->assign('companys',$companys);

        //获取汇总数据
        $companysAll = $companyService->selectInfo(['status' => COMPANY_STATUS_NORMAL],'company_name,desc');
        $rmb_where_all = [
            'company_id' => ['in',array_column(array_map(function($item){return $item->toArray();},$companysAll),'id')],
            'to'    => null, //to字段不存在是充值记录
            'type' => MONEY_PAY,
            'money_type' => MONEY_TYPE_RMB,
            'channel'  => ['in',[MONEY_CHANNEL_WEIXIN]],
            'create_time' => ['between',[$startTime,$endTime]]
        ];
        $deli_where_all = [
            'company_id' => ['in',array_column(array_map(function($item){return $item->toArray();},$companysAll),'id')],
            'to'    => null, //to字段不存在是充值记录
            'type' => MONEY_PAY,
            'money_type' => MONEY_TYPE_DELI,
            'channel'  => ['in',[MONEY_CHANNEL_WEIXIN]],
            'create_time' => ['between',[$startTime,$endTime]]
        ];
        $all['chargeTimes_rmb'] = $moneyLogService->counts($rmb_where_all);
        $all['chargeTimes_deli'] = $moneyLogService->counts($deli_where_all);
        $all['chargeMoney_rmb'] = $moneyLogService->sums($rmb_where_all,'money');
        $all['chargeMoney_deli'] = $moneyLogService->sums($deli_where_all,'money');
        $this->assign('all',$all);
    }

    /**
     * 清分报表导出功能 获取运营商充值数据公共方法
     * @param $startTime
     * @param $endTime
     * @return array
     */
    private function downloadReportCommon($startTime, $endTime){
        $companyService = new CompanyService();
        $companys = $companyService->selectInfo(['status' => COMPANY_STATUS_NORMAL],'company_name,desc');
        $moneyLogService = new MoneyLogService();
        $totalChargeTimes_rmb = 0;
        $totalChargeMoney_rmb = 0;
        $totalChargeTimes_deli = 0;
        $totalChargeMoney_deli = 0;
        foreach($companys as & $company){
            $rmb_where = [
                'company_id' => $company['id'],
                'to'    => null, //to字段不存在是充值记录
                'type' => MONEY_PAY,
                'money_type' => MONEY_TYPE_RMB,
                'channel'  => ['in',[MONEY_CHANNEL_WEIXIN]],
                'create_time' => ['between',[$startTime,$endTime]]
            ];
            $deli_where = [
                'company_id' => $company['id'],
                'to'    => null, //to字段不存在是充值记录
                'type' => MONEY_PAY,
                'money_type' => MONEY_TYPE_DELI,
                'channel'  => ['in',[MONEY_CHANNEL_WEIXIN]],
                'create_time' => ['between',[$startTime,$endTime]]
            ];
            $company['chargeTimes_rmb'] = $moneyLogService->counts($rmb_where);
            $company['chargeTimes_deli'] = $moneyLogService->counts($deli_where);
            $company['chargeMoney_rmb'] = $moneyLogService->sums($rmb_where,'money');
            $company['chargeMoney_deli'] = $moneyLogService->sums($deli_where,'money');

            $totalChargeTimes_rmb += $company['chargeTimes_rmb'];
            $totalChargeMoney_deli += $company['chargeMoney_deli'];
            $totalChargeTimes_deli += $company['chargeTimes_deli'];
            $totalChargeMoney_rmb += $company['chargeMoney_rmb'];
        }
        return [
            'companys' => $companys,
            'totalChargeTimes_rmb' => $totalChargeTimes_rmb,
            'totalChargeMoney_rmb' => $totalChargeMoney_rmb,
            'totalChargeTimes_deli' => $totalChargeTimes_deli,
            'totalChargeMoney_deli' => $totalChargeMoney_deli,
        ];
    }
}