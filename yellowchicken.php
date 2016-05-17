<?php

include("commfunc.php");
class simsimi
{
    public function simsimihttp($keyword)
    {
        $url = "http://www.simsimi.com/func/req?msg=".$keyword."&lc=ch";
        $output = httpRequest($url);
        $result = json_decode($output, true);
        return $result['response'];
    }
}

?>