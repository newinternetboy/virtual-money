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
        'M_Code.require'                => '{%M_Code Require}',
        'username.require'              => '{%Username Require}',
        'tel.require'                   => '{%Telephone Require}',
        'identity.require'              => '{%Identity Require}',
        'company_id.require'            => '{%Company_id Require}',
        'consumer_state.require'        => '{%Consumer_State Require}',
        'password.require'              => '{%Password Require}',
    ];

    protected $scene = [
        'insert'        => ['M_Code','username','tel','identity','company_id','consumer_state','password'],
        'changeMeter'   => ['M_Code','password'],
        'edit'          => ['username','tel'],
        'setOld'        => ['consumer_state']
    ];
}