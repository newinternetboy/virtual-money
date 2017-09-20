<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/9/19
 * Time: 下午3:27
 */

namespace app\admin\validate;


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
        'pulseRatio.require'        => '脉冲常量必须',
        'lowLimit.require'          => '低剩余报警必须',
        'overdraftLimit.require'    => '透支限额必须',
        'overdraftTime.require'     => '透支限制时间必须',
        'freezeTime.require'        => '冻结时间必须',
        'uploadTime.require'        => '自动上报时间必须',
        'SMSCode.require'           => '短信平台号码必须',
        'transformerRatio.require'  => '开机脉冲数必须',
        'overFlimit.require'               => '流量上限必须',
        'tag.require'        => '备用字段必须',
    ];
}