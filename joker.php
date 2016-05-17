<?php
include("commfunc.php");
class joker
{
    public function postJoker($object)
    {
        $keyword = trim($object->Content);
        $url = "http://apix.sinaapp.com/joke/?appkey=trialuser";
        $output = file_get_contents($url);
        $contentStr = json_decode($output, true);
        
        return $contentStr;
    }
}
?>