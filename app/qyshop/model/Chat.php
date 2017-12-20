<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/12/19
 * Time: 下午12:34
 */

namespace app\qyshop\model;


class Chat extends Basic
{
    public function consumer(){
        return $this->belongsTo('Consumer','uid');
    }


    public function paginateInfo($where = [],$param = [],$field = '')
    {
        if( $field ){
            return $this->where($where)->field($field)->order('user_update_time','desc')->paginate()->appends($param);
        }
        return $this->where($where)->order('user_update_time','desc')->paginate()->appends($param);
    }

    public function getPaginateGroupByUid($where,$skip,$limit){
        $connectString = 'mongodb://';
        if(config('database.username') && config('database.password')){
            $connectString .= config('database.username') . ':' .config('database.password') . '@';
        }
        $connectString .= config('database.hostname') . ':' . config('database.hostport') . '/' . config('database.database');
        $mongodb = new \MongoDB\Driver\Manager($connectString);
        $database = config('database.database');
        $command = new \MongoDB\Driver\Command([
            'aggregate' => 'chat',
            'pipeline' => [
                ['$match' => $where],
                ['$group' => ['_id' => ['uid' => '$uid'],'count' => ['$sum' => 1]]],
                ['$skip' => $skip],
                ['$limit' => $limit],
            ],
        ]);
        $result = $mongodb->executeCommand($database,$command);
        return $result->toArray();
    }

    public function read($where){
        return $this->where($where)->update(['status' => CHAT_STATUS_CHECKED]);
    }
}