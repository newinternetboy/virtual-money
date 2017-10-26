<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/10/16
 * Time: 上午10:59
 */

namespace app\admin\controller;

use think\Db;

/**
 * 报表
 * Class Report
 * @package app\admin\controller
 */
class Report extends Admin
{

    /**
     * 月报表
     * @return \think\response\View
     */
    public function monthReport(){
        $year = input('year') ? input('year') : date('Y');
        $where['company_id'] = $this->company_id;
        $report =getMonthReport($year,$where);
        $this->assign('report',$report);
        $this->assign('year',$year);
        return view();
    }

    /**
     * 年报表
     * @return \think\response\View
     */
    public function yearReport(){
        $startYear = input('startYear') ? input('startYear/d') : date('Y',strtotime('-5 years'));
        $endYear = input('endYear') ? input('endYear/d') : date('Y');
        $this->assign('startYear',$startYear);
        $this->assign('endYear',$endYear);
        $where['company_id'] = $this->company_id;
        $res = getYearReport($startYear,$endYear,$where);
        $this->assign('report',$res['report']);
        $this->assign('years',$res['years']);
        return view();
    }

    /**
     * 表具安装统计
     * @return \think\response\View
     */
    public function setupReport(){
        $year = input('year') ? input('year') : date('Y');
        $where['company_id'] = $this->company_id;
        for($month = 1;$month <= 12;$month++){
            $where['company_id'] = $this->company_id;
            $startTime = strtotime($year.'-'.$month.'-1 00:00:00');
            $endTime = strtotime('+1 month',$startTime) - 1;
            $where['setup_time'] = ['between',[$startTime,$endTime]];
            $report[$month] = model('Meter')->getAllMeterInfo($where,'count');
        }
        $this->assign('report',$report);
        $this->assign('year',$year);
        return view();
    }
}