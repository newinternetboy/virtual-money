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
    protected $fields = [
        'METID' =>  'M_Code',                 //表号
        //表具用量和费用信息
        'CSUMM' => 'totalCube',                 //表具当前累计用量
        'FSUMM' => 'totalCube_cost',            //表具扣费累计用量
        'ZSUMM' => 'initialCube',               //表具初始的免费用量
        'DSUM1' => 'dsum1',                     //表具日用量统计
        'DSUM2' => 'dsum2',
        'DSUM3' => 'dsum3',
        'DSUM4' => 'dsum4',
        'DSUM5' => 'dsum5',
        'DSUM6' => 'dsum6',
        'DSUM7' => 'dsum7',
        'DSUM8' => 'dsum8',
        'DSUM9' => 'dsum9',
        'DSUM10' => 'dsum10',
        'DSUM11' => 'dsum11',
        'DSUM12' => 'dsum12',
        'DSUM13' => 'dsum13',
        'DSUM14' => 'dsum14',
        'DSUM15' => 'dsum15',
        'DSUM16' => 'dsum16',
        'DSUM17' => 'dsum17',
        'DSUM18' => 'dsum18',
        'DSUM19' => 'dsum19',
        'DSUM20' => 'dsum20',
        'DSUM21' => 'dsum21',
        'DSUM22' => 'dsum22',
        'DSUM23' => 'dsum23',
        'DSUM24' => 'dsum24',
        'DSUM25' => 'dsum25',
        'DSUM26' => 'dsum26',
        'DSUM27' => 'dsum27',
        'DSUM28' => 'dsum28',
        'DSUM29' => 'dsum29',
        'DSUM30' => 'dsum30',
        'DFEE1' => 'dfee1',                     //表具日用金额统计
        'DFEE2' => 'dfee2',
        'DFEE3' => 'dfee3',
        'DFEE4' => 'dfee4',
        'DFEE5' => 'dfee5',
        'DFEE6' => 'dfee6',
        'DFEE7' => 'dfee7',
        'DFEE8' => 'dfee8',
        'DFEE9' => 'dfee9',
        'DFEE10' => 'dfee10',
        'DFEE11' => 'dfee11',
        'DFEE12' => 'dfee12',
        'DFEE13' => 'dfee13',
        'DFEE14' => 'dfee14',
        'DFEE15' => 'dfee15',
        'DFEE16' => 'dfee16',
        'DFEE17' => 'dfee17',
        'DFEE18' => 'dfee18',
        'DFEE19' => 'dfee19',
        'DFEE20' => 'dfee20',
        'DFEE21' => 'dfee21',
        'DFEE22' => 'dfee22',
        'DFEE23' => 'dfee23',
        'DFEE24' => 'dfee24',
        'DFEE25' => 'dfee25',
        'DFEE26' => 'dfee26',
        'DFEE27' => 'dfee27',
        'DFEE28' => 'dfee28',
        'DFEE29' => 'dfee29',
        'DFEE30' => 'dfee30',
        'BLANC'  => 'balance',                                  //表具余额
        //价格信息
        'STSUM' =>  'stsum',                              //表具阶梯累计用量
        'CPRICE' => 'currentPrice',                             //表具当前价格
        'BCSUM' =>  'low_cost',                                //表具最低花费(表具阶梯周期最低的消费金额)
        'LCUIN' =>  'type',                                     //阶梯周期单位(1:天 2:月 3:年)
        'LCYCL' =>  'period',                                   //阶梯周期
        'LADD1' =>  'first_val',                                //用量1
        'LPRIC1'=>  'first_price',                              //价格1
        'LADD2' =>  'second_val',                                //用量2
        'LPRIC2'=>  'second_price',                              //价格2
        'LADD3' =>  'third_val',                                //用量3
        'LPRIC3'=>  'third_price',                              //价格3
        'LADD4' =>  'fourth_val',                                //用量4
        'LPRIC4'=>  'fourth_price',                              //价格4
        'LADD5' =>  'fifth_val',                                //用量5
        'LPRIC5'=>  'fifth_price',                              //价格5
        'LADD6' =>  'sixth_val',                                //用量6
        'LPRIC6'=>  'sixth_price',                              //价格6
        'LTIME' =>  'endtime',                                  //阶梯第一周期结束时间 '19-12-31'
        //充值扣费信息
        'RECSU' =>  'total_charge',                             //累计充值金额
        'RETIM' =>  'total_charge_times',                       //累计充值次数
        'LRTIM' =>  'last_charge_times',                        //上一次充值次数
        'DEDSU' =>  'total_deduct',                             //扣费累计金额
        'DETIM' =>  'total_deduct_times',                       //扣费累计次数
        'LDTIM' =>  'last_deduct_times',                        //上一次扣费次数
        //表具运行状态信息
        'SERIP' =>  'serip',                                    //服务器ip
        'SERDO' =>  'serdo',                                    //服务器域名
        'APOSW' =>  'aposw',                                    //智能开机开关(0:关闭智能开关 1:开启)
        'TIMPW' =>  'uploadTime',                               //定时开机时间间隔 单位小时
        'CONPW' =>  'conpw',                                    //开机持续时间
        'MBPSW' =>  'mbpsw',                                    //月初开机开关(0:关闭 1:开启)
        'MEPSW' =>  'mepsw',                                    //月末开机开关(0:关闭 1:开启)
        'TPTIM' =>  'tptim',                                    //下次定时开机时间 '18-11-20 20:30:11'
        'MBTIM' =>  'mbtim',                                    //月初开机时间    '04:20:11'
        'METIM' =>  'metim',                                    //月末开机时间    '23:22:10'
        'PLUPW' =>  'transformerRatio',                         //脉冲开机数
        'VERSI' =>  'versi',                                    //版本号
        'ABBLA' =>  'lowLimit',                                 //低余额报警
        'AOVDR' =>  'overdraftLimit',                           //透支报警
        'DOORS' =>  'valve_status',                             //阀门状态 0:关闭 1:开启
        'MTIME' =>  'mtime',                                    //表具时间 '18-01-24 00:00:00'
        'POWTI' =>  'powti',                                    //开机次数
        'INSTS' =>  'insts',                                    //报装状态 0:未报装 1:已报装


        'INITF'  =>  'initFlag',                                //0:初始化 1:上报
        'FETOT' =>  'totalCost',                                //累计扣费金额
        'SENUM'       =>  'seq',                                //seqid

    ];

    //需要除以10000的字段
    protected $fields_spacial = [
        'CSUMM' => 'totalCube',                 //表具当前累计用量
        'FSUMM' => 'totalCube_cost',            //表具扣费累计用量
        'ZSUMM' => 'initialCube',               //表具初始的免费用量
        'DSUM1' => 'dsum1',                     //表具日用量统计
        'DSUM2' => 'dsum2',
        'DSUM3' => 'dsum3',
        'DSUM4' => 'dsum4',
        'DSUM5' => 'dsum5',
        'DSUM6' => 'dsum6',
        'DSUM7' => 'dsum7',
        'DSUM8' => 'dsum8',
        'DSUM9' => 'dsum9',
        'DSUM10' => 'dsum10',
        'DSUM11' => 'dsum11',
        'DSUM12' => 'dsum12',
        'DSUM13' => 'dsum13',
        'DSUM14' => 'dsum14',
        'DSUM15' => 'dsum15',
        'DSUM16' => 'dsum16',
        'DSUM17' => 'dsum17',
        'DSUM18' => 'dsum18',
        'DSUM19' => 'dsum19',
        'DSUM20' => 'dsum20',
        'DSUM21' => 'dsum21',
        'DSUM22' => 'dsum22',
        'DSUM23' => 'dsum23',
        'DSUM24' => 'dsum24',
        'DSUM25' => 'dsum25',
        'DSUM26' => 'dsum26',
        'DSUM27' => 'dsum27',
        'DSUM28' => 'dsum28',
        'DSUM29' => 'dsum29',
        'DSUM30' => 'dsum30',
        'DFEE1' => 'dfee1',                     //表具日用金额统计
        'DFEE2' => 'dfee2',
        'DFEE3' => 'dfee3',
        'DFEE4' => 'dfee4',
        'DFEE5' => 'dfee5',
        'DFEE6' => 'dfee6',
        'DFEE7' => 'dfee7',
        'DFEE8' => 'dfee8',
        'DFEE9' => 'dfee9',
        'DFEE10' => 'dfee10',
        'DFEE11' => 'dfee11',
        'DFEE12' => 'dfee12',
        'DFEE13' => 'dfee13',
        'DFEE14' => 'dfee14',
        'DFEE15' => 'dfee15',
        'DFEE16' => 'dfee16',
        'DFEE17' => 'dfee17',
        'DFEE18' => 'dfee18',
        'DFEE19' => 'dfee19',
        'DFEE20' => 'dfee20',
        'DFEE21' => 'dfee21',
        'DFEE22' => 'dfee22',
        'DFEE23' => 'dfee23',
        'DFEE24' => 'dfee24',
        'DFEE25' => 'dfee25',
        'DFEE26' => 'dfee26',
        'DFEE27' => 'dfee27',
        'DFEE28' => 'dfee28',
        'DFEE29' => 'dfee29',
        'DFEE30' => 'dfee30',
        'BLANC'  => 'balance',                                  //表具余额
        //价格信息
        'STSUM' =>  'stsum',                              //表具阶梯累计用量
        'CPRICE' => 'currentPrice',                             //表具当前价格
        'BCSUM' =>  'basic_price',                              //表具最低花费(表具阶梯周期最低的消费金额)
        'LADD1' =>  'first_val',                                //用量1
        'LPRIC1'=>  'first_price',                              //价格1
        'LADD2' =>  'second_val',                                //用量2
        'LPRIC2'=>  'second_price',                              //价格2
        'LADD3' =>  'third_val',                                //用量3
        'LPRIC3'=>  'third_price',                              //价格3
        'LADD4' =>  'fourth_val',                                //用量4
        'LPRIC4'=>  'fourth_price',                              //价格4
        'LADD5' =>  'fifth_val',                                //用量5
        'LPRIC5'=>  'fifth_price',                              //价格5
        'LADD6' =>  'sixth_val',                                //用量6
        'LPRIC6'=>  'sixth_price',                              //价格6
        //充值扣费信息
        'RECSU' =>  'total_charge',                             //累计充值金额
        'DEDSU' =>  'total_deduct',                             //扣费累计金额
        //表具运行状态信息
        'ABBLA' =>  'lowLimit',                                 //低余额报警
        'AOVDR' =>  'overdraftLimit',                           //透支报警


        'FETOT' =>  'totalCost',                                //累计扣费金额
    ];

    /**
     * 不需要入库的字段
     * @var array
     */
    protected $unset_fields = [
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
            if( isset($data['initFlag']) && $data['initFlag'] == 0 ){
                //unset不需要入库的字段
                $data = $this->generateData($data);
                //旧表生命周期结束
                $this->setOldMetersInactive($data['M_Code']);
                //单例模式,清空已实例化的数据库对象
                Loader::clearInstance();
                //新表具入库
                $newMeterId = $this->InitNewMeter($data);
                //上报数据入库
                //新建表具task charge deduct seq 记录
                $this->initAutoInc($newMeterId);
                $data['meter_id'] = $newMeterId;
                $this->insertMeterData($data,METER_INIT);
                //处理task
                //$newTask = $this->handleTask($newMeterId);
            }elseif(isset($data['ALARM'])) { //报警
                //根据表号获取表具信息
                $meterInfo = $this->getMeterInfo($data['M_Code']);
                $alarmData = [
                    'meter_id' =>   $meterInfo['id'],
                    'M_Code'    =>  $meterInfo['M_Code'],
                    'company_id'    =>  $meterInfo['company_id'],
                    'status'    =>  ALARM_STATUS_WAITING,
                    'reason'    =>  $data['ALARM'],
                ];
                if(!model('app\admin\model\Alarm')->save($alarmData)){
                    exception('保存报警信息失败:'.model('app\admin\model\Alarm')->getError());
                }
                //处理task
                $newTask = $this->handleTask($meterInfo['id']);
            }elseif(isset($data['seq'])){//上报任务执行情况
                //根据表号获取表具信息
                $meterInfo = $this->getMeterInfo($data['M_Code']);
                $data = $this->parseTaskResult($data);
                //处理task
                $newTask = $this->handleTask($meterInfo['id'],intval($data['seq']),isset($data['seqStatus']) ? $data['seqStatus'] : 0);
            }else{  //上报数据
                //unset不需要入库的字段
                $data = $this->generateData($data);
                //根据表号获取表具信息
                $meterInfo = $this->getMeterInfo($data['M_Code']);
                //计算阶梯量
                if(isset($meterInfo['P_ID'])){
                    $price = db('price')->where(['id' => $meterInfo['P_ID']])->find();
                    $newSTSUM = $data['stsum'];
                    $lastSTSUM = $meterInfo['stsum'];
                    $price_data = [];
                    $this->calcPriceUsageAndCost( $lastSTSUM, $newSTSUM, $price,$data['totalCube'] - $meterInfo['totalCube'], $data['totalCost']-$meterInfo['totalCost'],$price_data);
                }
                //检查表具余额是否需要添加提醒
                $this->checkBalance($data,$meterInfo['id']);
                //上报数据入库
                $this->insertMeterData(isset($price_data) ? array_merge($data,$price_data) : $data,METER_REPORT,$meterInfo);
                //更新累计流量表
                $this->updateMonthFlow($data['totalCube'] - $meterInfo['totalCube'],$data['totalCost']-$meterInfo['totalCost'],$meterInfo,isset($price_data) ? $price_data :[] );
                //更新表具信息
                $this->updateMeter($data,$meterInfo);
                //处理task
                $newTask = $this->handleTask($meterInfo['id']);
            }
            if( isset($newTask) ){
                $taskData = $this->parseTask($newTask);
                $ret = array_merge($ret,$taskData);
                $ret['METID'] = $data[$this->fields['METID']];
                $ret['SFEND'] = 0;
            }else{
                $ret['METID'] = $data[$this->fields['METID']];
                $ret['SFEND'] = 1;
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
        $local_data = [];
        foreach($data as $key => $value){
            if(in_array($key,array_keys($this->fields))){
                if(in_array($key,array_keys($this->fields_spacial))){
                    $local_data[$this->fields[$key]] = floatval($value)/10000;
                }else{
                    $local_data[$this->fields[$key]] = $value;
                }
            }else{
                $local_data[$key] = $value;
            }
        }
        return $local_data;
    }

    /**
     * 处理返回结果
     * @param $ret
     * @return \think\response\Json
     */
    private function generateRet($ret){
//        unset($ret['code']);
//        unset($ret['msg']);
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
    private function initNewMeter($data){
        $newMeterData['M_Code'] = $data['M_Code'];
        $newMeterData['totalCube'] = $data['totalCube'];
        $newMeterData['totalCube_cost'] = $data['totalCube_cost'];
        $newMeterData['totalCost'] = $data['totalCost'];
        $newMeterData['initialCube'] = $data['initialCube'];
        $newMeterData['meter_status'] = METER_STATUS_NEW;
        $newMeterData['meter_life'] = METER_LIFE_ACTIVE;
        $newMeterData['balance'] = $data['balance'];
        $newMeterData['stsum'] = $data['stsum'];
        $newMeterData['currentPrice'] = isset($data['currentPrice']) ? intval($data['currentPrice']) : 0;
        $newMeterData['balance_deli'] = 0;
        $newMeterData['balance_rmb'] = 0;
        $newMeterData['company_id'] = SHUANGDELI_ID;
        if(isset($data['valve_status'])){
            $newMeterData['valve_status'] = intval($data['valve_status']);
        }
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
        $field = 'M_Code,U_ID,P_ID,stsum,company_id,totalCube,totalCost';
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
        $meterData['totalCube'] = $data['totalCube'];
        $meterData['totalCube_cost'] = $data['totalCube_cost'];
        $meterData['balance'] = $data['balance'];
        $meterData['totalCost'] = $data['totalCost'];
        $meterData['currentPrice'] = $data['currentPrice'];
        $meterData['stsum'] = $data['stsum'];
        $meterData['valve_status'] = intval($data['valve_status']);
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
    private function updateMonthFlow($diffCube, $diffCost, $meterInfo, $price_data){
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
            if(isset($price_data) && $price_data){
                foreach($price_data as $key => $val){
                    if(isset($info[$month.'_'.$key])){
                        $updateData[$month.'_'.$key] = $info[$month.'_'.$key] + $val;
                    }else{
                        $updateData[$month.'_'.$key] = $val;
                    }
                }
            }
            db($tableName)->update($updateData);
        }else{
            $insertData['M_Code'] = $meterInfo['M_Code'];
            $insertData['meter_id'] = $meterInfo['id'];
            $insertData[$month] = $diffCube;
            $insertData[$month.'_cost'] = $diffCost;
            if(isset($price_data) && $price_data){
                foreach($price_data as $key => $val){
                    if(isset($info[$month.'_'.$key])){
                        $insertData[$month.'_'.$key] =  $info[$month.'_'.$key] + $val;
                    }else{
                        $insertData[$month.'_'.$key] = $val;
                    }
                }
            }
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
                //更新$lastSeq的task状态
                $updateCur['update_time'] = time();
                if( !db($this->taskTableName)->where(['meter_id' => $meter_id, 'seq_id' => $lastSeq])->update($updateCur) ){
                    Log::record(["更新Seq: $lastSeq 的task失败,meter_id" => $meter_id],'error');
                    exception("更新Seq: $lastSeq 的task失败",ERROR_CODE_DATA_ILLEGAL);
                }
            }
        }
        //获取下派新任务
        $newTask = db($this->taskTableName)->where(['meter_id' => $meter_id,'status' => ['in',[TASK_WAITING,TASK_SENT]],'seq_id' => ['>',$lastSeq ? $lastSeq : 0]])->order('seq_id','asc')->field('cmd,param,sdoty,send_times,seq_id')->find();
        //更新下派任务状态
        if($newTask){
            if(!db($this->taskTableName)->where(['id' => $newTask['id']])->update(['status' => TASK_SENT,'update_time' => time()])){
                Log::record(['更新task状态失败task_id' => $newTask['id']],'error');
                exception('更新task状态失败',ERROR_CODE_DATA_ILLEGAL);
            }
        }
        return $newTask;
    }

    /**
     * 解析成表具可以接收的格式
     * @param $newTask
     * @return mixed
     */
    private function parseTask($newTask){
        $taskData = [
            'seq'   =>  $newTask['seq_id']
        ];
        $param = $newTask['param'];
        switch($newTask['cmd']){
            case 'init_meter_param': //初始化运行参数
                $taskData['SBJBZ'] = 1;
                $taskData['ABBLA'] = $param[$this->fields['ABBLA']]*10000;
                $taskData['AOVDR'] = $param[$this->fields['AOVDR']]*10000;
                $taskData['PLUPW'] = $param[$this->fields['PLUPW']];
                $taskData['TIMPW'] = $param[$this->fields['TIMPW']];
                break;
            case 'init_meter_price': //初始化阶梯价格
                $taskData['SBJBZ'] = 1;
                $taskData['LCUIN'] = $param[$this->fields['LCUIN']];
                $taskData['LCYCL'] = $param[$this->fields['LCYCL']];
                $taskData['BCSUM'] = $param[$this->fields['BCSUM']]*10000;
                $taskData['LADD1'] = $param[$this->fields['LADD1']]*10000;
                $taskData['LPRIC1'] = $param[$this->fields['LPRIC1']]*10000;
                $taskData['LADD2'] = $param[$this->fields['LADD2']]*10000;
                $taskData['LPRIC2'] = $param[$this->fields['LPRIC2']]*10000;
                $taskData['LADD3'] = $param[$this->fields['LADD3']]*10000;
                $taskData['LPRIC3'] = $param[$this->fields['LPRIC3']]*10000;
                $taskData['LADD4'] = $param[$this->fields['LADD4']]*10000;
                $taskData['LPRIC4'] = $param[$this->fields['LPRIC4']]*10000;
                $taskData['LADD5'] = $param[$this->fields['LADD5']]*10000;
                $taskData['LPRIC5'] = $param[$this->fields['LPRIC5']]*10000;
                $taskData['LADD6'] = $param[$this->fields['LADD6']]*10000;
                $taskData['LPRIC6'] = $param[$this->fields['LPRIC6']]*10000;
                $taskData['LTIME']  =  substr(date('Y-m-d',$param[$this->fields['LTIME']]),2);
                break;
            case 'charge':
                $taskData['SBJCZ'] = 1;
                $taskData['SREAM'] = intval($newTask['param'])*10000;
                $taskData['SRETM'] = $newTask['send_times'];
                break;
            case 'deduct':
                $taskData['SBJKF'] = 1;
                $taskData['SKFAM'] = intval($newTask['param'])*10000;
                $taskData['SKFTM'] = $newTask['send_times'];
                break;
            case 'downloadPrice':
                $taskData['SJTJG'] = 1;
                $taskData['LCUIN'] = $param[$this->fields['LCUIN']];
                $taskData['LCYCL'] = $param[$this->fields['LCYCL']];
                $taskData['BCSUM'] = $param[$this->fields['BCSUM']]*10000;
                $taskData['LADD1'] = $param[$this->fields['LADD1']]*10000;
                $taskData['LPRIC1'] = $param[$this->fields['LPRIC1']]*10000;
                $taskData['LADD2'] = $param[$this->fields['LADD2']]*10000;
                $taskData['LPRIC2'] = $param[$this->fields['LPRIC2']]*10000;
                $taskData['LADD3'] = $param[$this->fields['LADD3']]*10000;
                $taskData['LPRIC3'] = $param[$this->fields['LPRIC3']]*10000;
                $taskData['LADD4'] = $param[$this->fields['LADD4']]*10000;
                $taskData['LPRIC4'] = $param[$this->fields['LPRIC4']]*10000;
                $taskData['LADD5'] = $param[$this->fields['LADD5']]*10000;
                $taskData['LPRIC5'] = $param[$this->fields['LPRIC5']]*10000;
                $taskData['LADD6'] = $param[$this->fields['LADD6']]*10000;
                $taskData['LPRIC6'] = $param[$this->fields['LPRIC6']]*10000;
                $taskData['LTIME']  =  substr(date('Y-m-d',$param[$this->fields['LTIME']]),2);
                break;
            case 'downloadMeterparam':
                $taskData['SYXCS'] = 1;
                $taskData['ABBLA'] = $param[$this->fields['ABBLA']]*10000;
                $taskData['AOVDR'] = $param[$this->fields['AOVDR']]*10000;
                $taskData['PLUPW'] = $param[$this->fields['PLUPW']];
                $taskData['TIMPW'] = $param[$this->fields['TIMPW']];
                break;
            case 'synchrodata':
                $taskData['SXGCS'] = 1;
                $taskData['ZSUMM'] = $param[$this->fields['ZSUMM']]*10000;
                $taskData['CSUMM'] = $param[$this->fields['CSUMM']]*10000;
                $taskData['FSUMM'] = $param[$this->fields['FSUMM']]*10000;
                $taskData['STSUM'] = $param[$this->fields['STSUM']]*10000;
                $taskData['CPRICE'] = $param[$this->fields['CPRICE']]*10000;
                $taskData['BLANC'] = $param[$this->fields['BLANC']]*10000;
                break;
            case 'turn_on':
                $taskData['SDOOR'] = 1;
                $taskData['SDCMD'] = 1;
                $taskData['SDOTY'] = 0;
                break;
            case 'turn_off':
                $taskData['SDOOR'] = 1;
                $taskData['SDCMD'] = 0;
                $taskData['SDOTY'] = isset($newTask['sdoty']) ? 1 : 0;
                break;
        }
        return $taskData;
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

    /**
     * 初始化任务编号/充值次数/扣款次数 数据
     * @param $meter_id
     */
    private function initAutoInc(string $meter_id){
        $autoIncData = [
            [
                'name'      =>  'task',
                'meter_id'  =>  $meter_id,
                'seq_id'    =>  0
            ],
            [
                'name'      =>  'charge',
                'meter_id'  =>  $meter_id,
                'seq_id'    =>  0
            ],
            [
                'name'      =>  'deduct',
                'meter_id'  =>  $meter_id,
                'seq_id'    =>  0
            ]
        ];
        initAuthoIncId('autoinc',$autoIncData);
    }

    private function parseTaskResult($data){
        if(isset($data['SBJBZ'])){ //表具报装
            if($data['SBJBZ'] == 1){
                $data['seqStatus'] = 1;
            }else{
                $data['seqStatus'] = 0;
            }
        }elseif(isset($data['SJTJG'])){ //阶梯价下发
            if($data['SJTJG'] == 1){
                $data['seqStatus'] = 1;
            }else{
                Log::record('阶梯价下发失败,错误代码:'.$data['JTERR'],'error');
                $data['seqStatus'] = 0;
            }
        }elseif(isset($data['SYXCS'])) { //运行参数下发
            if ($data['SYXCS'] == 1) {
                $data['seqStatus'] = 1;
            } else {
                Log::record('运行参数下发失败,错误代码:'.$data['SYERR'],'error');
                $data['seqStatus'] = 0;
            }
        }elseif(isset($data['SDOOR'])) { //阀门控制
            if ($data['SDCMD'] == $data['DOORS']) {
                $data['seqStatus'] = 1;
            } else {
                Log::record('阀门控制失败,错误代码:'.$data['SDCMD'],'error');
                $data['seqStatus'] = 0;
            }
        }elseif(isset($data['SBJCZ'])) { //充值
            if ($data['SBJCZ'] == 1) {
                $data['seqStatus'] = 1;
            } elseif($data['REERR'] == 1 || $data['REERR'] == 0) { //重复充值/充值成功
                $data['seqStatus'] = 1;
            }else{
                Log::record('充值失败,错误代码:'.$data['REERR'],'error');
                $data['seqStatus'] = 0;
            }
        }elseif(isset($data['SBJKF'])) { //扣费
            if ($data['SBJKF'] == 1) {
                $data['seqStatus'] = 1;
            } elseif($data['REERR'] == 1 || $data['REERR'] == 0) { //重复扣费/扣费成功
                $data['seqStatus'] = 1;
            }else{
                Log::record('扣费失败,错误代码:'.$data['REERR'],'error');
                $data['seqStatus'] = 0;
            }
        }elseif(isset($data['SXGCS'])){
            if($data['SXGCS'] == 1){
                $data['seqStatus'] = 1;
            }else{
                Log::record('修改表具参数失败,错误代码:'.$data['REERR'],'error');
                $data['seqStatus'] = 0;
            }
        }else{
            exception('下发任务返回结果数据错误');
        }
        return $data;
    }

    /**
     * 计算阶梯价用量和金额
     * @param $newSTSUM
     * @param $lastSTSUM
     * @param $price
     * @param $diffCube
     * @param $diffCost
     * @param $price_data
     */
    private function calcPriceUsageAndCost($lastSTSUM, $newSTSUM, $price, $diffCube, $diffCost, & $price_data)
    {
        if ($lastSTSUM >= $price['fifth_val']) {
            if ($newSTSUM > $lastSTSUM) {
                $price_data['la6'] = $newSTSUM - $lastSTSUM;
                $price_data['lp6'] = $diffCost;
            } elseif ($newSTSUM < $lastSTSUM) {
                if(isset($price_data['la6'])) {
                    $price_data['la6'] += $diffCube - $newSTSUM;
                    $price_data['lp6'] += ($diffCube - $newSTSUM)*$price['sixth_price'];
                }else {
                    $price_data['la6'] = $diffCube - $newSTSUM;
                    $price_data['lp6'] = ($diffCube - $newSTSUM)*$price['sixth_price'];
                }
                $this->calcPriceUsageAndCost(0,$newSTSUM, $price,$newSTSUM,$diffCost-($diffCube - $newSTSUM)*$price['sixth_price'], $price_data);
            }
        }elseif( $lastSTSUM >= $price['fourth_val'] ){
            if($newSTSUM > $lastSTSUM){
                if($newSTSUM >= $price['fifth_val']){
                    if(isset($price_data['la5'])) {
                        $price_data['la5'] += $price['fifth_val'] - $lastSTSUM;
                        $price_data['lp5'] += ($price['fifth_val'] - $lastSTSUM)*$price['fifth_price'];
                    }else {
                        $price_data['la5'] = $price['fifth_val'] - $lastSTSUM;
                        $price_data['lp5'] = ($price['fifth_val'] - $lastSTSUM)*$price['fifth_price'];
                    }
                    $this->calcPriceUsageAndCost($price['fifth_val'],$newSTSUM,$price,($diffCube-($price['fifth_val'] - $lastSTSUM)),$diffCost-(($price['fifth_val'] - $lastSTSUM)*$price['fifth_price']), $price_data);
                }else{
                    if(isset($price_data['la5'])) {
                        $price_data['la5'] += $newSTSUM - $lastSTSUM;
                        $price_data['lp5'] += $diffCost;
                    }else {
                        $price_data['la5'] = $newSTSUM - $lastSTSUM;
                        $price_data['lp5'] = $diffCost;
                    }
                }
            }elseif($newSTSUM < $lastSTSUM){
                if($lastSTSUM+$diffCube-$newSTSUM >= $price['fifth_val']){
                    if(isset($price_data['la5'])) {
                        $price_data['la5'] += $price['fifth_val'] - $lastSTSUM;
                        $price_data['lp5'] += ($price['fifth_val'] - $lastSTSUM)*$price['fifth_price'];
                    }else {
                        $price_data['la5'] = $price['fifth_val'] - $lastSTSUM;
                        $price_data['lp5'] = ($price['fifth_val'] - $lastSTSUM)*$price['fifth_price'];
                    }
                    $this->calcPriceUsageAndCost($price['fifth_val'],$lastSTSUM+$diffCube-$newSTSUM,$price,($lastSTSUM+$diffCube-$newSTSUM-$price['fifth_val']),$diffCost-$newSTSUM*$price['first_price']-($price['fifth_val'] - $lastSTSUM)*$price['fifth_price'], $price_data);
                }else{
                    if(isset($price_data['la5'])) {
                        $price_data['la5'] += $diffCube-$newSTSUM;
                        $price_data['lp5'] += ($diffCube-$newSTSUM)*$price['fifth_price'];
                    }else {
                        $price_data['la5'] = $diffCube-$newSTSUM;
                        $price_data['lp5'] = ($diffCube-$newSTSUM)*$price['fifth_price'];
                    }
                }
                $this->calcPriceUsageAndCost(0,$newSTSUM,$price,$newSTSUM,$newSTSUM*$price['first_price'] ,$price_data);
            }
        }elseif( $lastSTSUM >= $price['third_val'] ){
            if($newSTSUM > $lastSTSUM){
                if($newSTSUM >= $price['fourth_val']){
                    if(isset($price_data['la4'])) {
                        $price_data['la4'] += $price['fourth_val'] - $lastSTSUM;
                        $price_data['lp4'] += ($price['fourth_val'] - $lastSTSUM)*$price['fourth_price'];
                    }else {
                        $price_data['la4'] = $price['fourth_val'] - $lastSTSUM;
                        $price_data['lp4'] = ($price['fourth_val'] - $lastSTSUM)*$price['fourth_price'];
                    }
                    $this->calcPriceUsageAndCost($price['fourth_val'],$newSTSUM,$price,($diffCube-($price['fourth_val'] - $lastSTSUM)),$diffCost-(($price['fourth_val'] - $lastSTSUM)*$price['fourth_price']), $price_data);
                }else{
                    if(isset($price_data['la4'])) {
                        $price_data['la4'] += $newSTSUM - $lastSTSUM;
                        $price_data['lp4'] += $diffCost;
                    }else {
                        $price_data['la4'] = $newSTSUM - $lastSTSUM;
                        $price_data['lp4'] = $diffCost;
                    }
                }
            }elseif($newSTSUM < $lastSTSUM){
                if($lastSTSUM+$diffCube-$newSTSUM >= $price['fourth_val']){
                    if(isset($price_data['la4'])) {
                        $price_data['la4'] += $price['fourth_val'] - $lastSTSUM;
                        $price_data['lp4'] += ($price['fourth_val'] - $lastSTSUM)*$price['fourth_price'];
                    }else {
                        $price_data['la4'] = $price['fourth_val'] - $lastSTSUM;
                        $price_data['lp4'] = ($price['fourth_val'] - $lastSTSUM)*$price['fourth_price'];
                    }
                    $this->calcPriceUsageAndCost($price['fourth_val'],$lastSTSUM+$diffCube-$newSTSUM,$price,($lastSTSUM+$diffCube-$newSTSUM*$price['first_price']-$newSTSUM-$price['fourth_val']),$diffCost-($price['fourth_val'] - $lastSTSUM)*$price['fourth_price'], $price_data);
                }else{
                    if(isset($price_data['la4'])) {
                        $price_data['la4'] += $diffCube-$newSTSUM;
                        $price_data['lp4'] += ($diffCube-$newSTSUM)*$price['fourth_price'];
                    }else {
                        $price_data['la4'] = $diffCube-$newSTSUM;
                        $price_data['lp4'] = ($diffCube-$newSTSUM)*$price['fourth_price'];
                    }
                }
                $this->calcPriceUsageAndCost(0,$newSTSUM,$price,$newSTSUM,$newSTSUM*$price['first_price'] ,$price_data);
            }
        }elseif( $lastSTSUM >= $price['second_val'] ){
            if($newSTSUM > $lastSTSUM){
                if($newSTSUM >= $price['third_val']){
                    if(isset($price_data['la3'])) {
                        $price_data['la3'] += $price['third_val'] - $lastSTSUM;
                        $price_data['lp3'] += ($price['third_val'] - $lastSTSUM)*$price['third_price'];
                    }else {
                        $price_data['la3'] = $price['third_val'] - $lastSTSUM;
                        $price_data['lp3'] = ($price['third_val'] - $lastSTSUM)*$price['third_price'];
                    }
                    $this->calcPriceUsageAndCost($price['third_val'],$newSTSUM,$price,($diffCube-($price['third_val'] - $lastSTSUM)),$diffCost-(($price['third_val'] - $lastSTSUM)*$price['third_price']), $price_data);
                }else{
                    if(isset($price_data['la3'])) {
                        $price_data['la3'] += $newSTSUM - $lastSTSUM;
                        $price_data['lp3'] += $diffCost;
                    }else {
                        $price_data['la3'] = $newSTSUM - $lastSTSUM;
                        $price_data['lp3'] = $diffCost;
                    }
                }
            }elseif($newSTSUM < $lastSTSUM){
                if($lastSTSUM+$diffCube-$newSTSUM >= $price['third_val']){
                    if(isset($price_data['la3'])) {
                        $price_data['la3'] += $price['third_val'] - $lastSTSUM;
                        $price_data['lp3'] += ($price['third_val'] - $lastSTSUM)*$price['third_price'];
                    }else {
                        $price_data['la3'] = $price['third_val'] - $lastSTSUM;
                        $price_data['lp3'] = ($price['third_val'] - $lastSTSUM)*$price['third_price'];
                    }
                    $this->calcPriceUsageAndCost($price['third_val'],$lastSTSUM+$diffCube-$newSTSUM,$price,($lastSTSUM+$diffCube-$newSTSUM*$price['first_price']-$newSTSUM-$price['third_val']),$diffCost-($price['third_val'] - $lastSTSUM)*$price['third_price'], $price_data);
                }else{
                    if(isset($price_data['la3'])) {
                        $price_data['la3'] += $diffCube-$newSTSUM;
                        $price_data['lp3'] += ($diffCube-$newSTSUM)*$price['third_price'];
                    }else {
                        $price_data['la3'] = $diffCube-$newSTSUM;
                        $price_data['lp3'] = ($diffCube-$newSTSUM)*$price['third_price'];
                    }
                }
                $this->calcPriceUsageAndCost(0,$newSTSUM,$price,$newSTSUM,$newSTSUM*$price['first_price'] ,$price_data);
            }
        }elseif( $lastSTSUM >= $price['first_val'] ){
            if($newSTSUM > $lastSTSUM){
                if($newSTSUM >= $price['second_val']){
                    if(isset($price_data['la2'])) {
                        $price_data['la2'] += $price['second_val'] - $lastSTSUM;
                        $price_data['lp2'] += ($price['second_val'] - $lastSTSUM)*$price['second_price'];
                    }else {
                        $price_data['la2'] = $price['second_val'] - $lastSTSUM;
                        $price_data['lp2'] = ($price['second_val'] - $lastSTSUM)*$price['second_price'];
                    }
                    $this->calcPriceUsageAndCost($price['second_val'],$newSTSUM,$price,($diffCube-($price['second_val'] - $lastSTSUM)),$diffCost-(($price['second_val'] - $lastSTSUM)*$price['second_price']), $price_data);
                }else{
                    if(isset($price_data['la2'])) {
                        $price_data['la2'] += $newSTSUM - $lastSTSUM;
                        $price_data['lp2'] += $diffCost;
                    }else {
                        $price_data['la2'] = $newSTSUM - $lastSTSUM;
                        $price_data['lp2'] = $diffCost;
                    }
                }
            }elseif($newSTSUM < $lastSTSUM){
                if($lastSTSUM+$diffCube-$newSTSUM >= $price['second_val']){
                    if(isset($price_data['la2'])) {
                        $price_data['la2'] += $price['second_val'] - $lastSTSUM;
                        $price_data['lp2'] += ($price['second_val'] - $lastSTSUM)*$price['second_price'];
                    }else {
                        $price_data['la2'] = $price['second_val'] - $lastSTSUM;
                        $price_data['lp2'] = ($price['second_val'] - $lastSTSUM)*$price['second_price'];
                    }
                    $this->calcPriceUsageAndCost($price['second_val'],$lastSTSUM+$diffCube-$newSTSUM,$price,($lastSTSUM+$diffCube-$newSTSUM*$price['first_price']-$newSTSUM-$price['second_val']),$diffCost-($price['second_val'] - $lastSTSUM)*$price['second_price'], $price_data);
                }else{
                    if(isset($price_data['la2'])) {
                        $price_data['la2'] += $diffCube-$newSTSUM;
                        $price_data['lp2'] += ($diffCube-$newSTSUM)*$price['second_price'];
                    }else {
                        $price_data['la2'] = $diffCube-$newSTSUM;
                        $price_data['lp2'] = ($diffCube-$newSTSUM)*$price['second_price'];
                    }
                }
                $this->calcPriceUsageAndCost(0,$newSTSUM,$price,$newSTSUM,$newSTSUM*$price['first_price'] ,$price_data);
            }
        }else{
            if($newSTSUM > $lastSTSUM){
                if($newSTSUM >= $price['first_val']){
                    if(isset($price_data['la1'])) {
                        $price_data['la1'] += $price['first_val'] - $lastSTSUM;
                        $price_data['lp1'] += ($price['first_val'] - $lastSTSUM)*$price['first_price'];
                    }else {
                        $price_data['la1'] = $price['first_val'] - $lastSTSUM;
                        $price_data['lp1'] = ($price['first_val'] - $lastSTSUM)*$price['first_price'];
                    }
                    $this->calcPriceUsageAndCost($price['first_val'],$newSTSUM,$price,($diffCube-($price['first_val'] - $lastSTSUM)),$diffCost-(($price['first_val'] - $lastSTSUM)*$price['first_price']), $price_data);
                }else{
                    if(isset($price_data['la1'])) {
                        $price_data['la1'] += $newSTSUM - $lastSTSUM;
                        $price_data['lp1'] += $diffCost;
                    }else {
                        $price_data['la1'] = $newSTSUM - $lastSTSUM;
                        $price_data['lp1'] = $diffCost;
                    }
                }
            }elseif($newSTSUM < $lastSTSUM){
                if($lastSTSUM+$diffCube-$newSTSUM >= $price['first_val']){
                    if(isset($price_data['la1'])) {
                        $price_data['la1'] += $price['first_val'] - $lastSTSUM;
                        $price_data['lp1'] += ($price['first_val'] - $lastSTSUM)*$price['first_price'];
                    }else {
                        $price_data['la1'] = $price['first_val'] - $lastSTSUM;
                        $price_data['lp1'] = ($price['first_val'] - $lastSTSUM)*$price['first_price'];
                    }
                    $this->calcPriceUsageAndCost($price['first_val'],$lastSTSUM+$diffCube-$newSTSUM,$price,($lastSTSUM+$diffCube-$newSTSUM*$price['first_price']-$newSTSUM-$price['first_val']),$diffCost-($price['first_val'] - $lastSTSUM)*$price['first_price'], $price_data);
                }else{
                    if(isset($price_data['la1'])) {
                        $price_data['la1'] += $diffCube-$newSTSUM;
                        $price_data['lp1'] += ($diffCube-$newSTSUM)*$price['first_price'];
                    }else {
                        $price_data['la1'] = $diffCube-$newSTSUM;
                        $price_data['lp1'] = ($diffCube-$newSTSUM)*$price['first_price'];
                    }
                }
                $this->calcPriceUsageAndCost(0,$newSTSUM,$price,$newSTSUM,$newSTSUM*$price['first_price'] ,$price_data);
            }
        }
    }

    public function test(){
        $request = $this->request->param();
        if(is_array($request)){
            $response = json_encode($request);
        }else{
            $response = $request;
        }
        @unlink('response.txt');
        $file = fopen('response.txt','w');
        fwrite($file,"createAt:".date('Y-m-d H:i:s')."\n");
        fwrite($file,"request data:\n");
        fwrite($file,$response);
        fclose($file);
        return json(['msg' => '操作成功']);
    }
}