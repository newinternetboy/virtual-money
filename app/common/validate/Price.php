<?php
namespace app\common\validate;

use think\Validate;

class Price extends Validate
{

    protected $rule =   [
        'name'              => 'require|max:20',
        'type'              => 'require',
        'period'            => 'require',
        'first_price'       => 'require',
        'first_val'         => 'require',
        'second_price'      => 'require',
        'second_val'        => 'require',
        'third_price'       => 'require',
        'third_val'         => 'require',
        'fourth_price'      => 'require',
        'endtime'           => 'require',
    ];

    protected $message  =   [
        'name.require'              => '{%Price Name Require}',
        'type.require'              => '{%Type Require}',
        'period.require'            => '{%Period Require}',
        'first_price.require'       => '{%First Price Require}',
        'first_val.require'         => '{%First value Require}',
        'second_price.require'      => '{%Second Price Require}',
        'second_val.require'        => '{%Secong Value Require}',
        'third_price.require'       => '{%Third Price Require}',
        'third_val.require'         => '{%Third Value Require}',
        'fourth_price.require'      => '{%Fourth Price Require}',
        'endtime.require'           => '{%End Time Require}',
    ];
}


