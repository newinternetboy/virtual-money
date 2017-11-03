<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/10/24
 * Time: 上午11:57
 */

namespace app\manage\model;

use PHPExcel_IOFactory;
use PHPExcel;

use think\Model;

class BasicModel extends Model
{

    //设置主键名
    protected $pk  = 'id';

    /**
     * 查询单条记录
     * @param array $where
     * @param string $field
     * @param $M_Code 分表预留字段
     * @return array|false|\PDOStatement|string|Model
     */
    public function findInfo($where = [], $field = '',$M_Code = ''){
        if( $field ){
            return $this->where($where)->field($field)->order('create_time','desc')->find();
        }
        return $this->where($where)->order('create_time','desc')->find();
    }

    /**
     * 查询多条记录
     * @param array $where
     * @param string $field
     * @param $M_Code 分表预留字段
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function selectInfo($where = [], $field = '',$M_Code = ''){
        if( $field ){
            return $this->where($where)->field($field)->order('create_time','desc')->select();
        }
        return $this->where($where)->order('create_time','desc')->select();
    }

    /**
     * 翻页查询
     * @param array $where
     * @param array $param
     * @param string $field
     * @param $M_Code 分表预留字段
     * @return $this
     */
    public function getInfoPaginate($where = [], $param = [], $field = '',$M_Code = ''){
        if( $field ){
            return $this->where($where)->field($field)->order('create_time','desc')->paginate()->appends($param);
        }
        return $this->where($where)->order('create_time','desc')->paginate()->appends($param);
    }

    /**
     * 插入/更新
     * @param $data
     * @param bool|true $scene
     * @param $M_Code 分表预留字段
     * @return bool|string
     */
    public function upsert($data, $scene = true,$M_Code = ''){
        if( isset($data['id']) && !empty($data['id']) ){
            $result =  $this->validate($scene)->isUpdate(true)->save($data);
            if($result === false){
                return false;
            }
            return true;
        }else{
            unset($data['id']);
            $result = $this->validate($scene)->isUpdate(false)->save($data);
            if($result === false){
                return false;
            }
            return $this->getLastInsID();
        }
    }

    //查总个数；
    public function counts($where){
        return $this->where($where)->count();
    }
    //获取总和；
    public function sums($where,$field){
        return $this->where($where)->sum($field);
    }


    /*
    *处理Excel导出
    *@param $datas array 设置表格数据
    *@param $filename str 设置文件名
    */
    public function create_xls($data,$filename,$title){
        $filename=$filename.".xlsx";
        $path = dirname(__FILE__);
        vendor("PHPExcel.PHPExcel");
        vendor("PHPExcel.PHPExcel.Writer.Excel5");
        vendor("PHPExcel.PHPExcel.Writer.Excel2007");
        vendor("PHPExcel.PHPExcel.IOFactory");
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
            ->setCellValue('A3', 'ID编号')
            ->setCellValue('B3', '用户名')
            ->setCellValue('C3', '手机号')
            ->setCellValue('D3', '手机');
        $count = count($data);
        for ($i = 4; $i <= $count+3; $i++) {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $data[$i-4]['company_name']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $data[$i-4]['OPT_ID']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $data[$i-4]['count']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, $data[$i-4]['meterbalance']);
        }
        $objPHPExcel->getActiveSheet()->setTitle('user');      //设置sheet的名称
        $objPHPExcel->setActiveSheetIndex(0);                   //设置sheet的起始位置
        $PHPWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel,"Excel2007");
        header('Content-Disposition: attachment;filename='.$filename);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $PHPWriter->save("php://output"); //表示在$path路径下面生成demo.xlsx文件件
    }

    public function del($id){
        return $this->where(['id' => $id])->delete();
    }


}