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
        $companyService = new CompanyService();
        $companys = $companyService->getInfoPaginate(['status' => COMPANY_STATUS_NORMAL],['date' => $date],'company_name,desc');
        $moneyLogService = new MoneyLogService();
        foreach($companys as & $company){
            $rmb_where = [
                'company_id' => $company['id'],
                'to'    => null, //to字段不存在是充值记录
                'type' => MONEY_PAY,
                'money_type' => MONEY_TYPE_RMB,
                'create_time' => ['between',[strtotime($date.' 00:00:00'),strtotime($date.' 23:59:59')]]
            ];
            $deli_where = [
                'company_id' => $company['id'],
                'to'    => null, //to字段不存在是充值记录
                'type' => MONEY_PAY,
                'money_type' => MONEY_TYPE_DELI,
                'create_time' => ['between',[strtotime($date.' 00:00:00'),strtotime($date.' 23:59:59')]]
            ];
            $company['chargeTimes_rmb'] = $moneyLogService->counts($rmb_where);
            $company['chargeTimes_deli'] = $moneyLogService->counts($deli_where);
            $company['chargeMoney_rmb'] = $moneyLogService->sums($rmb_where,'money');
            $company['chargeMoney_deli'] = $moneyLogService->sums($deli_where,'money');
        }
        $this->assign('date',$date);
        $this->assign('companys',$companys);

        //获取汇总数据
        $rmb_where_all = [
            'to'    => null, //to字段不存在是充值记录
            'type' => MONEY_PAY,
            'money_type' => MONEY_TYPE_RMB,
            'create_time' => ['between',[strtotime($date.' 00:00:00'),strtotime($date.' 23:59:59')]]
        ];
        $deli_where_all = [
            'to'    => null, //to字段不存在是充值记录
            'type' => MONEY_PAY,
            'money_type' => MONEY_TYPE_DELI,
            'create_time' => ['between',[strtotime($date.' 00:00:00'),strtotime($date.' 23:59:59')]]
        ];
        $all['chargeTimes_rmb'] = $moneyLogService->counts($rmb_where_all);
        $all['chargeTimes_deli'] = $moneyLogService->counts($deli_where_all);
        $all['chargeMoney_rmb'] = $moneyLogService->sums($rmb_where_all,'money');
        $all['chargeMoney_deli'] = $moneyLogService->sums($deli_where_all,'money');
        $this->assign('all',$all);
        return view();
    }

    /**
     *下载充值日报
     */
    public function downloadDayReport(){
        $date = input('date',date('Y-m-d'));
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
                'create_time' => ['between',[strtotime($date.' 00:00:00'),strtotime($date.' 23:59:59')]]
            ];
            $deli_where = [
                'company_id' => $company['id'],
                'to'    => null, //to字段不存在是充值记录
                'type' => MONEY_PAY,
                'money_type' => MONEY_TYPE_DELI,
                'create_time' => ['between',[strtotime($date.' 00:00:00'),strtotime($date.' 23:59:59')]]
            ];
            $company['chargeTimes_rmb'] = $moneyLogService->counts($rmb_where);
            $company['chargeTimes_deli'] = $moneyLogService->counts($deli_where);
            $company['chargeMoney_rmb'] = $moneyLogService->sums($rmb_where,'money');
            $company['chargeMoney_deli'] = $moneyLogService->sums($deli_where,'money');

            $totalChargeTimes_rmb += $company['chargeTimes_rmb'];
            $totalChargeMoney_rmb += $company['chargeMoney_rmb'];
            $totalChargeTimes_deli += $company['chargeTimes_deli'];
            $totalChargeMoney_rmb += $company['chargeMoney_rmb'];
        }
        $moneyLogService->downloadDayReport($companys,'充值日报'.$date,'充值日报',$date,$totalChargeTimes_rmb,$totalChargeMoney_rmb,$totalChargeTimes_deli,$totalChargeMoney_deli);
    }
}