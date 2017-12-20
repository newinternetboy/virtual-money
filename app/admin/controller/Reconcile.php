<?php
/**
 * Created by PhpStorm.
 * User: ducongshu
 * Date: 2017/12/19
 * Time: 下午3:18
 */

namespace app\admin\controller;


use app\manage\service\CompanyService;
use app\manage\service\MeterService;
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
        $starttime = input('starttime',date('Y-m-d',strtotime('-1 day')));
        $endtime = input('endtime',date('Y-m-d'));
        $start = strtotime($starttime." 00:00:00");
        $end =strtotime($endtime." 23:59:59");
        $moneyLogService = new MoneyLogService();
        $money_log=[];
        $i=0;
        while($start < $end){
            $rmb_where = [
                'company_id' => $this->company_id,
                'to' => null, //to字段不存在是充值记录
                'type' => MONEY_PAY,
                'money_type' => MONEY_TYPE_RMB,
                //'channel' => ['in', [MONEY_CHANNEL_WEIXIN]],
                'create_time' => ['between', [$start, $start+86399]]
            ];
            $money_log[$i]['chargeTimes_rmb'] = $moneyLogService->counts($rmb_where);
            $money_log[$i]['chargeMoney_rmb'] = $moneyLogService->sums($rmb_where, 'money');
            $money_log[$i]['day'] = date('Y-m-d',$start);
            $i++;
            $start+=86400;
        }
        $this->assign('money_log', $money_log);
        $this->assign('starttime', $starttime);
        $this->assign('endtime',$endtime);
