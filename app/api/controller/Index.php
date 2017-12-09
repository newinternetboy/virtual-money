<?php

namespace app\api\controller;

use think\Controller;
use think\Log;
use think\Loader;
use think\Db;

class Index extends Controller
{

    //发送表具任务表
    protected $taskTableName = 'task';

    /**
     * 映射字段
     * @var array
     */
    public $fields = [
        '' => '',
    ];

    /**
     * 不需要入库的字段
     * @var array
     */
    public $unset_fields = [
        'initFlag',
    ];

    /**
     * 表具上报api
     * @return \think\response\Json
     */
    public function report(){
        $ret['code'] = 200;
        $ret['msg'] = '操作成功';
        try{
            $data = input('post.');
            $data = $this->parseData($data); //处理上传数据
            if( !isset($data['M_Code']) ){
                exception('上传数据不合法',ERROR_CODE_DATA_ILLEGAL);
            }
            //初始化新表具
            if( isset($data['initFlag']) && $data['initFlag'] ){
                //unset不需要入库的字段
                $data = $this->generateData($data);
                //旧表生命周期结束
                $this->setOldMetersInactive($data['M_Code']);
                //单例模式,清空已实例化的数据库对象
                Loader::clearInstance();
                //新表具入库
                $newMeterId = $this->InitNewMeter($data);
                //上报数据入库
                $data['meter_id'] = $newMeterId;
                $this->insertMeterData($data,METER_INIT);
                //处理task
                $newTask = $this->handleTask($newMeterId);
            }else{//上报数据
                //unset不需要入库的字段
                $data = $this->generateData($data);
                //根据表号获取表具信息
                $meterInfo = $this->getMeterInfo($data['M_Code']);
                //检查表具余额是否需要添加提醒
                $this->checkBalance($data,$meterInfo['id']);
                //上报数据入库
                $this->insertMeterData($data,METER_REPORT,$meterInfo);
                //更新累计流量表
                $this->updateMonthFlow($data['totalCube'] - $meterInfo['totalCube'],$data['totalCost']-$meterInfo['totalCost'],$meterInfo);
                //更新表具信息
                $this->updateMeter($data,$meterInfo);
                //处理task
                $newTask = $this->handleTask($meterInfo['id'],isset($data['seq']) ? intval($data['seq']) : '',isset($data['seqStatus']) ? $data['seqStatus'] : '');
            }
            if( $newTask ){
                $ret['task'] = $newTask;
            }
        }catch (\Exception $e){
            $ret['code'] = $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return $this->generateRet($ret);
    }

    /**
     *将表具上报的字段转换成meter_data表中的字段名
     * @param $data
     * @return array
     */
    private function parseData($data){
//        $result = [];
//        foreach( $this->fields as $key => $field ){
//            $result[$field] = $data[$key];
//        }
        $data['totalCube'] = floatval($data['totalCube']);
        $data['initialCube'] = floatval($data['initialCube']);
        $data['balance'] = floatval($data['balance']);
        $data['totalCost'] = floatval($data['totalCost']);
        return $data;
    }

    /**
     * 处理返回结果
     * @param $ret
     * @return \think\response\Json
     */
    private function generateRet($ret){
        return json($ret);
    }

    /**
     * 根据表号,将旧表具的生命周期置为结束状态
     * @param $M_Code
     */
    private function setOldMetersInactive($M_Code){
        $oldMeters = model('app\admin\model\Meter')->getAllMeterInfo(['M_Code' => $M_Code,'meter_life' => METER_LIFE_ACTIVE],'select');
        if( !empty($oldMeters) ){
            //活跃表生命周期置为结束
            $setOldMetersWhere['M_Code'] = $M_Code;
            $setOldMetersWhere['meter_life'] = METER_LIFE_ACTIVE;
            $setOldMetersData['meter_life'] = METER_LIFE_INACTIVE;
            if( !model('app\admin\model\Meter')->updateMeter($setOldMetersData,'Meter.init_old',$setOldMetersWhere) ){
                $error = model('app\admin\model\Meter')->getError();
                Log::record(['更新旧表生命周期字段失败' => $error,'data' => $oldMeters]);
                exception('更新旧表生命周期字段失败: '.$error,ERROR_CODE_DATA_ILLEGAL);
            }
            //如果表具绑定了用户和开店,则通通关闭
            foreach($oldMeters as $oldMeter){
                if(isset($oldMeter['U_ID'])){
                    //更新用户状态
                    if(model('app\admin\model\Consumer')->where(['consumer_state' => CONSUMER_STATE_NORMAL,'id' => $oldMeter['U_ID']])->find()){
                        $updateOldData['id'] = $oldMeter['U_ID'];
                        $updateOldData['consumer_state'] = CONSUMER_STATE_DISABLE;
                        if( !model('app\admin\model\Consumer')->upsertConsumer($updateOldData,'Consumer.setOld') ){
                            $error = model('app\admin\model\Consumer')->getError();
                            Log::record(['表具初始化更新旧用户状态失败' => $error,'data' => $updateOldData],'error');
                            exception("表具初始化更新旧用户状态失败:".$error,ERROR_CODE_DATA_ILLEGAL);
                        }
                    }
                    //关闭用户的个人店铺
                    if($shopInfo = model('app\admin\model\Shop')->where(['uid' => $oldMeter['U_ID'],'status' => SHOP_STATUS_OPEN])->find()){
                        if(!model('app\admin\model\Shop')->where(['id' => $shopInfo['id']])->update(['status' => SHOP_STATUS_CLOSE,'update_time' => time()])){
                            $error = model('app\admin\model\Shop')->getError();
                            Log::record(['表具初始化更新用户店铺状态失败' => $error,'data' => $shopInfo['id']],'error');
                            exception("表具初始化更新用户店铺状态失败:".$error,ERROR_CODE_DATA_ILLEGAL);
                        }
                    }
                }
            }
        }
    }

    /**
     * 初始化新表具
     * @param $M_Code
     */
    private function initNewMeter($newMeterData){
        $newMeterData['meter_status'] = METER_STATUS_NEW;
        $newMeterData['meter_life'] = METER_LIFE_ACTIVE;
        $newMeterData['balance_deli'] = 0;
        $newMeterData['balance_rmb'] = 0;
        $newMeterData['company_id'] = SHUANGDELI_ID;
        if( !$newMeterId = model('app\admin\model\Meter')->InitMeter($newMeterData,'Meter.init_new') ){
            $error = model('app\admin\model\Meter')->getError();
            Log::record(['新表初始化失败' => $error,'data' => $newMeterData]);
            exception('新表初始化失败: '.$error,ERROR_CODE_DATA_ILLEGAL);
        }
        return $newMeterId;
    }

    /**
     * 上报数据入库
     * @param $data
     * @param $action_type
     * @param $meterInfo
     */
    private function insertMeterData($data, $action_type,$meterInfo = []){
        switch($action_type){
            case METER_INIT:
                $data['source_type'] = METER;
                $data['action_type'] = $action_type;
                break;
            case METER_REPORT:
                $data['source_type'] = METER;
                $data['action_type'] = $action_type;
                $data['meter_id'] = $meterInfo['id'];
                $data['diffCost'] = $data['totalCost'] - $meterInfo['totalCost'];
                $data['diffCube'] = $data['totalCube'] - $meterInfo['totalCube'];
                //表具绑定用户,才插入用户id
                if(isset($meterInfo['U_ID'])){
                    $data['U_ID'] = $meterInfo['U_ID'];
                }
                //表具绑定公司,才插入公司id
                if(isset($meterInfo['company_id'])){
                    $data['company_id'] = $meterInfo['company_id'];
                }
                break;
        }
        if( !model('app\admin\model\MeterData')->upsert($data['M_Code'],$data,'report') ){
            $error = model('app\admin\model\MeterData')->getError();
            Log::record(['上报数据入库失败' => $error,'data' => $data]);
            exception('上报数据入库失败: '.$error,ERROR_CODE_DATA_ILLEGAL);
        }
    }

    /**
     * unset不需要入库的字段
     * @param $data
     * @return mixed
     */
    private function generateData($data){
        //unset useless fields
        foreach($this->unset_fields as $field){
            unset($data[$field]);
        }
        return $data;
    }

    /**
     * 根据表号获取表具信息
     * @param $M_Code
     * @return mixed
     */
    private function getMeterInfo($M_Code){
        $where['M_Code'] = $M_Code;
        $where['meter_life'] = METER_LIFE_ACTIVE;
        $field = 'M_Code,U_ID,company_id,totalCube,totalCost';
        if( !$meterInfo = model('app\admin\model\Meter')->getMeterInfo($where,'find',$field) ){
            Log::record(['没有符合上报数据表号的数据' => $M_Code],'error');
            exception('没有符合上报数据表号的数据',ERROR_CODE_DATA_ILLEGAL);
        }
        return $meterInfo;
    }

    /**
     * 同步表具数据
     * @param $data
     * @param $meterInfo
     */
    private function updateMeter($data, $meterInfo){
        $meterData['id'] = $meterInfo['id'];
        $meterData['initialCube'] = $data['initialCube'];
        $meterData['totalCube'] = $data['totalCube'];
        $meterData['balance'] = $data['balance'];
        $meterData['totalCost'] = $data['totalCost'];
        if( !model('app\admin\model\Meter')->updateMeter($meterData,'Meter.report') ){
            Log::record(['同步表具信息失败' => model('app\admin\model\Meter')->getError(),'data' => $data],'error');
            exception('同步表具信息失败',ERROR_CODE_DATA_ILLEGAL);
        }
    }

    /**
     * 更新按月统计流量使用情况表
     * 未包装的表具也允许上报数据,在上报时,一旦发现表具绑定了用户,则更新绑定用户和公司信息
     * @param $diffCube 新增流量
     * @param $diffCost 新增金额
     * @param $meterInfo 当前表具信息
     * @throws \think\Exception
     */
    private function updateMonthFlow($diffCube, $diffCost, $meterInfo){
        $tableName = MONTH_FLOW_TABLE_NAME.date('Y');
        $month = date('M');
        if( $info = db($tableName)->where(['meter_id' => $meterInfo['id']])->find() ){
            $updateData['id'] = $info['id'];
            $updateData['update_time'] = time();
            //如果表具之前一直未绑定用户,此次上报时表具已绑定了用户,则需插入用户id
            if( isset($meterInfo['U_ID']) && !isset($info['U_ID']) ){
                $updateData['U_ID'] = $meterInfo['U_ID'];
            }
            //如果表具之前一直未绑定公司,此次上报时表具已绑定了用户,则需插入公司id
            if( isset($meterInfo['company_id']) && !isset($info['company_id']) ){
                $updateData['company_id'] = $meterInfo['company_id'];
            }
            if( isset($info[$month]) ){
                $updateData[$month] = $info[$month] + $diffCube;
            }else{
                $updateData[$month] = $diffCube;
            }
            if( isset($info[$month.'_cost']) ){
                $updateData[$month.'_cost'] = $info[$month.'_cost'] + $diffCost;
            }else{
                $updateData[$month.'_cost'] = $diffCost;
            }
            db($tableName)->update($updateData);
        }else{
            $insertData['M_Code'] = $meterInfo['M_Code'];
            $insertData['meter_id'] = $meterInfo['id'];
            $insertData[$month] = $diffCube;
            $insertData[$month.'_cost'] = $diffCost;
            $insertData['create_time'] = time();
            //表具绑定用户,才插入用户id
            if(isset($meterInfo['U_ID'])){
                $insertData['U_ID'] = $meterInfo['U_ID'];
            }
            //表具绑定公司,才插入公司id
            if(isset($meterInfo['company_id'])){
                $insertData['company_id'] = $meterInfo['company_id'];
            }
            db($tableName)->insert($insertData);
        }
    }

    /**
     * 处理下派任务
     * @param $meter_id
     * @param null $lastSeq
     * @param null $lastSeqStatus
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\Exception
     */
    private function handleTask($meter_id, $lastSeq = null, $lastSeqStatus = null){
        //如果存在上次任务的seq_id,则更新此task的状态
        if( $lastSeq ){
            //更新小于$lastSeq的task状态
            $where['meter_id'] = $meter_id;
            $where['seq_id'] = ['<',$lastSeq];
            $updatePre['status'] = TASK_SUCCESS;
            $updatePre['update_time'] = time();
            if( db($this->taskTableName)->where(['meter_id' => $meter_id, 'seq_id' => ['<',$lastSeq], 'status' => ['in',[TASK_WAITING,TASK_SENT]]])->find() ){
                //更新待下发更改表具余额任务 中的余额
                $balance_rmb =  db($this->taskTableName)->where(['meter_id' => $meter_id,'seq_id' => ['<',$lastSeq],'status' => ['in',[TASK_WAITING,TASK_SENT],'money_log_id' => ['exists',true]]])->sum('balance_rmb');
                if( $balance_rmb != 0 ){
                    if(!model('app\admin\model\Meter')->updateMoney($meter_id,'dec','balance_rmb',$balance_rmb)){
                        Log::record(['task失败,更新sum(balance_rmb)失败' => model('app\admin\model\Meter')->getError(),'meter_id' => $meter_id,'balance_rmb' => $balance_rmb],'error');
                        exception('task失败,更新sum(balance_rmb)失败',ERROR_CODE_DATA_ILLEGAL);
                    }
                }
                if(!db($this->taskTableName)->where(['meter_id' => $meter_id, 'seq_id' => ['<',$lastSeq], 'status' => ['in',[TASK_WAITING,TASK_SENT]]])->update($updatePre) ){
                    Log::record(["更新Seq: $lastSeq 以前的task失败,meter_id" => $meter_id],'error');
                    exception("更新Seq: $lastSeq 以前的task失败",ERROR_CODE_DATA_ILLEGAL);
                }
            }
            //获取上次执行的task信息
            $task = db($this->taskTableName)->where(['meter_id' => $meter_id, 'seq_id' => $lastSeq])->field('money_log_id,balance_rmb,status')->find();
            //保护机制,如果返回seq_id的task已经确认处理过,则不进行状态更新操作,避免重复操作导致数据错乱
            if(in_array($task['status'],[TASK_WAITING,TASK_SENT])){
                //标记task执行状态
                if( $lastSeqStatus ) {
                    $updateCur['status'] = TASK_SUCCESS;
                }else{
                    $updateCur['status'] = TASK_FAIL;
                }
                //如果是消费task成功,则需要扣除消费金额数据
                if($updateCur['status'] == TASK_SUCCESS){
                    if( isset($task['money_log_id']) && $task['balance_rmb'] != 0 ){//如果是缴费task,抵扣meter表的balance_rmb字段金额
                        if(!model('app\admin\model\Meter')->updateMoney($meter_id,'dec','balance_rmb',$task['balance_rmb'])){
                            Log::record(['task失败,更新balance_rmb失败' => model('app\admin\model\Meter')->getError(),'meter_id' => $meter_id,'balance_rmb' => $task['balance_rmb']],'error');
                            exception('task失败,更新balance_rmb失败',ERROR_CODE_DATA_ILLEGAL);
                        }
                    }
                }
//                //task执行失败,如果是充值则插入失败记录
//                if( $updateCur['status'] === TASK_FAIL ){
//                    if( isset($task['money_log_id']) ){ //如果是消费task,则恢复消费金额数据
//                        $money_log_info = model('MoneyLog')->getMoneyLog(['id' => $task['money_log_id']],'find');
//                        //moneylog插入失败task记录
//                        $new_money_log_data = $money_log_info->toArray();
//                        $new_money_log_data['channel'] = MONEY_CHANNEL_MANAGE;
//                        $new_money_log_data['fail_meter_log_id'] = $money_log_info['id'];
//                        $new_money_log_data['fail_task_id'] = $task['id'];
//                        $new_money_log_data['dealStatus'] = MONEYLOG_FAIL_DEAL_STATUS_WAITING;
//                        $new_money_log_data['create_time'] = time();
//                        $new_money_log_data['update_time'] = time();
//                        unset($new_money_log_data['id']);
//                        if( !$moneyLogId = model('MoneyLog')->add($new_money_log_data) ){
//                            Log::record(['task失败,moneyLog添加失败' => model('MoneyLog')->getError(),'data' => $new_money_log_data],'error');
//                            exception('task失败,moneyLog添加失败',ERROR_CODE_DATA_ILLEGAL);
//                        }
//                    }
//                }
                //更新$lastSeq的task状态
                $updateCur['update_time'] = time();
                if( !db($this->taskTableName)->where(['meter_id' => $meter_id, 'seq_id' => $lastSeq])->update($updateCur) ){
                    Log::record(["更新Seq: $lastSeq 的task失败,meter_id" => $meter_id],'error');
                    exception("更新Seq: $lastSeq 的task失败",ERROR_CODE_DATA_ILLEGAL);
                }
            }
        }elseif($lastSeq === null){ //如果是初始化表具,增加task自增seq_id记录
            $autoIncData['name'] = 'task';
            $autoIncData['meter_id'] = $meter_id;
            $autoIncData['seq_id'] = 0;
            initAuthoIncId('autoinc',$autoIncData);
        }
        //获取下派新任务
        $newTask = db($this->taskTableName)->where(['meter_id' => $meter_id,'status' => ['in',[TASK_WAITING,TASK_SENT]],'seq_id' => ['>',$lastSeq ? $lastSeq : 0]])->order('seq_id','asc')->field('seq_id')->find();
        //更新下派任务状态
        if($newTask){

            if(!db($this->taskTableName)->where(['id' => $newTask['id']])->update(['status' => TASK_SENT,'update_time' => time()])){
                Log::record(['更新task状态失败task_id' => $newTask['id']],'error');
                exception('更新task状态失败',ERROR_CODE_DATA_ILLEGAL);
            }
        }

        //$newTask = $this->parseTask($newTask);
        return $newTask;
    }

    /**
     * 解析成表具可以接收的格式
     * @param $newTask
     * @return mixed
     */
    private function parseTask($newTask){
        if($newTask){
            return $newTask['cmd'];
        }
        return $newTask;
    }

    /**
     * 判断余额,如果小于阈值,则调用添加通知api
     * @param $data
     * @param $meter_id
     */
    private function checkBalance($data, $meter_id){
        $dict_threshold_value = db('Dict')->where('type',DICT_BALANCE_THRESHOLD_VALUE)->order('create_time')->find();
        $threshold_value = $dict_threshold_value['value'];
        if($data['balance'] < $threshold_value){
            $url = config('notificationUrl');
            $post_data = [
                'meter_id' => $meter_id,
                'M_Code'   => $data['M_Code'],
                'type'     => NOTICE_TYPE_LOW_BALANCE,
                'title'    => lang('Low Balance Title'),
                'content'  => lang('Low Balance Content',[$threshold_value])
            ];
            send_post($url,$post_data);
        }
    }
}