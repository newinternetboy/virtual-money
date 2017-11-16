<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/10/31
 * Time: 下午6:50
 */

namespace app\manage\model;


class MoneyLogModel extends BasicModel
{
    // 当前模型名称
    protected $name = 'MoneyLog';

    /**
     * from 字段关联meter模型
     * @return \think\model\relation\BelongsTo
     */
    public function meter(){
        return $this->belongsTo('MeterModel','from','id');
    }

    /**
     * to 字段关联meter模型
     * @return \think\model\relation\BelongsTo
     */
    public function tometer(){
        return $this->belongsTo('MeterModel','to','id');
    }

    /**
     * 通过whereor条件获取分页数据
     * @param array $where
     * @param array $whereor
     * @param array $param
     * @param string $field
     * @return $this
     */
    public function getInfoPaginateWhereor($where =[], $whereor = [], $param = [], $field = ''){
        if( $field ){
            return $this->where($where)->whereor($whereor)->field($field)->order('create_time','desc')->paginate()->appends($param);
        }
        return $this->where($where)->whereor($whereor)->order('create_time','desc')->paginate()->appends($param);
    }
}