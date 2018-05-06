<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2018/1/8
 * Time: 下午2:24
 */

namespace app\common\model;


class ArticleModel extends Common
{
    public $table = 'article';

    public function selectLimitInfo($where,$field,$skip,$limit,$order){
        if( $field ){
            return $this->where($where)->limit($skip,$limit)->field($field)->order($order)->select();
        }
        return $this->where($where)->limit($skip,$limit)->order($order)->select();
    }
}