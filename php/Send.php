<?php
//Авторизация
require_once 'link.php';

class Send
{
    function getlist_acc($path)
    {
        $link = "https://efilippovtest.amocrm.ru/api/v2/$path";

        $headers[] = "Accept: application/json";

//Curl options
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, "amoCRM-API-client-
undefined/2.0");
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_URL, $link);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_COOKIEFILE, dirname(__FILE__) . "/cookie.txt");
        curl_setopt($curl, CURLOPT_COOKIEJAR, dirname(__FILE__) . "/cookie.txt");
        $out = curl_exec($curl);
        curl_close($curl);

        $response = json_decode($out, true);
        return $response;

    }
}

