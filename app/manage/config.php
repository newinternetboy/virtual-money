<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/10/24
 * Time: 下午12:19
 */
return [

    //网站名称
    'website'   =>      [
        'name'          => '双得利清分系统',
        'keywords'      =>  '双得利清分系统',
        'description'   =>  '双得利清分系统'
    ],

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
//    'channels'  => [
//        MONEY_CHANNEL_WEIXIN => lang('Wei Xin'),
//        MONEY_CHANNEL_MANAGE => lang('Manage Platform'),
//    ],
    'moneytypes' => [
        MONEY_TYPE_RMB => lang('Rmb'),
        MONEY_TYPE_DELI => lang('Deli'),
    ],
//    //对账-充值类型
//    'chargeTypes' => [
//        [
//            'channel' => MONEY_CHANNEL_WEIXIN,
//            'channelName' =>  lang('Wei Xin'),
//            'type' => MONEY_PAY,
//            'money_type' => MONEY_TYPE_RMB,
//        ],
//    ],
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
        'turn_on'       => lang('Cmd Turn On'),
        'turn_off'      => lang('Cmd Turn Off'),
        'downloadPrice'  => lang('Cmd downloadPrice'),
        'downloadMeterparam'  => lang('Cmd downloadMeterparam'),
    ],
    'dictType'=>[
        DICT_COMPANY_ELE_BUSINESS => lang('Company Ele_business Type'),
        DICT_PERSON_ELE_BUSINESS  => lang('Deli Ele_business Type'),
        DICT_PERCENT              => lang('Deli_money Gifts'),
        DICT_BALANCE_THRESHOLD_VALUE    => lang('Low Balance Valve'),
        DICT_DEDECT_BALANCE_LOWEST_VALUE => lang('Deduct Balance Lowest Valve')
    ],
    //商铺封面图压缩尺寸限制
    'thumbMaxWidth' => 250,
    'thumbMaxHeight' => 250,

    //表具报修 状态
    'fixstatus' => [
        FIX_STATUS_WAITING => lang('Fix Waiting'),
        FIX_STATUS_DEAL => lang('Fix Deal'),
    ],


    //留言建议 状态
    'advicestatus' => [
        ADVICE_STATUS_WAITING => lang('Advice Waiting'),
        ADVICE_STATUS_DEAL => lang('Advice Deal'),
    ],

    //表具统计
    'meterstatistics'=>[
        NEW_STATISTICS=>lang('New Statistics'),
        SETUP_STATISTICS=>lang('Setup Statistics'),
        CHANGE_STATISTICS=>lang('Change Statistics')
    ],
    'logtypes'    =>[
        'Add/Edit Company',
        'Del Company',
        'Settle Success',
        'Cart Delivery',
        'Deduct',
        'Login succeed',
        'Logout succeed',
        'Handle Task',
        'Deal Fix',
        'Deal Advice',
        'Save Grshop',
        'Update QYShop',
        'Edit Gr/Qy Production',
        'Freeze Cart',
        'Insert QYShop',
        'Save ShopAdmin',
        'Edit Deli Production',
        'Save Dict',
        'Cart Delivery',
        'Save AuthRule',
        'Delete AuthRule',
        'Save Role',
        'Delete Role',
        'Save User',
        'Delete User',
        'Save AuthAccess',
        'Reply Comment',
        'Delete Comment'
    ],

    //充值日/月/年报 旧系统api
    'old_system_url' => 'http://192.168.223.220:8088/api/values',
];