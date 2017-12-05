<?php

return [
    //网站名称
    'website'   =>      [
                            'name'          => '双得利运营商系统',
                            'keywords'      =>  '双得利运营商系统',
                            'description'   =>  '双得利运营商系统'
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

    //验证码

    'captcha'  => [
        // 验证码字符集合
        'codeSet'  => '2345678abcdefhijkmnpqrstuvwxyzABCDEFGHJKLMNPQRTUVWXY', 
        // 验证码字体大小(px)
        'fontSize' => 50,
        // 是否画混淆曲线
        'useCurve' => false,
         // 验证码图片高度
        'imageH'   => 30,
        // 验证码图片宽度
        'imageW'   => 120,
        // 验证码位数
        'length'   => 5,
        // 验证成功后是否重置        
        'reset'    => true
    ],

    'log'                    => [
        // 日志记录方式，内置 file socket 支持扩展
        'type'  => 'File',
        // 日志保存目录
        'path'  => LOG_PATH,
        // 日志记录级别
        'level' => ['error'],
    ],

    //伪静态
    'url_html_suffix' => false,

    'response_auto_output' => false,
    //分页配置
    'paginate'               => [
        'type'      => 'bootstrap',
        'var_page'  => 'page',
        'list_rows' => 10
    ],
    'logtypes'    =>[
        'Save Area',
        'Delete Area',
        'Save AuthRule',
        'Delete AuthRule',
        'Save AuthAccess',
        'Update Blacklist Param',
        'Delete Blacklist Param',
        'Login succeed',
        'Logout succeed',
        'Save Meter',
        'Pass Meter',
        'Change Meter',
        'Edit Meter',
        'Delete Meter',
        'Edit MeterParam',
        'Delete MeterParam',
        'Save Price',
        'Delete Price',
        'Save Role',
        'Delete Role',
        'Save User',
        'Delete User',
        'Update Password',
        'Settle Success',
        'Freeze/Not Freeze Cart',
        'Edit Deli Production',
        'Cart Delivery',
    ],
    'changeDate' =>[
        'M_Type'           => lang('log_M_Type'),
        'M_Code'           => lang('log_M_Code'),
        'P_ID'             => lang('log_P_ID'),
        'M_Address'        => lang('log_M_Address'),
        'detail_address'   => lang('log_detail_address'),
        'U_ID'             => lang('log_U_ID'),
        'id'               => lang('log_id'),
        'company_id'       => lang('log_company_id'),
        'meter_status'     => lang('log_meter_status'),
        'setup_time'       => lang('log_setup_time'),
        'username'         => lang('log_username'),
        'tel'              => lang('log_tel'),
        'identity'         => lang('log_identity'),
        'family_num'       => lang('log_family_num'),
        'building_area'    => lang('log_building_area'),
        'income_peryear'   => lang('log_income_peryear'),
        'consumer_state'   => lang('log_consumer_state'),
        'role_id'          => lang('log_role_id'),
        'login_name'       => lang('log_login_name'),
        'status'           => lang('log_status'),
        'name'             => lang('log_name'),
        'remark'           => lang('log_remark'),
        'title'            => lang('log_title'),
        'pid'              => lang('log_pid'),
        'rule_val'         => lang('log_rule_val'),
        'display'          => lang('log_display'),
        'glyphicon'        => lang('log_glyphicon'),
        'sortnum'          => lang('log_sortnum'),
        'type'             => lang('log_type'),
        'period'           => lang('log_period'),
        'first_price'      => lang('log_first_price'),
        'first_val'        => lang('log_first_val'),
        'second_price'     => lang('log_second_price'),
        'second_val'       => lang('log_second_val'),
        'third_price'      => lang('log_third_price'),
        'third_val'        => lang('log_third_val'),
        'fourth_price'     => lang('log_fourth_price'),
        'endtime'          => lang('log_endtime'),
        'belong'           => lang('log_belong'),
        'desc'             => lang('log_desc'),
        'address'          => lang('log_address'),
        'param_name'       => '参数代号',
        'param_type'       => '参数类型',
        'opt_id'           => '参数',

    ],
    //统计报表默认查询时间差
    'meterDataRangeTime' => 60*60*24*7,

];