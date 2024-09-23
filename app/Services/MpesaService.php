<?php


namespace App\Services;

use GuzzleHttp\Client;

class MpesaService
{
    public function generateAccessToken()
    {
        $client = new Client();
        $response = $client->request('GET', config('app.mpesa_base_url') . '/oauth/v1/generate?grant_type=client_credentials', [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode(config('app.mpesa_consumer_key') . ':' . config('app.mpesa_consumer_secret')),
            ],
            'verify' => false,
        ]);
        
        return json_decode($response->getBody())->access_token;
    }

    public function stkPush($amount, $phoneNumber, $reference, $description)
    {
        $client = new Client();
        $token = $this->generateAccessToken();
        $timestamp = now()->format('YmdHis');
        $password = base64_encode(config('app.mpesa_shortcode') . config('app.mpesa_passkey') . $timestamp);

        $response = $client->post(config('app.mpesa_base_url') . '/mpesa/stkpush/v1/processrequest', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
            'json' => [
                'BusinessShortCode' => config('app.mpesa_shortcode'),
                'Password' => $password,
                'Timestamp' => $timestamp,
                'TransactionType' => 'CustomerPayBillOnline',
                'Amount' => $amount,
                'PartyA' => $phoneNumber,
                'PartyB' => config('app.mpesa_shortcode'),
                'PhoneNumber' => $phoneNumber,
                'CallBackURL' => config('app.mpesa_callback_url'),
                'AccountReference' => $reference,
                'TransactionDesc' => $description,
            ],
            'verify' => false,
            
        ]);

        return json_decode($response->getBody());
    }
}
