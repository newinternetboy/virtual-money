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
            if( !$moneyLogId = model('MoneyLog')->add($data) ){
                Log::record('moneyLog添加失败: '.$data,'error');
                exception('moneyLog添加失败',ERROR_CODE_DATA_ILLEGAL);
            }
            $ret['moneyLogId'] = $moneyLogId;
            if( isset($data['from']) && !empty($data['from']) && isset($data['to']) && !empty($data['to']) ){ //人对人
                if( $data['money_type'] == MONEY_PERSON ){
                    model('app\admin\model\Meter')->updateMoney($data['from'],'dec','balance_deli',$data['money']);
                    model('app\admin\model\Meter')->updateMoney($data['to'],'inc','balance_deli',$data['money']);
                }
            }elseif( isset($data['from']) && !empty($data['from']) ){
                if( $data['money_type'] == MONEY_PAY ){
                    model('app\admin\model\Meter')->updateMoney($data['from'],'dec','balance_rmb',$data['money']);
                }elseif($data['money_type'] == MONEY_PERSON ){
                    model('app\admin\model\Meter')->updateMoney($data['from'],'dec','balance_deli',$data['money']);
                }
            }elseif( isset($data['to']) && !empty($data['to']) ){
                if( $data['money_type'] == MONEY_PAY ){
                    model('app\admin\model\Meter')->updateMoney($data['to'],'inc','balance_rmb',$data['money']);
                }elseif($data['money_type'] == MONEY_PERSON ){
                    model('app\admin\model\Meter')->updateMoney($data['to'],'inc','balance_deli',$data['money']);
                }
            }
        }catch (\Exception $e){
            $ret['code'] = $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }
}