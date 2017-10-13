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
        $where=[];
        if($user_name){
            $where['user_id'] = $user_name;
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
        //对取出来的数据按照remark字段的值进行解析；返回的结果重新赋值给$logrecord;
        foreach($logrecord as & $vol){
            switch($vol['remark']){
                case '修改密码':
                    $vol['data'] = model('LogRecord')->UpdatePasswd($vol['data']);
                    break;
                case '表具过户':
                    $vol['data'] = model('LogRecord')->MeterPass($vol['data']);
                    break;
                case '表具修改':
                    $vol['data'] = model('LogRecord')->MeterUpdate($vol['data']);
                    break;
                case '表具报装':
                    $vol['data'] = model('LogRecord')->MeterBinding($vol['data']);
                    break;
                case '修改黑名单属性成功':
                    $vol['data'] = model('LogRecord')->UpdateBlacklistparam($vol['data']);
                    break;
                case '修改/添加权限':
                    $vol['data'] = model('LogRecord')->UpdateandAddauth($vol['data']);
                    break;
                case '添加/修改价格':
                    $vol['data'] = model('LogRecord')->UpdateandAddprice($vol['data']);
                    break;
                case '修改权限':
                    $vol['data'] = model('LogRecord')->UpdateAuth($vol['data']);
                    break;
                case '修改/添加用户':
                    $vol['data'] = model('LogRecord')->UpdateandAdduser($vol['data']);
                    break;
                case '添加/修改区域':
                    $vol['data'] = model('LogRecord')->UpdateandAddarea($vol['data']);
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
