<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/9/13
 * Time: 下午3:49
 */

namespace app\common\validate;


use think\Validate;

class Consumer extends Validate
{
    protected $rule =   [
        'M_Code'                => 'require',
        'username'              => 'require',
        'tel'                   => 'require',
        'identity'              => 'require',
        'company_id'            => 'require',
        'consumer_state'        => 'require',
        'password'              => 'require',
    ];

    protected $message  =   [
        'M_Code.require'                => '表号必须',
        'username.require'              => '姓名必须',
        'tel.require'                   => '电话号码必须',
        'identity.require'              => '身份证号必须',
        'company_id.require'            => '公司id必须',
        'consumer_state.require'        => '用户状态必须',
        'password.require'              => '用户密码必须',
    ];

    protected $scene = [
        'insert'        => ['M_Code','username','tel','identity','company_id','consumer_state','password'],
        'changeMeter'   => ['M_Code','password'],
        'edit'          => ['username','tel'],
        'setOld'        => ['consumer_state']
    ];
}