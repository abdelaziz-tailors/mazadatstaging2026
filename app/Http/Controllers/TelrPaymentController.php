<?php
namespace App\Http\Controllers;

use App\Services\TelrPaymentService;
use Illuminate\Http\Request;

class TelrPaymentController extends Controller
{
    private $telrService;

    public function __construct(TelrPaymentService $telrService)
    {
        $this->telrService = $telrService;
    }

    public function showForm()
    {
//        return view('payment.form');

        $client = new \GuzzleHttp\Client();
        $orderId = uniqid(); // Unique order ID


        $response = $client->request('POST', 'https://secure.telr.com/gateway/order.json', [
            'body' => '{"method":"create","store":31616,"authkey":"TVPmS~svpk^dxzTr","framed":0,"order":{"cartid":"'.$orderId.'","test":"1","amount":"10.50","currency":"SAR","description":"My purchase"},"return":{"authorised":"https://vlog-me.com/authorised","declined":"https://vlog-me.com/declined","cancelled":"https://vlog-me.com/cancelled"}}',
            'headers' => [
                'Content-Type' => 'application/json',
                'accept' => 'application/json',
            ],
        ]);
        echo $response->getBody();

    }


    public function handleCallback(Request $request)
    {
        $status = $request->input('status');
        return view('payment.callback', compact('status'));
    }
}
