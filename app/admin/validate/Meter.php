<?php
namespace app\admin\validate;

use think\Validate;

class Meter extends Validate
{

    protected $rule =   [
        'M_Type'                => 'require',
        'M_Code'                => 'require',
        'P_ID'                  => 'require',
        'U_ID'                  => 'require',
        'M_Address'             => 'require',
        'detail_address'        => 'require',
        'change_reason'         => 'require',
        'meter_status'          => 'require',
        'meter_life'            => 'require',
        'company_id'            => 'require',
        'balance'               => 'require',
    ];

    protected $message  =   [
        'M_Type.require'                => '表具类型必须',
        'M_Code.require'                => '表号必须',
        'P_ID.require'                  => '价格类型必须',
        'U_ID.require'                  => '用户必须',
        'M_Address.require'             => '地址必须',
        'detail_address.require'        => '详细地址必须',
        'change_reason.require'         => '换表原因必须',
        'meter_status.require'          => '表具状态必须',
        'meter_life.require'            => '表具活跃状态必须',
        'company_id.require'            => '公司id必须',
        'balance.require'               => '余额必须',
    ];

    protected $scene = [
        //表具报装
        'setup' => ['M_Type','M_Code','P_ID','U_ID','M_Address','detail_address','meter_status','company_id'],
        //表具过户
        'pass' => ['U_ID'],
        //表具更换
        'change_update_old_meter' => ['change_reason','meter_status','meter_life'],
        'change_update_new_meter' => ['M_Type','M_Code','P_ID','U_ID','M_Address','detail_address','meter_status','company_id'],
        //表具修改
        'edit' => ['M_Address','detail_address'],
        //表具信息维护
        'delete' => ['meter_status','meter_life']
    ];
}


