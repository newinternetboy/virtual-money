<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/10/24
 * Time: 下午12:19
 */
return [
    // 默认输出类型
    'default_return_type'               => 'html',
    // 默认跳转页面对应的模板文件
    'dispatch_success_tmpl'             => APP_PATH  . 'admin/view/' .DS. 'dispatch_jump.tpl',
    'dispatch_error_tmpl'               => APP_PATH  . 'admin/view/' .DS. 'dispatch_jump.tpl',

    //异常页面模板文件
    'exception_tmpl'                    => APP_PATH . 'admin/view' .DS. 'think_exception.tpl',

    'http_exception_template'           =>  [
        // 定义404错误的重定向页面地址
        404 =>  APP_PATH. 'admin/view' .DS. '404.html',
        // 还可以定义其它的HTTP status
        401 =>  APP_PATH. 'admin/view' .DS. '401.html',
    ],

    //模板布局
    'template'                          =>  [
        'layout_on'    =>  true,
        'layout_name'  =>  'layout',
        // 模板后缀
        // 'view_suffix'  => 'html',
        'taglib_pre_load'    =>    'think\template\taglib\Cx,app\admin\taglib\Tool',
        'taglib_build_in'    =>    'think\template\taglib\Cx,app\admin\taglib\Tool',
    ],
    //缓存
    'cache'                             => [
        // 驱动方式
        'type'   => 'File',
        // 缓存保存目录
        'path'   => RUNTIME_PATH.'system/adminData/',
        // 缓存前缀
        'prefix' => '',
        // 缓存有效期 0表示永久缓存
        'expire' => 0,
    ],

    // 'app_debug'              => true,

    'session'                => [
        'id'             => '',
        // SESSION_ID的提交变量,解决flash上传跨域
        'var_session_id' => '',
        // SESSION 前缀
        'prefix'         => '',
        // 驱动方式 支持redis memcache memcached
        'type'           => '',
        // 是否自动开启 SESSION
        'auto_start'     => true,
    ],

    // 视图输出字符串内容替换
    'view_replace_str'       => [
        '__CSS__'    => STATIC_PATH . 'admin/css',
        '__JS__'     => STATIC_PATH . 'admin/js',
        '__IMG__'    => STATIC_PATH . 'admin/images',
        '__LIB__'    => STATIC_PATH . 'admin/lib'
    ],
    //分页配置
    'paginate'               => [
        'type'      => 'bootstrap',
        'var_page'  => 'page',
        'list_rows' => 10,
    ],
    'log'                    => [
        // 日志记录方式，内置 file socket 支持扩展
        'type'  => 'File',
        // 日志保存目录
        'path'  => LOG_PATH,
        // 日志记录级别
        'level' => ['error'],
    ],
    //对账-充值明细 类型下拉菜单
    'channels'  => [
        MONEY_CHANNEL_WEIXIN => lang('Wei Xin'),
        MONEY_CHANNEL_MANAGE => lang('Manage Platform'),
    ],
    'moneytypes' => [
        MONEY_TYPE_RMB => lang('Rmb'),
        MONEY_TYPE_DELI => lang('Deli'),
    ],
    //对账-充值类型
    'chargeTypes' => [
        [
            'channel' => MONEY_CHANNEL_WEIXIN,
            'channelName' =>  lang('Wei Xin'),
            'type' => MONEY_PAY,
            'money_type' => MONEY_TYPE_RMB,
        ],
    ],
    //订单类型
    'ordertypes' => [
        MONEY_PAY => lang('Order Pay'),
        MONEY_DEDUCT => lang('Order Deduct'),
        MONEY_PERSON => lang('Order Person'),
        MONEY_COMPANY => lang('Order Company'),
        MONEY_DELI => lang('Order Deli'),
        MONEY_SYSTEM_DELI => lang('Order System Deli'),
    ],

    //task状态
    'taskStatus' => [
        TASK_WAITING        => lang('Task Waiting'),
        TASK_SENT           => lang('Task Sent'),
        TASK_SUCCESS        => lang('Task Success'),
        TASK_FAIL           => lang('Task Fail'),
        TASK_RESENT         => lang('Task Resent'),
        TASK_IGNORE         => lang('Task Ignore'),
    ],
    //task 命令
    'taskCmd' => [
        'charge'        => lang('Cmd Charge'),
        'deduct'        => lang('Cmd Deduct'),
        'changePrice'   => lang('Cmd Price'),
    ]
];