<?php
define('WEIXINTOKEN','Token');

require './lib/WeiXinMsg.class.php';
$wx = new WeiXinMsg();

$wx->on('all',function($obj,$that){
	$o = json_encode($obj);
    $that->responseText($obj,$o);
});

$wx->run();
