<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/9/11
 * Time: 下午5:14
 */

namespace app\admin\controller;

use think\Loader;
use think\Log;

/**
 * 价格维护
 * Class Price
 * @package app\admin\controller
 */
class Price extends Admin
{
    public function index(){
        return view();
    }

    /**
     * 异步获取列表数据
     *
     * @author chengbin
     * @return mixed
     */
    public function getList()
    {
        if(!request()->isAjax()) {
            $this->error(lang('Request type error'), 4001);
        }
        $request = request()->param();
        $request['company_id'] = $this->company_id;
        $data = model('Price')->getList( $request );
        $total = model('Price')->getTotalPriceNumber(['company_id' => $this->company_id]);
        return json(["total" => $total,"rows" => $data]);
    }

    /**
     * 添加
     */
    public function add()
    {
        return $this->fetch('edit');
    }

    /**
     * 编辑
     * @param  string $id 数据ID（主键）
     */
    public function edit($id = 0)
    {
        if(empty($id)){
            return info(lang('Data ID exception'), 0);
        }
        $price = model('Price')->where(['id' => $id])->find();
        $this->assign('data', $price);
        return $this->fetch();
    }

    /**
     * 保存数据
     * @param array $data
     *
     * @author chengbin
     */
    public function saveData()
    {
        $this->mustCheckRule();
        if(!request()->isAjax()) {
            return info(lang('Request type error'));
        }

        $data = input('post.');
        $data['company_id'] = $this->company_id;
        if(empty($data['id'])){
            unset($data['id']);
        }
        if( !model('Price')->saveData( $data ) ){
            Log::record(['添加价格失败' => model('Price')->getError(),'data' => $data],'error');
            $this->error(model('Price')->getError());
        }
        model('LogRecord')->record( lang('Save Price'),$data);
        $this->success(lang('Save success'));
    }

    /**
     * 删除
     * @param  string $id 数据ID（主键）
     */
    public function delete($id = 0){
        $this->mustCheckRule();
        if(empty($id)) {
            return info(lang('Data ID exception'), 0);
        }
        $prices = model('Price')->getPricesById($id,$this->company_id);
        if( count($prices) != count(explode(',',$id)) ){
            Log::record(['删除价格失败' => 0,'data' => $id],'error');
            $this->error('操作失败,信息有误');
        }
        if( !model('Price')->deleteById($id) ){
            Log::record(['删除价格失败' => model('Price')->getError(),'data' => $id],'error');
            $this->error('操作失败');
        }
        Loader::model('LogRecord')->record( lang('Delete Price'),$id );
        $this->success(lang('Delete succeed'));
    }
}