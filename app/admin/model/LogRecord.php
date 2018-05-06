<?php
namespace app\admin\model;

use \think\Config;
use \think\Model;
use \think\Session;


/**
 * 操作日志记录
 */
class LogRecord extends Admin
{
    protected $updateTime = false;
    protected $insert     = ['ip', 'user_id'];
    /*
     * 关联user表;
     */
    public function user()
    {
        return $this->belongsTO('user','user_id');
    }

    /**
     * 记录ip地址
     */
    protected function setIpAttr()
    {
        return \app\common\tools\Visitor::getIP();
    }

    /**
     * 用户id
     */
    protected function setUserIdAttr()
    {
        $user_id = 0;
        if (Session::has('userinfo', 'admin') !== false) {
            $user = Session::get('userinfo','admin');
            $user_id = $user['id'];
        }
        return $user_id;
    }
 
    public function record($data)
    {
        $this->save($data);
    }


    public function UniqueIpCount()
    {   
        $data = $this->column('ip');
        $data = count( array_unique($data) );
        return $data;
    }
    /*
     * 查询日志信息
     */
    public function getLogrecord($where,$data)
    {
        return $this->order('create_time desc')->where($where)->paginate()->appends($data);
    }



}
