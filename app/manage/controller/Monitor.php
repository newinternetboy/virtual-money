<?php
/**
 * Created by PhpStorm.
 * User: 杜从书
 * Date: 2017/12/24
 * Time: 下午12:01
 */

namespace app\manage\controller;

use app\manage\service\AreaService;
use app\manage\service\CompanyService;
use app\manage\service\MeterService;
use app\manage\service\MoneyLogService;

/**
 * 图形
 * Class Chart
 * @package app\Chart\controller
 */
class Monitor extends Admin
{
    /**
     * @return mixed;
     * 加载整个图表；
     */
    public function index(){
        return $this->fetch();
    }

    /**
     * @return \think\response\Json
     * 获取最近七天每天的充值钱数
     */
    public function recharge(){
        $start = strtotime(date('Y-m-d',strtotime('-6 days'))." 00:00:00");
        $end =time();
        $moneyLogService = new MoneyLogService();
        $money_number=[];
        $date=[];
        while($start < $end){
            $rmb_where = [
                'to' => null, //to字段不存在是充值记录
                'type' => MONEY_PAY,
                'money_type' => MONEY_TYPE_RMB,
                'create_time' => ['between', [$start, $start+86399]]
            ];
            $money_number[] = $moneyLogService->sums($rmb_where, 'money');
            $date[] = date('m-d',$start);
            $start+=86400;
        }
        $data=[
            'money_number'=>$money_number,
            'date'=>$date
        ];
        return json($data);
    }

    /**
     * @return mixed
     * 获取本月的充值总数
     */
    public function monthRecharge(){
        $start = strtotime(date('Y-m')."-1 00:00:00");
        $end =time();
        $moneyLogService = new MoneyLogService();
        $rmb_where = [
            'to' => null, //to字段不存在是充值记录
            'type' => MONEY_PAY,
            'money_type' => MONEY_TYPE_RMB,
            //'channel' => ['in', [MONEY_CHANNEL_WEIXIN]],
            'create_time' => ['between', [$start, $end]]
        ];
        $money = $moneyLogService->sums($rmb_where, 'money');
        return $money;
    }

    /**
     * @return \think\response\Json
     * 获取运营商本月充值前五
     */
    public function getCompanyRecharge(){
        $start = strtotime(date('Y-m')."-1 00:00:00");
        $end =time();
        $moneyLogService = new MoneyLogService();
        $where['to'] =null;
        $where['type']= MONEY_PAY;
        $where['money_type'] = MONEY_TYPE_RMB;
        $where['create_time'] = ['$gte' => $start,'$lte' => $end];
        $result = $moneyLogService->getAllGroupByCompany('money_log',$where);
        $res=$result[0]->result;
        $companyservice = new companyService();
        $company_name = [];
        $sum = [];
        foreach($res as $value){
            $company = $companyservice->findInfo(['id'=>$value->_id->company_id],'company_name');
            $company_name[]=$company['company_name'];
            $sum[] = $value->sum;
        }
        $data['company'] = array_reverse($company_name);
        $data['sum'] = array_reverse($sum);
       return json($data);
    }

    /**
     * @return \think\response\Json
     * 获取最近七日报装每日的报装量
     */
    public function getSetupNumber(){
        $start = strtotime(date('Y-m-d',strtotime('-6 days'))." 00:00:00");
        $end =time();
        $meterService = new MeterService();
        $number=[];
        $date=[];
        while($start < $end){
            $where = [
                'meter_status' => METER_STATUS_BIND,
                'meter_life' => METER_LIFE_ACTIVE,
                'setup_time' => ['between', [$start, $start+86399]]
            ];
            $number[] = $meterService->counts($where);
            $date[] = date('m-d',$start);
            $start+=86400;
        }
        $data=[
            'number'=>$number,
            'date'=>$date
        ];
        return json($data);
    }


    /**
     * @return \think\response\Json
     * 获取本月报装的水,电,气的量
     */
    public function getSetupNumberByM_Type(){
        $start = strtotime(date('Y-m')."-1 00:00:00");
        $end =time();
        $meterService = new MeterService();
        $where_water = [
            'meter_status' => METER_STATUS_BIND,
            'meter_life' => METER_LIFE_ACTIVE,
            'setup_time' => ['between', [$start, $end]],
            'M_Type'     => METER_TYPE_WATER
        ];
        $where_electric = [
            'meter_status' => METER_STATUS_BIND,
            'meter_life' => METER_LIFE_ACTIVE,
            'setup_time' => ['between', [$start, $end]],
            'M_Type'     => METER_TYPE_ELECTRICITY
        ];
        $where_gas = [
            'meter_status' => METER_STATUS_BIND,
            'meter_life' => METER_LIFE_ACTIVE,
            'setup_time' => ['between', [$start, $end]],
            'M_Type'     => METER_TYPE_GAS
        ];
        $number_water = $meterService->counts($where_water);
        $number_electric = $meterService->counts($where_electric);
        $number_gas = $meterService->counts($where_gas);
        $data=[
            $number_water,
            $number_electric,
            $number_gas
        ];
        return json($data);
    }

    /**
     * @return \think\response\Json
     * 获取全国每个城市的所有报装数量
     */
    public function getM_numberForProvince(){
        $province=['北京市','天津市','上海市','重庆市','河北省','山西省','辽宁省','吉林省','黑龙江省','江苏省','浙江省','安徽省','福建省','江西省','山东省','河南省','湖北省','湖南省','广东省','海南省','四川省','贵州省','云南省','陕西省','甘肃省','青海省','台湾省','内蒙古自治区','广西壮族自治区','西藏自治区','宁夏回族自治区','新疆维吾尔自治区','香港特别行政区','澳门特别行政区'];
        $areaService = new AreaService();
        $arrs=[];
        foreach($province as $value){
            $arrs[$value]['ids']=$areaService->columnInfo(['province'=>$value],'id');
            $arrs[$value]['number'] = 0;
        }
        $meterService = new MeterService();
        $meters = $meterService->selectInfo(['meter_status'=>METER_STATUS_BIND,'meter_life'=>METER_LIFE_ACTIVE],'M_Address');

        foreach($arrs as & $arr){
            foreach($meters as $meter){
                if(in_array($meter['M_Address'],$arr['ids'])){
                    $arr['number']+=1;
                }
            }
            unset($arr['ids']);
        }
        return json($arrs);
    }

    /**
     * @return \think\response\Json
     * 获取全国报装量排名前五的省份
     */
    public function getM_numberForTop_five(){
        $result = $this->getM_numberForProvince();
        $data = json_decode($result->getContent(),true);
        $arrs=[];
        foreach($data as $key=>$value){
            $arrs[$key]=$value['number'];
        }
        asort($arrs);
        $arrs = array_slice($arrs,-5);
        $province=[];
        $number=[];
        foreach($arrs as $key=>$val){
            $province[] = $key;
            $number[] = $val;
        }
        $datas=[
            'province'=>$province,
            'number'=>$number
        ];
        return json($datas);
    }

}
