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
        'meter_M_Code'              =>  'require',
        'U_ID'                      =>  'require',
        'company_id'                =>  'require',
        'source_type'               =>  'require',
        'action_type'               =>  'require',
    ];

    protected $message  =   [
        'meter_id.require'                => '表具id必须',
        'meter_M_Code.require'            => '表号必须',
        'U_ID.require'                    => '用户id必须',
        'company_id.require'              => '公司id必须',
        'source_type.require'             => '数据来源必须',
        'action_type.require'             => '操作行为必须',
    ];

    protected $scene = [
        'business'    => ['meter_id','meter_M_Code','U_ID','company_id','source_type','action_type'],   //业务
        'report'      => ['meter_id','meter_M_Code','source_type','action_type'],                       //上报数据
    ];
}