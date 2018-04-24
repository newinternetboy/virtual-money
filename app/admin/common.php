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

function trimMCode($M_Code){
    return trim($M_Code);
}


function formatName($name){
    return substr($name,'**',1,2);
}




