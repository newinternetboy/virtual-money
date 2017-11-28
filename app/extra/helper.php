<?php

/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/11/28
 * Time: 下午12:17
 */
use think\Loader;

//代码中使用model助手函数时,参数中的model类名有的大写有的小写,导致部署到生产环境因大小写问题找不到文件报错
//重写model()助手函数,将model类名统一转成首字母大写
if (!function_exists('model')) {
    /**
     * 实例化Model
     * @param string    $name Model名称
     * @param string    $layer 业务层名称
     * @param bool      $appendSuffix 是否添加类名后缀
     * @return \think\Model
     */
    function model($name = '', $layer = 'model', $appendSuffix = false)
    {
        if (false == strpos($name, '\\')) {
            $name = ucfirst($name);
        }else{
            $index = strripos($name,'\\');
            $pre = substr($name,0,$index+1);
            $modelClassName = substr($name,$index+1);
            $modelClassName = ucfirst($modelClassName);
            $name = $pre.$modelClassName;
        }
        return Loader::model($name, $layer, $appendSuffix);
    }
}