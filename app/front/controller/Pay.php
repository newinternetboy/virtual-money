<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/1 0001
 * Time: 14:02
 */

namespace app\front\controller;

use think\Controller;
use think\Db;
use app\front\model\Customer;


use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\LabelAlignment;
use Endroid\QrCode\QrCode;

use app\timetask\model\Wallet;
use app\admin\model\Payorder;
class Pay extends Controller
{
    public $wallet_address;
    public $pay_amount;
    //二维码（根据钱包地址生成）
    public function codeImage(){
/*
       var_dump($locolName);die;
        $locolName = $_SERVER['SERVER_NAME'];

        $url = 'https://'.$locolName.'/admin/coin/index';//加http://这样扫码可以直接跳转url*/
        $qrCode = new QrCode();
        $text = $this->getUserWalletAdress();
        if(!$text){
            $text = '暂无对应的钱包地址,请联系客户';
        }
        $qrCode->setText($text);
        $qrCode->setSize(300);

// Set advanced options
        $qrCode->setWriterByName('png');
        $qrCode->setMargin(10);
        $qrCode->setEncoding('UTF-8');
        $qrCode->setErrorCorrectionLevel(ErrorCorrectionLevel::HIGH);
        $qrCode->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0, 'a' => 0]);
        $qrCode->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255, 'a' => 0]);
        $qrCode->setLabel('Scan the code', 16, __DIR__.'/../../../vendor/endroid/qrcode/assets/noto_sans.otf', LabelAlignment::CENTER);
        $qrCode->setLogoPath(__DIR__.'/../../../public/static/admin/images/coin.jpg');
        $qrCode->setLogoWidth(100);
        $qrCode->setValidateResult(false);

// Directly output the QR code
        header('Content-Type: '.$qrCode->getContentType());
        echo $qrCode->writeString();exit;
    }

    /**
     * @return array 根据接收方钱包地址获取用户信息
     */
    public function getUserInfoByWI(){
        $wallet_address = $this->wallet_address;
        $user_info = Customer::get(['wallet_address'=>$wallet_address])->toArray();
        return $user_info;
    }

    //获取明文钱包地址
    public function getUserWalletAdress(){
        //登陆后用户的id，要存在session中
        $cid = $_SESSION['user']['cid'];
        //获取钱包地址
        $wallet_address = Customer::get(['id'=>$cid])->getData('wallet_address');
        if($wallet_address){
            return $wallet_address;
        }else{
            return json(['msg'=>'暂无钱包地址','status'=>false,'code'=>300]);
        }
    }

    //校验钱包地址是否存在
    public function getAllWalletAdress(){
        $addresses = Customer::all()[0]->column('wallet_address');
        $wallet_address = $this->wallet_address;
        if(empty($wallet_address)){
            $info['code'] = '300';
            $info['status'] = false;
            $info['msg'] = '钱包地址不能为空';
            return json($info);
        }
        if(!in_array($wallet_address,$addresses)){
            return json(['msg'=>'钱包地址不存在，请检查后重新输入',
                'code'=>300,
                'status' => false
            ]);
        }
        return true;
    }

    //校验当前用户虚拟币数量是否大于交易量
    public function checkPayMount(){
        $pay_amount = $this->pay_amount;
        //检验数量是否合法
        if(!is_numeric($pay_amount)){
            return json([
                'msg' => '请输入数字',
                'code' => 300,
                'status' => false
            ]);
        }
        //获取该用户钱包的虚拟币的数量
        $u_id = $_SESSION['user']['cid'];
//        $wallet = Wallet::get(['u_id' => $u_id])->getData('account_balance');
        $wallet = Db::table('wallet')->where('u_id',$u_id)->value('account_balance');
        $checkresult = bcsub($wallet,$pay_amount,4);
        if(!$checkresult){
            return json([
                'msg' => '钱包余额不足',
                'code' => 300,
                'status' => fasle
            ]);
        }
        return true;
    }

    //支付
    public function pay(){
        $u_id = $_SESSION['user']['cid'];
        $this->pay_amount = trim(input('post.pay_amount'));
        $this->wallet_address = trim(input('post.wallet_address'));
        $checkWallet = $this->getAllWalletAdress();
        if($checkWallet!==true){
            return $checkWallet;
        }
        $checkAmount = $this->checkPayMount();
        if($checkAmount!==true){
            return $checkAmount;
        }
        $u_to_info = $this->getUserInfoByWI();
        $u_id_to = $u_to_info['id'];
        //执行交易
        //交易逻辑 1 从用户的钱包转出，转到接受方  地址
        // 2 记录到 payorder；两条一条支出一条收入
        Db::startTrans();
        try{
            //支付方扣币
            Db::table('wallet')->where('u_id',$u_id)->setDec('account_balance',$this->pay_amount);
/*            $sql1 = "update wallet set account_balance =account_balance-{$this->pay_amount} where u_id={$u_id}";*/
            //接收方收币
/*            $sql2 = "update wallet set account_balance =account_balance+{$this->pay_amount} where u_id={$u_id_to}";*/
            Db::table('wallet')->where('u_id',$u_id_to)->setInc('account_balance',$this->pay_amount);
//            Db::query($sql1);
//            Db::query($sql2);

            //记下交易记录
            $pay_order = new Payorder();
            $order_id_to = 't_'.$u_id_to.date('YmdHis').mt_rand(1000,9999);
            $order_id_from = 'f_'.$u_id.date('YmdHis').mt_rand(1000,9999);
            $list =[
                ['order_id'=>$order_id_to,'u_id'=>$u_id,'volume'=>$this->pay_amount,'pay_type'=>1],
                ['order_id'=>$order_id_from,'u_id'=>$u_id_to,'volume'=>$this->pay_amount,'pay_type'=>2],
            ];
            $pay_order->saveAll($list);
        Db::commit();
            return json([
                'status'=>true,
                'msg' => '支付成功',
                'code'=> '200'
            ]);
        }catch (\Exception $e){
            Db::rollback();
            return json([
                'status'=>false,
                'code'=>'300',
                'msg'=>'系统繁忙，请稍后'
            ]);
        }
    }

    public function payMoney(){
        return $this->fetch('pay');
    }
}