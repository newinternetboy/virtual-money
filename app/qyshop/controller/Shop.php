<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/11/20
 * Time: 上午11:03
 */

namespace app\qyshop\controller;


class Shop extends Admin
{
    /**
     * 店铺管理
     * @return \think\response\View
     */
    public function index(){
        $shop_id = $this->shop_id;
        $shop = model('Shop')->findInfo(['id' => $shop_id]);
        $this->assign('shop',$shop);
        return view();
    }

    public function getQYShopById(){
        $ret['code'] = 200;
        $ret['msg'] = lang('Operation Success');
        try {
            $id = input('id');
            if(!$id){
                exception(lang('QYShop Id Require'),ERROR_CODE_DATA_ILLEGAL);
            }
            if(!$shop = model('Shop')->findInfo(['id' => $id],'name,desc,notify,img,personName,bank,cardNumber,status')){
                exception(lang('QYShop Not Exist'),ERROR_CODE_DATA_ILLEGAL);
            }
            $ret['data'] = $shop;
        } catch (\Exception $e) {
            $ret['code'] = $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

    /**
     * 添加企业商铺
     * @return \think\response\Json
     */
    public function addQYShop()
    {
        $ret['code'] = 200;
        $ret['msg'] = lang('Operation Success');
        try {
            $name = input('name');
            $id = input('id');
            if(!$shop = model('Shop')->findInfo(['id' => $id])){
                exception(lang('QYShop Not Exist'),ERROR_CODE_DATA_ILLEGAL);
            }
            if($shop = model('Shop')->findInfo(['name' => $name,'id' => ['neq',$id]])){
                exception(lang('Shop Name Unique'),ERROR_CODE_DATA_ILLEGAL);
            }
            $desc = input('desc');
            $notify = input('notify');
            $personName = input('personName');
            $bank = input('bank');
            $cardNumber = input('cardNumber');
            $status = input('status/d');
            $img = request()->file('img');
            if ($img) {
                $oriPath = DS . 'shopCover' . DS . 'origin';
                $thumbPath = DS . 'shopCover' . DS . 'thumb';
                $savedthumbFilePath = saveImg($img, $oriPath, $thumbPath);
                $data['img'] = $savedthumbFilePath;
            }
            $data['id'] = $id;
            $data['name'] = $name;
            $data['desc'] = $desc;
            $data['notify'] = $notify;
            $data['personName'] = $personName;
            $data['bank'] = $bank;
            $data['cardNumber'] = $cardNumber;
            $data['status'] = $status;
            if (!model('Shop')->upsert($data,false)) {
                exception(model('Shop')->getError(), ERROR_CODE_DATA_ILLEGAL);
            }
            model('app\admin\model\LogRecord')->record('Edit QYShop', ['data' => $data]);
        } catch (\Exception $e) {
            $ret['code'] = $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }
}