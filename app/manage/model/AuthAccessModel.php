<?php
/**
 * Created by PhpStorm.
 * User: ducongshu
 * Date: 2017/10/26
 * Time: 下午18:00
 */

namespace app\manage\model;


class AuthAccessModel extends BasicModel
{
    // 当前模型名称
    protected $name = 'authAccess';

    /**
     * 插入/更新
     * @param $data
     * @param bool|true $scene
     * @param $M_Code 分表预留字段
     * @return bool|string
     */
    public function upsert($data, $scene = true,$M_Code = ''){
        if( $authAccess = $this->findInfo(['role_id' => $data['role_id']]) ){
            $data['id'] = $authAccess['id'];
            $result = $this->validate($scene)->isUpdate(true)->save($data);
            if($result === false){
                return false;
            }
            return true;
        }else{
            unset($data['id']);
            $result = $this->validate($scene)->isUpdate(false)->save($data);
            if($result === false){
                return false;
            }
            return $this->getLastInsID();
        }
    }
}