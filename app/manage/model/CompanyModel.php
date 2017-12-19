<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/10/24
 * Time: 下午12:03
 */

namespace app\manage\model;


class CompanyModel extends BasicModel
{
    // 当前模型名称
    protected $name = 'company';

    //设置主键名
    protected $pk  = 'id';

    public function setLimitTimesAttr($value){
        return intval($value);
    }

    public function setLeftTimesAttr($value){
        return intval($value);
    }

    public function setChargeStatusAttr($value){
        return intval($value);
    }

    public function setPercentAttr($value){
        return floatval($value);
    }

}