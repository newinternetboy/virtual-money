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

use think\Model;
use think\Log;
use think\Db;

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
    $connectString = 'mongodb://';
    if(config('database.username') && config('database.password')){
        $connectString .= config('database.username') . ':' .config('database.password') . '@';
    }
    $connectString .= config('database.hostname') . ':' . config('database.hostport') . '/' . config('database.database');
    $mongodb = new \MongoDB\Driver\Manager($connectString);
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
    return Db($table)->insertAll($data);
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
    $tmp['la1'] = Db($table)->where($where)->sum($month.'_la1');
    $tmp['lp1'] = Db($table)->where($where)->sum($month.'_lp1');
    $tmp['la2'] = Db($table)->where($where)->sum($month.'_la2');
    $tmp['lp2'] = Db($table)->where($where)->sum($month.'_lp2');
    $tmp['la3'] = Db($table)->where($where)->sum($month.'_la3');
    $tmp['lp3'] = Db($table)->where($where)->sum($month.'_lp3');
    $tmp['la4'] = Db($table)->where($where)->sum($month.'_la4');
    $tmp['lp4'] = Db($table)->where($where)->sum($month.'_lp4');
    $tmp['la5'] = Db($table)->where($where)->sum($month.'_la5');
    $tmp['lp5'] = Db($table)->where($where)->sum($month.'_lp5');
    $tmp['la6'] = Db($table)->where($where)->sum($month.'_la6');
    $tmp['lp6'] = Db($table)->where($where)->sum($month.'_lp6');
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
            $tmp['la1'][$index] = $monthFlow['la1'];
            $tmp['lp1'][$index] = $monthFlow['lp1'];
            $tmp['la2'][$index] = $monthFlow['la2'];
            $tmp['lp2'][$index] = $monthFlow['lp2'];
            $tmp['la3'][$index] = $monthFlow['la3'];
            $tmp['lp3'][$index] = $monthFlow['lp3'];
            $tmp['la4'][$index] = $monthFlow['la4'];
            $tmp['lp4'][$index] = $monthFlow['lp4'];
            $tmp['la5'][$index] = $monthFlow['la5'];
            $tmp['lp5'][$index] = $monthFlow['lp5'];
            $tmp['la6'][$index] = $monthFlow['la6'];
            $tmp['lp6'][$index] = $monthFlow['lp6'];
        }
        $report[$startYear]['cube'] = array_sum($tmp['cube']);
        $report[$startYear]['cost'] = array_sum($tmp['cost']);
        $report[$startYear]['la1'] = array_sum($tmp['la1']);
        $report[$startYear]['lp1'] = array_sum($tmp['lp1']);
        $report[$startYear]['la2'] = array_sum($tmp['la2']);
        $report[$startYear]['lp2'] = array_sum($tmp['lp2']);
        $report[$startYear]['la3'] = array_sum($tmp['la3']);
        $report[$startYear]['lp3'] = array_sum($tmp['lp3']);
        $report[$startYear]['la4'] = array_sum($tmp['la4']);
        $report[$startYear]['lp4'] = array_sum($tmp['lp4']);
        $report[$startYear]['la5'] = array_sum($tmp['la5']);
        $report[$startYear]['lp5'] = array_sum($tmp['lp5']);
        $report[$startYear]['la6'] = array_sum($tmp['la6']);
        $report[$startYear]['lp6'] = array_sum($tmp['lp6']);
        $report[$startYear]['consumers'] = Db($table)->where($where)->count();
        $startYear += 1;
    }
    $res['years'] = $years;
    $res['report'] = $report;
    return $res;
}

/**
 * meter_data 表分表方法,根据表号返回不同表名
 * @param $M_Code
 * @return string
 */
function getMeterdataTablename($M_Code){
    return 'meter_data';
}

function parseDate($timestamp){
    return date('d',$timestamp);
}

/**
 * 权限排序
 * @param $authRules
 * @return array
 */
function sortAuthRules($authRules){
    $ret = [];
    foreach( $authRules as $authRule ){
        $authRule = ($authRule instanceof Model) ? $authRule->toArray() : $authRule;
        if( $authRule['pid'] == 0 ){
            sortChildren($authRule,$authRules);
            $ret[] = $authRule;
        }
    }
    return $ret;
}

