<?php
class WeiXinMsg{
	
	private $msgType = array('all','text','image','voice','video','shortvideo','location','link','event');
	private $event = array('subscribe','unsubscribe','scan','location','click','view','scancode_push','scancode_waitmsg','pic_sysphoto','pic_photo_or_album','pic_weixin','location_select');
	
	
	//微信接入
	private function bind(){
		$echoStr = $_GET["echostr"];
		if($echoStr){
			if($this->checkSignature()){
        	   echo $echoStr;
        	   exit;
            }
		}
	}
	
	private function checkSignature(){
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
		$tmpArr = array(WeiXinTOKEN, $timestamp, $nonce);
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode($tmpArr);
		$tmpStr = sha1( $tmpStr );
		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}
	
    //绑定事件
	public function on($msgtype,$fn,$event=''){
		$msgtype = strtolower($msgtype);
		if(!in_array($msgtype,$this->msgType)) return;
		if($event){
			$event = strtolower($event);
			if(!in_array($event,$this->event)) return;
			$msgtype .=$event;
		}
		if(!isset($this->$msgtype)) $this->$msgtype = array();
		array_push($this->$msgtype,$fn);
	}

    private function parseXML(){
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
		if (!empty($postStr)){
                libxml_disable_entity_loader(true);
              	$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
				$msgtype = strtolower($postObj->MsgType);
				if(!in_array($msgtype,$this->msgType)){
					echo '';
					exit;
				}
				if(isset($postObj->Event)){
                    $event = strtolower($postObj->Event);
			        if(!in_array($event,$this->event)){
						echo '';
					    exit;
					}
			        $msgtype .=$event;
				}
				
				//所有消息接口
				if(isset($this->all)){
					foreach($this->all as $v) $v($postObj,$this);
				}
				
				//指定消息接口
				if(isset($this->$msgtype)){
					foreach($this->$msgtype as $v) $v($postObj,$this);
				}else{
					echo "";
					exit;
				}
        }else {
        	echo "";
        	exit;
        }
	}
	
	
	public function responseText($obj,$txt){
		$tpl="<xml>
		<ToUserName><![CDATA[".$obj->FromUserName."]]></ToUserName>
		<FromUserName><![CDATA[".$obj->ToUserName."]]></FromUserName>
		<CreateTime>".time()."</CreateTime>
		<MsgType><![CDATA[text]]></MsgType>
		<Content><![CDATA[".$txt."]]></Content>
		</xml>";
		echo $tpl;
	}
	
    public function responseImage($obj,$media_id){
		$tpl="<xml>
		<ToUserName><![CDATA[".$obj->FromUserName."]]></ToUserName>
		<FromUserName><![CDATA[".$obj->ToUserName."]]></FromUserName>
		<CreateTime>".time()."</CreateTime>
		<MsgType><![CDATA[image]]></MsgType>
		<Image>
		<MediaId><![CDATA[".$media_id."]]></MediaId>
		</Image>
		</xml>";
		echo $tpl;
	}
	
	public function responseVoice($obj,$media_id){
		$tpl="<xml>
		<ToUserName><![CDATA[".$obj->FromUserName."]]></ToUserName>
		<FromUserName><![CDATA[".$obj->ToUserName."]]></FromUserName>
		<CreateTime>".time()."</CreateTime>
		<MsgType><![CDATA[voice]]></MsgType>
		<Voice>
		<MediaId><![CDATA[".$media_id."]]></MediaId>
		</Voice>
		</xml>";
		echo $tpl;
	}
	
	public function responseVideo($obj,$data){
		$tpl="<xml>
		<ToUserName><![CDATA[".$obj->FromUserName."]]></ToUserName>
		<FromUserName><![CDATA[".$obj->ToUserName."]]></FromUserName>
		<CreateTime>".time()."</CreateTime>
		<MsgType><![CDATA[video]]></MsgType>
		<Video>
		<MediaId><![CDATA[".$data['media_id']."]]></MediaId>
		<Title><![CDATA[".$data['title']."]]></Title>
		<Description><![CDATA[".$data['description']."]]></Description>
		</Video> 
		</xml>";
		echo $tpl;
	}
	
	public function responseMusic($obj,$data){
		$tpl="<xml>
		<ToUserName><![CDATA[".$obj->FromUserName."]]></ToUserName>
		<FromUserName><![CDATA[".$obj->ToUserName."]]></FromUserName>
		<CreateTime>".time()."</CreateTime>
		<MsgType><![CDATA[music]]></MsgType>
		<Music>
		<Title><![CDATA[".$data['TITLE']."]]></Title>
		<Description><![CDATA[".$data['DESCRIPTION']."]]></Description>
		<MusicUrl><![CDATA[".$data['MUSIC_Url']."]]></MusicUrl>
		<HQMusicUrl><![CDATA[".$data['HQ_MUSIC_Url']."]]></HQMusicUrl>
		<ThumbMediaId><![CDATA[".$data['media_id']."]]></ThumbMediaId>
		</Music>
		</xml>";
		echo $tpl;
	}
	
	public function responseNews($obj,$data){
		$cnt = count($data);
		$str = '';
		foreach($data as $v){
				$str.="<item>
				<Title><![CDATA[".$v['title']."]]></Title> 
				<Description><![CDATA[".$v['desc']."]]></Description>
				<PicUrl><![CDATA[".$v['pic']."]]></PicUrl>
				<Url><![CDATA[".$v['url']."]]></Url>
				</item>";
		}
		$tpl="<xml>
		<ToUserName><![CDATA[".$obj->FromUserName."]]></ToUserName>
		<FromUserName><![CDATA[".$obj->ToUserName."]]></FromUserName>
		<CreateTime>".time()."</CreateTime>
		<MsgType><![CDATA[news]]></MsgType>
		<ArticleCount>".$cnt."</ArticleCount>
		<Articles>".$str."</Articles>
		</xml>";
		echo $tpl;
	}

	public function run(){
		$this->bind();
		$this->parseXML();
	}
	
}