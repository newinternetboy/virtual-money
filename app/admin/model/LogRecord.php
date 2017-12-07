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
    protected $insert     = ['ip', 'user_id','browser','os','type','company_id'];
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

    /**
     * 日志来源
     * 1:运营商平台 2:清分平台
     * @return int
     */
    protected function setTypeAttr(){
        $type = PLATFORM_ADMIN;
        if (Session::has('userinfo', 'admin') !== false) {
            $user = Session::get('userinfo','admin');
            $type = $user['type'];
        }
        return $type;
    }

    /**
     * 日志所属公司
     * @return int
     */
    protected function setCompanyIdAttr(){
        $company_id = 0;
        if (Session::has('userinfo', 'admin') !== false) {
            $user = Session::get('userinfo','admin');
            if( $user['type'] == PLATFORM_ADMIN ){
                $company_id = $user['company_id'];
            }
        }
        return $company_id;
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

    //解析数据；
    public function translate($data){
        $arr=[];
        foreach($data as $key=>$value){
            foreach(config('extra_config.changeData') as $k=>$val){
                if($key == $k ){
                    $arr[lang($val)] = $value;
                }
            }
        }
        return $arr;
    }
    //公共的解析方法；
    public function common_trans($data){
        $datas = $this->translate($data);
        return json_encode($datas,JSON_UNESCAPED_UNICODE);
    }
    //解析表具修改
    public function MeterUpdate($data){
        $meter = $this->translate($data['meter']);
        $consumer = $this->translate($data['consumer']);
        $datas = array_merge($meter,$consumer);
        return json_encode($datas,JSON_UNESCAPED_UNICODE);
    }
    //解析表具报装
    public function MeterBinding($data){
        $meter = $this->translate($data['meter']);
        $consumer = $this->translate($data['consumer']);
        $datas = array_merge($meter,$consumer);
        return json_encode($datas,JSON_UNESCAPED_UNICODE);
    }
    //解析登录成功；
    public function LoginSucceed($data){
        return '登录成功';
    }

    //解析登出成功；
    public function Loginout($data){
        return '登出成功';
    }

    //解析删除公共的方法；
    public function commonDeleteTrans($data){
        $arr['id'] = $data;
        $datas = $this->translate($arr);
        return json_encode($datas,JSON_UNESCAPED_UNICODE);
    }

    //解析表具删除；
    public function MeterDelete($data){
        $arr['M_Code'] = $data;
        $datas = $this->translate($arr);
        return json_encode($datas,JSON_UNESCAPED_UNICODE);
    }
    /********以下未清分平台的解析***********/
    //解析扣除余额；
    public function deduct($data){
        if(is_array($data['source'])){
            foreach($data['source'] as & $value){
            $value = $this->translate($value);
            }
            $source = $data['source'];
        }else{
            $arr=['excel_file_path'=>$data['source']];
            $source = $this->translate($arr);
        }
        if(empty($data['faildata'])){
            $faildata = [];
        }else{
            foreach($data['faildata'] as & $value){
                $value = $this->translate($value);
            }
            $faildata = $data['faildata'];
        }
        $datas = array_merge($source,$faildata);
        return json_encode($datas,JSON_UNESCAPED_UNICODE);
    }

}
