<?php

namespace App\Helpers;

class RestCurl
{
    public static function exec($method, $url, $obj = array(), $headersReq = array())
    {
        $headerSend = array('Accept:application/json', 'Content-Type:application/json');
        foreach ($headersReq as $key => $valHeaderReq) {
            $headerSend[] = (string)$key . ':' . $valHeaderReq;
        }
        $curl = curl_init();

        switch ($method) {
            case 'GET':
                if (strrpos($url, "?") === FALSE) {
                    $url .= '?' . http_build_query($obj);
                }
                break;

            case 'POST':
                curl_setopt($curl, CURLOPT_POST, TRUE);
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($obj));
                break;

            case 'PUT':
            case 'DELETE':
            default:
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, strtoupper($method)); // method
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($obj)); // body
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headerSend);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, TRUE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, TRUE);
        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);
        $body = substr($response, $info['header_size']);
        $check = json_decode($body);
        if ($check == null) {
            return json_decode(json_encode(array(
                'status'  => 500,
                'error'   => true
            )), FALSE);
        }
        return $check;
    }

    public static function get($url, $obj = array(), $header = array())
    {
        return self::exec("GET", $url, $obj, $header);
    }

    public static function post($url, $obj = [], $header = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $obj);

        if (!empty($header)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }

        $response = curl_exec($ch);
        $error = curl_error($ch);

        curl_close($ch);

        if ($response === false) {
            throw new \Exception("cURL Error: " . $error);
        }

        return json_decode($response, true);
    }

    public static function put($url, $obj = array(), $header = array())
    {
        return self::exec("PUT", $url, $obj, $header);
    }

    public static function delete($url, $obj = array(), $header = array())
    {
        return self::exec("DELETE", $url, $obj, $header);
    }
}