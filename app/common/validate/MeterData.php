<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/9/27
 * Time: 下午3:56
 */

namespace app\common\validate;


use think\Validate;

class MeterData extends Validate
{

    protected $rule =   [
        'meter_id'                  =>  'require',
        'M_Code'                    =>  'require',
        'U_ID'                      =>  'require',
        'company_id'                =>  'require',
        'source_type'               =>  'require',
        'action_type'               =>  'require',
    ];

    protected $message  =   [
        'meter_id.require'                => '{%Meter Id Require}',
        'M_Code.require'                  => '{%M_Code Require}',
        'U_ID.require'                    => '{%User Id Require}',
        'company_id.require'              => '{%Company_id Require}',
        'source_type.require'             => '{%Data Sources Require}',
        'action_type.require'             => '{%Action Type Require}',
    ];

    protected $scene = [
        'business'    => ['meter_id','M_Code','U_ID','company_id','source_type','action_type'],   //业务
        'report'      => ['meter_id','M_Code','source_type','action_type'],                       //上报数据
    ];
}