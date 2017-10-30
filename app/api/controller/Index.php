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
        if( model('app\admin\model\meter')->getAllMeterInfo(['M_Code' => $M_Code],'find') ){
            $setOldMetersWhere['M_Code'] = $M_Code;
            $setOldMetersData['meter_life'] = METER_LIFE_INACTIVE;
            if( !model('app\admin\model\meter')->updateMeter($setOldMetersData,'Meter.init_old',$setOldMetersWhere) ){
                $error = model('app\admin\model\meter')->getError();
                Log::record(['更新旧表生命周期字段失败' => $error,'data' => $setOldMetersData]);
                exception('更新旧表生命周期字段失败: '.$error,ERROR_CODE_DATA_ILLEGAL);
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
        if( !$newMeterId = model('app\admin\model\meter')->InitMeter($newMeterData,'Meter.init_new') ){
            $error = model('app\admin\model\meter')->getError();
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
        if( !model('app\admin\model\meterData')->upsert($data['M_Code'],$data,'report') ){
            $error = model('app\admin\model\meterData')->getError();
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
        if( !$meterInfo = model('app\admin\model\meter')->getMeterInfo($where,'find',$field) ){
            Log::record(['没有符合上报数据表号的数据' => 0,'data' => $M_Code],'error');
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
        if( !model('app\admin\model\meter')->updateMeter($meterData,'Meter.report') ){
            Log::record(['同步表具信息失败' => model('app\admin\model\meter')->getError(),'data' => $data],'error');
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
        //如果存在上次任务的执行结果
        if( $lastSeq ){
            //更新小于$lastSeq的task状态
            $where['meter_id'] = $meter_id;
            $where['seq_id'] = ['<',$lastSeq];
            $updatePre['status'] = TASK_SUCCESS;
            $updatePre['update_time'] = time();
            db($this->taskTableName)->where(['meter_id' => $meter_id, 'seq_id' => ['<',$lastSeq], 'status' => TASK_WAITING])->update($updatePre);
            //更新$lastSeq的task状态
            if( $lastSeqStatus ) {
                $updateCur['status'] = TASK_SUCCESS;
            }else{
                $updateCur['status'] = TASK_FAIL;
            }
            $updateCur['update_time'] = time();
            db($this->taskTableName)->where(['meter_id' => $meter_id, 'seq_id' => $lastSeq])->update($updateCur);
            //task执行失败恢复交易金额
            if( $updateCur['status'] === TASK_FAIL ){
                $task = db($this->taskTableName)->where(['meter_id' => $meter_id, 'seq_id' => $lastSeq])->field('money_log_id')->find();
                if( isset($task['money_log_id']) ){ //如果是消费task,则恢复消费金额数据
                    $money_log_info = model('MoneyLog')->getMoneyLog(['id' => $task['money_log_id']],'find');
                    //moneylog插入失败task记录
                    $new_money_log_data = $money_log_info->toArray();
                    $new_money_log_data['fail_meter_log_id'] = $new_money_log_data['id'];
                    $new_money_log_data['fail_task_id'] = $task['id'];
                    $new_money_log_data['dealStatus'] = MONEYLOG_FAIL_DEAL_STATUS_WAITING;
                    $new_money_log_data['create_time'] = time();
                    $new_money_log_data['update_time'] = time();
                    unset($new_money_log_data['id']);
                    if( !$moneyLogId = model('MoneyLog')->add($new_money_log_data) ){
                        Log::record('moneyLog添加失败: '.$new_money_log_data,'error');
                        exception('moneyLog添加失败',ERROR_CODE_DATA_ILLEGAL);
                    }
                    if( isset($money_log_info['from']) && !empty($money_log_info['from']) && isset($money_log_info['to']) && !empty($money_log_info['to']) ){ //人对人
                        if( $money_log_info['money_type'] == MONEY_PERSON ){
                            model('app\admin\model\Meter')->updateMoney($money_log_info['from'],'inc','balance_deli',$money_log_info['money']);
                            model('app\admin\model\Meter')->updateMoney($money_log_info['to'],'dec','balance_deli',$money_log_info['money']);
                        }
                    }elseif( isset($money_log_info['from']) && !empty($money_log_info['from']) ){
                        if( $money_log_info['money_type'] == MONEY_PAY ){
                            model('app\admin\model\Meter')->updateMoney($money_log_info['from'],'inc','balance_rmb',$money_log_info['money']);
                        }elseif($money_log_info['money_type'] == MONEY_PERSON ){
                            model('app\admin\model\Meter')->updateMoney($money_log_info['from'],'inc','balance_deli',$money_log_info['money']);
                        }
                    }elseif( isset($money_log_info['to']) && !empty($money_log_info['to']) ){
                        if( $money_log_info['money_type'] == MONEY_PAY ){
                            model('app\admin\model\Meter')->updateMoney($money_log_info['to'],'dec','balance_rmb',$money_log_info['money']);
                        }elseif($money_log_info['money_type'] == MONEY_PERSON ){
                            model('app\admin\model\Meter')->updateMoney($money_log_info['to'],'dec','balance_deli',$money_log_info['money']);
                        }
                    }
                }
            }
        }elseif($lastSeq === null){ //如果是初始化表具,增加task自增seq_id记录
            $autoIncData['name'] = 'task';
            $autoIncData['meter_id'] = $meter_id;
            $autoIncData['seq_id'] = 0;
            initAuthoIncId('autoinc',$autoIncData);
        }
        //获取下派新任务
        $newTask = db($this->taskTableName)->where(['meter_id' => $meter_id,'status' => TASK_WAITING,'seq_id' => ['>',$lastSeq ? $lastSeq : 0]])->order('seq','asc')->find();

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

}