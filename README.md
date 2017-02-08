# php-weixin-simple-message
微信开发,PHP接口接入,处理消息回复

### 使用
1.绑定微信公众号后端的开发URL
2.设置微信公众号后端的Token
3.修改index.php里定义的WEIXINTOKEN为微信后台设置的Token

``` php
define('WEIXINTOKEN','Token');

include './lib/WeiXinMsg.class.php';
$wx = new WeiXinMsg();

//绑定全局事件
$wx->on('all',function($obj,$that){
   //处理全局事件,比如数据库操作
});

//绑定关键字事件
$wx->on('text',function($obj,$that){
	$o = json_encode($obj);
    $that->responseText($obj,$o);
});


$wx->run();
```
### 事件列表
-all *所有事件
-text
-image
-voice
-video
-shortvideo
-location
-link
-event
[微信公众平台开发者文档](http://mp.weixin.qq.com/wiki/home/)

### 事件回调函数

``` php
function(event,callback($object,$object2){
     //$object 微信数据相关
     //$object2 当前对象
});
```
