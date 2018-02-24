<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2018/2/2
 * Time: 下午2:06
 */

namespace app\admin\controller;


/**
 * Class Alarm
 * @package app\admin\controller
 */
class Alarm extends Admin
{
    /**
     * 报警列表
     * @return \think\response\View
     */
    public function index(){
        $status = input('status/d');
        $M_Code = input('M_Code');
        $level = input('level/d');
        $startDate = input('startDate');
        $endDate = input('endDate');
        $reason = input('reason/d');
        if($M_Code){
            $where['M_Code'] = $M_Code;
        }
        if($status){
            $where['status'] = $status;
        }
        if($level){
            $where['level'] = $level;
        }
        if($startDate && $endDate){
            $where['create_time'] = ['between',[strtotime($startDate.' 00:00:00'),strtotime($endDate.' 23:59:59')]];
        }elseif($startDate){
            $where['create_time'] = ['>=',strtotime($startDate.' 00:00:00')];
        }elseif($endDate){
            $where['create_time'] = ['<=',strtotime($endDate.' 23:59:59')];
        }
        if($reason){
            $where['reason'] = $reason;
        }
        $alarm_levels = config('alarm_levels');
        $alarm_reasons = config('alarm_reasons');
        $where['company_id'] = $this->company_id;
        $alarms = model('Alarm')->where($where)->order('create_time','desc')->paginate()->appends(['status' => $status,'M_Code' => $M_Code,'level' => $level,'reason' => $reason,'startDate' => $startDate,'endDate' => $endDate]);
        $this->assign('status',$status);
        $this->assign('M_Code',$M_Code);
        $this->assign('level',$level);
        $this->assign('startDate',$startDate);
        $this->assign('endDate',$endDate);
        $this->assign('reason',$reason);
        $this->assign('alarms',$alarms);
        $this->assign('alarm_levels',$alarm_levels);
        $this->assign('alarm_reasons',$alarm_reasons);
        return view();
    }

    /**
     * 处理单条报警
     * @return \think\response\Json
     */
    public function deal(){
        $ret['code'] = 200;
        $ret['msg'] = '操作成功';
        try{
            $id = input('id');
            $desc = input('desc');
            if( !$id ){
                exception('请提供报警信息id',ERROR_CODE_DATA_ILLEGAL);
            }
            $data['id'] = $id;
            $data['status'] = ALARM_STATUS_DEAL;
            $data['desc'] = $desc;
            $data['dealer_id'] = $this->uid;
            if(!model('Alarm')->isUpdate(true)->save($data)){
                exception('报警信息处理失败',ERROR_CODE_DATA_ILLEGAL);
            }
        }catch (\Exception $e){
            $ret['code'] =  $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

    /**
     * 批量处理报警
     * @return \think\response\Json
     */
    public function dealAll(){
        $ret['code'] = 200;
        $ret['msg'] = '操作成功';
        try{
            $status = input('status/d');
            $M_Code = input('M_Code');
            $level = input('level/d');
            $startDate = input('startDate');
            $endDate = input('endDate');
            $reason = input('reason/d');
            if($M_Code){
                $where['M_Code'] = $M_Code;
            }
            if($status){
                $where['status'] = $status;
            }
            if($level){
                $where['level'] = $level;
            }
            if($startDate && $endDate){
                $where['create_time'] = ['between',[strtotime($startDate.' 00:00:00'),strtotime($endDate.' 23:59:59')]];
            }elseif($startDate){
                $where['create_time'] = ['>=',strtotime($startDate.' 00:00:00')];
            }elseif($endDate){
                $where['create_time'] = ['<=',strtotime($endDate.' 23:59:59')];
            }
            if($reason){
                $where['reason'] = $reason;
            }
            $where['company_id'] = $this->company_id;
            $where['status'] = ALARM_STATUS_WAITING;
            $data['status'] = ALARM_STATUS_DEAL;
            $data['desc'] = '批量处理';
            $data['dealer_id'] = $this->uid;
            $data['update_time'] = time();
            if(!model('Alarm')->where($where)->update($data)){
                if(model('Alarm')->getError()){
                    exception('报警信息处理失败',ERROR_CODE_DATA_ILLEGAL);
                }
            }
        }catch (\Exception $e){
            $ret['code'] =  $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }
}