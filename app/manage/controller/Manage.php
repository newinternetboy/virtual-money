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
use think\Log;
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
        $where = [];
        $con_where = [];
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
            $con_where['username'] = $username;
        }
        $param['company_name'] = $company_name;
        $param['detail_address'] = $detail_address;
        $param['M_Code'] = $M_Code;
        $param['username'] = $username;
        $meterService = new MeterService();
        $consumerService = new ConsumerService();
        $meter = $meterService->getInfoPaginate($where,$param);
        if($con_where){
            foreach($meter as $key => $value){
                if(isset($value['M_Type'])&&$value['M_Type']==1){
                    $value['M_Type']="水表";
                }
                if(isset($value['M_Type'])&&$value['M_Type']==2){
                    $value['M_Type']="电表";
                }
                if(isset($value['M_Type'])&&$value['M_Type']==3){
                    $value['M_Type']="气表";
                }
                if(!isset($value['U_ID'])){
                    unset($meter[$key]);
                    continue;
                }else{
                    $con_where['id'] = new ObjectId($value['U_ID']);
                    if(!$consumerService->findInfo($con_where)){
                        unset($meter[$key]);
                    }
                }
            }
        }else{
            foreach($meter as & $value){
                if(isset($value['M_Type'])&&$value['M_Type']==1){
                    $value['M_Type']="水表";
                }
                if(isset($value['M_Type'])&&$value['M_Type']==2){
                    $value['M_Type']="电表";
                }
                if(isset($value['M_Type'])&&$value['M_Type']==3){
                    $value['M_Type']="气表";
                }
            }
        }

        $this->assign('meter',$meter);
        $this->assign('company_name',$company_name);
        $this->assign('detail_address',$detail_address);
        $this->assign('M_Code',$M_Code);
        $this->assign('username',$username);
        return $this->fetch();
    }

    //获取单条商铺信息；
    public function meterInfo(){
        $company_name = input('company_name');
        $detail_address = input('detail_address');
        $M_Code = input('M_Code');
        $name = input('username');
        $id = input('id');
        $meterService = new MeterService();
        $consumerService = new ConsumerService();
        $companyService = new CompanyService();
        $meter = $meterService->findInfo(['id'=>$id,'meter_life'=>METER_LIFE_ACTIVE]);
        switch($meter['meter_status']){
            case METER_STATUS_CHANGED:
                $meter['meter_status'] = '被更换的旧表';
                break;
            case METER_STATUS_BIND:
                $meter['meter_status'] = '已绑定';
                break;
            case METER_STATUS_DELETE:
                $meter['meter_status'] = '已删除';
                creak;
            default:
                $meter['meter_status'] = '新表';
        }
        if(isset($meter['M_Type'])&&$meter['M_Type']==METER_TYPE_WATER){
            $meter['M_Type']="水表";
        }
        if(isset($meter['M_Type'])&&$meter['M_Type']==METER_TYPE_ELECTRICITY){
            $meter['M_Type']="电表";
        }
        if(isset($meter['M_Type'])&&$meter['M_Type']==METER_TYPE_GAS){
            $meter['M_Type']="气表";
        }
        $consumer = $consumerService->findInfo(['meter_id'=>$id]);
        $company=[];
        if(isset($meter['company_id'])){
            $company = $companyService->findInfo(['id'=>$meter['company_id']]);
        }
        $this->assign('meter',$meter);
        $this->assign('consumer',$consumer);
        $this->assign('company',$company);
        $this->assign('company_name',$company_name);
        $this->assign('detail_address',$detail_address);
        $this->assign('M_Code',$M_Code);
        $this->assign('name',$name);
        return view();
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
}