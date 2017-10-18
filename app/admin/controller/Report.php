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
        $table = MONTH_FLOW_TABLE_NAME.$year;
        $where['company_id'] = $this->company_id;
        $monthAbbrs = getMonthAbbreviation();
        foreach($monthAbbrs as $index => $month){
            $tmp['consumers'] = Db::table($table)->where(['company_id' => $this->company_id,$month => ['neq',null]])->count();
            $tmp['cube'] = Db::table($table)->where($where)->sum($month);
            $tmp['cost'] = Db::table($table)->where($where)->sum($month.'_cost');
            $report[$index] = $tmp;
        }
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
        $report = [];
        $years = [];
        while( $endYear >= $startYear ){
            $years[] = $startYear;
            $table = MONTH_FLOW_TABLE_NAME.$startYear;
            $where['company_id'] = $this->company_id;
            $monthAbbrs = getMonthAbbreviation();
            foreach($monthAbbrs as $index => $month){
                $tmp['cube'][$index] = Db::table($table)->where($where)->sum($month);
                $tmp['cost'][$index] = Db::table($table)->where($where)->sum($month.'_cost');
            }
            $report[$startYear]['cube'] = array_sum($tmp['cube']);
            $report[$startYear]['cost'] = array_sum($tmp['cost']);
            $report[$startYear]['consumers'] = Db::table($table)->where($where)->count();
            $startYear += 1;
        }
        $this->assign('report',$report);
        $this->assign('years',$years);
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