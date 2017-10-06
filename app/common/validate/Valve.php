<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/9/20
 * Time: 下午6:05
 */

namespace app\common\validate;


use think\Validate;

class Valve extends Validate
{

    protected $rule =   [
        'valve_type'            => 'require|in:1,2',
        'data'                  => 'require',
        'option'                => 'require|in:1,2',
        'exectime'              => 'require'
    ];

    protected $message  =   [
        'valve_type.require'    => '控制方式必须',
        'valve_type.in'         => '控制方式状态有误',
        'data.require'          => '数据必须',
        'option.require'        => '操作方式必须',
        'option.in'             => '操作方式状态有误',
        'exectime.require'      => '执行时间必须'
    ];

    protected $scene = [
        'add' => ['valve_type','data', 'option','exectime'],
    ];
}