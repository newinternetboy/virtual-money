<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/9/19
 * Time: 下午4:32
 */

namespace app\admin\controller;

use think\Log;
use think\Loader;

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
                exception('操作失败,信息不完整',ERROR_CODE_DATA_ILLEGAL);
            }
            if(!isset($data['option']) || !in_array($data['option'],['turn_on','turn_off'])){
                exception('操作失败,命令不合法',ERROR_CODE_DATA_ILLEGAL);
            }
            $meters = [];
            $fail = '';
            if( isset($data['valve_type']) && $data['valve_type'] == VALVE_USER ){ //指定用户控制
                if( !isset($data['M_Codes']) || !$data['M_Codes'] ){
                    exception('请先输入表号',ERROR_CODE_DATA_ILLEGAL);
                }
                $m_codes = $data['M_Codes'];
                $m_codes = explode(';',$m_codes);
                $m_codes = array_map("trimMCode",$m_codes);

                //检查填写的表号是否属于当前公司
                $this->isBelongsToCompany($m_codes);
                $meter_where['M_Code'] = ['in',$m_codes];
                $meter_where['meter_status'] = METER_STATUS_BIND;
                $meters = model('Meter')->getMeterInfo($meter_where,'select','id,M_Code');

            }elseif( isset($data['valve_type']) && $data['valve_type'] == VALVE_AREA ){ //地区用户控制
                if( !isset($data['area']) || !$data['area'] ){
                    exception('请先选择区域',ERROR_CODE_DATA_ILLEGAL);
                }
                if( !model('Area')->getAreaInfo(['id' => $data['area'],'company_id' => $this->company_id],'find') ){
                    exception('您无权对该区域进行此操作',ERROR_CODE_DATA_ILLEGAL);
                }
                $meter_where['M_Address'] = $data['area'];
                $meter_where['meter_status'] = METER_STATUS_BIND;
                $meters = model('Meter')->getMeterInfo($meter_where,'select','id,M_Code');
            }else{
                exception('方式选择不合法',ERROR_CODE_DATA_ILLEGAL);
            }

            $task['cmd'] = $data['option'];
            foreach($meters as $meter){
                $task['meter_id'] = $meter['id'];
                if(true !== $result = upsertTask($task)){
                    $failresult[] = $result;
                    $fail .= $meter['M_Code'].';';
                }
            }
            if($fail){
                Log::record(['阀门控制失败' => $fail,'data' => $failresult],'error');
                exception('表号'.$fail.'执行失败!',ERROR_CODE_DATA_ILLEGAL);
            }
            Loader::model('LogRecord')->record( 'Valve Operation',$data );
        }catch (\Exception $e){
            $ret['code'] = $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
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
                exception('不能对表号 '.$m_code.' 进行此操作',ERROR_CODE_DATA_ILLEGAL);
            }
        }
    }
}