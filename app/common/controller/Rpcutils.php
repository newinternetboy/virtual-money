<?php

namespace app\common\controller;
use think\Controller;

class Rpcutils extends Controller
{

    public static $flag = 'shedeAccount-';


    private function __clone()
    {
    }


    /**
     * 根据钱包账户获取账户钱包地址(地址存在返回，不存在创建后返回)
     * getaccountaddress <account>
     * @param $user_id 用户id 用于拼写钱包账户
     * @return 地址
     */
    public static function getAccountAddress($user_id, $wallet_info)
    {
        $account = self::$flag . $user_id;
        $bitcoin = self::getBitcoinInstance($wallet_info);
        return $bitcoin->getaccountaddress($account);
    }

    /**
     * 根据指定账户 获取钱包余额
     * getbalance [account] [minconf=1]
     * @param uesr_id
     * @return 账户余额
     */
    public static function getBalance($user_id, $wallet_info)
    {
        $bitcoin = self::getBitcoinInstance($wallet_info);
        $account = self::$flag . $user_id;
        $balance = $bitcoin->getbalance($account);
        return $balance;
    }

    //获取钱包服务器资金总和
    public static function getTotalBalance($wallet_info)
    {
        $bitcoin = self::getBitcoinInstance($wallet_info);
        $balance = $bitcoin->getbalance();
        return $balance;
    }
    /**
     * [获取平台总账户资产数量]
     * @param  [type] $wallet_info [description]
     * @return [type]              [description]
     */
    public static function generalAccountBalance($wallet_info)
    {
        $bitcoin = self::getBitcoinInstance($wallet_info);
        $balance = $bitcoin->getbalance('shedeshequ1805');
        return $balance;
    }

    /**
     * 根据交易ID获取相关交易记录
     * gettransaction <txid>
     * @param txid 交易ID
     * @return array
     */
    public static function getTransaction($txid, $wallet_info)
    {
        $bitcoin = self::getBitcoinInstance($wallet_info);
        return $bitcoin->gettransaction($txid);
    }

    /**
     * 转币（从用户账户转到一个钱包地址）
     * @param $user_id      from user_id
     * @param $to_address    to address  钱包地址
     * @param int $num 转币数量
     * @param array $wallet_info 钱包信息
     * @return mixed
     */
    public static function sendfrom($user_id, $to_address, $num = 0, $wallet_info)
    {
        $account = self::$flag . $user_id;
        $bitcoin = self::getBitcoinInstance($wallet_info);
        return $bitcoin->sendfrom($account, $to_address, $num);
    }
    /**
     * 平台发币（从平台账户转到一个钱包地址）
     * @param $to_address    to address  钱包地址
     * @param int $num 转币数量
     * @param array $wallet_info 钱包信息
     * @return mixed
     */
    public static function generalAccountSendfrom($to_address, $num = 0, $wallet_info)
    {
        $account = 'shedeshequ1805';
        $bitcoin = self::getBitcoinInstance($wallet_info);
        return $bitcoin->sendfrom($account, $to_address, $num);
    }

    /**
     *  导出私钥
     * @param $address  用户钱包地址
     * @param $wallet_info 钱包信息
     * @return mixed 返回私钥
     */
    public static function dumpprivkey($address, $wallet_info)
    {
        $bitcoin = self::getBitcoinInstance($wallet_info);
        $privakey = $bitcoin->dumpprivkey($address);
        return $privakey;
    }

//获取Bitcoin实例
    private static function getBitcoinInstance($wallet_info)
    {
//        require_once EXTEND_PATH . 'wallet/assaeasybitcoin.php';
        return new Bitcoin($wallet_info['rpc_user'], $wallet_info['rpc_pwd'], $wallet_info['rpc_url'], $wallet_info['rpc_port']);
    }


}