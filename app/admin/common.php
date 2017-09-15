<?php

use think\Model;

function info($msg = '', $code = '', $url = '',  $data = '', $wait = 3 )
{
	if (is_numeric($msg)) {
        $code = $msg;
        $msg  = '';
    }
    if (is_null($url) && isset($_SERVER["HTTP_REFERER"])) {
        $url = $_SERVER["HTTP_REFERER"];
    } elseif ('' !== $url) {
        $url = preg_match('/^(https?:|\/)/', $url) ? $url : Url::build($url);
    }
	$result = [
        'code' => $code,
        'msg'  => $msg,
        'data' => $data,
        'url'  => $url,
        'wait' => $wait,
	];
	return $result;
}

function sortAuthRoles($authRules){
    $ret = [];
    foreach( $authRules as $authRule ){
        $authRule = ($authRule instanceof Model) ? $authRule->toArray() : $authRule;
        if( $authRule['pid'] == 0 ){
            sortChildren($authRule,$authRules);
            $ret[] = $authRule;
        }
    }
    return $ret;

//    foreach( $authRules as $authRule ){
//        $authRule = ($authRule instanceof Model) ? $authRule->toArray() : $authRule;
//        if( $authRule['pid'] == 0 ){
//            $ret[$authRule['id']] = array_merge( isset($ret[$authRule['id']]) ? $ret[$authRule['id']] : [], $authRule);
//        }else{
//            $ret[$authRule['pid']]['children'][] = $authRule;
//        }
//    }
//    return $ret;
}

function sortChildren(& $authRule,$authRules){
    foreach( $authRules as $item ) {
        $item = ($item instanceof Model ) ? $item->toArray() : $item;
        if( $item['pid'] == $authRule['id'] ){
            $authRule['children'][] = $item;
        }
    }
    if( isset($authRule['children']) ){
        foreach( $authRule['children'] as & $authChild ){
            sortChildren($authChild,$authRules);
        }
    }
}
?>