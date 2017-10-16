<?php

namespace app\admin\controller;


class MeterData extends Admin
{

    public function report(){
        $startTime =  date('Y-m-d',strtotime('-3 months'));
        $endTime =  date('Y-m-d');
        $this->assign('startTime',$startTime);
        $this->assign('endTime',$endTime);
        return view();
    }

    public function getReport(){
        $ret['code'] = 200;
        $ret['msg'] = '操作成功';
        try{
            $M_Code = input('M_Code');
            $startTime = input('startTime');
            $endTime = input('endTime');
            if( !$M_Code ){
                exception('请输入表号!',ERROR_CODE_DATA_ILLEGAL);
            }
            if( !$meterInfo = model('Meter')->getMeterInfo(['M_Code' => $M_Code,'company_id' => $this->company_id,'meter_status' => METER_STATUS_BIND],'find') ){
                exception('表号不存在!',ERROR_CODE_DATA_ILLEGAL);
            }
            $where['meter_id'] = $meterInfo['id'];
            $where['source_type'] = METER;
            //获取止日期记录
            $whereEndTime = $endTime ? strtotime($endTime.' 23:59:59') : time();
            $where['create_time'] = ['<=',$whereEndTime];
            $reportEnd = model('MeterData')->getMeterDataInfo($M_Code,$where,'find','totalCube,totalCost');
            //获取起日期记录
            $whereStartTime = $startTime ? strtotime($startTime.' 00:00:00') : time()-config('meterDataRangeTime');
            $where['create_time'] = ['>=',$whereStartTime];
            $reportStart =  model('MeterData')->getMeterDataInfo($M_Code,$where,'find','totalCube,totalCost','create_time','asc');
            $ret['data']['M_Code'] = $M_Code;
            $ret['data']['cube'] = $reportEnd['totalCube'] - $reportStart['totalCube'];
            $ret['data']['cost'] = $reportEnd['totalCost'] - $reportStart['totalCost'];
            $ret['data']['date'] = date('Y-m-d');
        }catch (\Exception $e){
            $ret['code'] =  $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }
}