function sortChildren(& $authRule,$authRules){
    foreach( $authRules as $item ) {
        $item = ($item instanceof Model ) ? $item->toArray() : $item;
        if( $item['pid'] == $authRule['id'] ){
            $authRule['children'][] = $item;
        }
    }
    if( isset($authRule['children']) ){
        foreach( $authRule['children'] as & $authChild ){
            sortChildren($authChild,$authRules);
        }
    }
}

function getRuleVals($x){
    return $x['rule_val'];
}

/**
 * 插入moneyLog
 * @param $data
 * @return mixed
 */
function insertMoneyLog($data){
    $data['money'] = floatval($data['money']);

    if( isset($data['from']) && !empty($data['from']) && isset($data['to']) && !empty($data['to']) ){ //人对人
        if( !$meter = model('app\admin\model\Meter')->getMeterInfo(['id' => $data['from']],'find') ){
            Log::record(['from表具不存在' => $data],'error');
            $ret['code'] = ERROR_CODE_DATA_ILLEGAL;
            $ret['msg'] = '付款方不存在';
            return $ret;
        }
        if( !model('app\admin\model\Meter')->getMeterInfo(['id' => $data['to']],'find') ){
            Log::record(['to表具不存在' => $data],'error');
            $ret['code'] = ERROR_CODE_DATA_ILLEGAL;
            $ret['msg'] = '收款方不存在';
            return $ret;
        }
        if( $data['money_type'] == MONEY_TYPE_RMB ){
            if(!model('app\admin\model\Meter')->updateMoney($data['from'],'inc','balance_rmb',$data['money'])){
                Log::record(['inc人民币余额失败' => model('app\admin\model\Meter')->getError(),'data' => $data],'error');
                $ret['code'] = ERROR_CODE_DATA_ILLEGAL;
                $ret['msg'] = '更新余额失败';
                return $ret;
            }
            if(!model('app\admin\model\Meter')->updateMoney($data['to'],'dec','balance_rmb',$data['money'])){
                Log::record(['dec人民币余额失败' => model('app\admin\model\Meter')->getError(),'data' => $data],'error');
                $ret['code'] = ERROR_CODE_DATA_ILLEGAL;
                $ret['msg'] = '更新余额失败';
                return $ret;
            }
        }
        if( $data['money_type'] == MONEY_TYPE_DELI ){
            if(!model('app\admin\model\Meter')->updateMoney($data['from'],'dec','balance_deli',$data['money'])){
                Log::record(['dec得力币余额失败' => model('app\admin\model\Meter')->getError(),'data' => $data],'error');
                $ret['code'] = ERROR_CODE_DATA_ILLEGAL;
                $ret['msg'] = '更新余额失败';
                return $ret;
            }
            if(!model('app\admin\model\Meter')->updateMoney($data['to'],'inc','balance_deli',$data['money'])){
                Log::record(['inc得力币余额失败' => model('app\admin\model\Meter')->getError(),'data' => $data],'error');
                $ret['code'] = ERROR_CODE_DATA_ILLEGAL;
                $ret['msg'] = '更新余额失败';
                return $ret;
            }
        }
    }elseif( isset($data['from']) && !empty($data['from']) ){
        if( !$meter = model('app\admin\model\Meter')->getMeterInfo(['id' => $data['from']],'find') ){
            Log::record(['from表具不存在' => $data],'error');
            $ret['code'] = ERROR_CODE_DATA_ILLEGAL;
            $ret['msg'] = '付款方不存在';
            return $ret;
        }
        if( $data['money_type'] == MONEY_TYPE_RMB ){
            if(!model('app\admin\model\Meter')->updateMoney($data['from'],'inc','balance_rmb',$data['money'])){
                Log::record(['inc人民币余额失败' => model('app\admin\model\Meter')->getError(),'data' => $data],'error');
                $ret['code'] = ERROR_CODE_DATA_ILLEGAL;
                $ret['msg'] = '更新余额失败';
                return $ret;
            }
        }elseif($data['money_type'] == MONEY_TYPE_DELI ){
            if(!model('app\admin\model\Meter')->updateMoney($data['from'],'dec','balance_deli',$data['money'])){
                Log::record(['dec得力币余额失败' => model('app\admin\model\Meter')->getError(),'data' => $data],'error');
                $ret['code'] = ERROR_CODE_DATA_ILLEGAL;
                $ret['msg'] = '更新余额失败';
                return $ret;
            }
        }
    }elseif( isset($data['to']) && !empty($data['to']) ){
        if( !$meter = model('app\admin\model\Meter')->getMeterInfo(['id' => $data['to']],'find') ){
            Log::record(['to表具不存在' => $data],'error');
            $ret['code'] = ERROR_CODE_DATA_ILLEGAL;
            $ret['msg'] = '收款方不存在';
            return $ret;
        }
        if( $data['money_type'] == MONEY_TYPE_RMB ){
            if(!model('app\admin\model\Meter')->updateMoney($data['to'],'dec','balance_rmb',$data['money'])){
                Log::record(['dec人民币余额失败' => model('app\admin\model\Meter')->getError(),'data' => $data],'error');
                $ret['code'] = ERROR_CODE_DATA_ILLEGAL;
                $ret['msg'] = '更新余额失败';
                return $ret;
            }
        }elseif($data['money_type'] == MONEY_TYPE_DELI ){
            if(!model('app\admin\model\Meter')->updateMoney($data['to'],'inc','balance_deli',$data['money'])){
                Log::record(['inc得力币余额失败' => model('app\admin\model\Meter')->getError(),'data' => $data],'error');
                $ret['code'] = ERROR_CODE_DATA_ILLEGAL;
                $ret['msg'] = '更新余额失败';
                return $ret;
            }
        }
    }else{
        Log::record(['信息不符合要求' => '','data' => $data],'error');
        $ret['code'] = ERROR_CODE_DATA_ILLEGAL;
        $ret['msg'] = '信息不符合要求';
        return $ret;
    }
    $data['create_time'] = time();
    $data['company_id'] = $meter['company_id'];
    if( !$moneyLogId = model('MoneyLog')->add($data) ){
        Log::record(['moneyLog添加失败' => model('MoneyLog')->getError(),'data' => $data],'error');
        $ret['code'] = ERROR_CODE_DATA_ILLEGAL;
        $ret['msg'] = '添加消费记录失败';
        return $ret;
    }
    return $moneyLogId;
}

