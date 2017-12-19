<?php
namespace app\admin\controller;

class Logrecord extends Admin
{
    /*
     * 操作日志的查询功能
     * */
    function search(){
        $user_name  = input( 'user_name' );
        $remark     = input( 'remark' );
        $start_time = input( 'start_time' );
        $end_time   = input( 'end_time' );
        $logtypes = config('logtypes');
        $this->assign('logtypes',$logtypes);
        $users = model('User')->getUserInfo(['company_id'=>$this->company_id],'select','id,username');
        $this->assign('users',$users);
        $where['company_id'] = $this->company_id;
        $where['type'] = PLATFORM_ADMIN ;
        if($user_name){
            $where['user_id'] = $user_name;
        }
        if($remark){
            $where['remark'] = $remark;
        }
        if($start_time){
            $where['create_time'] = ['>',strtotime($start_time." 00:00:00")];
        }
        if($end_time){
            $where['create_time'] = ['<',strtotime($end_time." 23:59:59")];
        }
        if($start_time&&$end_time){
            $where['create_time'] = ['between',[strtotime($start_time." 00:00:00"),strtotime($end_time." 23:59:59")]];
        }
        $data['user_name']  = $user_name;
        $data['remark']     = $remark;
        $data['start_time'] = $start_time;
        $data['end_time']   = $end_time;
        $logrecord = model('LogRecord')->getLogrecord( $where , $data);
//        var_dump($logrecord[0]['data']);die;
        //对取出来的数据按照remark字段的值进行解析；返回的结果重新赋值给$logrecord;
        foreach($logrecord as & $vol){
            switch($vol['remark']){
                case 'Edit Meter':
                    $vol['data'] = model('LogRecord')->MeterUpdate($vol['data']);
                    break;
                case 'Save Meter':
                    $vol['data'] = model('LogRecord')->MeterBinding($vol['data']);
                    break;
                case 'Delete Meter':
                    $vol['data'] = model('LogRecord')->MeterDelete($vol['data']);
                    break;
                case 'Delete Area':
                case 'Delete AuthRule':
                case 'Delete Blacklist Param':
                case 'Delete MeterParam':
                case 'Delete Price':
                case 'Delete Role':
                case 'Delete User':
                    $vol['data'] = model('LogRecord')->commonDeleteTrans($vol['data']);
                    break;
                case 'Download Price':
                case 'Download Meterparam':
                $vol['data'] = model('LogRecord')->downloadTrans($vol['data']);
                    break;
                case 'Login succeed':
                    $vol['data'] = model('LogRecord')->LoginSucceed($vol['data']);
                    break;
                case 'Logout succeed':
                    $vol['data'] = model('LogRecord')->Loginout($vol['data']);
                    break;
                default:
                    $vol['data'] = model('LogRecord')->common_trans($vol['data']);
                    break;
            }
        }
        $this->assign('user_name',$user_name);
        $this->assign('remark',$remark);
        $this->assign('start_time',$start_time);
        $this->assign('end_time',$end_time);
        $this->assign('logrecord',$logrecord);
        return $this->fetch();
    }

}
