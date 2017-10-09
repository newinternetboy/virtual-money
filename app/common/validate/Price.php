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
        'name.require'              => '价格名称必须',
        'type.require'              => '类型必须',
        'period.require'            => '周期必须',
        'first_price.require'       => '第一阶梯价格必须',
        'first_val.require'         => '第一阶梯量必须',
        'second_price.require'      => '第二阶梯价格必须',
        'second_val.require'        => '第二阶梯量必须',
        'third_price.require'       => '第三阶梯价格必须',
        'third_val.require'         => '第三阶梯量必须',
        'fourth_price.require'      => '第四阶梯价格必须',
        'endtime.require'           => '结束时间必须',
    ];
}


