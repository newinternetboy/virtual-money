<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/10/31
 * Time: 下午6:49
 */

namespace app\manage\service;

use app\manage\model\MoneyLogModel;

use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Style_Alignment;

class MoneyLogService extends BasicService
{
    public function __construct(){
        $this->dbModel = new MoneyLogModel();
    }

    /**
     * 导出充值日报
     * @param $data
     * @param $filename
     * @param $title
     * @param $totalChargeTimes_rmb
     * @param $totalChargeMoney_rmb
     * @param $totalChargeTimes_deli
     * @param $totalChargeMoney_deli
     */
    public function downloadDayReport($data, $filename, $title,$date, $totalChargeTimes_rmb, $totalChargeMoney_rmb, $totalChargeTimes_deli, $totalChargeMoney_deli){
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
        $objPHPExcel->getActiveSheet()->getStyle('A1:A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->setCellValue('A2', "(日期： $date)");
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A3', '名称')
            ->setCellValue('B3', '人民币充值次数')
            ->setCellValue('C3', '人民币充值金额')
            ->setCellValue('D3', '得力币充值次数')
            ->setCellValue('E3', '得力币充值金额')
            ->setCellValue('F3', '备注');
        $count = count($data);
        for ($i = 4; $i <= $count+3; $i++) {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $data[$i-4]['company_name']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $data[$i-4]['chargeTimes_rmb']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $data[$i-4]['chargeMoney_rmb']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, $data[$i-4]['chargeTimes_deli']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, $data[$i-4]['chargeMoney_deli']);
        }
        $last = $count + 4;
        $objPHPExcel->getActiveSheet()->setCellValue('A'.$last,'总计');
        $objPHPExcel->getActiveSheet()->setCellValue('B'.$last,$totalChargeTimes_rmb);
        $objPHPExcel->getActiveSheet()->setCellValue('C'.$last,$totalChargeMoney_rmb);
        $objPHPExcel->getActiveSheet()->setCellValue('D'.$last,$totalChargeTimes_deli);
        $objPHPExcel->getActiveSheet()->setCellValue('E'.$last,$totalChargeMoney_deli);
        $objPHPExcel->getActiveSheet()->getStyle('A'.$last)->getFont()->setName('宋体') //字体
        ->setSize(20) //字体大小
        ->setBold(true); //字体加粗
        $objPHPExcel->getActiveSheet()->setTitle($title);      //设置sheet的名称
        $objPHPExcel->setActiveSheetIndex(0);                   //设置sheet的起始位置
        $PHPWriter = PHPExcel_IOFactory::createWriter($objPHPExcel,"Excel2007");
        header('Content-Disposition: attachment;filename='.$filename);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $PHPWriter->save("php://output");
    }
}