<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/9/19
 * Time: 下午3:27
 */

namespace app\common\validate;


use think\Validate;

class MeterParam extends Validate
{
    protected $rule =   [
        'pulseRatio'        => 'require',
        'lowLimit'          => 'require',
        'overdraftLimit'    => 'require',
        'overdraftTime'     => 'require',
        'freezeTime'        => 'require',
        'uploadTime'        => 'require',
        'SMSCode'           => 'require',
        'transformerRatio'  => 'require',
        'tag'               => 'require',
        'overFlimit'        => 'require',
    ];

    protected $message  =   [
        'pulseRatio.require'        => '{%PulseRatio Require}',
        'lowLimit.require'          => '{%Low Residual Alarm Require}',
        'overdraftLimit.require'    => '{%Overdraft Limit Require}',
        'overdraftTime.require'     => '{%Overdraft Time Require}',
        'freezeTime.require'        => '{%Freeze Time Require}',
        'uploadTime.require'        => '{%Upload Record Time Require}',
        'SMSCode.require'           => '{%SMS Code Require}',
        'transformerRatio.require'  => '{%TransformerRatio Require}',
        'overFlimit.require'        => '{%OverFlimit Require}',
        'tag.require'               => '{%Tag Require}',
    ];
}