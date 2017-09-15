<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/9/11
 * Time: 下午5:14
 */

namespace app\admin\controller;

use think\Loader;

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
        $this->mustCheckRule($this->company_id,'');
        if(!request()->isAjax()) {
            return info(lang('Request type error'));
        }

        $data = input('post.');
        $data['company_id'] = $this->company_id;
        if(empty($data['id'])){
            unset($data['id']);
        }
        model('LogRecord')->record( lang('Save Price'),json_encode($data) );
        return model('Price')->saveData( $data );
    }

    /**
     * 删除
     * @param  string $id 数据ID（主键）
     */
    public function delete($id = 0){
        $this->mustCheckRule($this->company_id,'');
        if(empty($id)) {
            return info(lang('Data ID exception'), 0);
        }
        model('LogRecord')->record( lang('Delete Price'),json_encode($id) );
        return model('Price')->deleteById($id);
    }
}