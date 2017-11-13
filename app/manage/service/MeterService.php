<?php
/**
 * Created by PhpStorm.
 * User: ducongshu
 * Date: 2017/10/26
 * Time: 下午14:12
 */

namespace app\manage\service;

use app\manage\model\MeterModel;

class MeterService extends BasicService
{

    public function __construct(){
        $this->dbModel = new MeterModel();
    }


    /**
     * 获取表具上报数据
     * @param $meter_id
     * @param $M_Code
     * @param $startDate
     * @param $endDate
     * @param string $field
     * @return mixed
     */
    public function reportLogs($meter_id, $M_Code, $startDate, $endDate, $field = ''){
        $where['meter_id'] = $meter_id;
        $where['source_type'] = METER;
        $where['action_type'] = METER_REPORT;
        $where['create_time'] = ['between',[strtotime($startDate),strtotime($endDate)]];
        $meterDataService = new MeterDataService();
        $reportLogs = $meterDataService->selectInfo($where,$field,$M_Code);
        return $reportLogs;
    }

    /**
     *获取表具消费信息
     * @param $meter_id
     * @param $startDate
     * @param $endDate
     * @param $type     类型
     * @return mixed
     */
    public function moneyLogs($meter_id, $startDate, $endDate, $type){
        $where['from'] = $meter_id;
        $where['type'] = ['in',$type];
        $where['create_time'] = ['between',[strtotime($startDate),strtotime($endDate)]];
        $moneyLogService = new MoneyLogService();
        return $moneyLogService->selectInfo($where);
    }

    public function createExample_xls($filename,$title){
        $filename=$filename.".xlsx";
        $path = dirname(__FILE__);
        vendor("phpoffice.phpexcel.Classes.PHPExcel");
        vendor("phpoffice.phpexcel.Classes.PHPExcel.Writer.Excel5");
        vendor("phpoffice.phpexcel.Classes.PHPExcel.Writer.Excel2007");
        vendor("phpoffice.phpexcel.Classes.PHPExcel.IOFactory");
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getActiveSheet()->mergeCells('A1:C1');
        $objPHPExcel->getActiveSheet()->mergeCells('A2:C2');
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
        $objPHPExcel->getActiveSheet()->setCellValue('A1',$title);
        $objPHPExcel->getActiveSheet()->setCellValue('A2', '注：从第四行开始填入数据——'.date('Y-m-d',time()));
        $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setName('宋体') //字体
            ->setSize(20) //字体大小
            ->setBold(true); //字体加粗
        $objPHPExcel->getActiveSheet()->getStyle('A2')->getFont()->setName('宋体') //字体
            ->setSize(12) //字体大小
            ->setBold(true); //字体加粗
        $objPHPExcel->getActiveSheet()->getStyle('A1:A2')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->freezePane('A1');
        $objPHPExcel->getActiveSheet()->freezePane('A2');
        $objPHPExcel->getActiveSheet()->freezePane('A3');
        $objPHPExcel->getActiveSheet()->freezePane('A4');
        $objPHPExcel->setActiveSheetIndex()
            ->setCellValue('A3', '表号')
            ->setCellValue('B3', '扣除金额(元)')
            ->setCellValue('C3', '备注');
        $objPHPExcel->getActiveSheet()->setTitle('balanceStatistics');      //设置sheet的名称
        $objPHPExcel->setActiveSheetIndex(0);                   //设置sheet的起始位置
        $PHPWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel,"Excel2007");
        header('Content-Disposition: attachment;filename='.$filename);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $PHPWriter->save("php://output");
    }

    public function columnInfo($where,$field){
        return $this->dbModel->columnInfo($where,$field);
    }
}