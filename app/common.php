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

/**
 * 初始化自增id
 * @param $table
 * @param $data
 * @return int|string
 */
function initAuthoIncId($table, $data){
    return Db($table)->insert($data);
}

/**
 * consumer用户密码加密
 * @param $str
 * @return string
 * @throws \bcrypt\Exception
 */
function bcryptHash($str){
    $bcrypt = new \bcrypt\Bcrypt();
    return $bcrypt->hashPassword($str);
}

/**
 * 月用量统计
 * @param $year
 * @param $where
 * @return mixed
 */
function getMonthReport($year, $where){
    $monthAbbrs = getMonthAbbreviation();
    foreach($monthAbbrs as $index => $month){
        $report[$index] = getNamedMonthReport($year,$month,$where);
    }
    return $report;
}

/**
 * 指定月份用量统计
 * @param $year
 * @param $month
 * @param $where
 * @return mixed
 */
function getNamedMonthReport($year, $month, $where){
    $table =  MONTH_FLOW_TABLE_NAME.$year;
    $tmp['consumers'] = Db($table)->where($where)->where([$month => ['neq',null]])->count();
    $tmp['cube'] = Db($table)->where($where)->sum($month);
    $tmp['cost'] = Db($table)->where($where)->sum($month.'_cost');
    return $tmp;
}

/**
 * 年用量统计
 * @param $startYear
 * @param $endYear
 * @param $where
 * @return mixed
 */
function getYearReport($startYear, $endYear, $where){
    $report = [];
    $years = [];
    while( $endYear >= $startYear ){
        $years[] = $startYear;
        $table = MONTH_FLOW_TABLE_NAME.$startYear;
        $monthAbbrs = getMonthAbbreviation();
        foreach($monthAbbrs as $index => $month){
            $monthFlow = getNamedMonthReport($startYear,$month,$where);
            $tmp['cube'][$index] = $monthFlow['cube'];
            $tmp['cost'][$index] = $monthFlow['cost'];
        }
        $report[$startYear]['cube'] = array_sum($tmp['cube']);
        $report[$startYear]['cost'] = array_sum($tmp['cost']);
        $report[$startYear]['consumers'] = Db($table)->where($where)->count();
        $startYear += 1;
    }
    $res['years'] = $years;
    $res['report'] = $report;
    return $res;
}
