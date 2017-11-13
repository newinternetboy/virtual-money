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
     * 获取翻页信息
     * @param $where
     * @param $whereor
     * @param $method
     * @param $field
     * @return mixed
     */
    public function getInfoPaginateWhereor($where = [],$whereor = [], $param = [], $field = ''){
        return $this->dbModel->getInfoPaginateWhereor($where,$whereor, $param, $field);
    }

    /**
     * 导出充值报表
     * @param $data
     * @param $filename
     * @param $title
     * @param $totalChargeTimes_rmb
     * @param $totalChargeMoney_rmb
     * @param $totalChargeTimes_deli
     * @param $totalChargeMoney_deli
     */
    public function downloadClearReport($data, $filename, $title,$date, $totalChargeTimes_rmb, $totalChargeMoney_rmb, $totalChargeTimes_deli, $totalChargeMoney_deli){
        $filename=$filename.".xlsx";
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getActiveSheet()->mergeCells('A1:F1');
        $objPHPExcel->getActiveSheet()->mergeCells('A2:F2');
        $objPHPExcel->getActiveSheet()->setCellValue('A1',$title);
//        $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(18);
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
//            ->setCellValue('D3', '得力币充值次数')
//            ->setCellValue('E3', '得力币充值金额')
            ->setCellValue('F3', '备注');
        $count = count($data);
        for ($i = 4; $i <= $count+3; $i++) {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $data[$i-4]['company_name']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $data[$i-4]['chargeTimes_rmb']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $data[$i-4]['chargeMoney_rmb']);
//            $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, $data[$i-4]['chargeTimes_deli']);
//            $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, $data[$i-4]['chargeMoney_deli']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $i, $data[$i-4]['desc']);
        }
        $last = $count + 4;
        $objPHPExcel->getActiveSheet()->setCellValue('A'.$last,'总计');
        $objPHPExcel->getActiveSheet()->setCellValue('B'.$last,$totalChargeTimes_rmb);
        $objPHPExcel->getActiveSheet()->setCellValue('C'.$last,$totalChargeMoney_rmb);
//        $objPHPExcel->getActiveSheet()->setCellValue('D'.$last,$totalChargeTimes_deli);
//        $objPHPExcel->getActiveSheet()->setCellValue('E'.$last,$totalChargeMoney_deli);
        $objPHPExcel->getActiveSheet()->getStyle('A'.$last)->getFont()->setName('宋体') //字体
//        ->setSize(20) //字体大小
        ->setBold(true); //字体加粗
        $objPHPExcel->getActiveSheet()->setTitle($title);      //设置sheet的名称
        $objPHPExcel->setActiveSheetIndex(0);                   //设置sheet的起始位置
        $PHPWriter = PHPExcel_IOFactory::createWriter($objPHPExcel,"Excel2007");
        header('Content-Disposition: attachment;filename='.$filename);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $PHPWriter->save("php://output");
    }

    /**
     * 下载充值明细
     * @param $data
     * @param $filename
     * @param $title
     * @param $startTime
     * @param $endTime
     * @param $total
     */
    public function downloadChargeDetail($data, $filename, $title, $startDate, $endDate, $total){
        $filename=$filename.".xlsx";
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getActiveSheet()->mergeCells('A1:F1');
        $objPHPExcel->getActiveSheet()->mergeCells('A2:F2');
        $objPHPExcel->getActiveSheet()->setCellValue('A1',$title);
//        $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(18);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setName('宋体') //字体
        ->setSize(20) //字体大小
        ->setBold(true); //字体加粗
        $objPHPExcel->getActiveSheet()->getStyle('A2')->getFont()->setName('宋体') //字体
        ->setSize(14) //字体大小
        ->setBold(true); //字体加粗
        $objPHPExcel->getActiveSheet()->getStyle('A1:A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->setCellValue('A2', "(开始日期:$startDate 结束日期:$endDate)");
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A3', '表号')
            ->setCellValue('B3', '姓名')
            ->setCellValue('C3', '金额')
//            ->setCellValue('D3', '币种')
            ->setCellValue('E3', '类型')
            ->setCellValue('F3', '日期');
        $count = count($data);
        for ($i = 4; $i <= $count+3; $i++) {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $data[$i-4]->meter['M_Code']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $data[$i-4]->meter->consumer['username']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $data[$i-4]['money']);
//            $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, $data[$i-4]['money_type'] == MONEY_TYPE_RMB ? '人民币' : '得力币');
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, $data[$i-4]['channel'] == MONEY_CHANNEL_WEIXIN ? '微信' : '清分');
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $i, $data[$i-4]['create_time']);
        }
        $last = $count + 4;
        $objPHPExcel->getActiveSheet()->setCellValue('A'.$last,'总计');
        $total_str = '';
        foreach($total as $item){
            $total_str .= ($item['money_type'] == MONEY_TYPE_RMB ? '人民币' : '得力币').$item['total']." ";
        }
        $objPHPExcel->getActiveSheet()->setCellValue('B'.$last,$total_str);
        $objPHPExcel->getActiveSheet()->getStyle('A'.$last)->getFont()->setName('宋体') //字体
