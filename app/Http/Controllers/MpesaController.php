<?php

namespace App\Http\Controllers;
use Log;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Services\MpesaService;

class MpesaController extends Controller
{
    protected $mpesaService;

    public function __construct(MpesaService $mpesaService)
    {
        $this->mpesaService = $mpesaService;
    }

    public function stkPush(Request $request)
    {
        $amount = $request->amount;
        $phoneNumber = $request->phone;
        $accountReference = 'Test123';
        $transactionDesc = 'Payment for order #123';

        $response = $this->mpesaService->stkPush($amount, $phoneNumber, $accountReference, $transactionDesc);

        return response()->json($response);
    }

    public function handleCallback(Request $request)
    {
        // Process the callback data and update the transaction status accordingly
        // Retrieve the callback data
        $mpesaResponse = $request->all();
        print_r($request->all());
        exit;
        // You can log the response to inspect the callback
        \Log::info('MPesa Callback:', $mpesaResponse);

        // Check if the callback contains a successful transaction
        if (isset($mpesaResponse['Body']['stkCallback']['ResultCode']) && $mpesaResponse['Body']['stkCallback']['ResultCode'] == 0) {
            // Successful transaction
            $transactionData = $mpesaResponse['Body']['stkCallback']['CallbackMetadata']['Item'];

            // Extract necessary information
            $amount = $this->getMpesaItemValue($transactionData, 'Amount');
            $mpesaReceiptNumber = $this->getMpesaItemValue($transactionData, 'MpesaReceiptNumber');
            $phoneNumber = $this->getMpesaItemValue($transactionData, 'PhoneNumber');
            
            $user = Auth::User();
            $userid = $user->id;

            $data = Cart::where('user_id', '=', $userid)->get();
            // Save the order to the database

            foreach ($data as $data) 
            {
                $order = new Order();
            
                $order->name = $data->name;
                $order->email = $data->email;
                $order->phone = $data->$phoneNumber;
                $order->address = $data->address;
                $order->user_id = $data->user_id;

                $order->product_title = $data->product_title;
                $order->price = $data->price;
                $order->image = $data->image;
                $order->quantity = $data->quantity;
                $order->product_id = $data->product_id;

                $order->payment_status= "Paid with Mpesa";
                $order->delivery_status = "Processing";

                $order->save();

                $cartid = $data->id;

                $cart = Cart::find($cartid);

                $cart->delete();

            }
            
            // Return a success response to Safaricom
            return response()->json(['message' => 'Order saved successfully'], 200);
        } else {
            // Failed transaction (log this for troubleshooting)
            \Log::error('MPesa Transaction failed', $mpesaResponse);
            return response()->json(['error' => 'Transaction failed'], 400);
        }
    }

    private function getMpesaItemValue($items, $key)
    {
        foreach ($items as $item) {
            if ($item['Name'] == $key) {
                return $item['Value'];
            }
        }
        return null;
    }
    

}

