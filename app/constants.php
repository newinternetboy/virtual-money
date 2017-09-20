<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/9/13
 * Time: 下午6:24
 */

//表具信息
//表具类型
define('METER_TYPE_WATER',1); //水
define('METER_TYPE_ELECTRICITY',2); //电
define('METER_TYPE_GAS',3); //气
//表具状态
define('METER_STATUS_CHANGED',20); //被更换的旧表
define('METER_STATUS_BIND',10); //已绑定
define('METER_STATUS_DELETE',30); //已删除

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