<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2018/1/12
 * Time: 下午2:05
 */

namespace app\common\validate;


use think\Validate;

class Production extends Validate
{
    protected $rule =   [
        'name'                   => 'require|unique:production',
        'fund_number'             => 'require|unique:production',
    ];

    protected $message  =   [
        'name.require'               => '基金名称必须',
        'name.unique'                => '基金名称已存在',
        'fund_number.require'         => '基金编号必须',
        'fund_number.unique'          => '基金编号已存在',
    ];

    protected $scene = [
        'upsert'        =>      ['name']
    ];
}