<?php
namespace app\common\validate;

use think\Validate;

class Production extends Validate
{

    protected $rule =   [
        'name'          =>  'require'
    ];

    protected $message  =   [
        'name.require'     => '{%Production Name Reuqire}'
    ];

    protected $scene = [
        'edit'      =>      ['name']
    ];

}


