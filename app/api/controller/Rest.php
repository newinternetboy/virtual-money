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
            $result = insertTask($data);
            if(is_array($result)){
                return json($result);
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
            $result = insertMoneyLog($data);
            if(is_array($result)){
                return json($result);
            }
            $ret['moneyLogId'] = $result;
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