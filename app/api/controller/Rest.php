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
                exception('请先提供表id',ERROR_CODE_DATA_ILLEGAL);
            }
            if( !$meterInfo = model('app\admin\model\Meter')->getMeterInfo(['id' => $data['meter_id'],'meter_life' => METER_LIFE_ACTIVE],'find','id') ){
                exception('表id不存在',ERROR_CODE_DATA_ILLEGAL);
            }
            $data['meter_id'] = $meterInfo['id'];
            $data['status'] = TASK_WAITING;
            $data['seq_id'] = getAutoIncId('autoinc',['name' => 'task','meter_id' => $meterInfo['id']],'seq_id',1);
            $data['create_time'] = time();
            Db::name('task')->insert($data);
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
            $data['create_time'] = time();
            $data['money'] = floatval($data['money']);

            if( isset($data['from']) && !empty($data['from']) && isset($data['to']) && !empty($data['to']) ){ //人对人
                if( $data['money_type'] == MONEY_PERSON ){
                    if(!model('app\admin\model\Meter')->updateMoney($data['from'],'dec','balance_deli',$data['money'])){
                        exception('dec得力币余额失败',ERROR_CODE_DATA_ILLEGAL);
                    }
                    if(!model('app\admin\model\Meter')->updateMoney($data['to'],'inc','balance_deli',$data['money'])){
                        exception('inc得力币余额失败',ERROR_CODE_DATA_ILLEGAL);
                    }
                }
            }elseif( isset($data['from']) && !empty($data['from']) ){
                if( $data['money_type'] == MONEY_PAY ){
                    if(!model('app\admin\model\Meter')->updateMoney($data['from'],'dec','balance_rmb',$data['money'])){
                        exception('dec人民币余额失败',ERROR_CODE_DATA_ILLEGAL);
                    }
                }elseif($data['money_type'] == MONEY_PERSON ){
                    if(!model('app\admin\model\Meter')->updateMoney($data['from'],'dec','balance_deli',$data['money'])){
                        exception('dec得力币余额失败',ERROR_CODE_DATA_ILLEGAL);
                    }
                }
            }elseif( isset($data['to']) && !empty($data['to']) ){
                if( $data['money_type'] == MONEY_PAY ){
                    if(!model('app\admin\model\Meter')->updateMoney($data['to'],'inc','balance_rmb',$data['money'])){
                        exception('inc人民币余额失败',ERROR_CODE_DATA_ILLEGAL);
                    }
                }elseif($data['money_type'] == MONEY_PERSON ){
                    if(!model('app\admin\model\Meter')->updateMoney($data['to'],'inc','balance_deli',$data['money'])){
                        exception('inc得力币余额失败',ERROR_CODE_DATA_ILLEGAL);
                    }
                }
            }
            if( !$moneyLogId = model('MoneyLog')->add($data) ){
                exception('moneyLog添加失败',ERROR_CODE_DATA_ILLEGAL);
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
            $reportLogs = $meterService->ReportLogs($meterInfo['id'],$meterInfo['M_Code'],$startDate,$endDate,'diffCube,diffCost');
            $ret['data'] = $reportLogs;
        }catch (\Exception $e){
            $ret['code'] = $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }
}