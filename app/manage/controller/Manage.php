<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/10/24
 * Time: 下午12:01
 */

namespace app\manage\controller;

use app\manage\service\CompanyService;
use app\manage\service\ConsumerService;
use app\manage\service\FixService;
use app\manage\service\MeterService;
use app\manage\service\MoneyLogService;
use app\manage\service\UserService;
use app\manage\service\TaskService;
use app\manage\service\AdviceService;
use think\Loader;
use think\Log;

/**
 * 管理
 * Class Manage
 * @package app\manage\controller
 */
class Manage extends Admin
{

    /**
     * 运营商列表
     * @return \think\response\View
     */
    public function company(){
        $company = input('company');
        $companyService = new CompanyService();
        $where['status'] = COMPANY_STATUS_NORMAL;
        if( $company ){
            $where['company_name'] = $company;
        }
        $companys = $companyService->getInfoPaginate($where,'OPT_ID,company_name');
        $this->assign('companys',$companys);
        $this->assign('company',$company);
        return view();
    }

    /**
     * 添加/修改运营商api
     * @return \think\response\Json
     */
    public function saveCompany(){
        $data = input('data');
        $data = json_decode($data,true);
        $ret['code'] = 200;
        $ret['msg'] = lang('Operation Success');
        try{
            $companyService = new CompanyService();
            if( $data['id'] ){
                unset($data['OPT_ID']); //OPT_ID不允许修改
                $scene = 'Company.edit';
            }else{
                $data['status'] = COMPANY_STATUS_NORMAL;
                $scene = 'Company.add';
            }
            if( !$companyService->upsert($data,$scene) ){
                $error = $companyService->getError();
                Log::record(['添加运营商失败:' => $error,'data' => $data],'error');
                exception(lang('Operation fail').' : '.$error,ERROR_CODE_DATA_ILLEGAL);
            }
            model('app\admin\model\LogRecord')->record( 'Add/Edit Company',$data);
        }catch (\Exception $e){
            $ret['code'] =  $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

    /**
     * 根据运营商id获取运营商信息
     * @return \think\response\Json
     */
    public function getCompanyInfoById(){
        $id = input('id');
        $ret['code'] = 200;
        $ret['msg'] = lang('Operation Success');
        try{
            $companyService = new CompanyService();
            if( !$companyInfo = $companyService->findInfo(['id' => $id]) ){
                exception(lang('Data ID exception'),ERROR_CODE_DATA_ILLEGAL);
            }
            $ret['data'] = $companyInfo;
        }catch (\Exception $e){
            $ret['code'] =  $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);

    }

    /**
     * 删除运营商
     * @return \think\response\Json
     */
    public function delCompany(){
        $OPT_ID = input('OPT_ID');
        $ret['code'] = 200;
        $ret['msg'] = lang('Operation Success');
        try{
            $companyService = new CompanyService();
            if( !$companyInfo = $companyService->findInfo(['OPT_ID' => $OPT_ID],'id') ){
                exception(lang('OPT_ID Not Exist'),ERROR_CODE_DATA_ILLEGAL);
            }
            //标记运营商管理员为禁用状态
            $where_user['company_id'] = $companyInfo['id'];
            $where_user['status'] = 1;
            if( !(new UserService())->disableUser($where_user) ){
                $error = $companyService->getError();
                Log::record(['禁用运营商账号失败:' => $error,'data' => $where_user],'error');
                exception(lang('Operation fail').' : '.$error,ERROR_CODE_DATA_ILLEGAL);
            }

            //标记运营商删除状态
            $data['id'] = $companyInfo['id'];
            $data['OPT_ID'] = $OPT_ID;
            $data['status'] = COMPANY_STATUS_DEL;
            if( !$companyService->upsert($data,'Company.del') ){
                $error = $companyService->getError();
                Log::record(['删除运营商失败:' => $error,'data' => $data],'error');
                exception(lang('Operation fail').' : '.$error,ERROR_CODE_DATA_ILLEGAL);
            }
            model('app\admin\model\LogRecord')->record( 'Del Company',$data);
        }catch (\Exception $e){
            $ret['code'] =  $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

    public function manageCompany(){
        $company_name = input('company_name');
        $address = input('address');
        $OPT_ID = input('OPT_ID');
        if($company_name){
            $where['company_name'] = ['like',$company_name];
        }
        if($address){
            $where['address'] = ['like',$address];
        }
        if($OPT_ID){
            $where['OPT_ID'] = $OPT_ID;
        }
        if( isset($where) ){
            $where['status'] = COMPANY_STATUS_NORMAL;
            $companyService = new CompanyService();
            $companyInfo = $companyService->findInfo($where);
            $userService = new UserService();
            $users = $userService->selectInfo(['company_id' => $companyInfo['id'],'type' => PLATFORM_ADMIN, 'delete_time' => null]);
            $roles = model('app\admin\model\Role')->getList(['company_id' => $companyInfo['id'],'status' => 1]);
        }
        $this->assign('company_name',$company_name);
        $this->assign('address',$address);
        $this->assign('companyInfo',isset($companyInfo) ? $companyInfo : []);
        $this->assign('users',isset($users) ? $users : []);
        $this->assign('roles',isset($roles) ? $roles : []);
        return view();
    }

    public function saveUser(){
        $data = input('data');
        $data = json_decode($data,true);
        $ret['code'] = 200;
        $ret['msg'] = lang('Operation Success');
        try{
            $userService = new UserService();
            if(isset($data['id']) && !empty($data['id'])){
                unset($data['login_name']);
                if(!$data['password']){
                    unset($data['password']);
                }
                $scene = 'User.manageEdit';
            }else{
                if( $userService->findInfo(['login_name' => $data['login_name']]) ){
                    exception(lang('Login Name Exists'),ERROR_CODE_DATA_ILLEGAL);
                }
                $data['type'] = PLATFORM_ADMIN;
                $scene = 'User.manageAdd';
            }
            if(isset($data['role_id'])){
                $data['administrator'] = 0;
            }
            if( !$userService->upsert($data,$scene) ){
                $error = $userService->getError();
                Log::record(['添加失败:' => $error,'data' => $data],'error');
                exception(lang('Operation fail').' : '.$error,ERROR_CODE_DATA_ILLEGAL);
            }
            model('app\admin\model\LogRecord')->record('Save User',$data);
        }catch (\Exception $e){
            $ret['code'] =  $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

    public function getUserInfoById(){
        $id = input('id');
        $ret['code'] = 200;
        $ret['msg'] = lang('Operation Success');
        try{
            $userService = new UserService();
            if( !$userInfo = $userService->findInfo(['id' => $id],'id,username,login_name,status,ukey,tel,administrator,role_id') ){
                exception(lang('Data ID exception'),ERROR_CODE_DATA_ILLEGAL);
            }
            $ret['data'] = $userInfo;
        }catch (\Exception $e){
            $ret['code'] =  $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

    /*
     * @表具信息；
     * 接收company_id,deatail_address,M_Code,name字段；
     */
    public function meterMessage(){
        $company_name = input('company_name');
        $detail_address = input('detail_address');
        $M_Code= input('M_Code');
        $username = input('username');
        $where['meter_life'] = METER_LIFE_ACTIVE ;
        if($company_name){
            $companyService = new CompanyService();
            $company_id = $companyService->findInfo(['company_name'=>$company_name],'id')['id'];
            $where['company_id'] = $company_id;
        }
        if($detail_address){
            $where['detail_address'] = ['like',$detail_address];
        }
        if($M_Code){
            $where['M_Code'] = $M_Code;
        }
        if($username){
            $consumerService = new ConsumerService();
            $consumers = $consumerService->selectInfo(['username'=>$username],'id');
            $arrs=[];
            foreach($consumers as $value){
                $arrs[]=$value['id'];
            }
            $where['U_ID'] = ['in',$arrs];
        }
        $param['company_name'] = $company_name;
        $param['detail_address'] = $detail_address;
        $param['M_Code'] = $M_Code;
        $param['username'] = $username;
        $meterService = new MeterService();
        $meter = $meterService->getInfoPaginate($where,$param);
        $this->assign('meter',$meter);
        $this->assign('company_name',$company_name);
        $this->assign('detail_address',$detail_address);
        $this->assign('M_Code',$M_Code);
        $this->assign('username',$username);
        return $this->fetch();
    }

    /*
  * @表具统计；
  *
  */
    public function meterStatistics(){
        $statistics = input('statistics',SETUP_STATISTICS);
        $starttime = input('starttime',date('Y-m-d',strtotime('-1 month')));
        $endtime = input('endtime',date('Y-m-d'));
        if($statistics==NEW_STATISTICS){
            $where['create_time'] = ['between',[strtotime($starttime." 00:00:00"),strtotime($endtime." 23:59:59")]];
        }elseif($statistics==SETUP_STATISTICS){
            $where['setup_time'] = ['between',[strtotime($starttime." 00:00:00"),strtotime($endtime." 23:59:59")]];
        }else{
            $where['meter_status'] = METER_STATUS_BIND;
            $where['change_time'] = ['between',[strtotime($starttime." 00:00:00"),strtotime($endtime." 23:59:59")]];
        }
        $param['statistics'] = $statistics;
        $param['starttime'] = $starttime;
        $param['endtime'] = $endtime;
        $meterService = new MeterService();
        $meter = $meterService->getInfoPaginate($where,$param);
//        var_dump($meter);die;
        $meter_statistics = config('meterstatistics');
        $this->assign('meter_statistics',$meter_statistics);
        $this->assign('meter',$meter);
        $this->assign('statistics',$statistics);
        $this->assign('starttime',$starttime);
        $this->assign('endtime',$endtime);
        return $this->fetch();
    }

    //获取单条表具信息；
    public function meterInfo(){
        $id = input('id');
        $meterService = new MeterService();
        $meter = $meterService->findInfo(['id'=>$id,'meter_life'=>METER_LIFE_ACTIVE]);
        $this->assign('meter',$meter);
        return view();
    }

    public function meterStatisticsInfo(){
        $id = input('id');
        $meterService = new MeterService();
        $meter = $meterService->findInfo(['id'=>$id,'meter_life'=>METER_LIFE_ACTIVE]);
        $this->assign('meter',$meter);
        return view();
    }

    public function deductBalance(){
        return view();
    }

    public function exampleExcel(){
        $meterService = new MeterService();
        $filename = "扣除金额模板";
        $title = '扣除金额Excel';
        $meterService->createExample_xls($filename,$title);
    }

    /**
     * excel提交扣除余额
     * @return \think\response\Json
     */
    public function uploadExcel(){
        // 获取表单上传文件
        $file = request()->file('excel');
        $ajaxReturn['status'] = 200;
        $ajaxReturn['msg'] = lang('Operation Success');
        if(!$file){
            $ajaxReturn['status'] = 401;
            $ajaxReturn['msg'] = '请先上传文件！';
        }else{
            // 移动到框架应用根目录/public/uploads/ 目录下
            $info = $file->validate(['size'=>10*1024*1024,'ext'=>'xls,xlsx'])->move(ROOT_PATH . 'public' . DS . 'uploads');
            if($info){
                $localfile = ROOT_PATH . 'public' . DS . 'uploads'. DS .$info->getSaveName();
                if($filedata=$this->getFileData($localfile)){
                    $diff = $this->checkFileData($filedata); //check 表号
                    if(!empty($diff)){
                        foreach($diff as $key=>$value){
                            $arr[$key]['code']=$value;
                            $arr[$key]['reson'] = '表号不存在';
                        }
                        $ajaxReturn['status'] = 202;
                        $ajaxReturn['msg'] = $arr;
                    }elseif(!empty($wrongNumberTypes = $this->checkNumberType($filedata))){ //check 扣除金额 是否都是数字
                            foreach($wrongNumberTypes as $key=>$value){
                                $arr[$key]['code']=$value['M_Code'];
                                $arr[$key]['reson'] = "扣除金额:".$value['number']." 必须是数字";
                            }
                            $ajaxReturn['status'] = 202;
                            $ajaxReturn['msg'] = $arr;
                    }else{
                        $result = $this->addAllTask($filedata);
                        model('app\admin\model\LogRecord')->record( 'Deduct',['source' => $localfile,'faildata' => $result]);
                        if(!empty($result)){
                            $ajaxReturn['status'] = 201;
                            $ajaxReturn['msg'] = $result;
                        }
                    }
                }else{
                    $ajaxReturn['status'] = 402;
                    $ajaxReturn['msg'] = '上传excel数据为空，请重试！';
                }
            }else{
                $ajaxReturn['status'] = 400;
                $ajaxReturn['msg'] = $file->getError();
            }
        }
        return json($ajaxReturn);
    }

    /**
     * 获取文件中的数据
     * @param $path
     * @return array
     */
    private function getFileData($path){
        $data = [];
        $PHPReader = new \PHPExcel_Reader_Excel2007();
        if(!$PHPReader->canRead($path)){
            $PHPReader = new \PHPExcel_Reader_Excel5();
        }
        $PHPExcel = $PHPReader->load($path);
        $sheet = $PHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        if($highestRow >= 4 ){
            for ($row = 4; $row <= $highestRow; $row++){//行数是以第2行开始
                $M_Code = $sheet->getCellByColumnAndRow(0,$row)->getValue();
                $M_Code = is_object($M_Code) ? $M_Code->__toString() : $M_Code; //避免导入的value是object
                $number = $sheet->getCellByColumnAndRow(1,$row)->getValue();
                $number = is_object($number) ? $number->__toString() : $number; //避免导入的value是object
                $remark = $sheet->getCellByColumnAndRow(2,$row)->getValue();
                $remark = is_object($remark) ? $remark->__toString() : $remark; //避免导入的value是object
                $data[] = ['M_Code' => trim(strval($M_Code)),'number'=> $number,'remark' => strval($remark)];
            }
        }
        return $data;
    }

    /**
     * 检查表号是否全部存在
     * @param $filedata
     * @return array
     */
    public function checkFileData($filedata){
        foreach($filedata as $item){
            $codes[] = $item['M_Code'];
        }
        $where['M_Code'] = ['in',$codes];
        $where['meter_life'] = METER_LIFE_ACTIVE;
        $where['meter_status'] = METER_STATUS_BIND;
        $meterService = new MeterService();
        $meters = $meterService->columnInfo($where,'M_Code');
        return array_diff($codes,$meters);
    }

    public function checkNumberType($filedata){
        $wrongNumberTypes = [];
        foreach($filedata as $index => $item){
            if(!is_numeric($item['number'])){
                $wrongNumberTypes[$index] = [
                    'M_Code' => $item['M_Code'],
                    'number' => $item['number']
                ];
            }
        }
        return $wrongNumberTypes;
    }

    /**
     * 订单管理
     * @return \think\response\View
     */
    public function manageOrder(){
        $M_Code = input('M_Code');
        $order_id = input('order_id');
        $channel = input('channel/d');
        $type = input('type/d');
        //$money_type = input('money_type/d');
        $money_type = MONEY_TYPE_RMB; //现在只能查人民币
        $where = [
            'money_type' => $money_type
        ];
        $whereor = [];
        if($M_Code){
            $meter_id = (new MeterService())->findInfo(['M_Code' => $M_Code,'meter_life' => METER_LIFE_ACTIVE])['id'];
            $whereor = [
                'from' => $meter_id,
                'to'   => $meter_id
            ];
        }
        if($type){
            $where['type'] = $type;
        }
        if($channel){
            $where['channel'] = $channel;
        }
        if($order_id){
            $where['order_id'] = $order_id;
        }
        $moneylogs = (new MoneyLogService())->getInfoPaginateWhereOr($where,$whereor,['M_Code' => $M_Code,'channel' => $channel,'type' => $type,'order_id' => $order_id]);
        $this->assign('M_Code',$M_Code);
        $this->assign('order_id',$order_id);
        $this->assign('channel',$channel);
        $this->assign('type',$type);
        $this->assign('moneylogs',$moneylogs);
        $channels = config('channels');
        $this->assign('channels',$channels);
        $ordertypes = config('ordertypes');
        $this->assign('ordertypes',$ordertypes);
        return view();
    }

    /**
     *下载订单列表
     */
    public function downloadOrder(){
        $M_Code = input('M_Code');
        $order_id = input('order_id');
        $channel = input('channel/d');
        $type = input('type/d');
        //$money_type = input('money_type/d');
        $money_type = MONEY_TYPE_RMB; //现在只能查人民币
        $where = [
            'money_type' => $money_type
        ];
        $whereor = [];
        if($M_Code){
            $meter_id = (new MeterService())->findInfo(['M_Code' => $M_Code,'meter_life' => METER_LIFE_ACTIVE])['id'];
            $whereor = [
                'from' => $meter_id,
                'to'   => $meter_id
            ];
        }
        if($type){
            $where['type'] = $type;
        }
        if($channel){
            $where['channel'] = $channel;
        }
        if($order_id){
            $where['order_id'] = $order_id;
        }
        $moneylogs = (new MoneyLogService())->getInfoPaginateWhereOr($where,$whereor,['M_Code' => $M_Code,'channel' => $channel,'type' => $type,'order_id' => $order_id]);
        (new MoneyLogService())->downloadOrder($moneylogs,'订单详情'.date('Y-m-d'),'订单详情'.date('Y-m-d'));
    }

    private function addAllTask($datas){
        $arrs=[];
        foreach($datas as $key=> $value){
            //检查表具信息
            $meterService = new MeterService();
            $where['M_Code'] = $value['M_Code'];
            $where['meter_life'] = METER_LIFE_ACTIVE;
            $where['meter_status'] = METER_STATUS_BIND;
            if(!$meter=$meterService->findInfo($where,'id,company_id')){
                $arrs[$key]['code'] = $value['M_Code'];
                $arrs[$key]['reson'] = '表号不存在或不符合操作条件';
                continue;
            }
            //插入moneylog
            $money_log['money_type'] = MONEY_TYPE_RMB;
            $money_log['money'] = $value['number'];
            $money_log['type'] = MONEY_DEDUCT;
            $money_log['to'] = $meter['id'];
            $money_log['extra_desc'] = $value['remark'];
            $money_log['channel'] = MONEY_CHANNEL_MANAGE;
            Loader::clearInstance();
            $result = insertMoneyLog($money_log);
            if(is_array($result)){
                $arrs[$key]['code']=$value['M_Code'];
                $arrs[$key]['reson']=$result['msg'];
                continue;
            }
            //插入task
            $task['meter_id'] = $meter['id'];
            $task['cmd'] = 'deduct';
            $task['param'] = $value['number'];
            $task['money_log_id'] = $result;
            $task['balance_rmb'] = -floatval($value['number']);
            $ret = upsertTask($task);
            if(is_array($ret)){
                $arrs[$key]['code']=$value['M_Code'];
                $arrs[$key]['reson']=$ret['msg'];
                continue;
            }
        }
        return $arrs;

    }

    /**
     *输入框提价扣除余额
     * @return \think\response\Json
     */
    public function uploadData(){
        $M_Code = trim(input('M_Code'),';');
        $money = input('money');
        $message = input('message');
        $ajaxReturn['status'] = 200;
        $ajaxReturn['msg'] = lang('Operation Success');
        if(!$M_Code){
            $ajaxReturn['status'] = 401;
            $ajaxReturn['msg'] = '表号不能为空！';
            return $ajaxReturn;
        }
        if(!$money){
            $ajaxReturn['status'] = 402;
            $ajaxReturn['msg'] = '扣款金额不能为空！';
            return $ajaxReturn;
        }
        $arr=[];
        $code = explode(';',$M_Code);
        foreach($code as $key=>$value){
            $arr[$key]['M_Code']=$value;
            $arr[$key]['number'] = floatval($money);
            $arr[$key]['remark'] = $message;
        }
        $diff = $this->checkFileData($arr);
        if(!empty($diff)){
            foreach($diff as $key=>$value){
                $tmp[$key]['code']=$value;
                $tmp[$key]['reson'] = '表号不存在';
            }
            $ajaxReturn['status'] = 202;
            $ajaxReturn['msg'] = $tmp;
        }else{
            $result = $this->addAllTask($arr);
            model('app\admin\model\LogRecord')->record( 'Deduct',['source' => $arr,'faildata' => $result]);
            if(!empty($result)){
                $ajaxReturn['status'] = 201;
                $ajaxReturn['msg'] = $result;
            }
        }
        return json($ajaxReturn);
    }

    /**
     * 任务列表
     * @return \think\response\View
     */
    public function tasklist(){
        $M_Code = input('M_Code');
        $cmd = input('cmd');
        $taskStatus = input('taskStatus/d');
        $consumer_name = input('name');
        $consumer_identity = input('identity');
        $consumer_tel = input('tel');
        $where = [];
        if($M_Code){
            $meterInfo = (new MeterService())->findInfo(['M_Code' => $M_Code,'meter_life' => METER_LIFE_ACTIVE,'meter_status' => METER_STATUS_BIND],'id');
            $where['meter_id'] = $meterInfo['id'];
        }
        if($taskStatus){
            $where['status'] = $taskStatus;
        }
        if($cmd){
            $where['cmd'] = $cmd;
        }
        if($consumer_name){
            $consumer_where['username'] = $consumer_name;
        }
        if($consumer_identity){
            $consumer_where['identity'] = $consumer_identity;
        }
        if($consumer_tel){
            $consumer_tel['tel'] = $consumer_tel;
        }
        if(isset($consumer_where)){
            $consumer_where['consumer_state'] = CONSUMER_STATE_NORMAL;
            $consumers= (new ConsumerService())->selectInfo($consumer_where,'meter_id');
            $tmp = [];
            foreach($consumers as $consumer){
                $tmp[] = $consumer['meter_id'];
            }
            $where['meter_id'] = ['in',$tmp];
        }
        $tasklist = (new TaskService())->getInfoPaginate($where,['M_Code' => $M_Code,'cmd' => $cmd,'name' => $consumer_name,'identity' => $consumer_identity,'tel' => $consumer_tel]);
        $tasklist = $this->parseTaskStatus($tasklist);
        $tasklist = $this->parseTaskCmd($tasklist);
        $this->assign('M_Code',$M_Code);
        $this->assign('cmd',$cmd);
        $this->assign('consumer_name',$consumer_name);
        $this->assign('consumer_identity',$consumer_identity);
        $this->assign('consumer_tel',$consumer_tel);
        $this->assign('tasklist',$tasklist);
        $this->assign('statusList',config('taskStatus'));
        $this->assign('cmdList',config('taskCmd'));
        $this->assign('taskStatus',$taskStatus);
        return view();
    }

    /**
     * 解析task状态
     * @param $tasklist
     * @return mixed
     */
    private function parseTaskStatus($tasklist){
        $taskStatus = config('taskStatus');
        foreach($tasklist as & $task){
            $task['status_name'] = $taskStatus[$task['status']];
        }
        return $tasklist;
    }

    /**
     * 解析task命令
     * @param $tasklist
     * @return mixed
     */
    private function parseTaskCmd($tasklist){
        $cmdlist = config('taskCmd');
        foreach($tasklist as & $task){
            $task['cmd_name'] = $cmdlist[$task['cmd']];
        }
        return $tasklist;
    }

    /**
     * 查看订单
     * @return \think\response\View
     */
    public function showOrder(){
        $id = input('id');
        $moneyLog = (new MoneyLogService())->findInfo(['id' => $id]);
        $this->assign('moneyLog',$moneyLog);
        $channels = config('channels');
        $this->assign('channels',$channels);
        $ordertypes = config('ordertypes');
        $this->assign('ordertypes',$ordertypes);
        return view();
    }

    /**
     * 处理失败task
     * @return \think\response\Json
     */
    public function handleTask(){
        $id = input('id');
        $status = input('status/d');
        $ignore_reason = input('ignore_reason');
        $ret['code'] = 200;
        $ret['msg'] = lang('Operation Success');
        try{
            $taskService = new TaskService();
            if(!$task = $taskService->findInfo(['id' => $id,'status' => TASK_FAIL])){
                exception(lang('Task Not Found'),ERROR_CODE_DATA_ILLEGAL);
            }
            if($status == TASK_RESENT){
                $updateTaskResult = upsertTask(['id' => $id,'status' => $status]);
                if(is_array($updateTaskResult)){
                    return json($updateTaskResult);
                }
                $newTaskData = $task->toArray();
                unset($newTaskData['id']);
                unset($newTaskData['create_time']);
                unset($newTaskData['update_time']);
                unset($newTaskData['status']);
                unset($newTaskData['seq_id']);
                $newTaskData['exec_times'] += 1;
                $insertNewtaskResult = upsertTask($newTaskData);
                if(is_array($insertNewtaskResult)){
                    return json($insertNewtaskResult);
                }
            }elseif($status == TASK_IGNORE){
                $updateTaskResult = upsertTask(['id' => $id,'status' => $status,'ignore_reason' => trim($ignore_reason)]);
                if(is_array($updateTaskResult)){
                    return json($updateTaskResult);
                }
            }else{
                exception(lang('Task Status Illegal'),ERROR_CODE_DATA_ILLEGAL);
            }
            model('app\admin\model\LogRecord')->record( 'Handle Task',['id' => $id,'status' => $status,'ignore_reason' => $ignore_reason]);
        }catch (\Exception $e){
            $ret['code'] =  $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

    /**
     *表具报修
     */
    public function fixList(){
        $status = input('status/d',FIX_STATUS_WAITING);
        $startDate = input('startDate',date('Y-m-d',strtotime('-7 days')));
        $endDate = input('endDate',date('Y-m-d'));
        if($status){
            $where['status'] = $status;
        }
        $where['create_time'] = ['between',[strtotime($startDate.' 00:00:00'),strtotime($endDate.' 23:59:59')]];
        $fixlist = (new FixService())->getInfoPaginate($where,['status' => $status,'startDate' => $startDate,'endDate' => $endDate]);
        $this->assign('fixlist',$fixlist);
        $this->assign('fixstatus',config('fixstatus'));
        $this->assign('status',$status);
        $this->assign('startDate',$startDate);
        $this->assign('endDate',$endDate);
        return view();
    }

    /**
     * 处理报修
     */
    public function dealFix(){
        $ret['code'] = 200;
        $ret['msg'] = lang('Operation Success');
        try{
            $data = input('data');
            $data = json_decode($data,true);
            if(!isset($data['id'])){
                exception(lang('Fix Id Require'),ERROR_CODE_DATA_ILLEGAL);
            }
            $fixService = new FixService();
            if(!$fix = $fixService->findInfo(['id' => $data['id']])){
                exception(lang('Fix Not Exists'),ERROR_CODE_DATA_ILLEGAL);
            }
            $data['status'] = FIX_STATUS_DEAL;
            if(!$fixService->upsert($data,false)){
                $error = $fixService->getError();
                Log::record(['处理表具报修失败:' => $error,'data' => $data],'error');
                exception(lang('Operation fail').' : '.$error,ERROR_CODE_DATA_ILLEGAL);
            }
            model('app\admin\model\LogRecord')->record( 'Deal Fix',$data);
        }catch (\Exception $e){
            $ret['code'] =  $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

    /**
     *留言建议
     */
    public function adviceList(){
        $status = input('status/d',ADVICE_STATUS_WAITING);
        $startDate = input('startDate',date('Y-m-d',strtotime('-7 days')));
        $endDate = input('endDate',date('Y-m-d'));
        if($status){
            $where['status'] = $status;
        }
        $where['create_time'] = ['between',[strtotime($startDate.' 00:00:00'),strtotime($endDate.' 23:59:59')]];
        $advicelist = (new AdviceService())->getInfoPaginate($where,['status' => $status,'startDate' => $startDate,'endDate' => $endDate]);
        $this->assign('advicelist',$advicelist);
        $this->assign('advicestatus',config('advicestatus'));
        $this->assign('status',$status);
        $this->assign('startDate',$startDate);
        $this->assign('endDate',$endDate);
        return view();
    }

    /**
     * 留言建议处理
     */
    public function dealAdvice(){
        $ret['code'] = 200;
        $ret['msg'] = lang('Operation Success');
        try{
            $data = input('data');
            $data = json_decode($data,true);
            if(!isset($data['id'])){
                exception(lang('Advice Id Require'),ERROR_CODE_DATA_ILLEGAL);
            }
            $adviceService = new AdviceService();
            if(!$advice = $adviceService->findInfo(['id' => $data['id']])){
                exception(lang('Advice Not Exists'),ERROR_CODE_DATA_ILLEGAL);
            }
            $data['status'] = ADVICE_STATUS_DEAL;
            if(!$adviceService->upsert($data,false)){
                $error = $adviceService->getError();
                Log::record(['处理留言建议失败:' => $error,'data' => $data],'error');
                exception(lang('Operation fail').' : '.$error,ERROR_CODE_DATA_ILLEGAL);
            }
            model('app\admin\model\LogRecord')->record( 'Deal Advice',$data);
        }catch (\Exception $e){
            $ret['code'] =  $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

}