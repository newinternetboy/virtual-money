<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/9/19
 * Time: 下午4:32
 */

namespace app\admin\controller;


/**
 * 远程阀门控制
 * Class Valve
 * @package app\admin\controller
 */
class Valve extends Admin
{
    /**
     * 首页
     * @return \think\response\View
     */
    public function index(){
        $areas = model('Area')->getList(['company_id' => $this->company_id]);
        $this->assign('areas',$areas);
        return view();
    }

    /**
     * 添加阀门控制api
     * @return mixed
     */
    public function saveData(){
        $this->mustCheckRule($this->company_id,'');
        $data = input('data');
        $data = json_decode($data,true);
        $ret['code'] = 200;
        $ret['msg'] = '操作成功';
        try{
            if( !$data ){
                exception('操作失败,信息不完整');
            }

            //任务执行时间要大于当前时间
            if( !isset($data['exectime']) || !$data['exectime'] ){
                exception('请先填写任务执行时间');
            }
            if( time() >= strtotime($data['exectime']) ){
                exception('任务执行时间要大于当前时间');
            }

            if( isset($data['valve_type']) && $data['valve_type'] == VALVE_USER ){ //指定用户控制
                if( !isset($data['M_Codes']) || !$data['M_Codes'] ){
                    exception('请先输入表号');
                }
                $m_codes = $data['M_Codes'];
                $m_codes = explode(';',$m_codes);
                $m_codes = array_map("trimMCode",$m_codes);

                //检查填写的表号是否属于当前公司
                $this->isBelongsToCompany($m_codes);

                $valveData['data'] = $m_codes;

            }elseif( isset($data['valve_type']) && $data['valve_type'] == VALVE_AREA ){ //地区用户控制
                if( !isset($data['area']) || !$data['area'] ){
                    exception('请先选择区域');
                }
                if( !model('Area')->getAreaInfo(['id' => $data['area'],'company_id' => $this->company_id],'find') ){
                    exception('您无权对该区域进行此操作');
                }
                $valveData['data'] = $data['area'];
            }else{
                exception('方式选择不合法');
            }

            $valveData['valve_type'] = $data['valve_type'];
            $valveData['option'] = $data['option'];
            $valveData['exectime'] = $data['exectime'];
            $valveData['valve_status'] = VALVE_WAITING;
            $valveData['company_id'] = $this->company_id;
            if( !model('Valve')->add($valveData,'Valve.add') ){
                exception('操作失败: '.model('Valve')->getError());
            }
        }catch (\Exception $e){
            $ret['code'] = 9999;
            $ret['msg'] = $e->getMessage();
        }
        return $ret;
    }

    /**
     * 如果是进行表号控制,检查表号是否所属该公司
     * @param $m_codes
     */
    private function isBelongsToCompany($m_codes){
        $company_id = $this->company_id;
        foreach( $m_codes as $m_code ){
            $where['M_Code'] = $m_code;
            $where['company_id'] = $company_id;
            $where['meter_status'] = METER_STATUS_BIND;
            $isBelongs = model('Meter')->getMeterInfo($where,'find');
            if( !$isBelongs ){
                exception('不能对表号 '.$m_code.' 进行此操作');
            }
        }
    }
}