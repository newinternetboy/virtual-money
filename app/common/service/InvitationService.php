<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2018/1/8
 * Time: 下午3:42
 */

namespace app\common\service;

use app\common\model\InvitationModel;

use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Style_Alignment;

class InvitationService extends CommonService
{
    public function __construct()
    {
        $this->dbModel = new InvitationModel();
    }

    public function getInfoPaginateNoorder($where = [], $param = [], $field = ''){
        return $this->dbModel->getInfoPaginateNoorder($where, $param, $field);
    }

    /*
     * 导出excel;
     */
    public function downloadInvitation($data,$filename){
        $filename=$filename.".xlsx";
        $objPHPExcel = new PHPExcel();
        $count = count($data);
        for ($i = 1; $i <= $count; $i++) {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, isset($data[$i-1]['in_code']) ? $data[$i-1]['in_code'] : '-');
        }
        $objPHPExcel->getActiveSheet()->setTitle($filename);      //设置sheet的名称
        $objPHPExcel->setActiveSheetIndex(0);                   //设置sheet的起始位置
        $PHPWriter = PHPExcel_IOFactory::createWriter($objPHPExcel,"Excel2007");
        header('Content-Disposition: attachment;filename='.$filename);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $PHPWriter->save("php://output");

    }



}