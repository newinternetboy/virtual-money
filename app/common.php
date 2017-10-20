<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件


/**
 * 调试输出
 * @param unknown $data
 */
function print_data($data, $var_dump = false)
{
    header("Content-type: text/html; charset=utf-8");
    echo "<pre>";
    if ($var_dump) {
        var_dump($data);
    } else {
        print_r($data);
    }
    exit();
}

/**
 * 输出json格式数据
 * @param unknown $object
 */
function print_json($object)
{
    header("content-type:text/plan;charset=utf-8");
    echo json_encode($object);
    exit();
}

/**
 * 账户密码加密
 * @param  string $str password
 * @return string(32)       
 */
function md6($str)
{
	$key = 'account_nobody';
	return md5(md5($str).$key);
}

/**
 * 替换字符串中间位置字符为星号
 * @param  [type] $str [description]
 * @return [type] [description]
 */
function replaceToStar($str)
{
    $len = strlen($str) / 2; //a0dca4d0****************ba444758]
    return substr_replace($str, str_repeat('*', $len), floor(($len) / 2), $len);
}

function mduser( $str )
{
    $user_auth_key = \think\Config::get('user_auth_key');
    return md5(md5($user_auth_key).$str);
}

/**
 * php库函数生成的12个月英文缩写
 * @return array
 */
function getMonthAbbreviation(){
    for($i=0;$i<=11;$i++){
        $abbr[$i+1] = date('M',strtotime("+$i months",strtotime(date('Y').'0101')));
    }
    return $abbr;
}

/**
 * 获取自增id
 * 注:调用此方法前,数据库中必须存在$table表,且存在包含$query,$autoField字段的数据
 * @param String $table         自增表名
 * @param Array $query          查询条件
 * @param Field $autoField      自增字段
 * @param Number $step          自增量
 * @return  Int                 自增值
 */
function getAutoIncId($table, $query, $autoField, $step){
    $update = [
        '$inc' => [
                    $autoField => $step
            ]
    ];

    $mongodb = new MongoDB\Driver\Manager();
    $command = new MongoDB\Driver\Command([
        'findandmodify'=> $table,
        'update'=>$update,
        'query'=>$query,
        'new'=>true,
        'upsert'=>true
    ]);
    $database = config('database.database');
    $result = $mongodb->executeCommand($database,$command);
    $result = $result->toArray();
    return $result[0]->value->$autoField;
}

function initAuthoIncId($table,$data){
    return Db($table)->insert($data);
}

function bcryptHash($str){
    $bcrypt = new \bcrypt\Bcrypt();
    return $bcrypt->hashPassword($str);
}