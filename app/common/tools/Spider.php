<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2018/1/10
 * Time: 上午10:50
 */

namespace app\common\tools;

use QL\QueryList;

class Spider
{
    public function spiderSimuInfo($register_id){

        $url = config('spider_config.url');
        $spider_url_prefix = config('spider_config.spider_url_prefix');
        $data = [
            'keyword'   =>  $register_id
        ];
        $result = send_post($url,$data);
        $result = json_decode($result,true);
        if(empty($result['content'])){
            exception('没有符合条件的结果');
        }
        $spider_url = $spider_url_prefix.$result['content'][0]['url'];
        $ql = QueryList::get($spider_url);
        $tbody = $ql->find('table[class=table table-center table-info]');
        $tbody->find('tr:first')->_empty();
        $simudata = [
           'name'                               =>                          $tbody->find('#complaint1')->text(),
           'name_english'                       =>                          $tbody->find('tr:eq(3)')->find('.td-content')->text(),
           'register_id'                        =>                          $register_id,
           'organization_code'                  =>                          $tbody->find('tr:eq(5)')->find('.td-content')->text(),
           'regis_time'                         =>                          $tbody->find('tr:eq(6)')->find('.td-content')->eq(0)->text(),
           'found_time'                         =>                          $tbody->find('tr:eq(6)')->find('.td-content')->eq(1)->text(),
           'R_address'                          =>                          $tbody->find('tr:eq(7)')->find('.td-content')->text(),
           'work_address'                       =>                          $tbody->find('tr:eq(8)')->find('.td-content')->text(),
           'regis_money'                        =>                          $tbody->find('tr:eq(9)')->find('.td-content')->eq(0)->text(),
           'paid_capital'                       =>                          $tbody->find('tr:eq(9)')->find('.td-content')->eq(1)->text(),
           'company_nature'                     =>                          $tbody->find('tr:eq(10)')->find('.td-content')->eq(0)->text(),
           'paid_percent'                       =>                          $tbody->find('tr:eq(10)')->find('.td-content')->eq(1)->text(),
           'institution_type'                   =>                          $tbody->find('tr:eq(11)')->find('.td-content')->eq(0)->text(),
           'service_type'                       =>                          $tbody->find('tr:eq(11)')->find('.td-content')->eq(1)->text(),
           'people'                             =>                          $tbody->find('tr:eq(12)')->find('.td-content')->eq(0)->text(),
           'institution_web'                    =>                          $tbody->find('tr:eq(12)')->find('.td-content')->eq(1)->find('a')->text(),
           'member'                             =>                          $tbody->find('tr:eq(14)')->find('.td-content')->text(),
           'member_type'                        =>                          $tbody->find('tr:eq(15)')->find('.td-content')->eq(0)->text(),
           'membership_time'                    =>                          $tbody->find('tr:eq(15)')->find('.td-content')->eq(1)->text(),
           'legal_opinion_status'               =>                          $tbody->find('tr:eq(17)')->find('.td-content')->text(),
           'legal_person'                       =>                          $tbody->find('tr:eq(19)')->find('.td-content')->text(),
           'qualification'                      =>                          $tbody->find('tr:eq(20)')->find('.td-content')->eq(0)->text(),
           'qualification_way'                  =>                          $tbody->find('tr:eq(20)')->find('.td-content')->eq(1)->text(),
           'last_update_time'                   =>                          $tbody->find('tr:last')->prev()->find('.td-content')->text(),
           'special_message'                    =>                          $tbody->find('tr:last')->find('.td-content')->text(),
        ];
        $work_experience =  $ql->rules([
            'time'  =>      ['tr:contains(法定代表人/执行事务合伙人(委派代表)工作履历:)>td>table:first>tbody>tr>td:nth-child(3n-2)','text'],
            'company'  =>   ['tr:contains(法定代表人/执行事务合伙人(委派代表)工作履历:)>td>table:first>tbody>tr>td:nth-child(3n-1)','text'],
            'job'  =>       ['tr:contains(法定代表人/执行事务合伙人(委派代表)工作履历:)>td>table:first>tbody>tr>td:nth-child(3n)','text'],
        ])->query()->getData()->all();
        $senior_message =  $ql->rules([
            'name'  =>      ['tr:contains(高管情况:)>td>table:first>tbody>tr>td:nth-child(3n-2)','text'],
            'job'  =>   ['tr:contains(高管情况:)>td>table:first>tbody>tr>td:nth-child(3n-1)','text'],
            'quality'  =>       ['tr:contains(高管情况:)>td>table:first>tbody>tr>td:nth-child(3n)','text'],
        ])->query()->getData()->all();
        $before_productions = $ql->rules([
            'a'  =>  ['table[class=table table-center table-info]>tbody>tr:contains(暂行办法实施前成立的基金)>td>p>a','href'],
            'name'  =>  ['table[class=table table-center table-info]>tbody>tr:contains(暂行办法实施前成立的基金)>td>p:even','text'],
            'report'  =>  ['table[class=table table-center table-info]>tbody>tr:contains(暂行办法实施前成立的基金)>td>p:odd','text'],
        ])->query()->getData()->all();
        $after_productions = $ql->rules([
            'a'  =>  ['table[class=table table-center table-info]>tbody>tr:contains(暂行办法实施后成立的基金)>td>p>a','href'],
            'name'  =>  ['table[class=table table-center table-info]>tbody>tr:contains(暂行办法实施后成立的基金)>td>p:even','text'],
            'report'  =>  ['table[class=table table-center table-info]>tbody>tr:contains(暂行办法实施后成立的基金)>td>p:odd','text'],
        ])->query()->getData()->all();
        $simudata['work_experience'] = $work_experience;
        $simudata['senior_message'] = $senior_message;
        $simudata['before_production'] = $before_productions;
        $simudata['after_production'] = $after_productions;
        return $simudata;
    }
}