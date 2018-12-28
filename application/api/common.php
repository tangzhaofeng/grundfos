<?php

/**
 * 返回数据
 */
function returnjson($code,$msg,$data){
    return json_encode([
        'code' => $code,
        'msg' => $msg,
        'data' => $data
    ]);
}

/**
 * 返回错误
 */
function errorjson()
{
    $info['code'] = '0';
    $info['msg'] = '错误';
    return json($info);
}




   
	
