<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/11/6
 * Time: 下午3:18
 */

namespace app\manage\controller;


use app\manage\service\CompanyService;
use app\manage\service\MeterService;
use app\manage\service\MoneyLogService;
use think\Log;

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
        $source = input('source','new');
        $this->clearReportCommon($source,$date,$date,$date);
        $this->assign('source',$source);
        return view();
    }

    /**
     *下载充值日报
     */
    public function downloadDayReport(){
        $date = input('date',date('Y-m-d'));
        $source = input('source','new');
        $result = $this->downloadReportCommon($source,$date,$date);
        (new moneyLogService())->downloadClearReport($result['companys'],"充值日报($source)".$date,"充值日报($source)",$date,$result['totalChargeTimes_rmb'],$result['totalChargeMoney_rmb'],$result['totalChargeTimes_deli'],$result['totalChargeMoney_deli']);
    }

    /**
     * 充值月报
     * @return \think\response\View
     */
    public function clearMonthReport(){
        $date = input('date',date('Y-m'));
        $source = input('source','new');
        $startDate = $date.'-01';
        $endDate = date('Y-m-t',strtotime($startDate));
        $this->clearReportCommon($source,$date,$startDate,$endDate);
        $this->assign('source',$source);
        return view();
    }

    /**
     *下载充值月报
     */
    public function downloadMonthReport(){
        $date = input('date',date('Y-m'));
        $source = input('source','new');
        $startDate = $date.'-01';
        $endDate = date('Y-m-t',strtotime($startDate));
        $result = $this->downloadReportCommon($source,$startDate,$endDate);
        (new moneyLogService())->downloadClearReport($result['companys'],"充值月报($source)".$date,"充值月报($source)",$date,$result['totalChargeTimes_rmb'],$result['totalChargeMoney_rmb'],$result['totalChargeTimes_deli'],$result['totalChargeMoney_deli']);
    }

    /**
     * 充值年报
     * @return \think\response\View
     */
    public function clearYearReport(){
        $date = input('date',date('Y'));
        $source = input('source','new');
        $startDate = $date.'-01-01';
        $endDate = date('Y-12-t',strtotime($startDate));
        $this->clearReportCommon($source,$date,$startDate,$endDate);
        $this->assign('source',$source);
        return view();
    }

    /**
     *下载充值年报
     */
    public function downloadYearReport(){
        $date = input('date',date('Y'));
        $source = input('source','new');
        $startDate = $date.'-01-01';
        $endDate = date('Y-12-t',strtotime($startDate));
        $result = $this->downloadReportCommon($source,$startDate,$endDate);
        (new moneyLogService())->downloadClearReport($result['companys'],"充值年报($source)".$date,"充值年报($source)",$date,$result['totalChargeTimes_rmb'],$result['totalChargeMoney_rmb'],$result['totalChargeTimes_deli'],$result['totalChargeMoney_deli']);
    }

    /**
     * 清分报表 获取运营商充值数据公共方法
     * @param $source 数据源标识 new:新系统  old:旧系统
     * @param $date
     * @param $startdate
     * @param $enddate
     */
    private function clearReportCommon($source,$date,$startdate,$enddate){
        if($source == 'new') {
            $startTime = strtotime($startdate.' 00:00:00');
            $endTime = strtotime($enddate.' 23:59:59');
            $companyService = new CompanyService();
            $companys = $companyService->getInfoPaginate(['status' => COMPANY_STATUS_NORMAL], ['date' => $date], 'company_name');
            $moneyLogService = new MoneyLogService();
            foreach ($companys as & $company) {
                $rmb_where = [
                    'company_id' => $company['id'],
                    'to' => null, //to字段不存在是充值记录
                    'type' => MONEY_PAY,
                    'money_type' => MONEY_TYPE_RMB,
                    //'channel' => ['in', [MONEY_CHANNEL_WEIXIN]],
                    'create_time' => ['between', [$startTime, $endTime]]
                ];
                //            $deli_where = [
                //                'company_id' => $company['id'],
                //                'to'    => null, //to字段不存在是充值记录
                //                'type' => MONEY_PAY,
                //                'money_type' => MONEY_TYPE_DELI,
                //                'channel'  => ['in',[MONEY_CHANNEL_WEIXIN]],
                //                'create_time' => ['between',[$startTime,$endTime]]
                //            ];
                $company['chargeTimes_rmb'] = $moneyLogService->counts($rmb_where);
                //            $company['chargeTimes_deli'] = $moneyLogService->counts($deli_where);
                $company['chargeMoney_rmb'] = $moneyLogService->sums($rmb_where, 'money');
                //            $company['chargeMoney_deli'] = $moneyLogService->sums($deli_where,'money');
            }

            //获取汇总数据
            $companysAll = $companyService->selectInfo(['status' => COMPANY_STATUS_NORMAL], 'company_name');
            $rmb_where_all = [
                'company_id' => ['in', array_column(array_map(function ($item) {
                    return $item->toArray();
                }, $companysAll), 'id')],
                'to' => null, //to字段不存在是充值记录
                'type' => MONEY_PAY,
                'money_type' => MONEY_TYPE_RMB,
                //'channel' => ['in', [MONEY_CHANNEL_WEIXIN]],
                'create_time' => ['between', [$startTime, $endTime]]
            ];
            //        $deli_where_all = [
            //            'company_id' => ['in',array_column(array_map(function($item){return $item->toArray();},$companysAll),'id')],
            //            'to'    => null, //to字段不存在是充值记录
            //            'type' => MONEY_PAY,
            //            'money_type' => MONEY_TYPE_DELI,
            //            'channel'  => ['in',[MONEY_CHANNEL_WEIXIN]],
            //            'create_time' => ['between',[$startTime,$endTime]]
            //        ];
            $all['chargeTimes_rmb'] = $moneyLogService->counts($rmb_where_all);
            //        $all['chargeTimes_deli'] = $moneyLogService->counts($deli_where_all);
            $all['chargeMoney_rmb'] = $moneyLogService->sums($rmb_where_all, 'money');
            //        $all['chargeMoney_deli'] = $moneyLogService->sums($deli_where_all,'money');
        }else{
            $old_system_url = config('old_system_url');
            $url = $old_system_url;
            $post_data = [
                'type'  => des_encrypt('1'),
                "startDate" => des_encrypt($startdate),
                'endDate' => des_encrypt($enddate)
            ];
            $result_api = send_post($url,$post_data);
            $result_api = des_decrypt($result_api);
            $result = json_decode($result_api,true);
            if(!is_array($result)){
                Log::record(['获取旧系统数据失败' => $result_api],'error');
            }
            $result = is_array($result) ? $result : [];
            $companys = [];
            foreach($result as $item){
                $companys[] =[
                    'company_name' => $item[0],
                    'chargeTimes_rmb' => $item[1],
                    'chargeMoney_rmb' => $item[2],
                ];
            }
            $all['chargeTimes_rmb'] = array_sum(array_column($companys,'chargeTimes_rmb'));
            $all['chargeMoney_rmb'] = array_sum(array_column($companys,'chargeMoney_rmb'));
        }
        $this->assign('companys', $companys);
        $this->assign('all', $all);
        $this->assign('date', $date);
    }

    /**
     * 清分报表导出功能 获取运营商充值数据公共方法
     * @param $source
     * @param $startdate
     * @param $endTdate
     * @return array
     */
    private function downloadReportCommon($source ,$startdate, $enddate){
        $totalChargeTimes_rmb = 0;
        $totalChargeMoney_rmb = 0;
        $totalChargeTimes_deli = 0;
        $totalChargeMoney_deli = 0;
        if($source == 'new'){
            $startTime = strtotime($startdate.' 00:00:00');
            $endTime = strtotime($enddate.' 23:59:59');
            $companyService = new CompanyService();
            $companys = $companyService->selectInfo(['status' => COMPANY_STATUS_NORMAL],'company_name');
            $moneyLogService = new MoneyLogService();
            foreach($companys as & $company){
                $rmb_where = [
                    'company_id' => $company['id'],
                    'to'    => null, //to字段不存在是充值记录
                    'type' => MONEY_PAY,
                    'money_type' => MONEY_TYPE_RMB,
                    //'channel'  => ['in',[MONEY_CHANNEL_WEIXIN]],
                    'create_time' => ['between',[$startTime,$endTime]]
                ];
    //            $deli_where = [
    //                'company_id' => $company['id'],
    //                'to'    => null, //to字段不存在是充值记录
    //                'type' => MONEY_PAY,
    //                'money_type' => MONEY_TYPE_DELI,
    //                'channel'  => ['in',[MONEY_CHANNEL_WEIXIN]],
    //                'create_time' => ['between',[$startTime,$endTime]]
    //            ];
                $company['chargeTimes_rmb'] = $moneyLogService->counts($rmb_where);
    //            $company['chargeTimes_deli'] = $moneyLogService->counts($deli_where);
                $company['chargeMoney_rmb'] = $moneyLogService->sums($rmb_where,'money');
    //            $company['chargeMoney_deli'] = $moneyLogService->sums($deli_where,'money');

                $totalChargeTimes_rmb += $company['chargeTimes_rmb'];
    //            $totalChargeMoney_deli += $company['chargeMoney_deli'];
    //            $totalChargeTimes_deli += $company['chargeTimes_deli'];
                $totalChargeMoney_rmb += $company['chargeMoney_rmb'];
            }
        }else{
            $old_system_url = config('old_system_url');
            $url = $old_system_url;
            $post_data = [
                'type'  => des_encrypt('1'),
                "startDate" => des_encrypt($startdate),
                'endDate' => des_encrypt($enddate)
            ];
            $result_api = send_post($url,$post_data);
            $result_api = des_decrypt($result_api);
            $result = json_decode($result_api,true);
            if(!is_array($result)){
                Log::record(['获取旧系统数据失败' => $result_api],'error');
            }
            $result = is_array($result) ? $result : [];
            $companys = [];
            foreach($result as $item){
                $companys[] =[
                    'company_name' => $item[0],
                    'chargeTimes_rmb' => $item[1],
                    'chargeMoney_rmb' => $item[2],
                ];
            }
            $totalChargeTimes_rmb = array_sum(array_column($companys,'chargeTimes_rmb'));
            $totalChargeMoney_rmb = array_sum(array_column($companys,'chargeMoney_rmb'));
        }
        return [
            'companys' => $companys,
            'totalChargeTimes_rmb' => $totalChargeTimes_rmb,
            'totalChargeMoney_rmb' => $totalChargeMoney_rmb,
            'totalChargeTimes_deli' => $totalChargeTimes_deli,
            'totalChargeMoney_deli' => $totalChargeMoney_deli,
        ];
    }

    /**
     * 充值明细
     * @return \think\response\View
     */
    public function chargeDetail(){
        $company_name = input('company_name');
        $M_Code = input('M_Code');
        $channel = input('channel/d');
        $source = input('source','new');
        //$money_type = input('money_type/d');
        $money_type = MONEY_TYPE_RMB; //现在只能查人民币
        $endDate = input('endDate',date('Y-m-d'));
        $startDate = input('startDate',date('Y-m-d'));

        if($source == 'new'){
            if( $company_name ){
                $company = (new CompanyService())->findInfo(['status' => COMPANY_STATUS_NORMAL,'company_name' => $company_name],'company_name,desc');
                $meter_where['company_id'] = $company['id'];

            }
            if( $M_Code ){
                $meter_where['M_Code'] = $M_Code;
            }
            if(isset($meter_where)){
                $meter_where['meter_life'] = METER_LIFE_ACTIVE;
                $meters = (new MeterService())->selectInfo($meter_where,'id');
                $meterIds = array_column(array_map(function($item){return $item->toArray();},$meters),'id');
                $moneylog_where['from'] = ['in',$meterIds];
            }
            $moneyLogService = new MoneyLogService();
            $moneylog_where['type'] = MONEY_PAY;
            $moneylog_where['to'] = null;
            if($channel){
                $moneylog_where['channel'] = $channel;
            }
            if($money_type){
                $moneylog_where['money_type'] = $money_type;
            }
            $moneylog_where['create_time'] = ['between',[strtotime($startDate.' 00:00:00'),strtotime($endDate.' 23:59:59')]];
            $moneylogs = $moneyLogService->getInfoPaginate($moneylog_where,['company_name' => $company_name,'M_Code' => $M_Code,'channel' => $channel,'startDate' => $startDate,'endDate' => $endDate]);

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
        }else{
            $old_system_url = config('old_system_url');
            $url = $old_system_url;
            $post_data = [
                'type'      => des_encrypt('2'),
                'startDate' => des_encrypt($startDate),
                'endDate' => des_encrypt($endDate),
                'M_Code' => $M_Code ? des_encrypt($M_Code) : null,
                'company' => $company_name ? des_encrypt($company_name) : null,
                'page'  => des_encrypt('-1'),
                'pagesize' => des_encrypt('10'),
            ];
            $result_api = send_post($url,$post_data);
            $result_api = des_decrypt($result_api);
            $result = json_decode($result_api,true);
            if(!is_array($result)){
                Log::record(['获取旧系统数据失败' => $result_api],'error');
            }
            $result = is_array($result) ? $result : [];
            $moneylogs = [];
            foreach($result as $item){
                $moneylogs[] = [
                    'meter' => ['M_Code' => $item[1],'consumer' => ['username' => $item[2]]],
                    'money' => $item[3],
                    'channel' => $item[4],
                    'create_time' => $item[6]
                ];
            }

            $total[] = [
                'total' => array_sum(array_column($moneylogs,'money'))
            ];
        }

        $this->assign('moneylogs',$moneylogs);
        $this->assign('company_name',$company_name);
        $this->assign('M_Code',$M_Code);
        $this->assign('channel',$channel);
        $this->assign('money_type',$money_type);
        $this->assign('startDate',$startDate);
        $this->assign('endDate',$endDate);
        $this->assign('source',$source);

        $channels = config('extra_config.meter_charge_type');
        $this->assign('channels',$channels);
        $moneytypes = config('moneytypes');
        $this->assign('moneytypes',$moneytypes);
        $this->assign('total',$total);
        return view();
    }

    /**
     *下载充值明细
     */
    public function downloadChargeDetail(){
        $company_name = input('company_name');
        $M_Code = input('M_Code');
        $channel = input('channel/d');
        $source = input('source','new');
        //$money_type = input('money_type/d');
        $money_type = MONEY_TYPE_RMB; //现在只能查人民币
        $endDate = input('endDate',date('Y-m-d'));
        $startDate = input('startDate',date('Y-m-d'));
        if($source == 'new'){
            if( $company_name ){
                $company = (new CompanyService())->findInfo(['status' => COMPANY_STATUS_NORMAL,'company_name' => $company_name],'company_name,desc');
                $meter_where['company_id'] = $company['id'];

            }
            if( $M_Code ){
                $meter_where['M_Code'] = $M_Code;
            }
            if(isset($meter_where)){
                $meter_where['meter_life'] = METER_LIFE_ACTIVE;
                $meters = (new MeterService())->selectInfo($meter_where,'id');
                $meterIds = array_column(array_map(function($item){return $item->toArray();},$meters),'id');
                $moneylog_where['from'] = ['in',$meterIds];
            }
            $moneyLogService = new MoneyLogService();
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
        }else{
            $old_system_url = config('old_system_url');
            $url = $old_system_url;
            $post_data = [
                'type'      => des_encrypt('2'),
                "startDate" => des_encrypt($startDate),
                'endDate' => des_encrypt($endDate),
                'M_Code' => $M_Code ? des_encrypt($M_Code) : null,
                'company' => $company_name ? des_encrypt($company_name) : null,
                'page'  => des_encrypt('-1'),
                'pagesize' => des_encrypt('10'),
            ];
            $result_api = send_post($url,$post_data);
            $result_api = des_decrypt($result_api);
            $result = json_decode($result_api,true);
            if(!is_array($result)){
                Log::record(['获取旧系统数据失败' => $result_api],'error');
            }
            $result = is_array($result) ? $result : [];
            $moneylogs = [];
            foreach($result as $item){
                $moneylogs[] = [
                    'meter' => ['M_Code' => $item[1],'consumer' => ['username' => $item[2]]],
                    'money' => $item[3],
                    'channel' => $item[4],
                    'create_time' => $item[6]
                ];
            }

            $total[] = [
                'total' => array_sum(array_column($moneylogs,'money'))
            ];
        }
       (new MoneyLogService())->downloadChargeDetail($moneylogs,$company_name.$M_Code."充值明细($source)".date('Y-m-d'),$company_name.$M_Code."充值明细($source)",$startDate,$endDate,$total,$source);
    }

    /**
     * 充值类型统计
     * @return \think\response\View
     */
    public function chargeTypeReport(){
        $company_name = input('company_name');
        $source = input('source','new');
        $startDate = input('startDate',date('Y-m-d',strtotime('-1 day')));
        $endDate = input('endDate',date('Y-m-d'));
        if($source == 'new'){
            if( $company_name ){
                $company = (new CompanyService())->findInfo(['status' => COMPANY_STATUS_NORMAL,'company_name' => $company_name],'id');
                $where['company_id'] = $company['id'];
            }
            $where['create_time'] = ['between',[strtotime($startDate.' 00:00:00'),strtotime($endDate.' 23:59:59')]];
            $moneyLogService = new MoneyLogService();
            $channels = config('extra_config.meter_charge_type');
            foreach($channels as $index => $channel){
                $where['channel'] = $index;
                $where['money_type'] = MONEY_TYPE_RMB;
                $where['type'] = MONEY_PAY;
                $chargeTimes = $moneyLogService->counts($where);
                $chargeMoney = $moneyLogService->sums($where,'money');
                $reports[] = [
                    'typeName'  => $channel,
                    'times' => $chargeTimes,
                    'money' => $chargeMoney,
                ];
            }
        }else{
            $old_system_url = config('old_system_url');
            $url = $old_system_url;
            $post_data = [
                'type'      => des_encrypt('3'),
                "startDate" => des_encrypt($startDate),
                'endDate' => des_encrypt($endDate),
                'company' => $company_name ? des_encrypt($company_name) : null,
            ];
            $result_api = send_post($url,$post_data);
            $result_api = des_decrypt($result_api);
            $result = json_decode($result_api,true);
            if(!is_array($result)){
                Log::record(['获取旧系统数据失败' => $result_api],'error');
            }
            $result = is_array($result) ? $result : [];
            $reports = [];
            foreach($result as $item){
                $reports[] = [
                    'typeName' => $item[0],
                    'times' => $item[1],
                    'money' => $item[2],
                ];
            }
        }
        $this->assign('company_name',$company_name);
        $this->assign('source',$source);
        $this->assign('startDate',$startDate);
        $this->assign('endDate',$endDate);
        $this->assign('reports',$reports);
        return view();
    }
}