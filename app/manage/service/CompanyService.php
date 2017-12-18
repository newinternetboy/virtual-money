<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/10/24
 * Time: 下午12:02
 */

namespace app\manage\service;

use app\manage\model\CompanyModel;

class CompanyService extends BasicService
{

    public function __construct(){
        $this->dbModel = new CompanyModel();
    }

    /*
    *处理Excel导出
    *@param $datas array 设置表格数据
    *@param $filename str 设置文件名
    */
    public function create_xls($data,$filename,$title,$total){
        $filename=$filename.".xlsx";
        $path = dirname(__FILE__);
        vendor("phpoffice.phpexcel.Classes.PHPExcel");
        vendor("phpoffice.phpexcel.Classes.PHPExcel.Writer.Excel5");
        vendor("phpoffice.phpexcel.Classes.PHPExcel.Writer.Excel2007");
        vendor("phpoffice.phpexcel.Classes.PHPExcel.IOFactory");
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getActiveSheet()->mergeCells('A1:D1');
        $objPHPExcel->getActiveSheet()->mergeCells('A2:D2');
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
            ->setCellValue('A3', '序号')
            ->setCellValue('B3', '运营商')
            ->setCellValue('C3', '用户数量')
            ->setCellValue('D3', '表具余额');
        $count = count($data);
        for ($i = 4; $i <= $count+3; $i++) {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $data[$i-4]['company_name']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $data[$i-4]['OPT_ID']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $data[$i-4]['count']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, $data[$i-4]['meterbalance']);
        }
        $last = $count + 4;
        $objPHPExcel->getActiveSheet()->setCellValue('A'.$last,$total);
        $objPHPExcel->getActiveSheet()->mergeCells('A'.$last.':D'.$last);
        $objPHPExcel->getActiveSheet()->getStyle('A'.$last)->getFont()->setName('宋体') //字体
            ->setSize(12) //字体大小
            ->setBold(true); //字体加粗
        $objPHPExcel->getActiveSheet()->setTitle('balanceStatistics');      //设置sheet的名称
        $objPHPExcel->setActiveSheetIndex(0);                   //设置sheet的起始位置
        $PHPWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel,"Excel2007");
        header('Content-Disposition: attachment;filename='.$filename);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $PHPWriter->save("php://output");
    }

    /**
     * 运营商充值
     * @param $data
     * @return int|true
     */
    public function charge($data){
        return $this->dbModel->where(['id' => $data['id']])->setInc('charge_limit',$data['charge_limit']);
    }
}