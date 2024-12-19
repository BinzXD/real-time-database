<?php

namespace App\Services;

use App\Helpers\RestCurl;

class WablasService {

    public static function sendOTP($phone, $message)
    {
        $url = config('wablas.API_DOMAIN'). '/send-message';
        $token = config('wablas.API_TOKEN');

        $response = RestCurl::post(
            $url,
            [
                'phone' => $phone,
                'message' => $message,
            ],
            [
                'Authorization: ' . $token,
            ]
        );

        return $response;
    }
}