<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/30 0030
 * Time: 14:32
 */

namespace app\timetask\command;

use app\timetask\model\Wallet;
use think\console\Command;
use think\console\Input;
use think\console\Output;

use app\timetask\model\Currency;
use app\timetask\model\Releaselog;
use think\Db;
use app\timetask\model\Release;
use app\common\controller\Rpcutils;

class Current extends Command
{
    public function __construct()
    {
        parent::__construct();
        $this->releaseLog = new Releaselog();
        $this->release = new Release();
        $this->wallet = new Wallet();
    }

    protected function configure()
    {
        $this->setName('ReleaseCoin')->setDescription('计划任务 定时发送虚拟币');
    }

    protected function execute(Input $input, Output $output)
    {
        //$output->writeln('Date Crontab job start...');
        /*** 这里写计划任务列表集 START ***/
        $this->Index();
        /*** 这里写计划任务列表集 END ***/
        //$output->writeln('Date Crontab job end...');
    }

    public function Index()
    {
        $current = new Currency();
        $list = $current->field(['id', 'cid', 'number'])
            ->where('rest_number', '>', 0)
            ->where('send', '=', 1)->select();
        //获取对应的coin链接信息
        $wallet_info = Db::table('coin')
            ->field('rpc_user,rpc_pwd,rpc_url,rpc_port')
            ->where('code','RFT')
            ->find();
        if (!$wallet_info){
            $data['errormsg'] = '无对应的钱包链接信息';
            $data['type'] = 3;
            $this->addErrorLog($data);
            return;
        }
        if ($list) {
            foreach ($list as $k => $v) {
                $list2 = $v->toArray();
                //每条记录当天对应的发币量  每天每次释放量 = 单次获赠虚拟币数量*0.1/365/2
                // $k 每条记录对应的id $v 没条记录对应的总的虚拟币数
                //计算每条记录应该每天每次需要派发的虚拟币数
                $day_releast = round($list2['number'] * 0.1 / 365 / 2,6);
                //根据uid获取钱包地址
                $wd = Db::table('customer')->where('id',$list2['cid'])->value('wallet_address');
                //发币
                $releast_result =Rpcutils::generalAccountSendfrom($wd,$day_releast,$wallet_info);
                if($releast_result == false){
                    $data['errormsg'] = '发币报错';
                    $data['type'] = 4;
                    $this->addErrorLog($data);
                    return;
                }
                //执行更新操作
                Db::startTrans();
                try {
/*                    $sql = "update currency set rest_number=rest_number-{$day_releast},send_number=send_number+{$day_releast} where id={$list2['id']}";*/
                    Db::table('currency')->where('id',$list2['id'])->setDec('rest_number',$day_releast);
                    Db::table('currency')->where('id',$list2['id'])->setInc('send_number',$day_releast);
//                    Db::query($sql);
                    //发币成功则记录发币信息到发币信息表(release)
                    $this->release->currency_id = $k;
                    $this->release->c_id = $v['cid'];
                    $this->release->release_number = $day_releast;
                    $this->release->isUpdate(false)->save();
                    //更新钱包表
/*                    $sql2 = "update wallet set account_balance =account_balance+{$day_releast} where u_id={$list2['cid']}";*/
                    Db::table('wallet')->where('u_id',$list2['cid'])->setInc('account_balance',$day_releast);
//                    Db::query($sql2);
                    Db::commit();
                } catch (\Exception $e) {
                    //发币失败日志表
                    $error = $e->getMessage();
                    //新建发币错误日志表
                    $data['errormsg'] = $error;
                    $data['type'] = 1;
                    $this->addErrorLog($data);
                }
            }
        } else {
            //没有需要发币的,记录任务当前执行的时间
            //错误日志 任务执行类型为 2 无币发状态
            $data['errormsg'] = '暂无需要发币的用户';
            $data['type'] = 2;
            $this->addErrorLog($data);
        }
    }

    public function addErrorLog($data)
    {
        $this->releaseLog->errormsg = $data['errormsg'];
        $this->releaseLog->type = $data['type'];
        $this->releaseLog->create_time = time();
        $this->releaseLog->isUpdate(false)->save();
    }
}