//        ->setSize(20) //字体大小
        ->setBold(true); //字体加粗
        $objPHPExcel->getActiveSheet()->setTitle($title);      //设置sheet的名称
        $objPHPExcel->setActiveSheetIndex(0);                   //设置sheet的起始位置
        $PHPWriter = PHPExcel_IOFactory::createWriter($objPHPExcel,"Excel2007");
        header('Content-Disposition: attachment;filename='.$filename);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $PHPWriter->save("php://output");
    }

    /**
     * 下载订单明细
     * @param $data
     * @param $filename
     * @param $title
     * @param $startTime
     * @param $endTime
     * @param $total
     */
    public function downloadOrder($data,$filename,$title){
        $filename=$filename.".xlsx";
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getActiveSheet()->mergeCells('A1:G1');
        $objPHPExcel->getActiveSheet()->mergeCells('A2:G2');
        $objPHPExcel->getActiveSheet()->setCellValue('A1',$title);
//        $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(18);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setName('宋体') //字体
        ->setSize(20) //字体大小
        ->setBold(true); //字体加粗
        $objPHPExcel->getActiveSheet()->getStyle('A2')->getFont()->setName('宋体') //字体
        ->setSize(14) //字体大小
        ->setBold(true); //字体加粗
        $objPHPExcel->getActiveSheet()->getStyle('A1:A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->setCellValue('A2', "(导出日期:".date('Y-m-d'.")"));
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A3', lang('From M_Code'))
            ->setCellValue('B3', lang('To M_Code'))
            ->setCellValue('C3', lang('Order Id'))
            ->setCellValue('D3', lang('Order Money'))
            ->setCellValue('E3', lang('Money Type'))
            ->setCellValue('F3', lang('Order Type'))
            ->setCellValue('G3', lang('Order Channel'))
            ->setCellValue('H3', lang('Date'));
        $count = count($data);
        for ($i = 4; $i <= $count+3; $i++) {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, isset($data[$i-4]['from']) ? $data[$i-4]->meter['M_Code'] : '-');
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, isset($data[$i-4]['to']) ? $data[$i-4]->tometer['M_Code'] : '-');
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, isset($data[$i-4]['order_id']) ? $data[$i-4]['order_id'] : '-');
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, $data[$i-4]['money']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, $data[$i-4]['money_type'] == MONEY_TYPE_RMB ? lang('Rmb') : lang('Deli'));
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $i, config('ordertypes')[$data[$i-4]['type']]);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $i, config('channels')[$data[$i-4]['channel']]);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $i, $data[$i-4]['create_time']);
        }
        $objPHPExcel->getActiveSheet()->setTitle($title);      //设置sheet的名称
        $objPHPExcel->setActiveSheetIndex(0);                   //设置sheet的起始位置
        $PHPWriter = PHPExcel_IOFactory::createWriter($objPHPExcel,"Excel2007");
        header('Content-Disposition: attachment;filename='.$filename);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $PHPWriter->save("php://output");
    }
}