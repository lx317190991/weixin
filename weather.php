<?php
include("commfunc.php");
function getWeatherInfo($cityname)
{
    $url = "http://api.map.baidu.com/telematics/v3/weather?location=".$cityname."&output=json&ak=ECe3698802b9bf4457f0e01b544eb6aa";
	$output = httpRequest($url);
    $result = json_decode($output, true);
    if ($result["error"] != 0){
        return $result["status"];
    }
    $curHour = (int)date('H',time());
    $weather = $result["results"][0];
    $weatherArray[] = array("Title" =>$weather['currentCity']."天气预报", "Description" =>"", "PicUrl" =>"", "Url" =>"");
    for ($i = 0; $i < count($weather["weather_data"]); $i++) {
        $weatherArray[] = array("Title"=>
            $weather["weather_data"][$i]["date"]."\n".
            $weather["weather_data"][$i]["weather"]." ".
            $weather["weather_data"][$i]["wind"]." ".
            $weather["weather_data"][$i]["temperature"],
        "Description"=>"", 
        "PicUrl"=>(($curHour >= 6) && ($curHour < 18))?$weather["weather_data"][$i]["dayPictureUrl"]:$weather["weather_data"][$i]["nightPictureUrl"], "Url"=>"");
    }
    return $weatherArray;
}

?>