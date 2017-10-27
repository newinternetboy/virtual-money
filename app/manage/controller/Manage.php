<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/10/24
 * Time: 下午12:01
 */

namespace app\manage\controller;

use app\manage\service\CompanyService;
use app\manage\service\UserService;
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
        $data['id'] = input('id');
        $data['company_name'] = input('company_name');
        $data['OPT_ID'] = input('OPT_ID');
        $data['address'] = input('address');
        $data['quality'] = input('quality');
        $data['contacts_tel'] = input('contacts_tel');
        $data['contacts_name'] = input('contacts_name');
        $data['fax'] = input('fax');
        $data['legal_person'] = input('legal_person');
        $data['bank_name'] = input('bank_name');
        $data['bank_card'] = input('bank_card');
        $data['tax_code'] = input('tax_code');
        $data['sms_tel'] = input('sms_tel');
        $data['secret_key_url'] = input('secret_key_url');
        $data['secret_key'] = input('secret_key');
        $data['charge_status'] = input('charge_status/d');
        $data['charge_date'] = input('charge_date');
        $data['limit_times'] = input('limit_times');
        $data['left_times'] = input('left_times');
        $data['desc'] = input('desc');
        $data['alarm_tel'] = input('alarm_tel');
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
}