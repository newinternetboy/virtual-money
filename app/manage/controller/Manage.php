<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/10/24
 * Time: 下午12:01
 */

namespace app\manage\controller;

use app\manage\service\CompanyService;

class Manage extends Admin
{

    public function company(){
        $company = input('company');
        $companyService = new CompanyService();
        $where=[];
        if( $company ){
            $where['id'] = $company;
        }
        $companys = $companyService->getInfoPaginate($where);
        $companysAll = $companyService->selectInfo();
        $this->assign('companys',$companys);
        $this->assign('companysAll',$companysAll);
        $this->assign('company',$company);
        return view();
    }

    public function saveCompany(){
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

        $companyService = new CompanyService();
        if( !$companyService->upsert($data) ){
            $this->error($companyService->getError(),url('manage/manage/company'));
        }
        $this->success('操作成功',url('manage/manage/company'));
    }
}