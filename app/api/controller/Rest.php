<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/10/23
 * Time: 下午12:35
 */

namespace app\api\controller;

use think\Log;
use think\Db;
use app\manage\service\MeterService;

class Rest extends LanFilter
{
    /**
     * 添加task
     * @return \think\response\Json
     */
    public function addTask(){
        $data = input('post.');
        $ret['code'] = 200;
        $ret['msg'] = '操作成功';
        try{
            if( !isset($data['meter_id']) ){
                Log::record(['添加task失败,meter_id为空' => $data],'error');
                $ret['code'] = ERROR_CODE_DATA_ILLEGAL;
                $ret['msg'] = '请先提供表id';
                return json($ret);
            }
            if( !$meterInfo = model('app\admin\model\Meter')->getMeterInfo(['id' => $data['meter_id'],'meter_life' => METER_LIFE_ACTIVE],'find','id') ){
                Log::record(['添加task失败,表id不存在' => $data],'error');
                $ret['code'] = ERROR_CODE_DATA_ILLEGAL;
                $ret['msg'] = '表id不存在';
                return json($ret);
            }
            $data['meter_id'] = $meterInfo['id'];
            $data['status'] = TASK_WAITING;
            $data['seq_id'] = getAutoIncId('autoinc',['name' => 'task','meter_id' => $meterInfo['id']],'seq_id',1);
            //改变表具余额的task,都需要此字段,值就是待下发给表具的金额,可以为负数,用于report api处理task
            if(isset($data['money_log_id'])){
                if(isset($data['balance_rmb'])){
                    $data['balance_rmb'] = floatval($data['balance_rmb']);
                }else{
                    $data['balance_rmb'] = 0;
                }
            }
            $data['create_time'] = time();
            if(!Db::name('task')->insert($data)){
                Log::record(['添加task失败' => $data],'error');
                $ret['code'] = ERROR_CODE_DATA_ILLEGAL;
                $ret['msg'] = '添加task失败';
                return json($ret);
            }
        }catch (\Exception $e){
            $ret['code'] = $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

    /**
     * moneylog相关业务api
     * @return \think\response\Json
     */
    public function moneyBusiness(){
        $data = input('post.');
        $ret['code'] = 200;
        $ret['msg'] = '操作成功';
        try{
            $data['money'] = floatval($data['money']);
            if($data['type'] == MONEY_PAY){
                if( isset($data['from']) && !empty($data['from']) && isset($data['to']) && !empty($data['to']) ){ //人对人
                    if( !$meter = model('app\admin\model\Meter')->getMeterInfo(['id' => $data['from']],'find') ){
                        Log::record(['from表具不存在' => $data],'error');
                        $ret['code'] = ERROR_CODE_DATA_ILLEGAL;
                        $ret['msg'] = '付款方不存在';
                        return json($ret);
                    }
                    if( !model('app\admin\model\Meter')->getMeterInfo(['id' => $data['to']],'find') ){
                        Log::record(['to表具不存在' => $data],'error');
                        $ret['code'] = ERROR_CODE_DATA_ILLEGAL;
                        $ret['msg'] = '收款方不存在';
                        return json($ret);
                    }
                    if( $data['money_type'] == MONEY_TYPE_RMB ){
                        if(!model('app\admin\model\Meter')->updateMoney($data['from'],'inc','balance_rmb',$data['money'])){
                            Log::record(['inc人民币余额失败' => model('app\admin\model\Meter')->getError(),'data' => $data],'error');
                            $ret['code'] = ERROR_CODE_DATA_ILLEGAL;
                            $ret['msg'] = '更新余额失败';
                            return json($ret);
                        }
                        if(!model('app\admin\model\Meter')->updateMoney($data['to'],'dec','balance_rmb',$data['money'])){
                            Log::record(['dec人民币余额失败' => model('app\admin\model\Meter')->getError(),'data' => $data],'error');
                            $ret['code'] = ERROR_CODE_DATA_ILLEGAL;
                            $ret['msg'] = '更新余额失败';
                            return json($ret);
                        }
                    }
                    if( $data['money_type'] == MONEY_TYPE_DELI ){
                        if(!model('app\admin\model\Meter')->updateMoney($data['from'],'dec','balance_deli',$data['money'])){
                            Log::record(['dec得力币余额失败' => model('app\admin\model\Meter')->getError(),'data' => $data],'error');
                            $ret['code'] = ERROR_CODE_DATA_ILLEGAL;
                            $ret['msg'] = '更新余额失败';
                            return json($ret);
                        }
                        if(!model('app\admin\model\Meter')->updateMoney($data['to'],'inc','balance_deli',$data['money'])){
                            Log::record(['inc得力币余额失败' => model('app\admin\model\Meter')->getError(),'data' => $data],'error');
                            $ret['code'] = ERROR_CODE_DATA_ILLEGAL;
                            $ret['msg'] = '更新余额失败';
                            return json($ret);
                        }
                    }
                }elseif( isset($data['from']) && !empty($data['from']) ){
                    if( !$meter = model('app\admin\model\Meter')->getMeterInfo(['id' => $data['from']],'find') ){
                        Log::record(['from表具不存在' => $data],'error');
                        $ret['code'] = ERROR_CODE_DATA_ILLEGAL;
                        $ret['msg'] = '付款方不存在';
                        return json($ret);
                    }
                    if( $data['money_type'] == MONEY_TYPE_RMB ){
                        if(!model('app\admin\model\Meter')->updateMoney($data['from'],'inc','balance_rmb',$data['money'])){
                            Log::record(['inc人民币余额失败' => model('app\admin\model\Meter')->getError(),'data' => $data],'error');
                            $ret['code'] = ERROR_CODE_DATA_ILLEGAL;
                            $ret['msg'] = '更新余额失败';
                            return json($ret);
                        }
                    }elseif($data['money_type'] == MONEY_TYPE_DELI ){
                        if(!model('app\admin\model\Meter')->updateMoney($data['from'],'dec','balance_deli',$data['money'])){
                            Log::record(['dec得力币余额失败' => model('app\admin\model\Meter')->getError(),'data' => $data],'error');
                            $ret['code'] = ERROR_CODE_DATA_ILLEGAL;
                            $ret['msg'] = '更新余额失败';
                            return json($ret);
                        }
                    }
                }else{
                    Log::record(['充值信息不符合要求' => '','data' => $data],'error');
                    $ret['code'] = ERROR_CODE_DATA_ILLEGAL;
                    $ret['msg'] = '充值信息不符合要求';
                    return json($ret);
                }
            }elseif($data['type'] == MONEY_SYSTEM_DELI){
                if( isset($data['to']) && !empty($data['to']) ){
                    if( !$meter = model('app\admin\model\Meter')->getMeterInfo(['id' => $data['to']],'find') ){
                        Log::record(['to表具不存在' => $data],'error');
                        $ret['code'] = ERROR_CODE_DATA_ILLEGAL;
                        $ret['msg'] = '收款方不存在';
                        return json($ret);
                    }
                    if($data['money_type'] == MONEY_TYPE_DELI ){
                        if(!model('app\admin\model\Meter')->updateMoney($data['to'],'inc','balance_deli',$data['money'])){
                            Log::record(['赠送得力币失败' => model('app\admin\model\Meter')->getError(),'data' => $data],'error');
                            $ret['code'] = ERROR_CODE_DATA_ILLEGAL;
                            $ret['msg'] = '更新余额失败';
                            return json($ret);
                        }
                    }
                }else{
                    Log::record(['赠送得力币信息不符合要求' => '','data' => $data],'error');
                    $ret['code'] = ERROR_CODE_DATA_ILLEGAL;
                    $ret['msg'] = '赠送得力币信息不符合要求';
                    return json($ret);
                }
            }
            $data['create_time'] = time();
            $data['company_id'] = $meter['company_id'];
            if( !$moneyLogId = model('MoneyLog')->add($data) ){
                Log::record(['moneyLog添加失败' => model('MoneyLog')->getError(),'data' => $data],'error');
                $ret['code'] = ERROR_CODE_DATA_ILLEGAL;
                $ret['msg'] = '添加消费记录失败';
                return json($ret);
            }
            $ret['moneyLogId'] = $moneyLogId;
        }catch (\Exception $e){
            $ret['code'] = $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
            Log::record(['moneyBusinessApi执行失败' => $e->getMessage(),'post' => $data,'ret' => $ret ],'error');
        }
        return json($ret);
    }

    /**
     * 表具月用量详情api
     * @return \think\response\Json
     */
    public function meterUsageDetail(){
        $searchDate = input('searchDate',date('Y-m'));
        $meter_id = input('meter_id');
        $ret['code'] = 200;
        $ret['msg'] = '操作成功';
        try{
            if(!$meter_id){
                exception("请先提供表id",ERROR_CODE_DATA_ILLEGAL);
            }
            $where['id'] = $meter_id;
            $where['meter_life'] = METER_LIFE_ACTIVE;
            $meterService = new MeterService();
            if( !$meterInfo = $meterService->findInfo($where,'M_Code') ){
                exception('表id不存在',ERROR_CODE_DATA_ILLEGAL);
            }
            $searchDate .= '-01';
            $startDate = $searchDate.' 00:00:00';
            $endDate = date('Y-m-d H:i:s',strtotime('+1 month',strtotime($searchDate))-1);
            $reportLogs = $meterService->reportLogs($meterInfo['id'],$meterInfo['M_Code'],$startDate,$endDate,'diffCube,diffCost,create_time');
            $ret['data'] = $reportLogs;
        }catch (\Exception $e){
            $ret['code'] = $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

    /**
     * 获取表具时间段内充值记录
     * @return \think\response\Json
     */
    public function meterChargeDetail(){
        $searchData = input('searchData');
        $searchData = json_decode($searchData,true);

        $ret['code'] = 200;
        $ret['msg'] = '操作成功';
        try{
            if(!$searchData){
                exception('请求数据格式错误',ERROR_CODE_DATA_ILLEGAL);
            }
            $searchDate = $searchData['searchDate'];
            $meter_id = $searchData['meter_id'];
            $type = $searchData['type'];
            $channel = $searchData['channel'];
            if(!$meter_id){
                exception("请先提供表id",ERROR_CODE_DATA_ILLEGAL);
            }
            $where['id'] = $meter_id;
            $where['meter_life'] = METER_LIFE_ACTIVE;
            $meterService = new MeterService();
            if( !$meterInfo = $meterService->findInfo($where,'M_Code') ){
                exception('表id不存在',ERROR_CODE_DATA_ILLEGAL);
            }
            $searchDate .= '-01';
            $startDate = $searchDate.' 00:00:00';
            $endDate = date('Y-m-d H:i:s',strtotime('+1 month',strtotime($searchDate))-1);
            $chargeLogs = $meterService->moneyLogs($meterInfo['id'],$startDate,$endDate,$type,$channel);
            $ret['data'] = $chargeLogs;
        }catch (\Exception $e){
            $ret['code'] = $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

    /**
     * 获取表具月使用量
     * @return \think\response\Json
     */
    public function meterMonthUsage(){
        $searchDate = input('searchDate',date('Y-m'));
        $meter_id = input('meter_id');
        $ret['code'] = 200;
        $ret['msg'] = '操作成功';
        try{
            if(!$meter_id){
                exception("请先提供表id",ERROR_CODE_DATA_ILLEGAL);
            }
            $where['id'] = $meter_id;
            $where['meter_life'] = METER_LIFE_ACTIVE;
            $meterService = new MeterService();
            if( !$meterInfo = $meterService->findInfo($where,'M_Code') ){
                exception('表id不存在',ERROR_CODE_DATA_ILLEGAL);
            }
            $searchDate .= '-01';
            $year = date('Y',strtotime($searchDate));
            $month = date('M',strtotime($searchDate));
            $condition['meter_id'] = $meter_id;
            $ret['data'] = getNamedMonthReport($year,$month,$condition);
        }catch (\Exception $e){
            $ret['code'] = $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }
}