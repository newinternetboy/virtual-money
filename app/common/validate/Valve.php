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
        'valve_type.require'    => '{%Valve Type Require}',
        'valve_type.in'         => '{%The state of the control mode is incorrect}',
        'data.require'          => '{%Date Require}',
        'option.require'        => '{%Opeation Type Require}',
        'option.in'             => '{%Operation state error}',
        'exectime.require'      => '{%Exectime Require}'
    ];

    protected $scene = [
        'add' => ['valve_type','data', 'option','exectime'],
    ];
}