<?php
/**
 * Created by PhpStorm.
 * User: ducongshu
 * Date: 2017/10/26
 * Time: 下午12:28
 */

namespace app\manage\service;

use app\manage\model\MeterdataModel;
use PHPExcel;
use PHPExcel_IOFactory;

class MeterDataService extends BasicService
{

    public function __construct(){
        $this->dbModel = new MeterDataModel();
    }

    /**
     * 获取信息 单条记录 降序
     * @param $where
     * @param $method
     * @param $field
     * @return mixed
     */
    public function findInfo($where,  $field = '',$M_Code = ''){
        return $this->dbModel->findInfo($where, $field,$M_Code);
    }

    /**
     * 获取信息 单条记录 升序
     * @param $where
     * @param $method
     * @param $field
     * @return mixed
     */
    public function findInfoAsc($where,  $field = '',$M_Code = ''){
        return $this->dbModel->findInfoAsc($where, $field,$M_Code);
    }

    /**
     * 获取信息 多条记录
     * @param $where
     * @param $method
     * @param $field
     * @return mixed
     */
    public function selectInfo($where = [],  $field = '',$M_Code = ''){
        return $this->dbModel->selectInfo($where, $field,$M_Code);
    }

    /**
     * 获取翻页信息
     * @param $where
     * @param $method
     * @param $field
     * @return mixed
     */
    public function getInfoPaginate($where = [], $param = [], $field = '',$M_Code = ''){
        return $this->dbModel->getInfoPaginate($where, $param, $field,$M_Code);
    }

    /**
     * 插入/更新
     * @param $data
     * @param bool|true $scene
     * @return mixed
     */
    public function upsert($data, $scene = true,$M_Code = ''){
        return $this->dbModel->upsert($data,$scene,$M_Code);
    }

    /**
     * @deprecated  应甲方需求,查询逻辑变更,此方法废弃
     * 问题:
     * 1.因为是sum方法,会把起时间与它前一天的diff差值也算进去,所以有误差,但可以和month_flow表中数据保持一致
     * 2.如果表具在选定时间段未上报,此方法返回数据中则不包含该表具用量信息,因为时间段筛选时没有该表具的记录
     * @param $table
     * @param $where
     * @return mixed
     */
    public function getAllMeterUsageData($table, $where){
        $connectString = 'mongodb://';
        if(config('database.username') && config('database.password')){
            $connectString .= config('database.username') . ':' .config('database.password') . '@';
        }
        $connectString .= config('database.hostname') . ':' . config('database.hostport') . '/' . config('database.database');
        $mongodb = new \MongoDB\Driver\Manager($connectString);
        $database = config('database.database');
        $command = new \MongoDB\Driver\Command([
            'aggregate' => $table,
            'pipeline' => [
                ['$match' => $where],
                ['$group' => ['_id' => ['meter_id' => '$meter_id','M_Code' => '$M_Code'],'sum' => ['$sum' => '$diffCube']]],

            ],
        ]);
        $result = $mongodb->executeCommand($database,$command);
        return $result->toArray();
    }

    /**
     * 获取表具时间段用量
     * @param $meter
     * @param $startDate
     * @param $endDate
     * @return array
     */
    public function getMeterUsage($meter, $startDate, $endDate){
        $maxUsage = $this->findInfo(['meter_id' => $meter['id'], 'source_type' => METER, 'create_time' => ['between', [strtotime($startDate . ' 00:00:00'), strtotime($endDate . ' 23:59:59')]]], '', $meter['M_Code']);
        $minUsage = $this->findInfoAsc(['meter_id' => $meter['id'], 'source_type' => METER, 'create_time' => ['between', [strtotime($startDate . ' 00:00:00'), strtotime($endDate . ' 23:59:59')]]], '', $meter['M_Code']);
        $diffUsage = ($maxUsage ? $maxUsage['totalCube'] : 0) - ($minUsage ? $minUsage['totalCube'] : 0);
        $usage = [
            'M_Code' => $meter['M_Code'],
            'consumer_name' => $meter->consumer->username,
            'consumer_tel' => $meter->consumer->tel,
            'detail_address' => $meter['detail_address'],
            'diffUsage' => $diffUsage,
            'setup_time' => isset($meter['setup_time']) ? date('Y-m-d',$meter['setup_time']) : date('Y-m-d',$meter['change_time']),
        ];
        return $usage;
    }

    /**
     * 导出表具用量excel
     * @param $data
     * @param $filename
     * @param $title
     */
    public function downloadMeterUsageExcel($data, $filename, $title){
        $filename=$filename.".xlsx";
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getActiveSheet()->mergeCells('A1:F1');
        $objPHPExcel->getActiveSheet()->mergeCells('A2:F2');
        $objPHPExcel->getActiveSheet()->setCellValue('A1',$title);
        $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(18);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setName('宋体') //字体
        ->setSize(20) //字体大小
        ->setBold(true); //字体加粗
        $objPHPExcel->getActiveSheet()->getStyle('A2')->getFont()->setName('宋体') //字体
        ->setSize(14) //字体大小
        ->setBold(true); //字体加粗
        $objPHPExcel->getActiveSheet()->getStyle('A1:A2')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->setCellValue('A2', '(导出日期：'.date('Y-m-d',time()).')');
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A3', '表号')
            ->setCellValue('B3', '姓名')
            ->setCellValue('C3', '安装地址')
            ->setCellValue('D3', '联系号码')
            ->setCellValue('E3', '表具用量')
            ->setCellValue('F3', '安装日期');
        $count = count($data);
        for ($i = 4; $i <= $count+3; $i++) {
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('A' . $i, $data[$i-4]['M_Code']);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $i, $data[$i-4]['consumer_name']);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('C' . $i, $data[$i-4]['detail_address']);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('D' . $i, $data[$i-4]['consumer_tel']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, $data[$i-4]['diffUsage']);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('F' . $i, $data[$i-4]['setup_time']);
        }

        $objPHPExcel->getActiveSheet()->setTitle($title);      //设置sheet的名称
        $objPHPExcel->setActiveSheetIndex(0);                   //设置sheet的起始位置
        $PHPWriter = PHPExcel_IOFactory::createWriter($objPHPExcel,"Excel2007");
        header('Content-Disposition: attachment;filename='.$filename);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $PHPWriter->save("php://output");
    }
}