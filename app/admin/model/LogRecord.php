<?php
namespace app\admin\model;

use \think\Config;
use \think\Model;
use \think\Session;


/**
 * 操作日志记录
 */
class logRecord extends Admin
{
    protected $updateTime = false;
    protected $insert     = ['ip', 'user_id','browser','os'];
    protected $type       = [
        'create_time' => 'timestamp',
    ];

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
     * 浏览器把版本
     */
    protected function setBrowserAttr()
    {
        return \app\common\tools\Visitor::getBrowser().'-'.\app\common\tools\Visitor::getBrowserVer();
    }

    /**
     * 系统类型
     */
    protected function setOsAttr()
    {
        return \app\common\tools\Visitor::getOs();
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
 
    public function record($remark,$data = '')
    {
        $this->save(['remark' => $remark,'data' => $data]);
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
        return $this->order('create_time')->where($where)->paginate()->appends($data);
    }
    //解析修改密码
    public function UpdatePasswd($data){
        @$str  = '修改密码';
        return $str;
    }
    //解析表具过户
    public function MeterPass($data){
        $data = json_decode($data,true);
        return "就表号是：".$data['old_M_Code']."；新表号是：".$data['new_M_Code']."；原因是：".$data['change_reason'];
    }
    //解析表具修改
    public function MeterUpdate($data){
        $data = json_decode($data,true);
        return "修改后的表号是：".$data['meter']['M_Code']."；地址是：".$data['meter']['detail_address']."；用户是：".$data['consumer']['username'];
    }
    //解析表具报装
    public function MeterBinding($data){
        $data = json_decode($data,true);
        return "报装的类型是：".$data['meter']['M_Type']."；表号是：".$data['meter']['M_Code']."；用户是：".$data['consumer']['username'];
    }
    //解析修改黑名单属性成功
    public function UpdateBlacklistparam($data){
        return "参数代号是：".$data['param_name']."；参数描述：".$data['desc']."；参数类型：".$data['param_type']."；参数：".$data['opt_id'];
    }
    //解析修改/添加权限
    public function UpdateandAddauth($data){
        $data = json_decode($data,true);
        return "修改或添加的标题为：".$data['title']."；路径是：".$data['rule_val'];
    }
    //解析添加/修改价格
    public function UpdateandAddprice($data){
        return "价格名称是：".$data['name'];
    }
    //解析修改权限
    public function UpdateAuth($data){
        $data = json_decode($data,true);
        return "角色id：".$data['role_id'];
    }
    //解析修改/添加用户
    public function UpdateandAdduser($data){
        $data = json_decode($data,true);
        return "用户名为：".$data['username']."；号码是：".$data['mobile'];
    }
    //解析添加/修改区域
    public function UpdateandAddarea($data){
        return "区域名称为：".$data['name']."；所属区域为：".$data['belong']."；描述：".$data['desc']."；详细地址为：".$data['address'];
    }
    //解析修改运行参数；
    public function UpdateMeterparam($data){
        return "脉冲常量：".$data['pulseRatio']."；低剩余报警：".$data['lowLimit']."；透视限额：".$data['overdraftLimit']."；透支限制时间：".$data['overdraftTime']."；冻结时间：".$data['freezeTime']."；自动上报时间".$data['uploadTime']."；短信平台号码：".$data['SMSCode']."；开机脉冲数：".$data['transformerRatio']."；流量上限：".$data['overFlimit']."；参数名称：".$data['tag'];
    }

}
