<?php
/**
  * wechat php test
  */

//define your token
define("TOKEN", "weixin");
$wechatObj = new wechatCallbackapiTest();
//$wechatObj->valid();
$wechatObj->responseMsg();

class wechatCallbackapiTest
{
	public function valid()
    {
        $echoStr = $_GET["echostr"];

        //valid signature , option
        if($this->checkSignature()){
        	echo $echoStr;
        	exit;
        }
    }
    
    private function parseRequestStr($keyword, &$type, &$content)
    {
        $length = mb_strlen($keyword, 'utf-8');
        $type = mb_substr($keyword, 0, 2, 'utf-8');
        $content = mb_substr($keyword, 3, $length-3, 'utf-8');
    }

    public function responseMsg()
    {
		//get post data, May be due to the different environments
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

      	//extract post data
		if (!empty($postStr)){
                /* libxml_disable_entity_loader is to prevent XML eXternal Entity Injection,
                   the best way is to check the validity of xml by yourself */
                libxml_disable_entity_loader(true);
              	$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
                $type = $postObj->MsgType;
                $keyword = trim($postObj->Content);

				if(!empty( $keyword ))
                {
                    $this->parseRequestStr($keyword, $firstLabel, $secondLabel);
					if ($firstLabel == "音乐")
					{
                        if ($secondLabel == "所有")
                        {
                            $content = $this->showAllMusic();
                            $resultStr = $this->responseText($postObj, $content);
                            echo $resultStr;
                        }
                        else
                        {
                            $url = $this->linkDBresult($secondLabel);
                            if (!$url)
                            {
                                $content = "no such sing";
                                $resultStr = $this->responseText($postObj, $content);
                                echo $resultStr;
                            }
                            else
                            {
                                $resultStr = $this->responseMusic($postObj,  $url);
                                echo $resultStr;
                            }
                        }

					}
                    else if ($firstLabel == "天气")
					{
                        if (!$secondLabel)
                        {
                            $content = "Input right city...";
                            $resultStr = $this->responseText($postObj, $content);
                            echo $resultStr;
                        }
                        else
                        {
                            include("weather.php");
                            $content = getWeatherInfo($secondLabel);
                            $resultStr = $this->transmitNews($postObj, $content);
                            echo $resultStr;
                        }
					}
                    else if ($firstLabel == "农历")
                    {
                        include("lunar.php");
                        $lunar = new Lunar();
                        $content = $lunar->S2L($secondLabel);
                        if (!$content)
                        {
                            $content = "Input right date...";
                            $resultStr = $this->responseText($postObj, $content);
                            echo $resultStr;
                        }
                        else
                        {
                            $resultStr = $this->responseText($postObj, $content);
                        	echo $resultStr;
                        }

                    }
                    else if ($firstLabel == "公历")
                    {
                        include("lunar.php");
                        $lunar = new Lunar();
                        $content = $lunar->L2S($secondLabel);
                        if (!$content)
                        {
                            $content = "Input right date...";
                            $resultStr = $this->responseText($postObj, $content);
                            echo $resultStr;
                        }
                        else
                        {
                            $resultStr = $this->responseText($postObj, $content);
                        	echo $resultStr;
                        }

                    }
                    else if ($firstLabel == "笑话")
                    {
                        include("joker.php");
                        $j = new joker();
                        $contentStr = $j->postJoker();
                        if (is_array($contentStr))
                        {
                            $resultStr = $this->transmitNews($postObj, $contentStr);
                            echo $resultStr;
                        }
                        else
                        {
                            $resultStr = $this->responseText($postObj, $contentStr);
                            echo $resultStr;
                        }
                    }
                    else if ($firstLabel == "解梦")
                    {
                        //$contentStr = "&lt;a href=&quot;http://zhougongjiemeng.1518.com&quot;&gt;周公解梦&lt;/a&gt;";
                        $contentStr = "<a href='http:\/\/zhougongjiemeng.1518.com'>周公解梦</a>";
                        $resultStr = $this->responseText($postObj, $contentStr);
                        echo $resultStr;
                    }
					else
					{
                        $content = "Love You~";
                        //include("yellowchicken.php");
                        //$f = new simsimi();
                        //$content = $f->simsimihttp($keyword);
                        $resultStr = $this->responseText($postObj, $content);
                        echo $resultStr;
					}

                }
				else
                {
                	echo "Input something...";
                }
            			
        }
        else 
        {
        	echo "";
        	exit;
        }
    }
    
