<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/9/13
 * Time: 下午6:24
 */

//系统常量
//错误代码
define('ERROR_CODE_DEFAULT',9999);      //未定义错误代码 5000以上是系统错误代码
define('ERROR_CODE_DATA_ILLEGAL',5000); //数据不合法导致的错误
define('ERROR_CODE_SYS',5001);          //系统错误导致

//操作的执行状态
define('ACTION_SUCCESS',1); //操作成功
define('ACTION_FAIL',5);    //操作失败

//表具信息
//表具类型
define('METER_TYPE_WATER',1); //水
define('METER_TYPE_ELECTRICITY',2); //电
define('METER_TYPE_GAS',3); //气
//表具状态
define('METER_STATUS_CHANGED',20); //被更换的旧表
define('METER_STATUS_BIND',10); //已绑定
define('METER_STATUS_DELETE',30); //已删除
//表具生命周期状态
define('METER_LIFE_INIT',1); //新表具
define('METER_LIFE_START',5); //表具报装
define('METER_LIFE_END',10); //表具一次生命周期结束

//阀门
//阀门控制方式
define('VALVE_USER',1); //指定用户控制
define('VALVE_AREA',2); //指定区域控制
//阀门操作
define('VALVE_OPEN',2); //开阀
define('VALVE_CLOSE',1); //关阀
//阀门控制记录状态
define('VALVE_WAITING',1); //未执行
define('VALVE_DONE',5);    //已执行

//客户信息
//客户状态
define('CONSUMER_STATE_NORMAL',10); //正常(绑定了表具)
define('CONSUMER_STATE_OLD',15);    //过户(过户前的用户信息)