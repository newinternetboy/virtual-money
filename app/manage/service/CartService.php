<?php
/**
 * Created by PhpStorm.
 * User: ducongshu
 * Date: 2017/11/02
 * Time: 下午15:35
 */

namespace app\manage\service;

use app\manage\model\CartModel;

class CartService extends BasicService
{

    public function __construct(){
        $this->dbModel = new CartModel();
    }

    public function getAllGroupByShop($table, $where){
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
                ['$group' => ['_id' => ['sid' => '$sid'],'count' => ['$sum' => 1],'sum' => ['$sum' => '$totalCost']]],

            ],
        ]);
        $result = $mongodb->executeCommand($database,$command);
        return $result->toArray();
    }

    public function getPaginateGroupByShop($table, $where,$skip,$limit){
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
                ['$group' => ['_id' => ['sid' => '$sid'],'count' => ['$sum' => 1],'sum' => ['$sum' => '$totalCost']]],
                ['$skip' => $skip],
                ['$limit' => $limit],

            ],

        ]);
        $result = $mongodb->executeCommand($database,$command);
        return $result->toArray();
    }

    public function updateCart($where,$change){
        return $this->dbModel->updateCart($where,$change);
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
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:H1');
        $objPHPExcel->getActiveSheet()->mergeCells('A2:H2');
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
            ->setCellValue('A3', '商铺名称')
            ->setCellValue('B3', '商铺类型')
            ->setCellValue('C3', '订单数量')
            ->setCellValue('D3', '总金额')
            ->setCellValue('E3', '开户银行')
            ->setCellValue('F3', '银行卡号')
            ->setCellValue('G3', '持卡人姓名')
            ->setCellValue('H3', '联系电话');
        $count = count($data);
        for ($i = 4; $i <= $count+3; $i++) {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $data[$i-4]->shopname);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $data[$i-4]->type);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $data[$i-4]->count);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, $data[$i-4]->sum);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, $data[$i-4]->bank);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $i, $data[$i-4]->cardNumber);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $i, $data[$i-4]->personName);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $i, $data[$i-4]->tel);
        }
        $last = $count + 4;
        $objPHPExcel->getActiveSheet()->setCellValue('A'.$last,$total);
        $objPHPExcel->getActiveSheet()->mergeCells('A'.$last.':H'.$last);
        $objPHPExcel->getActiveSheet()->getStyle('A'.$last)->getFont()->setName('宋体') //字体
            ->setSize(16) //字体大小
            ->setBold(true); //字体加粗
        $objPHPExcel->getActiveSheet()->getStyle('A'.$last)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->setTitle('balanceStatistics');      //设置sheet的名称
        $objPHPExcel->setActiveSheetIndex(0);                   //设置sheet的起始位置
        $PHPWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel,"Excel2007");
        header('Content-Disposition: attachment;filename='.$filename);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $PHPWriter->save("php://output");
    }
}