/**
 * 修改/添加task
 * @param $data
 * @return \think\response\Json
 */
function upsertTask($data){
    if(isset($data['id'])){
        $data['update_time'] = time();
        if(!Db::name('task')->update($data)){
            Log::record(['修改task失败' => $data],'error');
            $ret['code'] = ERROR_CODE_DATA_ILLEGAL;
            $ret['msg'] = '修改task失败';
            return $ret;
        }
    }else{
        if( !isset($data['meter_id']) ){
            Log::record(['添加task失败,meter_id为空' => $data],'error');
            $ret['code'] = ERROR_CODE_DATA_ILLEGAL;
            $ret['msg'] = '请先提供表id';
            return $ret;
        }
        if( !$meterInfo = model('app\admin\model\Meter')->getMeterInfo(['id' => $data['meter_id'],'meter_life' => METER_LIFE_ACTIVE],'find','id') ){
            Log::record(['添加task失败,表id不存在' => $data],'error');
            $ret['code'] = ERROR_CODE_DATA_ILLEGAL;
            $ret['msg'] = '表id不存在';
            return $ret;
        }
        $data['meter_id'] = $meterInfo['id'];
        $data['exec_times'] = isset($data['exec_times']) ? $data['exec_times'] : 1; //执行次数,默认为1
        $data['status'] = TASK_WAITING;
        $data['seq_id'] = getAutoIncId('autoinc',['name' => 'task','meter_id' => $meterInfo['id']],'seq_id',1);
        if(in_array($data['cmd'],['charge','deduct'])){ //如果是扣款/充值任务,需要增加扣款/充值次数字段
            $data['send_times'] = getAutoIncId('autoinc',['name' => $data['cmd'],'meter_id' => $meterInfo['id']],'seq_id',1);
        }
        //改变表具余额的task,都需要此字段,值就是待下发给表具的金额,可以为负数,用于report api处理task
        if(isset($data['money_log_id'])){
            if(isset($data['balance_rmb'])){
                $data['balance_rmb'] = floatval($data['balance_rmb']);
            }else{
                $data['balance_rmb'] = 0;
            }
        }
        $data['create_time'] = time();
        if(!Db::name('task')->insert($data)){
            Log::record(['添加task失败' => $data],'error');
            $ret['code'] = ERROR_CODE_DATA_ILLEGAL;
            $ret['msg'] = '添加task失败';
            return $ret;
        }
    }
    return true;
}

