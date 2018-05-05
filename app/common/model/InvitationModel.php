<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2018/1/8
 * Time: 下午2:24
 */

namespace app\common\model;


class InvitationModel extends Common
{
    public $table = 'invitation';

    public function getInfoPaginateNoorder($where = [], $param = [], $field = ''){
        if( $field ){
            return $this->where($where)->field($field)->paginate()->appends($param);
        }
        return $this->where($where)->paginate()->appends($param);
    }



}