    private function showAllMusic()
	{
		$link = @mysql_connect(SAE_MYSQL_HOST_M.':'.SAE_MYSQL_PORT,SAE_MYSQL_USER,SAE_MYSQL_PASS);
        mysql_query("SET NAMES UTF8");
		mysql_query("set character_set_client=utf8"); 
		mysql_query("set character_set_results=utf8");
		if (!$link)
		{
			die("connect server failed". mysql_error());
		}
		if (!mysql_select_db(SAE_MYSQL_DB, $link))
		{
			die("select server failed". mysql_error($link));
		}
		$sql = "SELECT * FROM `music` LIMIT 0, 30 ";
		$query = mysql_query($sql);
        $nums = mysql_num_rows($query);
		if ($nums == "0")
		{
			return NULL;
		}
        
		while ($rs = mysql_fetch_array($query))
		{
			$contentStr = $contentStr . $rs['id'] . " " . $rs['name'] . " " . $rs['url'] . "\n";
		}
		
		mysql_close($link);
		return $contentStr;
	}
    
    private function linkDBresult($name)
	{
		$link = @mysql_connect(SAE_MYSQL_HOST_M.':'.SAE_MYSQL_PORT,SAE_MYSQL_USER,SAE_MYSQL_PASS);
        mysql_query("SET NAMES UTF8");
		mysql_query("set character_set_client=utf8"); 
		mysql_query("set character_set_results=utf8");
		if (!$link)
		{
			die("connect server failed". mysql_error());
		}
		if (!mysql_select_db(SAE_MYSQL_DB, $link))
		{
			die("select server failed". mysql_error($link));
		}
		$sqlmedo = "SELECT * FROM `music` WHERE name = '%s' LIMIT 0, 30 ";
        $sql = sprintf($sqlmedo, $name);
		$query = mysql_query($sql);
        $nums = mysql_num_rows($query);
		if ($nums == "0")
		{
			return NULL;
		}
        $rs = mysql_fetch_array($query);
		$contentStr = $rs['url'];
		return $contentStr;
	}
	
	public function responseText($object, $content, $flag=0)
    {
        $textTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[text]]></MsgType>
                    <Content><![CDATA[%s]]></Content>
                    <FuncFlag>%d</FuncFlag>
                    </xml>";
        $resultStr = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $content, $flag);
        return $resultStr;
    }
	
	public function responseMusic($object, $url)
    {
        $textTpl = "<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[music]]></MsgType>
					<Music>
					<Title><![CDATA[李白]]></Title>
					<Description><![CDATA[ALIN]]></Description>
					<MusicUrl><![CDATA[%s]]></MusicUrl>
					<HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
					</Music>
					</xml>";

        $resultStr = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $url, $url);
        return $resultStr;
    }
    
    private function transmitNews($object, $arr_item)
    {
        if(!is_array($arr_item))
            return;

        $itemTpl = "<item>
        <Title><![CDATA[%s]]></Title>
        <Description><![CDATA[%s]]></Description>
        <PicUrl><![CDATA[%s]]></PicUrl>
        <Url><![CDATA[%s]]></Url>
		</item>";
		
        $item_str = "";
        foreach ($arr_item as $item)
            $item_str .= sprintf($itemTpl, $item['Title'], $item['Description'], $item['PicUrl'], $item['Url']);

        $newsTpl = "<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[news]]></MsgType>
					<Content><![CDATA[]]></Content>
					<ArticleCount>%s</ArticleCount>
					<Articles>
					$item_str</Articles>
					</xml>";

        $result = sprintf($newsTpl, $object->FromUserName, $object->ToUserName, time(), count($arr_item));
        return $result;
    }
		
	private function checkSignature()
	{
        // you must define TOKEN by yourself
        if (!defined("TOKEN")) {
            throw new Exception('TOKEN is not defined!');
        }
        
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        		
		$token = TOKEN;
		$tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		
		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}
}

?>
