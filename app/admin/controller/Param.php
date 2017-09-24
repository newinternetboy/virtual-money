<?php
namespace app\admin\controller;
class Param extends Admin
{
	function index(){
		return view();
	}
   public function getList() 
    {
        if(!request()->isAjax()) {
            $this->error(lang('Request type error'), 4001);
        }
        $request = request()->param();
        $request['company_id'] = $this->company_id;
        $data = model('Param')->getList( $request );
        $total = model('Param')->getTotalAreaNumber(['company_id' => $this->company_id]);
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
        $price = model('Param')->where(['id' => $id])->find();
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
        model('LogRecord')->record( lang('修改黑名单属性成功'),json_encode($data) );
        return model('Param')->saveData( $data );
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
        model('LogRecord')->record( lang('修改黑名单属性成功'),json_encode($id) );
        return model('Param')->deleteById($id);
    }

}