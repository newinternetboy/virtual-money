<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2018/1/8
 * Time: 下午2:24
 */

namespace app\common\model;


class SlideModel extends Common
{
    public $table = 'slide';

    public function getInfoForNumber($where,$number){
       return $this->where($where)->order('create_time','desc')->limit($number)->select();
    }
}