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
        $where['company_id'] = ['neq',''];
        if($user_name){
            $user = model('User')->getUserInfo(['username'=>$user_name], 'find','id');
            $where['user_id'] = $user['id'];
        }
        if($remark){
            $where['remark'] = $remark;
        }
        if($start_time){
            $where['create_time'] = ['>',strtotime($start_time)];
        }
        if($end_time){
            $where['create_time'] = ['<',strtotime($end_time)];
        }
        if($start_time&&$end_time){
            $where['create_time'] = ['between',[strtotime($start_time),strtotime($end_time)]];
        }
        $data['user_name']  = $user_name;
        $data['remark']     = $remark;
        $data['start_time'] = $start_time;
        $data['end_time']   = $end_time;
        $logrecord = model('LogRecord')->getLogrecord( $where , $data);
        $this->assign('logrecord',$logrecord);
        return $this->fetch();
    }

}
