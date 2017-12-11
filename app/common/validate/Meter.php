<?php
namespace app\common\validate;

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
        'initialCube'           => 'require',
        'totalCube'             => 'require',
        'operator'              => 'require',
        'totalCost'              => 'require',
    ];

    protected $message  =   [
        'M_Type.require'                => '{%Meter Type Require}',
        'M_Code.require'                => '{%Meter Code Require}',
        'P_ID.require'                  => '{%Price Type Require}',
        'U_ID.require'                  => '{%User Require}',
        'M_Address.require'             => '{%M_Address Require}',
        'detail_address.require'        => '{%Detail Address Require}',
        'change_reason.require'         => '{%Change Reason Require}',
        'meter_status.require'          => '{%Meter Status Require}',
        'meter_life.require'            => '{%Meter Life Status Require}',
        'company_id.require'            => '{%Company_id Require}',
        'balance.require'               => '{%Balance Require}',
        'initialCube.require'           => '{%Initial Cube Require}',
        'totalCube.require'             => '{%Total Cube Require}',
        'totalCost.require'             => '{%Total Cost Require}'
    ];

    protected $scene = [
        //表具报装
        'setup' => ['M_Type','M_Code','P_ID','U_ID','M_Address','detail_address','meter_status','company_id'],
        //表具过户
        'pass' => ['U_ID'],
        //表具更换
        'change_update_old_meter' => ['change_reason','meter_status','operator'],
        'change_update_new_meter' => ['M_Type','P_ID','U_ID','M_Address','detail_address','meter_status','company_id'],
        //表具修改
        'edit' => ['M_Address','detail_address'],
        //表具信息维护
        'delete' => ['meter_status'],
        //初始化新表时,旧表具生命周期结束
        'init_old' => ['meter_life'],
        //初始化新表具
        'init_new' => ['M_Code','meter_life','meter_status'],
        //表具上报
        'report' => ['balance','initialCube','totalCube']
    ];
}