/**
 * 保存并压缩图片
 * @param $img
 * @param $oriPath
 * @param $thumbPath
 * @return string
 */
function saveImg($img, $oriPath, $thumbPath){
    $publicPath =  ROOT_PATH . 'public' ;
    // 保存原图
    $info = $img->validate(['size' => 10 * 1024 * 1024, 'ext' => 'jpg,jpeg,png'])->rule('uniqid')->move($publicPath.$oriPath);
    if ($info) {
        $filename = $info->getSaveName();
        //保存缩略图
        if (!is_dir($publicPath.$thumbPath)) {
            mkdir($publicPath.$thumbPath);
        }
        $image = \think\Image::open($publicPath.$oriPath . DS . $filename);
        if (!$image->thumb(config('thumbMaxWidth'), config('thumbMaxHeight'))->save($publicPath.$thumbPath . DS . $filename)) {
            exception();
        }
    }else{
        exception($img->getError());
    }
    return $thumbPath . DS . $filename;
}

function saveVideo($video){
    $savePath = ROOT_PATH . 'public' . DS .'productionCover' . DS . 'video';
    $info = $video->validate(['size' => 20 * 1024 * 1024, 'ext' => 'mp4'])->rule('uniqid')->move($savePath);
    if ($info) {
        $filename = $info->getSaveName();
    }else{
        exception($video->getError());
    }
    return DS .'productionCover' . DS . 'video' . DS . $filename;
}


function send_post($url, $data){
    $data = json_encode($data);
    $curl = curl_init();
    //设置提交的url
    curl_setopt($curl, CURLOPT_URL, $url);
    //设置头文件的信息作为数据流输出
    curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Content-Length: ' . strlen($data)]); //api接收的json格式
    //设置获取的信息以文件流的形式返回，而不是直接输出。
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    //设置post方式提交
    curl_setopt($curl, CURLOPT_POST, 1);
    //设置post数据
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    //执行命令
    $result = curl_exec($curl);
    //关闭URL请求
    curl_close($curl);
    //获得数据并返回
    return $result;
}

function send_get($url){
    //初始化
    $curl = curl_init();
    //设置抓取的url
    curl_setopt($curl, CURLOPT_URL, $url);
    //设置头文件的信息作为数据流输出
    curl_setopt($curl, CURLOPT_HEADER, 0);
    //设置获取的信息以文件流的形式返回，而不是直接输出。
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    //执行命令
    $data = curl_exec($curl);
    //关闭URL请求
    curl_close($curl);
    //显示获得的数据
    return $data;
}

function fixString($arr){
    foreach($arr as $key => & $value){
        $arr[$key] = urlencode($value);
    }
    return $arr;
}

function des_encrypt($string) {
    //加密用的密钥文件
    $key = config('extra_config.DesKey');

    //加密方法
    $cipher_alg = MCRYPT_TRIPLEDES;
    //初始化向量来增加安全性
    $iv = mcrypt_create_iv(mcrypt_get_iv_size($cipher_alg,MCRYPT_MODE_ECB), MCRYPT_RAND);

    //开始加密
    $encrypted_string = mcrypt_encrypt($cipher_alg, $key, $string, MCRYPT_MODE_ECB, $iv);
    return base64_encode($encrypted_string);//转化成16进制
//        return $encrypted_string;
}

function des_decrypt($string) {
    $string = base64_decode($string);

    //加密用的密钥文件
    $key = config('extra_config.DesKey');

    //加密方法
    $cipher_alg = MCRYPT_TRIPLEDES;
    //初始化向量来增加安全性
    $iv = mcrypt_create_iv(mcrypt_get_iv_size($cipher_alg,MCRYPT_MODE_ECB), MCRYPT_RAND);

    //开始解密
    $decrypted_string = mcrypt_decrypt($cipher_alg, $key, $string, MCRYPT_MODE_ECB, $iv);
    return trim($decrypted_string);
}