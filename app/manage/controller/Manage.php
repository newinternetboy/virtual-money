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
use app\manage\service\MeterService;
use app\manage\service\MoneyLogService;
use app\manage\service\UserService;
use app\manage\service\TaskService;
use app\manage\service\Money_logService;
use think\Log;
use think\Loader;
use MongoDB\BSON\ObjectId;

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
            $where['id'] = $company;
        }
        $companys = $companyService->getInfoPaginate($where,'OPT_ID,company_name');
        $companysAll = $companyService->selectInfo($where,'company_name');
        $this->assign('companys',$companys);
        $this->assign('companysAll',$companysAll);
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
            $roles = model('app\admin\model\role')->getList(['company_id' => $companyInfo['id'],'status' => 1]);
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

    //获取单条商铺信息；
    public function meterInfo(){
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

    public function uploadexcel(){
        // 获取表单上传文件
        $file = request()->file('excel');
        if(!$file){
            $ajaxReturn['status'] = 401;
            $ajaxReturn['msg'] = '请先上传文件！';
        }else{
            // 移动到框架应用根目录/public/uploads/ 目录下
            $info = $file->validate(['size'=>10*1024*1024,'ext'=>'xls,xlsx'])->move(ROOT_PATH . 'public' . DS . 'uploads');
            if($info){
                $localfile = ROOT_PATH . 'public' . DS . 'uploads'. DS .$info->getSaveName();
                if($filedata=$this->getFileData($localfile)){
                    $ajaxReturn = $this->importToDb($filedata);
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
        /**读取excel文件中的第一个工作表*/
        $sheet = $PHPExcel->getSheet(0);
        /**取得一共有多少行*/
        $highestRow = $sheet->getHighestRow();
        /**从第二行开始输出，因为excel表中第一行为列名*/
        if($highestRow >= 4 ){
            for ($row = 4; $row <= $highestRow; $row++){//行数是以第2行开始
                $M_Code = $sheet->getCellByColumnAndRow(0,$row)->getValue();
                $M_Code = is_object($M_Code) ? $M_Code->__toString() : $M_Code; //避免导入的value是object
                $number = $sheet->getCellByColumnAndRow(1,$row)->getValue();
                $number = is_object($number) ? $number->__toString() : $number; //避免导入的value是object
                $remark = $sheet->getCellByColumnAndRow(2,$row)->getValue();
                $remark = is_object($remark) ? $remark->__toString() : $remark; //避免导入的value是object
                $data[] = ['M_Code' => $M_Code,'number'=> $number,'remark' => $remark];
            }
        }
        return $data;
    }

    public function importToDb($filedata){
        $ajaxReturn['status'] = 200;
        $ajaxReturn['msg'] = '扣除余额成功';
        $taskService = new TaskService();
        $meterService = new MeterService();
        $metercodes ='';
        foreach($filedata as $key=>$value){
            $metercodes.=$value['M_Code'].',';
        }
        $metercodes = trim($metercodes,',');
        $codes = explode(',',$metercodes);
        $where['M_Code'] = ['in',$metercodes];
        $where['meter_life'] = METER_LIFE_ACTIVE;
        $where['meter_status'] = METER_STATUS_BIND;
        $meters = $meterService->columnInfo($where,'M_Code');
        $diff = array_diff($codes,$meters);
        if(!empty($meters)&&isset($meters)&&empty($diff)){
            $result =$this->addAllTask($filedata);
            if($result['status'] == 2000){
                $ajaxReturn['status'] = 200;
                $ajaxReturn['msg'] = $result['msg'];
                return $ajaxReturn;
            }
            $ajaxReturn['status'] = 201;
            $ajaxReturn['msg'] = $result['msg'];
            return $ajaxReturn;

        }else{
            $arr=[];
            foreach($diff as $key=>$value){
                $arr[$key]['code']=$value;
                $arr[$key]['reson'] = '表号不存在';
            }
            $ajaxReturn['status'] = 202;
            $ajaxReturn['msg'] = $arr;
            return $ajaxReturn;
        }

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

    public function addAllTask($datas){
        $result['status'] = 2000;
        $result['msg'] = '扣除余额成功';
        $arrs=array();
        foreach($datas as $key=> $value){
            $meterService = new MeterService();
            $money_logService = new Money_logService();
            $taskService = new TaskService();
            $where['M_Code'] = strval($value['M_Code']);
            if(!$meter=$meterService->findInfo($where,'id,company_id')){
                $arrs[$key]['code']=$value['M_Code'];
                $arrs[$key]['reson']=$meterService->getError();
                Log::record(['表号不存在' => $value['M_Code']],'error');
                continue;
            }
            $money_log['money_type'] = MONEY_TYPE_RMB;
            $money_log['money'] = $value['number'];
            $money_log['type'] = MONEY_DEDUCT;
            $money_log['to'] = $meter['id'];
            $money_log['reson'] = $value['remark'];
            $money_log['channel'] = MONEY_CHANNEL_MANAGE;
            $money_log['create_time'] = time();
            $money_log['company_id'] = $meter['company_id'];
            if(!$money_log_id=$money_logService->upsert($money_log)){
                $arrs[$key]['code']=$value['M_Code'];
                $arrs[$key]['reson']=$money_logService->getError();
                Log::record(['添加money_log失败' => $money_log],'error');
                continue;
            }

            $task['meter_id'] = $meter['id'];
            $task['cmd'] = 'deduct';
            $task['param'] = -$value['number'];
            $task['money_log_id'] = $money_log_id;
            $task['balance_rmb'] = 0;
            $task['status'] = TASK_WAITING;
            $task['seq_id'] = getAutoIncId('autoinc',['name' => 'task','meter_id' => $meter['id']],'seq_id',1);
            $task['create_time'] = time();
            if(!$taskService->upsert($task)){
                $arrs[$key]['code']=$value['M_Code'];
                $arrs[$key]['reson']=$taskService->getError();
                Log::record(['存入task失败' => $task],'error');
                continue;
            }
        }

        if(!empty($arrs)&&isset($arrs)){
            $result['status'] = 2001;
            $result['msg'] = $arrs;
            return $result;
        }
        return $result;

    }

    public function uploaddata(){
        $M_Code = trim(input('M_Code'),';');
        $money = input('money');
        $message = input('message');
        $code = explode(';',$M_Code);
        if(!isset($M_Code)||empty($M_Code)){
            $ajaxReturn['status'] = 401;
            $ajaxReturn['msg'] = '表号不能为空！';
            return $ajaxReturn;
        }
        if(!isset($money)||empty($money)){
            $ajaxReturn['status'] = 402;
            $ajaxReturn['msg'] = '扣款金额不能为空！';
            return $ajaxReturn;
        }
        $arr=[];
        foreach($code as $key=>$value){
            $arr[$key]['M_Code']=$value;
            $arr[$key]['number'] = $money;
            $arr[$key]['remark'] = $message;
        }
        $ajaxReturn = $this->importToDb($arr);
        return json($ajaxReturn);
    }
    
}