//        $this->clearReportCommon($date,$date,$date);

        return view();
    }

    /**
     * 充值月报
     * @return \think\response\View
     */
    public function clearMonthReport(){
        $starttime = input('starttime',date('Y-m'));
        $endtime = input('endtime',date('Y-m'));
        $start = strtotime($starttime."-1 00:00:00");
        $end = strtotime(date('Y-m-t 23:59:59',strtotime($endtime)));
        $moneyLogService = new MoneyLogService();
        $money_log=[];
        $i=0;
        while($start < $end){
            $rmb_where = [
                'company_id' => $this->company_id,
                'to' => null, //to字段不存在是充值记录
                'type' => MONEY_PAY,
                'money_type' => MONEY_TYPE_RMB,
                //'channel' => ['in', [MONEY_CHANNEL_WEIXIN]],
                'create_time' => ['between', [$start, strtotime(date('Y-m-t 23:59:59',$start))]]
            ];
            $money_log[$i]['chargeTimes_rmb'] = $moneyLogService->counts($rmb_where);
            $money_log[$i]['chargeMoney_rmb'] = $moneyLogService->sums($rmb_where, 'money');
            $money_log[$i]['month'] = date('Y-m',$start);
            $i++;
            $date =date('Y-m-d',$start);
            $start=strtotime(date('Y-m-1 00:00:00',strtotime("$date +1 month")));
        }
        $this->assign('money_log', $money_log);
        $this->assign('starttime', $starttime);
        $this->assign('endtime',$endtime);
        return view();
    }


    /**
     * 充值明细
     * @return \think\response\View
     */
    public function chargeDetail(){
        $M_Code = input('M_Code');
        $channel = input('channel/d');
        $money_type = MONEY_TYPE_RMB; //现在只能查人民币
        $endDate = input('endDate',date('Y-m-d'));
        $startDate = input('startDate',date('Y-m-d'));
        if( $M_Code ){
            $meter_where['M_Code'] = $M_Code;
        }
        if(isset($meter_where)){
            $meter_where['meter_life'] = METER_LIFE_ACTIVE;
            $meter_where['company_id'] = $this->company_id;
            $meters = (new MeterService())->selectInfo($meter_where,'id');
            $meterIds = array_column(array_map(function($item){return $item->toArray();},$meters),'id');
            $moneylog_where['from'] = ['in',$meterIds];
        }
        $moneyLogService = new MoneyLogService();
        $moneylog_where['company_id'] = $this->company_id;
        $moneylog_where['type'] = MONEY_PAY;
        $moneylog_where['to'] = null;
        if($channel){
            $moneylog_where['channel'] = $channel;
        }
        if($money_type){
            $moneylog_where['money_type'] = $money_type;
        }
        $moneylog_where['create_time'] = ['between',[strtotime($startDate.' 00:00:00'),strtotime($endDate.' 23:59:59')]];
        $moneylogs = $moneyLogService->getInfoPaginate($moneylog_where,['M_Code' => $M_Code,'channel' => $channel,'startDate' => $startDate,'endDate' => $endDate]);

        //汇总数据
        if($money_type){
            $moneyall = $moneyLogService->sums($moneylog_where,'money');
            $total = [
                [
                    'money_type' => $money_type,
                    'total'      => $moneyall
                ]
            ];
        }else{
            $moneylogAll_deli_where = $moneylog_where;
            $moneylogAll_deli_where['money_type'] = MONEY_TYPE_DELI;
            $moneylogsAll_deli = $moneyLogService->sums($moneylogAll_deli_where,'money');
            $moneylogAll_rmb_where = $moneylog_where;
            $moneylogAll_rmb_where['money_type'] = MONEY_TYPE_RMB;
            $moneylogsAll_rmb = $moneyLogService->sums($moneylogAll_rmb_where,'money');
            $total = [
                [
                    'money_type' => MONEY_TYPE_RMB,
                    'total'     => $moneylogsAll_rmb
                ],
                [
                    'money_type' => MONEY_TYPE_DELI,
                    'total'     => $moneylogsAll_deli
                ],
            ];
        }
        $this->assign('moneylogs',$moneylogs);
        $this->assign('M_Code',$M_Code);
        $this->assign('channel',$channel);
        $this->assign('startDate',$startDate);
        $this->assign('endDate',$endDate);
        $channels = config('extra_config.meter_charge_type');
        $this->assign('channels',$channels);
        $this->assign('total',$total);
        return view();
    }

    /**
     *下载充值明细
     */
    public function downloadChargeDetail(){
        $M_Code = input('M_Code');
        $channel = input('channel/d');
        $money_type = MONEY_TYPE_RMB; //现在只能查人民币
        $endDate = input('endDate',date('Y-m-d'));
        $startDate = input('startDate',date('Y-m-d'));
            if( $M_Code ){
                $meter_where['M_Code'] = $M_Code;
            }
            if(isset($meter_where)){
                $meter_where['meter_life'] = METER_LIFE_ACTIVE;
                $meter_where['company_id'] = $this->company_id;
                $meters = (new MeterService())->selectInfo($meter_where,'id');
                $meterIds = array_column(array_map(function($item){return $item->toArray();},$meters),'id');
                $moneylog_where['from'] = ['in',$meterIds];
            }
            $moneyLogService = new MoneyLogService();
            $moneylog_where['company_id'] = $this->company_id;
            $moneylog_where['type'] = MONEY_PAY;
            $moneylog_where['to'] = null;
            if($channel){
                $moneylog_where['channel'] = $channel;
            }
            if($money_type){
                $moneylog_where['money_type'] = $money_type;
            }
            $moneylog_where['create_time'] = ['between',[strtotime($startDate.' 00:00:00'),strtotime($endDate.' 23:59:59')]];
            $moneylogs = $moneyLogService->selectInfo($moneylog_where,'from,money_type,channel,type,money,create_time');
            //汇总数据
            if($money_type){
                $moneyall = $moneyLogService->sums($moneylog_where,'money');
                $total = [
                    [
                        'money_type' => $money_type,
                        'total'     => $moneyall
                    ]
                ];
            }else{
                $moneylogAll_deli_where = $moneylog_where;
                $moneylogAll_deli_where['money_type'] = MONEY_TYPE_DELI;
                $moneylogsAll_deli = $moneyLogService->sums($moneylogAll_deli_where,'money');
                $moneylogAll_rmb_where = $moneylog_where;
                $moneylogAll_rmb_where['money_type'] = MONEY_TYPE_RMB;
                $moneylogsAll_rmb = $moneyLogService->sums($moneylogAll_rmb_where,'money');
                $total = [
                    [
                        'money_type' => MONEY_TYPE_RMB,
                        'total'     => $moneylogsAll_rmb
                    ],
                    [
                        'money_type' => MONEY_TYPE_DELI,
                        'total'     => $moneylogsAll_deli
                    ],
                ];
            }
       (new MoneyLogService())->downloadChargeDetail($moneylogs,$M_Code.date('Y-m-d'),"充值明细excel",$startDate,$endDate,$total);
    }

}