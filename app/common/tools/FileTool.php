<?php

namespace app\common\tools;

use think\File;

/**
 * 文件工具
 * Class FileTool
 * @package app\common\tool
 */
class FileTool
{
    /**
     * 校验文件是否存在
     * @param $file
     * @return bool
     */
    public function isfile($file){
        if(gettype($file) != 'object'){
            exception('请先上传格式正确的文件!');
        }
        return true;
    }

    /**
     * 校验文件大小
     * @param File $file
     * @param string $size
     * @return bool
     */
    public function checkSize($file,  $size){
        if (!$file->checkSize($size)){
            exception('上传文件过大,最大支持'.($size/1024/1024).'M');
        }
        return true;
    }

    /**
     * 校验文件格式
     * @param File $file
     * @param string $ext 文件后缀,支持多个格式校验,用","分隔
     * @return bool
     */
    public function checkExt( $file,  $ext){
        if(!$file->checkExt($ext)){
            exception('上传文件格式不正确,支持格式:'.$ext);
        }
        return true;
    }
}