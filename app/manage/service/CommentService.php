<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/12/4
 * Time: 上午10:46
 */

namespace app\manage\service;

use app\manage\model\CommentModel;

class CommentService extends BasicService
{
    public function __construct(){
        $this->dbModel = new CommentModel();
    }
}