<?php
/**
 * Created by PhpStorm.
 * User: ducongshu
 * Date: 2017/10/25
 * Time: 下午15:30
 */

namespace app\manage\controller;

use app\manage\service\CompanyService;


class Report extends Admin
{
    //月用量报表；
    public function monthReport(){
        $year       = input('year') ? input('year') : date('Y');
        $companyService = new CompanyService();
        $companys   = $companyService->selectInfo();
        $company_id = input('company_id') ? input('company_id') : $companys[0]['id'];
        $company = $companyService->findInfo(['id'=>$company_id],'company_name');
        $where['company_id'] = $company_id;
        $report =getMonthReport($year,$where);
        $this->assign('report',$report);
        $this->assign('companys',$companys);
        $this->assign('year',$year);
        $this->assign('company_id',$company_id);
        $this->assign('company_name',$company['company_name']);
        return view();
    }
    //年用量报表；
    public function yearReport(){
        $startYear = input('startYear') ? input('startYear/d') : date('Y',strtotime('-5 years'));
        $endYear = input('endYear') ? input('endYear/d') : date('Y');
        $companyService = new CompanyService();
        $companys = $companyService->selectInfo();
        $company_id = input('company_id') ? input('company_id') : $companys[0]['id'];
        $company = $companyService->findInfo(['id'=>$company_id],'company_name');
        $where['company_id'] = $company_id;
        $res = getYearReport($startYear,$endYear,$where);
        $this->assign('companys',$companys);
        $this->assign('company_id',$company_id);
        $this->assign('company_name',$company['company_name']);
        $this->assign('startYear',$startYear);
        $this->assign('endYear',$endYear);
        $this->assign('report',$res['report']);
        $this->assign('years',$res['years']);
        return view();
    }
}