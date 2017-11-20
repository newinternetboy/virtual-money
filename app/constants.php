<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/9/13
 * Time: 下午6:24
 */

/***************系统常量***************/
//错误代码
define('ERROR_CODE_DEFAULT',9999);      //未定义错误代码 5000以上是系统错误代码
define('ERROR_CODE_DATA_ILLEGAL',5000); //数据不合法导致的错误
define('ERROR_CODE_SYS',5001);          //系统错误导致

//双得利集团ID
define('SHUANGDELI_ID',100);
define('SHUANGDELI_NAME','双得利集团');

//操作的执行状态
define('ACTION_SUCCESS',1); //操作成功
define('ACTION_FAIL',5);    //操作失败

/***************表具信息***************/
//表具类型
define('METER_TYPE_WATER',1); //水
define('METER_TYPE_ELECTRICITY',2); //电
define('METER_TYPE_GAS',3); //气
//表具状态
define('METER_STATUS_CHANGED',20); //被更换的旧表
define('METER_STATUS_BIND',10); //已绑定
define('METER_STATUS_DELETE',30); //已删除
define('METER_STATUS_NEW',5); //新表
//表具活跃状态
define('METER_LIFE_ACTIVE',1); //活跃表具
define('METER_LIFE_INACTIVE',2); //表具停止使用

/*************表具上报记录表*************/
//source_type字段
define('BUSINESS',1);   //业务
define('METER',2);      //表具
define('MONEY',3);      //余额
//action_type字段
define('BUSINESS_SETUP',1);     //报装
define('BUSINESS_PASS',2);      //过户
define('BUSINESS_CHANGE',3);    //换表
define('BUSINESS_EDIT',4);      //修改
define('BUSINESS_DELETE',5);    //删除
define('METER_INIT',1);      //初始化
define('METER_REPORT',2);    //上报数据

/*****************阀门*****************/
//阀门控制方式
define('VALVE_USER',1); //指定用户控制
define('VALVE_AREA',2); //指定区域控制
//阀门操作
define('VALVE_OPEN',2); //开阀
define('VALVE_CLOSE',1); //关阀
//阀门控制记录状态
define('VALVE_WAITING',1); //未执行
define('VALVE_DONE',5);    //已执行

/***************客户信息***************/
//客户状态
define('CONSUMER_STATE_NORMAL',10); //正常(绑定了表具)
define('CONSUMER_STATE_OLD',15);    //过户(过户前的用户信息)
define('CONSUMER_STATE_DISABLE',20);    //曾经报装过该表号的用户,表具重复利用后这个状态的用户属于失效状态

//按月统计流量表名
define('MONTH_FLOW_TABLE_NAME','month_flow_');

/***************task表***************/
//task状态
define('TASK_WAITING',1);  //待处理任务
define('TASK_SENT',2);     //已下发任务
define('TASK_SUCCESS',3);  //执行成功
define('TASK_FAIL',4);     //执行失败
define('TASK_RESENT',5);     //已重发
define('TASK_IGNORE',6);     //已忽略

/***************后台用户表***************/
define('PLATFORM_ADMIN',1);  //运营商后台用户
define('PLATFORM_MANAGE',2); //清分平台用户
define('PLATFORM_QYSHOP',3); //企业商铺管理员

/***************moneyLog表***************/
//钱类型 money_type
define('MONEY_TYPE_RMB',1); //人民币
define('MONEY_TYPE_DELI',2); //得力币
//消费类型 type
define('MONEY_PAY',1); //缴费
define('MONEY_PERSON',2); //个人电商中消费
define('MONEY_COMPANY',3); //企业电商中消费
define('MONEY_DELI',4); //得力专供中消费
define('MONEY_DEDUCT',10); //清分扣除余额
define('MONEY_SYSTEM_DELI',5); //系统赠送得力币
//付费方式
define('MONEY_CHANNEL_WEIXIN',1); //微信
define('MONEY_CHANNEL_MANAGE',2); //清分后台

//task执行失败的记录处理状态
define('MONEYLOG_FAIL_DEAL_STATUS_WAITING',1); //待处理
define('MONEYLOG_FAIL_DEAL_STATUS_DONE',2); //已处理
//operator字段
define('MONEYLOG_OPERATOR_WEIXIN',1); //微信充值
define('MONEYLOG_OPERATOR_MANAGE',2); //清分充值

/***************运营商表***************/
//收费状态
define('UNCHARGE',1); //未收费
define('CHARGED',2); //已收费
//状态
define('COMPANY_STATUS_NORMAL',1); //正常
define('COMPANY_STATUS_DEL',2);    //已删除

/***************shop表***************/
//店铺类型
define('PERSONAL_ELE_BUSINESS',0); //个人电商
define('COMPANY_ELE_BUSINESS',1);  //企业电商
//店铺状态
define('SHOP_STATUS_CLOSE',0); //关闭
define('SHOP_STATUS_OPEN',1); //打开

/***************production表***************/
//商品状态
define('PRODUCTION_SHELVES',1); //上架
define('PRODUCTION_OFF_SHELVES',0); //下架
//商铺id状态
define('PRODUCTION_ID_DELI','0');//得利商铺

/***************cart表***************/
//订单冻结状态
define('ORDER_FREEZE',1);//待处理
define('ORDER_NORMAL',0);//正常
//订单状态
define('ORDER_UNPAID',1);//待付款
define('ORDER_WAITING_SEED',2);//待发货
define('ORDRE_WAITING_TASK',3);//待收货
define('ORDER_SEED_WAITING_COMMENT',4);//待评价
define('ORDER_OVER',5);//订单完成
//订单结算状态
define('ORDER_ALREADY_ACCOUNT',1);//已结算
define('ORDER_NOT_ACCOUNT',0);//未结算

/***************dict表***************/
//类型
define('DICT_COMPANY_ELE_BUSINESS',1); //企业电商类别
define('DICT_PERSON_ELE_BUSINESS',2); //得利专供类别
define('DICT_PERCENT',3); //充值时赠送得利币的比例