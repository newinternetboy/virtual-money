<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/12/1
 * Time: 下午1:48
 */

namespace app\manage\model;


class CommentModel extends BasicModel
{
    // 当前模型名称
    protected $name = 'comment';

    public function consumer()
    {
        return $this->belongsTo('ConsumerModel','uid');
    }
}